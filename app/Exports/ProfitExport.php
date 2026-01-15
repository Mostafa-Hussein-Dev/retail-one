<?php

namespace App\Exports;

use App\Models\SaleItem;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class ProfitExport implements WithHeadings, WithMapping, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'المنتج',
            'الكمية',
            'الإيرادات',
            'التكلفة',
            'الربح',
            'هامش الربح (%)',
        ];
    }

    public function map($item): array
    {
        return [
            $item['name'],
            number_format($item['total_quantity'], 2),
            number_format($item['total_revenue'], 2),
            number_format($item['total_cost'], 2),
            number_format($item['profit'], 2),
            number_format($item['margin'], 2) . '%',
        ];
    }

    public function title(): string
    {
        return 'تقرير الأرباح';
    }
}
