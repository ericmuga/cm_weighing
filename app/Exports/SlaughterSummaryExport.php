<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SlaughterSummaryExport implements FromCollection, WithHeadings
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
        return [
            'ReceiptNo',
            'SupplierCode',
            'SupplierName',
            'ProductCode',
            'First Weighing DateTime',
            'Last Weighing DateTime',
            'No. Weighed',
            'Total Net Weight',
            'CDW Weight(97.5%)'
        ];
    }
}
