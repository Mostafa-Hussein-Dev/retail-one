<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Http\Request;

class SalesExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected $request;

    public function __construct(array $request = [])
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Sale::with(['customer', 'user']);

        // Date filters
        if (!empty($this->request['date_from'])) {
            $query->where('sale_date', '>=', $this->request['date_from']);
        }
        if (!empty($this->request['date_to'])) {
            $query->where('sale_date', '<=', $this->request['date_to']);
        }

        // Payment method filter
        if (!empty($this->request['payment_method'])) {
            $query->where('payment_method', $this->request['payment_method']);
        }

        // Customer filter
        if (!empty($this->request['customer_id'])) {
            $query->where('customer_id', $this->request['customer_id']);
        }

        // Exclude voided sales
        $query->where('is_voided', false);

        return $query->orderBy('sale_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'رقم الإيصال',
            'التاريخ',
            'العميل',
            'الإجمالي',
            'طريقة الدفع',
            'الموظف',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->receipt_number,
            $sale->sale_date?->format('Y-m-d H:i') ?? '-',
            $sale->customer?->name ?? '-',
            number_format($sale->total_amount, 2),
            $sale->getPaymentMethodText(),
            $sale->user?->name ?? '-',
        ];
    }

    public function title(): string
    {
        return 'تقرير المبيعات';
    }
}
