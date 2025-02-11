<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class IDTLinesExport implements FromCollection, withHeadings
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
                'IDT No.',
                'Item Code',
                'Item Description',
                'Total Weight',
                'Total Pieces',
                'From Location',
                'To Location',
                'Issuer',
                'Narration',
                'Recorded Date Time',
            ];
    }
}
