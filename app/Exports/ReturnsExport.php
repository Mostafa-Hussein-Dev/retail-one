<?php

namespace App\Exports;

use App\Models\ReturnModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Http\Request;

class ReturnsExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected $request;

    public function __construct(array $request = [])
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = ReturnModel::with(['sale.customer', 'items.product']);

        // Date filters
        if (!empty($this->request['date_from'])) {
            $query->where('return_date', '>=', $this->request['date_from']);
        }
        if (!empty($this->request['date_to'])) {
            $query->where('return_date', '<=', $this->request['date_to']);
        }

        // Payment method filter
        if (!empty($this->request['payment_method'])) {
            $query->where('payment_method', $this->request['payment_method']);
        }

        return $query->orderBy('return_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'رقم المرتج',
            'التاريخ',
            'رقم الإيصال',
            'العميل',
            'الإجمالي',
            'طريقة الاسترداد',
            'السبب',
        ];
    }

    public function map($return): array
    {
        return [
            $return->return_number,
            $return->return_date->format('Y-m-d H:i'),
            $return->sale?->receipt_number ?? '-',
            $return->sale?->customer?->name ?? '-',
            number_format($return->total_return_amount, 2),
            $return->getPaymentMethodText(),
            $return->reason,
        ];
    }

    public function title(): string
    {
        return 'تقرير المرتجعات';
    }
}
