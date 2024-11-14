<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\Transfer;
use App\Models\Item;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function transfers(Helpers $helpers)
    {
        $title = 'Transfer';

        $products = Cache::remember('prod_items', now()->addMinutes(120), function () {
            return Item::where('category', 'cm-prod')->get();
        });

        $configs = Cache::remember('weigh_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('scale', 'Scale 1')
                ->where('section', 'transfers')
                ->select('tareweight', 'comport')
                ->get()->toArray();
        });

        $transfers = Transfer::orderBy('created_at', 'desc')->limit(1000)->get();

        return view('transfers.form', compact('title','configs', 'products', 'transfers', 'helpers'));
        
    }

    public function saveTransfer(Request $request, Helpers $helpers) {
        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }

        if ($request->to_location_code == "FCL") {
            $transfer_type = "external";
        } else {
            $transfer_type = "internal";
        }

        try {
            Transfer::create([
                'item_code'=> $request->item_code,
                'batch_no'=> $request->batch_no,
                'scale_reading'=> $request->scale_reading,
                'net_weight'=> $request->net_weight,
                'no_of_pieces'=> $request->no_of_pieces,
                'from_location_code'=> $request->from_location_code,
                'to_location_code'=> $request->to_location_code,
                'transfer_type'=> $transfer_type,
                'narration'=> $request->narration,
                'manual_weight'=> $manual_weight,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Transfer saved successfully']);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save transfer. Error: ' . $e->getMessage()]);
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

        return view('transfers.stocks', compact('title', 'products', 'helpers', 'entries'));
        
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
}
