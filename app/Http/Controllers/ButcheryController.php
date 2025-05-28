<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Helpers $helpers)
    {
        $title = 'Butchery Dashboard';
        $scale_filter = 'butchery';

        return view('butchery.dashboard', compact('title', 'helpers', 'scale_filter'));
        
    }

    public function scale2(Helpers $helpers)
    {
        $title = 'Butchery Scale 2';
        $scale_filter = 'butchery';

        $configs = Cache::remember('butch-scale_configs', now()->addHours(10), function () use($scale_filter) {
            return DB::table('scale_configs')
                ->where('section', $scale_filter)
                ->get();
        });

        $data = DB::table('butchery_sides')
            ->select('id', 'carcass_count', 'product_code', 'scale_reading', 'tareweight', 'netweight', 'is_manual', 'created_at')
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->get();

        return view('butchery.scale2', compact('title', 'scale_filter', 'configs', 'data', 'helpers'));
    }

    public function scale2Save(Request $request)
    {
        // dd($request->all());

        $validatedData = $this->validate($request, [
            'side_count' => 'required',
            'net_weight' => 'required|numeric',
        ]);

        if (!$validatedData) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }
        try {
            DB::table('butchery_sides')->insert([
            'carcass_count'=> $request->side_count,
            'product_code'=> 'BG1021',
            'scale_reading'=> $request->reading,
            'tareweight'=> $request->tare_weight,
            'netweight'=> $request->net_weight,
            'is_manual'=> $manual_weight,
            'user_id' => Auth::id(),
            ]);

            Toastr::success('Butchery side weight saved successfully', 'Success');
            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }    

    public function scale3()
    {
        $title = 'Butchery';
        $scale_filter = 'butchery';

        $deboning_data = DB::table('deboning as a')
            ->leftJoin('items as b', 'a.product_code', '=', 'b.code' )
            ->select('a.id', 'b.description', 'a.product_code', 'a.scale_reading', 'a.tareweight', 'a.netweight', 'a.is_manual', 'a.no_of_pieces', 'a.process_code', 'a.narration', 'a.created_at')
            ->whereDate('a.created_at', today())
            ->orderBy('a.id', 'desc')
            ->get();

        $products = DB::table('items')
            ->select('id', 'code', 'description')
            ->where('category', 'cm-prod')
            ->orderBy('code', 'asc')
            ->get();

        $configs = Cache::remember('butch-scale_configs', now()->addHours(10),  function () use($scale_filter) {
                return DB::table('scale_configs')
                    ->where('section', $scale_filter)
                    ->get();
            });

        return view('butchery.scale3', compact('title', 'scale_filter', 'products', 'configs', 'deboning_data'));
    }

    public function scale3Save(Request $request)
    {
        // dd($request->all());

        $validatedData = $this->validate($request, [
            'product' => 'required',
            'net' => 'required|numeric',
        ]);


        if (!$validatedData) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        // dd('validated');

        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }
        try {
            DB::table('deboning')->insert([
            'no_of_pieces'=> $request->no_of_pieces,
            'product_code'=> $request->product,
            'scale_reading'=> $request->reading,
            'tareweight'=> $request->tareweight,
            'netweight'=> $request->net,
            'process_code'=> $request->production_process_code,
            'is_manual'=> $manual_weight,
            'user_id' => Auth::id(),
            ]);

            Toastr::success('Deboning weight saved successfully', 'Success');
            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    } 

    public function scale3Update(Request $request)
    {
        dd($request->all());
        $validatedData = $this->validate($request, [
            'edit_product' => 'required',
            'edit_net' => 'required|numeric',
            'item_id' => 'required',
        ]);

        if (!$validatedData) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }
        $id = $request->item_id;
        try {
            DB::table('deboning')->where('id', $id)->update([
            'product_code'=> $request->product,
            'scale_reading'=> $request->reading,
            'tareweight'=> $request->tareweight,
            'netweight'=> $request->net,
            'process_code'=> $request->production_process_code,
            'is_manual'=> $manual_weight,
            'user_id' => Auth::id(),
            ]);

            Toastr::success('Deboning weight updated successfully', 'Success');
            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }
}
