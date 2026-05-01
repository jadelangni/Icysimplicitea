<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductInventoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $branches;

    public function __construct()
    {
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();
    }

    public function collection()
    {
        return Product::with(['category', 'inventory.branch'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        $headers = ['Product Name', 'Category', 'Price (₱)'];
        
        foreach ($this->branches as $branch) {
            $headers[] = $branch->name . ' - Stock';
            $headers[] = $branch->name . ' - Min Stock';
            $headers[] = $branch->name . ' - Status';
        }
        
        $headers[] = 'Total Stock';
        $headers[] = 'Overall Status';
        
        return $headers;
    }

    public function map($product): array
    {
        $row = [
            $product->name,
            $product->category->name ?? 'Uncategorized',
            number_format($product->price, 2),
        ];

        $totalStock = 0;
        $hasLowStock = false;
        $hasOutOfStock = false;

        foreach ($this->branches as $branch) {
            $inventory = $product->inventory->where('branch_id', $branch->id)->first();
            $qty = $inventory ? $inventory->quantity : 0;
            $minStock = $inventory ? $inventory->min_stock_level : 10;

            $totalStock += $qty;

            $status = 'In Stock';
            if ($qty <= 0) {
                $status = 'No Stock';
                $hasOutOfStock = true;
            } elseif ($qty <= $minStock) {
                $status = 'Low Stock';
                $hasLowStock = true;
            }

            $row[] = $qty;
            $row[] = $minStock;
            $row[] = $status;
        }

        $row[] = $totalStock;
        $row[] = $hasOutOfStock ? 'No Stock' : ($hasLowStock ? 'Low Stock' : 'In Stock');

        return $row;
    }

    public function title(): string
    {
        return 'Product Inventory';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
