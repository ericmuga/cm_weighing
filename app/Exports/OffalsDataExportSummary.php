<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OffalsDataExportSummary implements FromCollection, WithHeadings
{
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
        return
            [
                'Customer Name','Product Code', 'Product Name','Total Net Weight', 'Total Records Count'
            ];
    }
}
