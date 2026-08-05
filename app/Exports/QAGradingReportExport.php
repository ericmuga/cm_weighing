<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QAGradingReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect(Session::get('qa_grading_export_data'));
    }

    public function headings(): array
    {
        return [
            'Vendor No',
            'Vendor Name',
            'Receipt No',
            'Carcass No (Agg)',
            'Dentition',
            'Fat Cover',
            'Fat Color',
            'Meat Color',
            'Bruising',
            'Muscles',
            'QA Grade',
            'Weight Classification',
            'Narration',
        ];
    }
}
