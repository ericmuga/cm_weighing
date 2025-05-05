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

        $configs = Cache::remember('butch-scale_configs', now()->addMinutes(120), function () use($scale_filter) {
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
        $title = 'Butchery Scale 3';
        $scale_filter = 'butchery_scale_3';

        return view('butchery.scale3', compact('title', 'scale_filter'));
    }
}
