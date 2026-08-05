<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SlaughterGradingReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect(Session::get('slaughter_grading_export_data'));
    }

    public function headings(): array
    {
        return [
            'Vendor No',
            'Vendor Name',
            'Receipt No',
            'Qty Supplied',
            'Total CDW (kg)',
            'Premium',
            'High Grade',
            'Commercial',
            'Poor C',
            '1st Grade',
            '2nd Grade',
            'Class R',
            'Downgraded Count',
        ];
    }
}
