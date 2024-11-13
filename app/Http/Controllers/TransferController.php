<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\Transfer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function form(Helpers $helpers)
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
}
