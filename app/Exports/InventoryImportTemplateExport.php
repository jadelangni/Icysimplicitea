<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryImportTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return ['Item Name', 'Qty', 'Unit', 'Branch'];
    }

    public function array(): array
    {
        return [
            ['Milk', 20, 'L', ''],
            ['Sugar', 10, 'KG', ''],
            ['C2', 50, 'PCS', ''],
        ];
    }

    public function title(): string
    {
        return 'Inventory Import';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
