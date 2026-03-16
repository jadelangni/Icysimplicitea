<?php

namespace App\Exports;

use App\Models\Ingredient;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IngredientInventoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $branches;

    public function __construct()
    {
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();
    }

    public function collection()
    {
        return Ingredient::with(['inventories.branch'])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        $headers = ['Ingredient Name', 'Unit'];
        
        foreach ($this->branches as $branch) {
            $headers[] = $branch->name . ' - Quantity';
            $headers[] = $branch->name . ' - Min Stock';
            $headers[] = $branch->name . ' - Status';
        }
        
        $headers[] = 'Total Quantity';
        $headers[] = 'Overall Status';
        
        return $headers;
    }

    public function map($ingredient): array
    {
        $row = [
            $ingredient->name,
            $ingredient->unit,
        ];

        $totalQty = 0;
        $hasLowStock = false;
        $hasOutOfStock = false;

        foreach ($this->branches as $branch) {
            $inventory = $ingredient->inventories->where('branch_id', $branch->id)->first();
            $qty = $inventory ? (float)$inventory->quantity : 0;
            $minStock = $inventory ? (float)$inventory->min_stock_level : 10;

            $totalQty += $qty;

            $status = 'In Stock';
            if ($qty <= 0) {
                $status = 'Out of Stock';
                $hasOutOfStock = true;
            } elseif ($qty <= $minStock) {
                $status = 'Low Stock';
                $hasLowStock = true;
            }

            $row[] = $qty;
            $row[] = $minStock;
            $row[] = $status;
        }

        $row[] = $totalQty;
        $row[] = $hasOutOfStock ? 'Out of Stock' : ($hasLowStock ? 'Low Stock' : 'In Stock');

        return $row;
    }

    public function title(): string
    {
        return 'Ingredient Inventory';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
