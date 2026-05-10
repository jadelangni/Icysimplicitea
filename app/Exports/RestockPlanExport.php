<?php

namespace App\Exports;

use App\Services\InventoryForecastService;
use Illuminate\Contracts\Collection as CollectionContract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RestockPlanExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected InventoryForecastService $forecastService;
    protected int $branchId;
    protected int $lookbackDays;
    protected int $leadTimeDays;
    protected int $targetCoverDays;

    public function __construct(InventoryForecastService $forecastService, int $branchId, int $lookbackDays = 30, int $leadTimeDays = 7, int $targetCoverDays = 14)
    {
        $this->forecastService = $forecastService;
        $this->branchId = $branchId;
        $this->lookbackDays = $lookbackDays;
        $this->leadTimeDays = $leadTimeDays;
        $this->targetCoverDays = $targetCoverDays;
    }

    public function collection()
    {
        $result = $this->forecastService->generateForBranch(
            $this->branchId,
            $this->lookbackDays,
            $this->leadTimeDays,
            $this->targetCoverDays
        );

        $ingredientRestock = collect($result['ingredientForecasts'])->filter(fn($it) => $it->suggested_reorder_qty > 0);
        $productRestock = collect($result['productForecasts'])->filter(fn($it) => $it->suggested_reorder_qty > 0);

        // Combine and tag type
        $rows = collect();

        foreach ($ingredientRestock as $it) {
            $rows->push((object) array_merge((array)$it, ['type' => 'Ingredient']));
        }

        foreach ($productRestock as $p) {
            $rows->push((object) array_merge((array)$p, ['type' => 'Product']));
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Type',
            'Name',
            'Suggested',
            'Current',
            'Unit',
            'Reason',
            'Risk',
        ];
    }

    public function map($row): array
    {
        // Ingredients have unit, products may not
        $unit = $row->unit ?? '';
        $suggested = isset($row->suggested_reorder_qty) ? $row->suggested_reorder_qty : '';
        $current = $row->current_quantity ?? '';
        $reason = $row->restock_reason ?? '';
        $risk = $row->risk_label ?? '';

        return [
            $row->type ?? '',
            $row->name ?? '',
            $suggested,
            $current,
            $unit,
            $reason,
            $risk,
        ];
    }

    public function title(): string
    {
        return 'Restock Plan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
