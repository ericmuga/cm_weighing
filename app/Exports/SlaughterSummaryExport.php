<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SlaughterSummaryExport implements FromCollection, withHeadings
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
                'ReceiptNo', 'SupplierCode', 'SupplierName', 'ProductCode', 'No. Weighed', 'Total Net Weight', 'CDW Weight(97.5%)'
            ];
    }
}
