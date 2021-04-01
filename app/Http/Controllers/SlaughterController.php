<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SlaughterController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";
        $date = today();

        $lined_up = Cache::remember('lined_up', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('slaughter_date', today())
                ->sum('receipts.received_qty');
        });

        $slaughtered = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->count();

        $total_weight = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->sum('slaughter_data.total_net');

        return view('slaughter.dashboard', compact('title', 'helpers', 'date', 'lined_up', 'slaughtered', 'total_weight'));
    }

    public function weigh()
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
                ->whereDate('slaughter_date', Carbon::today())
                ->select('receipt_no')
                ->get();
        });

        return view('slaughter.weigh', compact('title', 'configs'));
    }

    public function receipts(Helpers $helpers)
    {
        $title = "receipts";

        $receipts = Cache::remember('imported_receipts', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('created_at', '>=', Carbon::yesterday())
                ->orderBy('created_at', 'DESC')
                ->take(100)
                ->get();
        });

        return view('slaughter.receipts', compact('title', 'receipts', 'helpers'));
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
                            'user_id' => $helpers->authenticatedUserId(),
                            'slaughter_date' => $database_date,
                        ]
                    );
                }
            });

            Toastr::success('receipts uploaded successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error Occurred. Wrong Data format!. Records not saved!');
            return back()
                ->withInput();
        }
    }

    public function slaughterReport(Helpers $helpers)
    {
        $title = "Slaughter Data";

        $slaughter_data = DB::table('slaughter_data')
            ->leftJoin('carcass_types', 'slaughter_data.ear_tag', '=', 'carcass_types.code')
            ->select('slaughter_data.*', 'carcass_types.description')
            ->orderBy('slaughter_data.created_at', 'DESC')
            ->take(1000)
            ->get();

        return view('slaughter.report', compact('title', 'helpers', 'slaughter_data'));
    }

    public function scaleConfigs(Helpers $helpers)
    {
        $title = "scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', 'slaughter')
            ->get();

        return view('slaughter.scale_configs', compact('title', 'scale_settings', 'helpers'));
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
                    'updated_at' => Carbon::now(),
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
}
