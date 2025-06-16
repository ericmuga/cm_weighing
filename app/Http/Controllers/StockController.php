<?php

namespace App\Http\Controllers;

use App\Exports\IDTLinesExport;
use App\Exports\IDTSummaryExport;
use App\Models\Helpers;
use App\Models\Transfer;
use App\Models\Item;
use App\Models\Stock;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;

if (!defined('SOCKET_EAGAIN')) {
    define('SOCKET_EAGAIN', 11);
}

if (!defined('SOCKET_EWOULDBLOCK')) {
    define('SOCKET_EWOULDBLOCK', 11);
}

if (!defined('SOCKET_EINTR')) {
    define('SOCKET_EINTR', 4);
}
class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Helpers $helpers)
    {
        $title = 'Stocks Dashboard';

        $todaysStockEntriesCount = Stock::where('stock_date', now()->toDateString())->count();

        $todaysStockTransferIssuesCount = Transfer::where('created_at', '>=', now()->toDateString())->count();

        $todaysStockTransferReceiptsCount = Transfer::where('received_date', '>=', now()->toDateString())->count();

        return view('stocks.dashboard', compact('title', 'helpers', 'todaysStockEntriesCount', 'todaysStockTransferIssuesCount', 'todaysStockTransferReceiptsCount'));
        
    }

    public function transfersIssue(Helpers $helpers)
    {
        $title = 'Transfers Issue';

        $products = Cache::remember('prod_items', now()->addMinutes(120), function () {
            return Item::where('category', 'cm-prod')->get();
        });

        $configs = Cache::remember('transfer-scale_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'transfers')
                ->get();
        });

        $transfer_locations = Cache::remember('transfer_locations', now()->addHours(12), function () {
            return DB::table('transfer_locations')
                ->select('name', 'location_code')
                ->orderBy('location_code', 'asc')
                ->get();
        });

        $transfers = DB::table('transfers')
        ->whereDate('transfers.created_at', '>=', today()->subDays(2))
        ->leftJoin('items', 'transfers.item_code', '=', 'items.code')
        ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
        ->select('transfers.*', 'items.description as item_description', 'users.username as issuer')
        ->orderBy('transfers.created_at', 'desc')
        ->get();

        return view('stocks.issue_transfers', compact('title','configs', 'transfer_locations', 'products', 'transfers', 'helpers'));
        
    }

    public function transfersReceive(Helpers $helpers)
    {
        $title = 'Transfers Receive';

        $transfersDue = Transfer::where('received_date', null)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('transfer_type', 'internal')
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();


        $transfersReceived = Transfer::where('received_date', '>=', now()->subDays(7))
            ->where('transfer_type', 'internal')
            ->orderBy('received_date', 'desc')
            ->limit(1000)
            ->get();
        
        return view('stocks.receive_transfers', compact('title', 'transfersDue', 'transfersReceived', 'helpers'));
        
    }

    public function saveTransfer(Request $request, Helpers $helpers) {
        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }

        if ($request->to_location_code == "FCL") {
            $transfer_type = "external";
            $data = $request->all();
            $data['issuer'] = Auth::id();
            $data['manual_weight'] = $manual_weight;
            $helpers->publishToQueue($data, 'intercompany_transfers.wms');
        } else {
            $transfer_type = "internal";
        }

        try {
            Transfer::create([
                'item_code'=> $request->item_code,
                'batch_no'=> $request->batch_no,
                'scale_reading'=> $request->reading,
                'net_weight'=> $request->net_weight,
                'no_of_pieces'=> $request->no_of_pieces,
                'from_location_code'=> $request->from_location_code,
                'to_location_code'=> $request->to_location_code,
                'transfer_type'=> $transfer_type,
                'narration'=> $request->narration,
                'manual_weight'=> $manual_weight,
                'vehicle_no'=> $request->vehicle_no,
                'user_id' => Auth::id(),
            ]);

            // return response()->json(['success' => true, 'message' => 'Transfer saved successfully']);
            Toastr::success("Transfer saved successfully", 'Success');
            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }

    public function transferUpdate(Request $request, Helpers $helpers) {
        try {
            Transfer::where('id', $request->transfer_id)
                ->update([
                    'received_weight' => $request->received_weight,
                    'received_pieces' => $request->received_pieces,
                    'received_date' => now(),
                    'received_by' => Auth::id(),
                ]);
            return response()->json(['success' => true, 'message' => 'Transfer received successfully']);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to receive transfer. Error: ' . $e->getMessage()]);
        }
    }

    public function stockTake(Helpers $helpers)
    {
        $title = 'Stock Take';

        $products = Cache::remember('prod_items', now()->addMinutes(120), function () {
            return Item::where('category', 'cm-prod')->get();
        });


        $entries = Stock::where('stock_date', '>=', now()->subDays(10))
                ->orderBy('stock_date', 'desc')
                ->limit(1000)
                ->get();

        return view('stocks.records', compact('title', 'products', 'helpers', 'entries'));
        
    }

    public function stockUpdate(Request $request) {
        try {
            Stock::create([
                'item_code'=> $request->item_code,
                'unit_of_measure'=> $request->unit_of_measure,
                'weight'=> $request->weight,
                'pieces'=> $request->pieces,
                'location_code'=> $request->location_code,
                'stock_date'=> $request->stock_date,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Stock saved successfully']);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save stock. Error: ' . $e->getMessage()]);
        }
    }

    public function idtLinesReport(Request $request, Helpers $helpers, $filter = null)
    {
        $title = "IDT Report";

        $q = DB::table('transfers')
            ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
            ->leftJoin('items', 'transfers.item_code', '=', 'items.code')
            ->select(
                'transfers.id',
                'transfers.item_code',
                'items.description as item_description',
                'transfers.net_weight',
                'transfers.no_of_pieces',
                'transfers.batch_no',
                'transfers.from_location_code',
                'transfers.to_location_code',
                'users.username as issuer',
                'transfers.narration',
                'transfers.created_at'
                )
            ->orderBy('transfers.created_at', 'DESC');
       
        if ($request->from_location) {
            $q->where('transfers.from_location_code', $request->from_location);
        }

        if ($request->to_location) {
            $q->where('transfers.to_location_code', $request->to_location);
        }

        if ($request->from_date) {
            $q->whereDate('transfers.created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $q->whereDate('transfers.created_at', '<=', $request->to_date);
        }

        if ($request->item_code) {
            $q->where('transfers.item_code', $request->item_code);
        }

        if ($request->user_id) {
            $q->where('transfers.user_id', $request->user_id);
        }

        if ($request->item_code) {
            $q->where('transfers.item_code', $request->item_code);
        }

        if (!$request->from_date && !$request->to_date && !$request->from_location && !$request->to_location && !$request->item_code && !$request->user_id && !$request->item_code) {
            $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
        }

        $issuers = DB::table('transfers')
            ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
            ->whereDate('transfers.created_at', '>=', now()->subDays(30))
            ->select('users.username', 'users.id')
            ->distinct()
            ->get();

        $entries = $q->get();

        $locations = [
            'B1020' => 'Slaughter',
            'B1570' => 'Butchery',
            'B3535' => 'Despatch',
            'FCL' => 'FCL',
        ];

        $products = Cache::remember('cm_items', now()->addMinutes(120), function () {
            return Item::all();
        });

        return view('stocks.idt_lines_report', compact('title', 'helpers', 'entries', 'locations', 'issuers', 'products'));
    }

    public function idtLinesReportExport(Request $request, Helpers $helpers)
    {
        $title = 'IDT Lines Report';
        $q = DB::table('transfers')
            ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
            ->leftJoin('items', 'transfers.item_code', '=', 'items.code')
            ->select(
                'transfers.id',
                'transfers.item_code',
                'items.description as item_description',
                'transfers.net_weight',
                'transfers.no_of_pieces',
                'transfers.from_location_code',
                'transfers.to_location_code',
                'users.username as issuer',
                'transfers.narration',
                'transfers.created_at'
                )
            ->orderBy('transfers.created_at', 'DESC');
       
        if ($request->from_location) {
            $q->where('transfers.from_location_code', $request->from_location);
            $title .= ' from location ' . $request->from_location;
        }

        if ($request->to_location) {
            $q->where('transfers.to_location_code', $request->to_location);
            $title .= ' to location ' . $request->from_location;
        }

        if ($request->from_date) {
            $q->whereDate('transfers.created_at', '>=', $request->from_date);
            $title .= ' from ' . $request->from_date;
        }

        if ($request->to_date) {
            $q->whereDate('transfers.created_at', '<=', $request->to_date);
            $title .= ' to ' . $request->to_date;
        }

        if ($request->item_code) {
            $q->where('transfers.item_code', $request->item_code);
            $title .= ' for item ' . $request->item_code;
        }

        if ($request->from_idt_no) {
            $q->where('transfers.id', '>=', $request->from_idt_no);
            $title .= ' from IDT No. ' . $request->from_idt_no;
        }

        if ($request->to_idt_no) {
            $q->where('transfers.id', '<=', $request->to_idt_no);
            $title .= ' to IDT No. ' . $request->to_idt_no;
        }

        if ($request->user_id) {
            $q->where('transfers.user_id', $request->user_id);
            $username = DB::table('users')->where('id', $request->user_id)->first()->username;
            $title .= ' issued by ' . $username;
        }

        if (!$request->from_date && !$request->to_date && !$request->from_location && !$request->to_location && !$request->item_code && !$request->user_id) {
            $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
        }

        $data = $q->get();

        $exports = Session::put('session_export_data', $data);

        return Excel::download(new IDTLinesExport,  $title . '.xlsx');
    }

    public function idtSummaryReport(Request $request, Helpers $helpers, $filter = null)
    {
        $title = "IDT Report";

        $q = DB::table('transfers')
        ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
        ->leftJoin('items', 'transfers.item_code', '=', 'items.code')
        ->select(
            'transfers.item_code',
            'items.description as item_description',
            \DB::raw('SUM(net_weight) as total_weight'),
            \DB::raw('SUM(no_of_pieces) as total_pieces'),
            'transfers.from_location_code',
            'transfers.to_location_code',
            )
        ->groupBy('item_code', 'items.description', 'from_location_code', 'to_location_code');
       
        if ($request->from_location) {
            $q->where('transfers.from_location_code', $request->from_location);
        }

        if ($request->to_location) {
            $q->where('transfers.to_location_code', $request->to_location);
        }

        if ($request->from_date) {
            $q->whereDate('transfers.created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $q->whereDate('transfers.created_at', '<=', $request->to_date);
        }

        if ($request->item_code) {
            $q->where('transfers.item_code', $request->item_code);
        }

        if ($request->user_id) {
            $q->where('transfers.user_id', $request->user_id);
        }

        if (!$request->from_date && !$request->to_date && !$request->from_location && !$request->to_location && !$request->item_code && !$request->user_id) {
            $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
        }

        $issuers = DB::table('transfers')
            ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
            ->whereDate('transfers.created_at', '>=', now()->subDays(30))
            ->select('users.username', 'users.id')
            ->distinct()
            ->get();

        $summary = $q->get();

        $locations = [
            'B1020' => 'Slaughter',
            'B1570' => 'Butchery',
            'B3535' => 'Despatch',
            'FCL' => 'FCL',
        ];

        $products = Cache::remember('cm_items', now()->addMinutes(120), function () {
            return Item::all();
        });

        return view('stocks.idt_summary_report', compact('title', 'helpers', 'summary', 'locations', 'issuers', 'products'));
    }

    public function idtSummaryReportExport(Request $request, Helpers $helpers)
    {
        $title = 'IDT Summary Report';

        $q = DB::table('transfers')
            ->leftJoin('users', 'transfers.user_id', '=', 'users.id')
            ->leftJoin('items', 'transfers.item_code', '=', 'items.code')
            ->select(
                'transfers.item_code',
                'items.description as item_description',
                \DB::raw('SUM(net_weight) as total_weight'),
                \DB::raw('SUM(no_of_pieces) as total_pieces'),
                'transfers.from_location_code',
                'transfers.to_location_code',
                )
            ->groupBy('item_code', 'items.description', 'from_location_code', 'to_location_code');
       
        if ($request->from_location) {
            $q->where('transfers.from_location_code', $request->from_location);
            $title .= ' from location ' . $request->from_location;
        }

        if ($request->to_location) {
            $q->where('transfers.to_location_code', $request->to_location);
            $title .= ' to location ' . $request->from_location;
        }

        if ($request->from_date) {
            $q->whereDate('transfers.created_at', '>=', $request->from_date);
            $title .= ' from ' . $request->from_date;
        }

        if ($request->to_date) {
            $q->whereDate('transfers.created_at', '<=', $request->to_date);
            $title .= ' to ' . $request->to_date;
        }

        if ($request->item_code) {
            $q->where('transfers.item_code', $request->item_code);
            $title .= ' for item ' . $request->item_code;
        }

        if ($request->from_idt_no) {
            $q->where('transfers.id', '>=', $request->from_idt_no);
            $title .= ' from IDT No. ' . $request->from_idt_no;
        }

        if ($request->to_idt_no) {
            $q->where('transfers.id', '<=', $request->to_idt_no);
            $title .= ' to IDT No. ' . $request->to_idt_no;
        }

        if ($request->user_id) {
            $q->where('transfers.user_id', $request->user_id);
            $username = DB::table('users')->where('id', $request->user_id)->first()->username;
            $title .= ' issued by ' . $username;
        }

        if (!$request->from_date && !$request->to_date && !$request->from_location && !$request->to_location && !$request->item_code && !$request->user_id) {
            $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
        }

        $data = $q->get();

        $exports = Session::put('session_export_data', $data);

        return Excel::download(new IDTSummaryExport, $title . '.xlsx');
    }
}
