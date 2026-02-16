<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DeboningReportExport implements FromCollection, WithHeadings
{
    /**
     * @var \Illuminate\Support\Collection
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
                'id' => $row->id,
                'product_code' => $row->product_code,
                'product_name' => $row->description,
                'scale_weight_kg' => (float) $row->scale_reading,
                'net_weight_kg' => (float) $row->netweight,
                'pieces' => $row->no_of_pieces,
                'process_code' => $row->process_code,
                'narration' => $row->narration,
                'is_manual' => $row->is_manual ? 'Yes' : 'No',
                'created_at' => $row->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sno',
            'Product Code',
            'Product Name',
            'Scale Weight (kg)',
            'Net Weight (kg)',
            'Pieces',
            'Process Code',
            'Narration',
            'Manually Recorded',
            'Created At',
        ];
    }
}
