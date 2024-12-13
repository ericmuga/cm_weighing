<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QAController extends Controller
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
            ->select('a.*', 'b.vendor_no', 'b.description', 'b.item_code', 'c.settlement_weight')
            ->join('receipts as b', function ($join) {
                $join->on('a.receipt_no', '=', 'b.receipt_no')
                    ->on('a.slaughter_date', '=', 'b.slaughter_date');
            })
            ->leftJoin('slaughter_data as c', function ($join) {
                $join->on('a.agg_no', '=', 'c.agg_no')
                    ->on('a.receipt_no', '=', 'c.receipt_no')
                    ->whereDate('c.created_at', '=', today());
            })            
            ->where('a.slaughter_date', today())
            ->get();

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
                        'grading_user' =>  Auth::id(),
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
            ->select('receipt_no', 'agg_no', 'classification')
            ->get();

        // if ($qa_graded->isNotEmpty()) {
            // Combine receipt_no and agg_no into pairs
            $combined_pairs = $qa_graded->map(function ($item) {
                return $item->receipt_no . '_' . $item->agg_no;
            });

            $slaughter_data = DB::table('slaughter_data as a')
                ->whereDate('a.created_at', today())
                ->whereIn(DB::raw("CONCAT(a.receipt_no, '_', a.agg_no)"), $combined_pairs)
                ->join('qa_grading as b', function ($join) {
                    $join->on('b.receipt_no', '=', 'a.receipt_no')
                        ->on('b.agg_no', '=', 'a.agg_no');
                })
                ->leftJoin('receipts', 'a.receipt_no', '=', 'receipts.receipt_no')
                ->select('a.settlement_weight', 'a.receipt_no', 'a.agg_no', 'b.classification', 'receipts.description as receipt_description', 'a.item_code')
                ->get();

            foreach ($slaughter_data as $d) {
                $classification = $d->classification ?? $d->receipt_description;
                
                $class_type = $this->getClassificationCode($classification, $d->settlement_weight, $d->item_code);

                $this->updateClassificationCode($d->receipt_no, $d->agg_no, $class_type);
            }


            if ($slaughter_data->isEmpty()) {
                // Handle case where no slaughter data found
            }
        // }

        return 1;
    }
    
    // private function getClassificationCode($class_type, $settlement_weight, $item_code)
    // {
    //     $classification_code = '--';

    //     if ($settlement_weight > 1 && $item_code != '') {
    //         if ($item_code == 'BG1101' || $item_code == 'BG1201') {
    //             // lamb/goat classes
    //             switch (true) {
    //                 case ($item_code == 'BG1101'):
    //                     // lamb
    //                     if ($settlement_weight > 25) {
    //                         $classification_code = 'LAMB-STD';

    //                     } else if ($settlement_weight >= 14 && $settlement_weight < 25) {
    //                         $classification_code = 'LAMB-PRM';

    //                     } else if ($settlement_weight >= 11 && $settlement_weight < 14) {
    //                         $classification_code = 'LAMB-STD';
    //                     }
    //                     break;

    //                 case ($item_code == 'BG1201'):
    //                     // goat
    //                     $classification_code = 'GOATLCL';
    //                     break;

    //                 default:
    //                     $classification_code = '**';
    //             }

    //         } else if ($class_type == 2) {
    //             // High Grade 
    //             switch (true) {
    //                 case ($settlement_weight < 120):
    //                     $classification_code = 'STDB-119';
    //                     break;

    //                 case ($settlement_weight >= 120 && $settlement_weight < 150):
    //                     $classification_code = 'STDA-149';
    //                     break;

    //                 case ($settlement_weight >= 150 && $settlement_weight < 160):
    //                     $classification_code = 'FAQ+150';                        
    //                     break;

    //                 case ($settlement_weight >= 160 && $settlement_weight < 170):
    //                     $classification_code = 'HG+160';
    //                     break;

    //                 case ($settlement_weight >= 170):
    //                     $classification_code = 'HG+170';
    //                     break;

    //                 default:
    //                     $classification_code = '**';
    //             }
    //         } else if ($class_type == 3) {
    //             // comm-beef
    //             switch (true) {
    //                 case ($settlement_weight < 120):
    //                     $classification_code = 'CG-120';
    //                     break;

    //                 case ($settlement_weight >= 120 && $settlement_weight < 150):
    //                     $classification_code = 'CG+120';
    //                     break;

    //                 case ($settlement_weight >= 150 && $settlement_weight < 160):
    //                      $classification_code = 'CG+150';
    //                     break;

    //                 case ($settlement_weight >= 160 && $settlement_weight < 170):
    //                     $classification_code = 'CG+160';
    //                     break;

    //                 case ($settlement_weight >= 170):
    //                     $classification_code = 'CG+170';
    //                     break;

    //                 default:
    //                     $classification_code = '**';
    //             }
    //         } else if ($class_type == 1) {
    //             // premium
    //             switch (true) {
    //                 case ($settlement_weight > 170):
    //                     $classification_code = 'PG+170';
    //                     break;
    //                 default:
    //                     $classification_code = '**';
    //             }
    //         } else if ($class_type == 4) {
    //             // premium
    //             $classification_code = 'Poor C';
    //         }
    //     }

    //     return $classification_code;
    // }

    private function getClassificationCode($class_type, $settlement_weight, $item_code)
    {
        if ($settlement_weight <= 1 || $item_code == '') {
            return '--'; // Early exit for invalid conditions
        }

        switch ($item_code) {
            //lamb
            case 'BG1900':
                if ($settlement_weight > 25) {
                    return '2nd Grade';
                } elseif ($settlement_weight >= 14) {
                    return '1st Grade';
                } elseif ($settlement_weight <= 11) {
                    return 'Class R';
                } else {
                    return 'lamb**';
                }
                break;

            //Goat
            case 'BG1202':
                return 'GOATLCL'; // Direct return for goat classification
                break;

            default:
                break;
        }

        switch ($class_type) {
            case is_string($class_type) && str_contains($class_type, 'High Grade'):
            case 2: // High Grade
                if ($settlement_weight < 120) {
                    return 'STDB-119';
                } elseif ($settlement_weight < 150) {
                    return 'STDA-149';
                } elseif ($settlement_weight < 160) {
                    return 'FAQ+150';
                } elseif ($settlement_weight < 170) {
                    return 'HG+160';
                } else {
                    return 'HG+170';
                }
                break;

            case is_string($class_type) && str_contains($class_type, 'Comm'):
            case 3: // Comm-Beef
                if ($settlement_weight < 120) {
                    return 'CG-120';
                } elseif ($settlement_weight < 150) {
                    return 'CG+120';
                } elseif ($settlement_weight < 160) {
                    return 'CG+150';
                } elseif ($settlement_weight < 170) {
                    return 'CG+160';
                } else {
                    return 'CG+170';
                }
                break;

            case 1: // Premium
                if ($settlement_weight > 170) {
                    return 'PG+170';
                } else {
                    return '**';
                }
                break;

            case 4: // Poor C
                return 'Poor C';
                break;

            default:
                return '**'; // Default case
                break;
        }
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
            // Log::info('grading class successful');
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
                        'dentition' => $request->dentition,  
                        'fat_cover' => $request->fat_cover,  
                        'fat_color' => $request->fat_color,  
                        'meat_color' => $request->meat_color,  
                        'bruising' => $request->bruising,  
                        'muscle_conformation' => $request->muscle,  
                        'graded_by' =>  Auth::id(),
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
