<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QAController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
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

        $graded = DB::table('slaughter_data')
            ->whereDate('created_at', today())
            ->where('deleted', '!=', 1)
            ->where('fat_group', '!=', null)
            ->count();

        return view('QA.dashboard', compact('title', 'helpers', 'lined_up', 'slaughtered', 'graded'));
    }

    public function grade(Helpers $helpers)
    {
        $title = "Grading";

        $slaughter_data = DB::table('slaughter_data')
            ->whereDate('slaughter_data.created_at', today())
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.*', 'carcass_types.description AS item_name')
            ->orderBy('slaughter_data.created_at', 'DESC')
            ->get();

        $classifications = DB::table('fat_groups')
            ->select('code')
            ->get();

        return view('QA.grading', compact('title', 'helpers', 'slaughter_data', 'classifications'));
    }

    public function gradeV2(Helpers $helpers)
    {
        $title = "Grading V2";

        $grading_data = DB::table('qa_grading as a')
            ->select('a.*', 'b.vendor_no', 'b.description', 'b.item_code')
            ->join('receipts as b', function ($join) {
                $join->on('a.receipt_no', '=', 'b.receipt_no')
                    ->on('a.slaughter_date', '=', 'b.slaughter_date');
            })
            ->where('a.slaughter_date', today())
            ->get();

        // dd($data);

        return view('QA.grading-v2', compact('title', 'helpers', 'grading_data'));
    }

    public function updateGrading(Request $request, Helpers $helpers)
    {
        try {
            DB::transaction(function () use ($request, $helpers) {
                DB::table('slaughter_data')
                    ->where('id', $request->item_id)
                    ->update([
                        'fat_group' => $request->fat_group,
                        'narration' => $request->narration,
                        'grading_user' => $helpers->authenticatedUserId(),
                        'graded_at' => now(),
                    ]);
                $desc = 'new fat_group:' . $request->fat_group . ', narration: ' . $request->narration;

                $helpers->insertChangeDataLogs('slaughter_data', $request->item_id, '3', $desc);
            });

            Toastr::success("Carcass no. {$request->agg_no} graded successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error($e->getMessage());
            return back();
        }
    }

    public function runGradingClasses()
    {
        $qa_graded = DB::table('qa_grading')
            ->whereDate('slaughter_date', today())
            ->whereNotNull('classification')
            ->select('receipt_no', 'agg_no', 'classification')
            ->get();

        if ($qa_graded->isNotEmpty()) {
            // Combine receipt_no and agg_no into pairs
            $combined_pairs = $qa_graded->map(function ($item) {
                return $item->receipt_no . '_' . $item->agg_no;
            });

            $slaughter_data = DB::table('slaughter_data as a')
                ->whereDate('a.created_at', today())
                ->whereIn(DB::raw("CONCAT(a.receipt_no, '_', a.agg_no)"), $combined_pairs)
                ->select('a.settlement_weight', 'a.receipt_no', 'a.agg_no', 'b.classification')
                ->join('qa_grading as b', function ($join) {
                    $join->on('b.receipt_no', '=', 'a.receipt_no')
                        ->on('b.agg_no', '=', 'a.agg_no');
                })
                ->get();

            info('Slaughter:');
            info($slaughter_data);

            foreach ($slaughter_data as $d) {
                # code...
                
                $class_type = $this->getClassificationCode($d->classification, $d->settlement_weight);

                $this->updateClassificationCode($d->receipt_no, $d->agg_no, $class_type);
            }


            if ($slaughter_data->isEmpty()) {
                // Handle case where no slaughter data found
            }
        }

        return 1;
    }
    
    private function getClassificationCode($class_type, $settlement_weight)
    {
        return 'HG+170';
    }

    private function updateClassificationCode($receipt_no, $agg_no, $class_type)
    {
        try {
            //code...
            DB::table('qa_grading')
                ->where('receipt_no', $receipt_no)
                ->where('agg_no', $agg_no)
                ->update([
                    'classification_code' => $class_type
                ]);
            Log::info('grading class successful');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back();
        }
        
    }

    public function updateGradingV2(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        try {
            DB::transaction(function () use ($request, $helpers) {
                DB::table('qa_grading')
                    ->where('id', $request->item_id)
                    ->update([
                        'classification' => $request->fat_group,
                        'narration' => $request->narration,
                        'graded_by' => $helpers->authenticatedUserId(),
                    ]);
                $desc = 'new fat_group:' . $request->fat_group . ', narration: ' . $request->narration;

                $helpers->insertChangeDataLogs('qa_grading', $request->item_id, '3', $desc);
            });

            Toastr::success("Carcass no. {$request->agg_no} graded successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error($e->getMessage());
            return back();
        }
    }
}
