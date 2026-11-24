<?php

namespace App\Exports;

use App\Models\CarcassGradingService;
use App\Models\LambGradingService;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SlaughterSummaryExport implements FromCollection, WithHeadings
{
    /**
     * Column alias => system grade label, as stored in
     * qa_grading.auto_classification. One count column per grade, in this order.
     */
    public const SYSTEM_GRADES = [
        'premium'      => CarcassGradingService::LABELS[CarcassGradingService::PREMIUM],
        'high_grade'   => CarcassGradingService::LABELS[CarcassGradingService::HIGHGRADE],
        'faq'          => CarcassGradingService::LABELS[CarcassGradingService::FAQ],
        'standard'     => CarcassGradingService::LABELS[CarcassGradingService::STANDARD],
        'commercial'   => CarcassGradingService::LABELS[CarcassGradingService::COMMERCIAL],
        'poor_c'       => CarcassGradingService::LABELS[CarcassGradingService::POOR_C],
        'first_grade'  => LambGradingService::LABELS[LambGradingService::CLASS_1],
        'second_grade' => LambGradingService::LABELS[LambGradingService::CLASS_2],
        'class_r'      => LambGradingService::LABELS[LambGradingService::CLASS_R],
        'condemned'    => CarcassGradingService::LABELS[CarcassGradingService::CONDEMNED],
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $export = Session::get('session_export_data');
        return $export;
    }

    public function headings(): array
    {
        return array_merge([
            'ReceiptNo',
            'SupplierCode',
            'SupplierName',
            'ProductCode',
            'First Weighing Date',
            'Last Weighing Date',
            'No. Weighed',
            'Total Net Weight',
            'CDW Weight(97.5%)',
        ], array_values(self::SYSTEM_GRADES), [
            'Not graded yet',
        ]);
    }
}
