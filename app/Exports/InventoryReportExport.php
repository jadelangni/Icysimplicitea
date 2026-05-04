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
    protected $branchName;

    public function __construct($branchId = null, $branchName = null)
    {
        $this->branchId = $branchId;
        $this->branchName = $branchName;
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
        return [
            'Ingredient Name',
            'Unit',
            'Current Stock',
            'Stock Status',
            'Total Value',
            'Branch',
            'Last Updated'
        ];
    }

    public function map($ingredient): array
    {
        $inventory = $ingredient->inventories->where('branch_id', $this->branchId)->first();
        $qty = $inventory ? (float)$inventory->quantity : 0;
        $unitCost = $inventory ? (float)$inventory->unit_cost : 0;
        $totalValue = $qty * $unitCost;
        
        // Determine stock status
        $minStock = $inventory ? ($inventory->min_stock_level ?? $ingredient->min_stock_level) : $ingredient->min_stock_level;
        if ($qty <= 0) {
            $status = 'No Stock';
        } elseif ($qty <= $minStock) {
            $status = 'Low Stock';
        } else {
            $status = 'In Stock';
        }
        
        // Get branch name
        $branch = $inventory ? $inventory->branch : null;
        $branchName = $branch ? $branch->name : 'N/A';
        
        // Get last updated
        $lastUpdated = $inventory && $inventory->updated_at ? $inventory->updated_at->format('Y-m-d H:i:s') : 'N/A';
        
        return [
            $ingredient->name,
            $ingredient->unit,
            $qty,
            $status,
            number_format($totalValue, 2),
            $branchName,
            $lastUpdated
        ];
    }

    public function title(): string
    {
        return $this->branchName ? $this->branchName . ' Inventory Report' : 'Inventory Report';
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
