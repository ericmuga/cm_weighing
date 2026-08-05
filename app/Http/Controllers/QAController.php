<?php

namespace App\Http\Controllers;

use App\Exports\QAGradingReportExport;
use App\Exports\SlaughterGradingReportExport;
use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

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
            ->select('a.*', 'b.vendor_no', 'ct.description', 'c.settlement_weight', 'c.agg_no as slaughter_agg_no')
            ->join(DB::raw('(SELECT DISTINCT receipt_no, slaughter_date, vendor_no FROM receipts) as b'), function ($join) {
                $join->on('a.receipt_no', '=', 'b.receipt_no')
                    ->on('a.slaughter_date', '=', 'b.slaughter_date');
            })
            ->leftJoin('carcass_types as ct', 'a.item_code', '=', 'ct.code')
            ->leftJoin('slaughter_data as c', function ($join) {
                $join->on('a.agg_no', '=', 'c.agg_no')
                    ->on('a.receipt_no', '=', 'c.receipt_no')
                    ->on('a.item_code', '=', 'c.item_code')
                    ->whereDate('c.created_at', '=', today());
            })
            ->where('a.slaughter_date', today())
            ->orderBy('a.created_at', 'asc')
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
            ->select('receipt_no', 'agg_no', 'item_code', 'classification')
            ->get();

        if ($qa_graded->isEmpty()) {
            app(\App\Http\Controllers\SlaughterController::class)->insertForQAGrading(today());

            $qa_graded = DB::table('qa_grading')
                ->whereDate('slaughter_date', today())
                ->select('receipt_no', 'agg_no', 'item_code', 'classification')
                ->get();
        }

        // if ($qa_graded->isNotEmpty()) {
            // Combine receipt_no and agg_no into pairs
            $combined_pairs = $qa_graded->map(function ($item) {
                return $item->receipt_no . '_' . $item->agg_no . '_' . $item->item_code;
            });

            $slaughter_data = DB::table('slaughter_data as a')
                ->whereDate('a.created_at', today())
                ->whereIn(DB::raw("CONCAT(a.receipt_no, '_', a.agg_no, '_', a.item_code)"), $combined_pairs)
                ->join('qa_grading as b', function ($join) {
                    $join->on('b.receipt_no', '=', 'a.receipt_no')
                        ->on('b.agg_no', '=', 'a.agg_no')
                        ->on('b.item_code', '=', 'a.item_code');
                })
                ->leftJoin('receipts', 'a.receipt_no', '=', 'receipts.receipt_no')
                ->select('a.settlement_weight', 'a.receipt_no', 'a.agg_no', 'a.item_code', 'b.classification', 'receipts.description as receipt_description')
                ->get();

            foreach ($slaughter_data as $d) {
                $classification = $d->classification ?? $d->receipt_description;

                $class_type = $this->getClassificationCode($classification, $d->settlement_weight, $d->item_code);

                $this->updateClassificationCode($d->receipt_no, $d->agg_no, $d->item_code, $class_type);
            }


            if ($slaughter_data->isEmpty()) {
                // Handle case where no slaughter data found
            }

        return 1;
    }

    private function getClassificationCode($class_type, $settlement_weight, $item_code)
    {
        if ($settlement_weight <= 1 || empty($item_code)) {
            return '--';
        }

        switch ($item_code) {
            //lamb
            case 'BG1900':
                if ($settlement_weight < 11) {
                    return 'Class R';
                } elseif ($settlement_weight < 14) {
                    return '2nd Grade';
                } elseif ($settlement_weight <= 30) {
                    return '1st Grade';
                } else {
                    return '2nd Grade';
                }

            //Goat
            case 'BG1202':
                return 'GOATLCL'; // Direct return for goat classification

            default:
                break;
        }

        // Beef codes only — BG1900 (lamb) and BG1202 (goat) already returned above

        if ((is_string($class_type) && str_contains($class_type, 'High Grade')) || (int) $class_type === 2) {
            if ($settlement_weight < 120) {
                return 'STDB-119';
            } elseif ($settlement_weight < 150) {
                return 'STDA-149';
            } elseif ($settlement_weight < 160) {
                return 'FAQ+150';
            } elseif ($settlement_weight < 170) {
                return 'HG+160';
            }

            return 'HG+170';
        }

        if ((is_string($class_type) && str_contains($class_type, 'Comm')) || (int) $class_type === 3) {
            if ($settlement_weight < 120) {
                return 'CG-120';
            } elseif ($settlement_weight < 150) {
                return 'CG+120';
            } elseif ($settlement_weight < 160) {
                return 'CG+150';
            } elseif ($settlement_weight < 170) {
                return 'CG+160';
            }

            return 'CG+170';
        }

        if ((int) $class_type === 1) { // Premium
            return $settlement_weight > 170 ? 'PG+170' : '**';
        }

        if ((int) $class_type === 4) { // Poor C
            return 'Poor C';
        }

        return '**'; // Default case
    }

    private function computeIsDowngraded(int $classification, string $classificationCode): ?int
    {
        $matches = match ($classification) {
            1 => $classificationCode === 'PG+170',
            2 => in_array($classificationCode, ['STDB-119', 'STDA-149', 'FAQ+150', 'HG+160', 'HG+170']),
            3 => in_array($classificationCode, ['CG-120', 'CG+120', 'CG+150', 'CG+160', 'CG+170']),
            4 => $classificationCode === 'Poor C',
            5 => $classificationCode === '1st Grade',
            6 => $classificationCode === '2nd Grade',
            7 => $classificationCode === 'Class R',
            default => null,
        };

        return $matches === null ? null : ($matches ? 0 : 1);
    }

    private function updateClassificationCode(string $receipt_no, int $agg_no, string $item_code, string $class_type)
    {
        try {
            DB::table('qa_grading')
                ->where('receipt_no', $receipt_no)
                ->where('agg_no', $agg_no)
                ->where('item_code', $item_code)
                ->update(['classification_code' => $class_type]);

            // Compute is_downgraded if QA classification is already set
            $row = DB::table('qa_grading')
                ->where('receipt_no', $receipt_no)
                ->where('agg_no', $agg_no)
                ->where('item_code', $item_code)
                ->value('classification');

            if ($row) {
                $isDowngraded = $this->computeIsDowngraded((int) $row, $class_type);
                if ($isDowngraded !== null) {
                    DB::table('qa_grading')
                        ->where('receipt_no', $receipt_no)
                        ->where('agg_no', $agg_no)
                        ->where('item_code', $item_code)
                        ->update(['is_downgraded' => $isDowngraded]);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function updateGradingV2(Request $request, Helpers $helpers)
    {
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
                        'graded_by' => Auth::id(),
                    ]);

                $desc = 'new fat_group:' . $request->fat_group . ', narration: ' . $request->narration;
                $helpers->insertChangeDataLogs('qa_grading', $request->item_id, '3', $desc);
            });

            // Compute is_downgraded now that QA classification is saved
            $classificationCode = DB::table('qa_grading')->where('id', $request->item_id)->value('classification_code');
            $isDowngraded = null;
            if ($classificationCode) {
                $isDowngraded = $this->computeIsDowngraded((int) $request->fat_group, $classificationCode);
                if ($isDowngraded !== null) {
                    DB::table('qa_grading')->where('id', $request->item_id)
                        ->update(['is_downgraded' => $isDowngraded]);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => "Carcass no. {$request->agg_no} graded successfully",
                    'is_downgraded' => $isDowngraded,
                ]);
            }

            Toastr::success("Carcass no. {$request->agg_no} graded successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function qaGradingReportExport(Request $request)
    {
        $from = $request->from_date;
        $to   = $request->to_date;

        $dentitionMap  = [1 => 'Full mouth', 2 => '3 pairs', 3 => '2 pairs', 4 => '1 pair', 5 => 'Milk Teeth'];
        $fatCoverMap   = [1 => 'Good fat cover', 2 => 'Fair fat cover', 3 => 'Minimum/inadequate'];
        $fatColorMap   = [1 => 'Creamish white', 2 => 'Deep yellow'];
        $meatColorMap  = [1 => 'Bright red', 2 => 'Dark meat'];
        $bruisingMap   = [0 => 'No Bruises', 1 => 'Mild', 2 => 'Extensive', 3 => 'Severely bruised', 4 => 'Cysts Bovis', 5 => 'Other discolouration'];
        $muscleMap     = [1 => 'Well finished', 2 => 'Fair', 3 => 'Poor'];
        $classMap      = [1 => 'Premium', 2 => 'High Grade', 3 => 'Commercial', 4 => 'Poor C', 5 => '1st Grade', 6 => '2nd Grade', 7 => 'Class R'];

        $rows = DB::table('qa_grading as a')
            ->join(DB::raw('(SELECT DISTINCT receipt_no, slaughter_date, vendor_no, vendor_name FROM receipts) as r'), function ($join) {
                $join->on('a.receipt_no', '=', 'r.receipt_no')
                     ->on('a.slaughter_date', '=', 'r.slaughter_date');
            })
            ->whereBetween(DB::raw('CAST(a.slaughter_date AS DATE)'), [$from, $to])
            ->orderBy('a.receipt_no')->orderBy('a.agg_no')
            ->select('r.vendor_no', 'r.vendor_name', 'a.receipt_no', 'a.agg_no',
                     'a.dentition', 'a.fat_cover', 'a.fat_color', 'a.meat_color',
                     'a.bruising', 'a.muscle_conformation', 'a.classification',
                     'a.classification_code', 'a.narration')
            ->get()
            ->map(fn($row) => [
                $row->vendor_no,
                $row->vendor_name,
                $row->receipt_no,
                $row->agg_no,
                $dentitionMap[$row->dentition]         ?? '--',
                $fatCoverMap[$row->fat_cover]          ?? '--',
                $fatColorMap[$row->fat_color]          ?? '--',
                $meatColorMap[$row->meat_color]        ?? '--',
                $bruisingMap[$row->bruising]           ?? '--',
                $muscleMap[$row->muscle_conformation]  ?? '--',
                $classMap[$row->classification]        ?? '--',
                $row->classification_code              ?? '--',
                $row->narration                        ?? '',
            ]);

        Session::put('qa_grading_export_data', $rows);

        return Excel::download(new QAGradingReportExport, "QA-Grading-Report-{$from}-to-{$to}.xlsx");
    }

    public function slaughterGradingReportExport(Request $request)
    {
        $from = $request->from_date;
        $to   = $request->to_date;

        // Pre-aggregate CDW per (receipt_no, item_code) to prevent row multiplication
        // when joined against qa_grading's many rows.
        $rows = DB::table('qa_grading as a')
            ->join(
                DB::raw('(SELECT receipt_no, slaughter_date, vendor_no, vendor_name, received_qty FROM receipts) as r'),
                function ($join) {
                    $join->on('a.receipt_no', '=', 'r.receipt_no')
                         ->on('a.slaughter_date', '=', 'r.slaughter_date');
                }
            )
            ->leftJoin(
                // Sum CDW once per (receipt_no, item_code) — scoped to the same date
                // range as the outer filter so historical re-entries don't inflate totals.
                DB::raw("(SELECT receipt_no, item_code, ROUND(SUM(settlement_weight), 2) AS cdw
                          FROM slaughter_data
                          WHERE (deleted IS NULL OR deleted != 1)
                            AND CAST(created_at AS DATE) BETWEEN '{$from}' AND '{$to}'
                          GROUP BY receipt_no, item_code) AS sd"),
                function ($join) {
                    $join->on('a.receipt_no', '=', 'sd.receipt_no')
                         ->on('a.item_code', '=', 'sd.item_code');
                }
            )
            ->whereBetween(DB::raw('CAST(a.slaughter_date AS DATE)'), [$from, $to])
            ->groupBy('r.vendor_no', 'r.vendor_name', 'a.receipt_no', 'r.received_qty')
            ->orderBy('a.receipt_no')
            ->select(
                'r.vendor_no', 'r.vendor_name', 'a.receipt_no',
                'r.received_qty',                               // source-of-truth from receipts
                DB::raw('MAX(sd.cdw) AS total_cdw'),            // unique per receipt after pre-agg
                DB::raw('SUM(CASE WHEN a.classification = 1 THEN 1 ELSE 0 END) as premium'),
                DB::raw('SUM(CASE WHEN a.classification = 2 THEN 1 ELSE 0 END) as high_grade'),
                DB::raw('SUM(CASE WHEN a.classification = 3 THEN 1 ELSE 0 END) as commercial'),
                DB::raw('SUM(CASE WHEN a.classification = 4 THEN 1 ELSE 0 END) as poor_c'),
                DB::raw('SUM(CASE WHEN a.classification = 5 THEN 1 ELSE 0 END) as first_grade'),
                DB::raw('SUM(CASE WHEN a.classification = 6 THEN 1 ELSE 0 END) as second_grade'),
                DB::raw('SUM(CASE WHEN a.classification = 7 THEN 1 ELSE 0 END) as class_r'),
                DB::raw('SUM(CASE WHEN a.is_downgraded = 1 THEN 1 ELSE 0 END) as downgraded_count')
            )
            ->get()
            ->map(fn($row) => [
                $row->vendor_no, $row->vendor_name, $row->receipt_no,
                $row->received_qty, $row->total_cdw,
                $row->premium, $row->high_grade, $row->commercial, $row->poor_c,
                $row->first_grade, $row->second_grade, $row->class_r,
                $row->downgraded_count,
            ]);

        Session::put('slaughter_grading_export_data', $rows);

        return Excel::download(new SlaughterGradingReportExport, "Slaughter-Grading-Summary-{$from}-to-{$to}.xlsx");
    }
}
