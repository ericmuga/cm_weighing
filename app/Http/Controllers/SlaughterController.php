<?php

namespace App\Http\Controllers;

use App\Events\ReceiptsUploadCompleted;
use App\Exports\SlaughterSummaryExport;
use App\Models\Customer;
use App\Models\Helpers;
use App\Models\Item;
use App\Models\Offal;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SlaughterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $lined_up = Cache::remember('lined_up', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('slaughter_date', today())
                ->sum('receipts.received_qty');
        });

        $slaughtered = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->count();

        $total_weight = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->sum('slaughter_data.total_net');

        return view('slaughter.dashboard', compact('title', 'helpers', 'lined_up', 'slaughtered', 'total_weight'));
    }

    public function weigh(Helpers $helpers)
    {
        $title = "weigh";

        $configs = Cache::remember('weigh_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('scale', 'Scale 1')
                ->where('section', 'slaughter')
                ->select('tareweight', 'comport')
                ->get()->toArray();
        });

        $receipts = Cache::remember('weigh_receipts', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('slaughter_date', today())
                ->select('receipt_no', 'vendor_name')
                ->get();
        });

        $slaughter_data = DB::table('slaughter_data')
            ->whereDate('slaughter_data.created_at', today())
            ->where('slaughter_data.deleted', '!=', 1)
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.*', 'carcass_types.description')
            ->orderBy('slaughter_data.created_at', 'ASC')
            ->get();

        return view('slaughter.weigh', compact('title', 'configs', 'receipts', 'helpers', 'slaughter_data'));
    }

    public function weighOffals(Helpers $helpers, $customer = null) {
        $title = "Offals";

        $offals_products = Item::where('category', 'cm-offals')->get();

        $customers = Customer::where('active', 1)->get();

        $configs = DB::table('scale_configs')->where('section', 'offals')->get();

        $entries = Offal::whereDate('offals.created_at', Carbon::today())
        ->leftJoin('users', 'offals.user_id', '=', 'users.id')
        ->leftJoin('customers', 'offals.customer_id', '=', 'customers.id')
        ->join('items', 'offals.product_code', '=', 'items.code')
        ->where('offals.archived', 0)
        ->where('offals.created_at', '>=', now()->subDays(2))
        ->select('offals.*', 'users.username', 'customers.name AS customer_name', 'items.description AS product_name')
        ->orderBy('offals.created_at', 'DESC')
        ->when($customer, function ($query, $customer) {
            return $query->where('offals.customer_id', $customer);
        })
        ->get();

        return view('slaughter.weigh_offals', compact('title', 'customers', 'configs', 'offals_products', 'entries', 'helpers'));
    }

    public function saveOffalsWeights(Request $request, Helpers $helpers) {
        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }
        try {
            Offal::create([
                'customer_id' => $request->customer_id,
                'product_code'=> $request->product_code,
                'scale_reading'=> $request->reading,
                'net_weight'=> $request->net_weight,
                'is_manual'=> $manual_weight,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('Offal weight saved successfully', 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }

    public function updateOffals(Request $request) {
        try {
            if (Offal::where('id', $request->id)->where('published', 1)->exists()) {
                Toastr::error('Cannot update published offal weight', 'Error!');
                return redirect()->back();
            }
            Offal::where('id', $request->id)->update([
                'customer_id' => $request->customer_id,
                'updated_by' => Auth::id(),
            ]);

            Toastr::success('Offal weight updated successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }

    public function archiveOffals(Request $request) {
        try {
            if (Offal::where('id', $request->id)->where('published', 1)->exists()) {
                Toastr::error('Cannot delete published offal weight', 'Error!');
                return redirect()->back();
            }
            Offal::where('id', $request->id)->update(['archived' => 1]);
            Toastr::success('Offal weight deleted successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }

    public function publishOffals(Request $request, Helpers $helpers) {
        try {
            $weights = [];
            foreach ($request->entries as $entry) {
                $decodedEntry = json_decode($entry);
                if (Offal::where('id', $decodedEntry->id)->where('published', 0)->where('archived', 0)->exists()) {
                    $weight = [
                        'id' => $decodedEntry->id,
                        'customer_id' => $decodedEntry->customer_id,
                        'customer_name' => $decodedEntry->customer_name,
                        'product_code' => $decodedEntry->product_code,
                        'product_name' => $decodedEntry->product_name,
                        'scale_reading' => $decodedEntry->scale_reading,
                        'net_weight' => $decodedEntry->net_weight,
                        'is_manual' => $decodedEntry->is_manual,
                        'user_id' => $decodedEntry->user_id,
                    ];
                    $weights[] = $weight;
                }
            }
            Log::info('Publishing offals weights:', $weights);
            $data = [
                'customer_name' => json_decode($request->entries[0])->customer_name,
                'customer_id' => json_decode($request->entries[0])->customer_id,
                'weights' => $weights,
            ];
            $helpers->publishToQueue($data, 'offals.bc');
            Offal::whereIn('id', array_column($weights, 'id'))->update(['published' => 1]);
            return response()->json(['success' => true, 'message' => 'Offals published successfully']);
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to publish offals. Error: ' . $e->getMessage()]);
        }
    }

    public function loadWeighDataAjax(Request $request)
    {
        $total_weighed_per_vendor = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->where('receipt_no', $request->receipt)
            ->count();

        $agg_count = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->where('receipt_no', $request->receipt)
            ->count();

        $weighed_net = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->where('receipt_no', $request->receipt)
            ->sum('total_net');

        $vendor_data = DB::table('receipts')
            ->whereDate('slaughter_date', today())
            ->where('receipt_no', $request->receipt)
            ->select('vendor_no', 'vendor_name', 'item_code', 'description', DB::raw('SUM(received_qty) as total_received'))
            ->groupBy('vendor_no', 'vendor_name', 'item_code', 'description')
            ->get()->toArray();

        $dataArray = array('agg_count' => $agg_count, 'total_weighed' => $total_weighed_per_vendor, 'weighed_net' => $weighed_net, 'vendor' => $vendor_data);

        return response()->json($dataArray);
    }

    public function nextReceiptAjax(Request $request)
    {
        $receipts_arr = DB::table('receipts')
            ->whereDate('created_at', '>=', today())
            ->select('receipt_no')
            ->get()->toArray();

        $request_receipt_index = array_search($request->receipt_no, array_column($receipts_arr, 'receipt_no'));

        $selected_receipt = $receipts_arr[$request_receipt_index + 1]->receipt_no;

        return response()->json($selected_receipt);
    }

    public function saveWeighData(Request $request, Helpers $helpers)
    {
        try {
            if (!($request->item_code == 'BG1101') && !($request->item_code == 'BG1201')) {
                # insert for beef
                DB::table('slaughter_data')->insert([
                    'agg_no' => $request->agg_no,
                    'receipt_no' => $request->receipt_no,
                    'item_code' => $helpers->transformToCarcassCode($request->item_code),
                    'vendor_no' => $request->vendor_no,
                    'vendor_name' => $request->vendor_name,
                    'sideA_weight' => $request->side_A,
                    'sideB_weight' => $request->side_B,
                    'total_weight' => $request->total_weight,
                    'tare_weight' => $request->tare_weight,
                    'total_net' => $request->total_net, //updated
                    'settlement_weight' => $request->settlement_weight,
                    'classification_code' => $request->classification_code,
                    'user_id' => Auth::id(),
                ]);
            } else {
                # insert for lamb/goat
                DB::table('slaughter_data')->insert([
                    'agg_no' => $request->agg_no,
                    'receipt_no' => $request->receipt_no,
                    'item_code' => $helpers->transformToCarcassCode($request->item_code),
                    'vendor_no' => $request->vendor_no,
                    'vendor_name' => $request->vendor_name,
                    'total_weight' => $request->total_weight2,
                    'tare_weight' => $request->tare_weight,
                    'total_net' => $request->total_net,
                    'settlement_weight' => $request->settlement_weight,
                    'classification_code' => $request->classification_code,
                    'user_id' => Auth::id(),
                ]);
            }

            Toastr::success('record added successfully', 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function edit(Request $request, Helpers $helpers)
    {
        try {
            // Retrieve the current data
            $itemId = $request->item_id;
            $currentData = DB::table('slaughter_data')->where('id', $itemId)->first();

            // update
            DB::transaction(function () use ($request, $helpers, $itemId, $currentData) {
                DB::table('slaughter_data')
                    ->where('id', $itemId)
                    ->update([
                        'tare_weight' => $request->edit_tareweight,
                        'total_weight' => $request->edit_total,
                        'total_net' => $request->edit_net,
                        'sideA_weight' => $request->edit_A,
                        'sideB_weight' => $request->edit_B,
                        'settlement_weight' => $request->edit_settlement,
                        'classification_code' => $request->edit_classification_code,
                        'updated_at' => now(),
                    ]);

                // previous data
                $desc = json_encode($currentData);

                $helpers->insertChangeDataLogs('slaughter_data', $request->item_id, '3', $desc);
            });

            Toastr::success("record {$request->edit_item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withErrors($e)
                ->withInput();
        }
    }

    public function receipts(Helpers $helpers, $filter = null)
    {
        $title = "receipts";

        if (!$filter) {
            $receipts = Cache::remember('imported_receipts', now()->addMinutes(360), function () {
                return DB::table('receipts')
                    ->orderBy('created_at', 'DESC')
                    ->take(100)
                    ->get();
            });
        } elseif ($filter == 'today') {
            $receipts =  DB::table('receipts')
                ->orderBy('created_at', 'DESC')
                ->whereDate('receipts.slaughter_date', today())
                ->get();
        }

        return view('slaughter.receipts', compact('title', 'receipts', 'helpers', 'filter'));
    }

    public function importReceipts(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
            'slaughter_date' => 'required',

        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        // upload
        $database_date = Carbon::parse($request->slaughter_date);

        // forgetCache data
        $helpers->forgetCache('lined_up');
        $helpers->forgetCache('weigh_receipts');
        $helpers->forgetCache('imported_receipts');

        try {
            //code...
            DB::transaction(function () use ($request, $helpers, $database_date) {

                //delete existing records of same slaughter date
                DB::table('receipts')->where('slaughter_date', $database_date)->delete();

                $fileD = fopen($request->file, "r");
                // $column = fgetcsv($fileD); // skips first row as header

                while (!feof($fileD)) {
                    $rowData[] = fgetcsv($fileD);
                }

                foreach ($rowData as $key => $row) {

                    DB::table('receipts')->insert(
                        [
                            'receipt_no' => $row[0],
                            'vendor_no' => $row[2],
                            'vendor_name' => $row[3],
                            'receipt_date' => $row[4],
                            'item_code' => $row[5],
                            'description' => $row[6],
                            'received_qty' => $row[7],
                            'user_id' => Auth::id(),
                            'slaughter_date' => $database_date,
                        ]
                    );
                }
            });

            // info($database_date);

            event(new ReceiptsUploadCompleted($database_date));

            Toastr::success('receipts uploaded successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error Occurred. Wrong Data format!. Records not saved!');
            return back()
                ->withInput();
        }
    }

    public function insertForQAGrading($database_date)
    {
        $getReceipts = DB::table('receipts')
            ->where('slaughter_date', $database_date)
            ->get();

        // Check if there are receipts for the given slaughter date
        if ($getReceipts->count() > 0) {
            foreach ($getReceipts as $receipt) {
                $receivedQty = intval($receipt->received_qty);

                for ($i = 1; $i <= $receivedQty; $i++) {
                    // Check if the record already exists
                    $exists = DB::table('qa_grading')
                        ->where('receipt_no', $receipt->receipt_no)
                        ->where('agg_no', $i)
                        ->where('slaughter_date', $database_date)
                        ->exists();

                    // If the record does not exist, then insert it
                    if (!$exists) {
                        DB::table('qa_grading')->insert([
                            'receipt_no' => $receipt->receipt_no,
                            'agg_no' => $i,
                            'slaughter_date' => $database_date
                        ]);
                    }
                }
            }
        }
    }

    public function slaughterReport(Helpers $helpers, $filter = null)
    {
        $title = "Slaughter Data";

        if (!$filter) {
            $slaughter_data = DB::table('slaughter_data')
                ->where('slaughter_data.deleted', '!=', 1)
                ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
                ->select('slaughter_data.*', 'carcass_types.description AS item_name')
                ->orderBy('slaughter_data.created_at', 'DESC')
                ->take(5000)
                ->get();
        } elseif ($filter == 'today') {
            $slaughter_data = DB::table('slaughter_data')
                ->where('slaughter_data.deleted', '!=', 1)
                ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
                ->select('slaughter_data.*', 'carcass_types.description AS item_name')
                ->orderBy('slaughter_data.id', 'ASC')
                ->whereDate('slaughter_data.created_at', today())
                ->get();
        }

        return view('slaughter.report', compact('title', 'helpers', 'slaughter_data', 'filter'));
    }

    public function slaughterSummaryReport(Request $request, Helpers $helpers)
    {

        $from = date($request->from_date);
        $to = date($request->to_date);

        $data = DB::table('slaughter_data')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select('receipt_no', 'vendor_no', 'vendor_name', 'item_code', DB::raw('COUNT(slaughter_data.id) as qty'), DB::raw('SUM(slaughter_data.total_net) as total_net'), DB::raw('ROUND(SUM(slaughter_data.total_net * 0.975), 2) as total_settlement'))
            ->groupBy('receipt_no', 'vendor_no', 'vendor_name', 'item_code')
            ->get();

        $exports = Session::put('session_export_data', $data);

        return Excel::download(new SlaughterSummaryExport, 'SlaughterReportSummaryFrom-' . $request->from_date . '-To-' . $request->to_date . '.xlsx');
    }

    public function scaleConfigs(Helpers $helpers, $section = null)
    {
        $title = "scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', $section)
            ->get();

        return view('slaughter.scale_configs', compact('title', 'section', 'scale_settings', 'helpers'));
    }

    public function updateScaleConfigs(Request $request, Helpers $helpers)
    {
        try {
            // forgetCache weigh_configs
            $helpers->forgetCache('weigh_configs');

            // update
            DB::table('scale_configs')
                ->where('id', $request->item_id)
                ->update([
                    'comport' => $request->edit_comport,
                    'baudrate' => $request->edit_baud,
                    'tareweight' => $request->edit_tareweight,
                    'updated_at' => now(),
                ]);

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function readScaleApiService(Request $request, Helpers $helpers)
    {
        $result = $helpers->get_scale_read($request->comport);
        return response()->json($result);
    }

    public function comportListApiService(Helpers $helpers)
    {
        $result = $helpers->get_comport_list();

        return response()->json($result);
    }

    public function pendingEtimsData(Helpers $helpers)
    {
        $title = "Purchases for Etims Update";

       $results = DB::connection('main')->table('CM$Purch_ Inv_ Header as a')
            ->select(
                'v.Phone No_ as phonenumber',
                'a.Uncommitted as is_sms_sent',
                DB::raw('a.[Buy-from Vendor No_] as vendor_no'),
                DB::raw('a.[Buy-from Vendor Name] as vendor_name'),
                DB::raw('a.[Your Reference] as settlement_no'),
                DB::raw('SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END) AS totalWeight'),
                DB::raw('COALESCE(SUM(b.Amount), 0) - 
                    (SELECT ISNULL(SUM(Amount), 0) 
                        FROM [CM$Purch_ Cr_ Memo Line] as c 
                        INNER JOIN [CM$Purch_ Cr_ Memo Hdr_] as d 
                            ON d.No_ = c.[Document No_] 
                            AND RIGHT(d.[Vendor Cr_ Memo No_], 2) <> \'-R\'
                            AND d.[Your Reference] = a.[Your Reference]) AS netAmount'),
                    DB::raw('(CASE WHEN SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END) = 0 THEN 0 ELSE
                        (COALESCE(SUM(b.Amount), 0) - 
                            (SELECT ISNULL(SUM(Amount), 0) 
                                FROM [CM$Purch_ Cr_ Memo Line] as c 
                                INNER JOIN [CM$Purch_ Cr_ Memo Hdr_] as d 
                                    ON d.No_ = c.[Document No_] 
                                    AND d.[Your Reference] = a.[Your Reference])) / 
                        (SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END)) END) AS unitPrice')

        )
        ->join('CM$Purch_ Inv_ Line as b', 'a.No_', '=', 'b.Document No_')
        ->join('CM$Vendor as v', 'a.Pay-to Vendor No_', '=', 'v.No_')
        ->where('a.Vendor Posting Group', '=', 'COWFARMERS')
        ->where('a.Buy-from County', '=', '')
        ->where('a.Posting Date', '>=', '2024-05-02 00:00:00.000')
        ->where('a.Your Reference', '<>', '')
        ->where(function ($query) {
            $query->whereRaw('(
                SELECT COUNT(*) 
                FROM [CM$Purch_ Cr_ Memo Hdr_] as e 
                WHERE e.[Vendor Cr_ Memo No_] = CONCAT(a.[Your Reference], \'-R\')
            ) = 0');
        })
        ->groupBy('a.Your Reference', 'a.Buy-from Vendor No_', 'v.Phone No_', 'a.Buy-from Vendor No_', 'a.Buy-from Vendor Name', 'a.Your Reference', 'a.Uncommitted')
        ->orderBy('a.Buy-from Vendor No_')
        ->get();

        return view('slaughter.pending-etims', compact('title', 'results', 'helpers'));
    }

    public function updatePendingEtimsData(Request $request, Helpers $helpers)
    {
        try {
            
            info($request->item_name.':'.$request->cu_inv_no);            
            // $helpers->forgetCache('pendings_for_etims');

            DB::transaction(function () use ($request, $helpers) {
                DB::connection('main')
                    ->table('CM$Purch_ Inv_ Header')
                    ->where('Your Reference', $request->item_name) // Use column name directly without alias
                    ->update([
                        'Buy-from County' => $request->cu_inv_no,
                    ]);
                
                DB::table('settlement_purchase_invoices')
                    ->insert([
                        'settlement_no' => $request->item_name,
                        'cu_inv_no' => $request->cu_inv_no,
                        'user_id' => Auth::id(),
                    ]);
            });

            Toastr::success("Purchase Invoice no for  {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            $helpers->CustomErrorlogger($e->getMessage(),  __FUNCTION__);
            return back();
        }
    }

    public function sendSmsCurl(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('app.sms_send_url'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($request->all()),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL certificate verification
            CURLOPT_SSL_VERIFYHOST => false
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            info("CURL Error: {$error_msg}");
        }
        
        curl_close($curl);
        // info($response);
        return $response;
    }

    public function updateSmsSentStatus(Request $request)
    {
        try {
            $settlementNo = $request->input('settlement_no');

            DB::connection('main')
                ->table('CM$Purch_ Inv_ Header')
                ->where('Your Reference', $settlementNo)
                ->update(['Uncommitted' => true]);

            return response()->json(['success' => true, 'message' => 'SMS status for ' . $settlementNo . ' updated successfully']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update SMS status. Error: ' . $e->getMessage()]);
        }
    }
}
