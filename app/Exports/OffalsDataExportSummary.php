<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OffalsDataExportSummary implements FromArray, WithHeadings
{
    private array $columns;
    private array $rows;

    public function __construct()
    {
        $export = Session::get('session_export_data') ?? collect();

        $this->columns = $export
            ->pluck('product_name')
            ->filter() // drop null product names to avoid empty heading
            ->unique()
            ->sort()
            ->values()
            ->all();

        $pivot = [];

        foreach ($export as $row) {
            $customer = $row->cust ?? 'Unknown';
            $productName = $row->product_name ?? 'Unknown Product';

            if (!isset($pivot[$customer])) {
                $pivot[$customer] = array_fill_keys($this->columns, 0.0);
            }

            $pivot[$customer][$productName] = ($pivot[$customer][$productName] ?? 0.0) + (float) $row->total_net_weight;
        }

        $this->rows = [];

        foreach ($pivot as $customer => $weights) {
            $values = [];
            foreach ($this->columns as $column) {
                $values[] = isset($weights[$column]) && $weights[$column] !== null
                    ? (float) $weights[$column]
                    : 0.0;
            }

            $this->rows[] = array_merge([$customer], $values);
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_merge(['Customer Name'], $this->columns);
    }
}
