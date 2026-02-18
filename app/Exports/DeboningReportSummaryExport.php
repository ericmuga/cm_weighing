<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeboningReportSummaryExport implements FromCollection, WithHeadings
{
    /**
     * Aggregated rows to export.
     */
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                'product_code' => $row->product_code,
                'product_name' => $row->description,
                'total_net_weight_kg' => (float) $row->total_netweight,
                'total_pieces' => $row->total_pieces,
                'total_records' => $row->total_records,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Total Net Weight (kg)',
            'Total Pieces',
            'Total Records',
        ];
    }
}
