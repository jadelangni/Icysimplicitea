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

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $branchId;
    protected $isAll;
    protected $branches;

    public function __construct($branchId = null)
    {
        $this->branchId = $branchId;
        $this->isAll = $branchId === 'all' || $branchId === null;
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();
    }

    public function collection()
    {
        $query = Ingredient::with(['inventories.branch'])
            ->where('is_active', true)
            ->orderBy('name');

        if (!$this->isAll) {
            $query->with(['inventories' => function ($q) {
                $q->where('branch_id', $this->branchId);
            }]);
        }

        return $query->get()->sortBy(function ($ingredient) {
            return sprintf('%02d-%s', $this->getStatusPriority($ingredient), strtolower($ingredient->name));
        })->values();
    }

    public function headings(): array
    {
        $headers = ['Ingredient Name', 'Unit', 'Min Stock Level'];

        if ($this->isAll) {
            foreach ($this->branches as $branch) {
                $headers[] = $branch->name . ' - Qty';
                $headers[] = $branch->name . ' - Status';
            }
            $headers[] = 'Total Quantity';
        } else {
            $headers[] = 'Quantity';
            $headers[] = 'Unit Cost (₱)';
            $headers[] = 'Total Value (₱)';
        }

        $headers[] = 'Overall Status';
        $headers[] = 'Last Updated';
        return $headers;
    }

    public function map($ingredient): array
    {
        $row = [
            $ingredient->name,
            $ingredient->unit,
            $ingredient->min_stock_level,
        ];

        if ($this->isAll) {
            $totalQty = 0;
            $hasLowStock = false;
            $hasOutOfStock = false;

            foreach ($this->branches as $branch) {
                $inventory = $ingredient->inventories->where('branch_id', $branch->id)->first();
                $qty = $inventory ? (float)$inventory->quantity : 0;
                $minStock = $inventory ? ($inventory->min_stock_level ?? $ingredient->min_stock_level) : $ingredient->min_stock_level;
                
                $totalQty += $qty;

                $status = 'In Stock';
                if ($qty <= 0) {
                    $status = 'No Stock';
                    $hasOutOfStock = true;
                } elseif ($qty <= $minStock) {
                    $status = 'Low Stock';
                    $hasLowStock = true;
                }

                $row[] = $qty;
                $row[] = $status;
            }

            $row[] = $totalQty;
            $row[] = $hasOutOfStock ? 'No Stock' : ($hasLowStock ? 'Low Stock' : 'In Stock');
        } else {
            $inventory = $ingredient->inventories->first();
            $qty = $inventory ? (float)$inventory->quantity : 0;
            $unitCost = $inventory ? (float)$inventory->unit_cost : 0;
            $totalValue = $qty * $unitCost;

            $row[] = $qty;
            $row[] = number_format($unitCost, 2);
            $row[] = number_format($totalValue, 2);
            $row[] = $this->getReportStatus($ingredient);
        }

        $row[] = $this->getLastUpdated($ingredient);

        return $row;
    }

    public function title(): string
    {
        return 'Inventory Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    protected function getReportStatus($ingredient): string
    {
        if ($this->isAll) {
            $hasOutOfStock = $ingredient->inventories->isEmpty() || $ingredient->inventories->contains(function ($inv) {
                return $inv->quantity <= 0;
            });

            if ($hasOutOfStock) {
                return 'No Stock';
            }

            $hasLowStock = $ingredient->inventories->contains(function ($inv) use ($ingredient) {
                return $inv->quantity > 0 && $inv->quantity <= ($inv->min_stock_level ?? $ingredient->min_stock_level);
            });

            return $hasLowStock ? 'Low Stock' : 'In Stock';
        }

        $inventory = $ingredient->inventories->first();
        if (!$inventory) {
            return 'No Stock';
        }

        return $ingredient->getStatusForBranch($this->branchId);
    }

    protected function getStatusPriority($ingredient): int
    {
        return match ($this->getReportStatus($ingredient)) {
            'No Stock' => 0,
            'Low Stock' => 1,
            default => 2,
        };
    }

    protected function getLastUpdated($ingredient): string
    {
        if ($this->isAll) {
            $lastUpdated = $ingredient->inventories->sortByDesc('updated_at')->first()?->updated_at;
        } else {
            $lastUpdated = $ingredient->inventories->first()?->updated_at;
        }

        return $lastUpdated ? $lastUpdated->format('M d, Y h:i A') : 'N/A';
    }
}
