<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DebtAgingExport implements WithHeadings, WithMapping, WithTitle
{
    protected $agingData;
    protected $type;

    public function __construct($agingData, $type = 'customer')
    {
        $this->agingData = $agingData;
        $this->type = $type;
    }

    public function collection()
    {
        return collect($this->agingData);
    }

    public function headings(): array
    {
        $nameColumn = $this->type === 'customer' ? 'العميل' : 'المورد';

        return [
            $nameColumn,
            '0-30 يوم',
            '31-60 يوم',
            '61-90 يوم',
            '+90 يوم',
            'الإجمالي',
        ];
    }

    public function map($item): array
    {
        $entity = $this->type === 'customer' ? $item['customer'] : $item['supplier'];

        return [
            $entity->name,
            number_format($item['aging']['0-30'], 2),
            number_format($item['aging']['31-60'], 2),
            number_format($item['aging']['61-90'], 2),
            number_format($item['aging']['90+'], 2),
            number_format($item['total'], 2),
        ];
    }

    public function title(): string
    {
        return $this->type === 'customer' ? 'أعمار ديون العملاء' : 'أعمار ديون الموردين';
    }
}
