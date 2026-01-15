<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LowStockExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected $threshold;

    public function __construct($threshold = 10)
    {
        $this->threshold = $threshold;
    }

    public function query()
    {
        return Product::with(['category', 'supplier'])
            ->where('quantity', '<', $this->threshold)
            ->orderBy('quantity', 'asc');
    }

    public function headings(): array
    {
        return [
            'المنتج',
            'الفئة',
            'المورد',
            'الكمية الحالية',
            'الحد الأدنى',
            'سعر التكلفة',
            'سعر البيع',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->category?->name ?? '-',
            $product->supplier?->name ?? '-',
            $product->quantity,
            $this->threshold,
            number_format($product->cost_price, 2),
            number_format($product->selling_price, 2),
        ];
    }

    public function title(): string
    {
        return 'تنبيهات المخزون المنخفض';
    }
}
