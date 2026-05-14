<?php

namespace App\Services;

use App\Models\IngredientInventory;
use App\Models\Inventory;
use App\Models\SalesItem;
use Carbon\Carbon;

class InventoryForecastService
{
    /**
     * Build a branch-level predictive inventory forecast.
     *
     * @return array{productForecasts: \Illuminate\Support\Collection, ingredientForecasts: \Illuminate\Support\Collection, summary: array<string, int|float>}
     */
    public function generateForBranch(int $branchId, int $lookbackDays = 7, int $leadTimeDays = 7, int $targetCoverDays = 14): array
    {
        $lookbackDays = max(7, $lookbackDays);
        $leadTimeDays = max(1, $leadTimeDays);
        $targetCoverDays = max($leadTimeDays, $targetCoverDays);

        $lookbackStart = Carbon::now()->subDays($lookbackDays)->startOfDay();
        $lookbackEnd = Carbon::now()->endOfDay();

        $directSalesTotals = SalesItem::query()
            ->whereHas('sale', function ($query) use ($branchId, $lookbackStart, $lookbackEnd) {
                $query->where('branch_id', $branchId)
                    ->whereBetween('created_at', [$lookbackStart, $lookbackEnd]);
            })
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where(function ($typeQuery) {
                        $typeQuery->whereNull('product_type')
                            ->orWhere('product_type', 'direct');
                    });
            })
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        $productForecasts = Inventory::query()
            ->with(['product.category'])
            ->where('branch_id', $branchId)
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where(function ($typeQuery) {
                        $typeQuery->whereNull('product_type')
                            ->orWhere('product_type', 'direct');
                    });
            })
            ->get()
            ->map(function (Inventory $inventory) use ($directSalesTotals, $lookbackDays, $leadTimeDays, $targetCoverDays) {
                $totalSold = (float) ($directSalesTotals[$inventory->product_id] ?? 0);
                $dailyRate = $lookbackDays > 0 ? $totalSold / $lookbackDays : 0;
                $quantity = (float) $inventory->quantity;
                $minStockLevel = (float) $inventory->min_stock_level;
                $daysUntilStockout = $dailyRate > 0 ? $quantity / $dailyRate : null;
                $projectedStockoutDate = $daysUntilStockout !== null
                    ? Carbon::now()->addDays((int) ceil($daysUntilStockout))
                    : null;
                $reorderPoint = ($dailyRate * ($leadTimeDays + $targetCoverDays)) + $minStockLevel;
                $suggestedReorderQty = max(0, (int) ceil($reorderPoint - $quantity));
                $riskLabel = $this->riskLabel($daysUntilStockout, $quantity, $minStockLevel, $targetCoverDays);

                // Short human-friendly reason for restock recommendations
                $restockReason = '';
                if ($suggestedReorderQty > 0) {
                    if ($dailyRate <= 0) {
                        $restockReason = 'No recent sales data - keep minimum stock available';
                    } elseif ($daysUntilStockout !== null && $daysUntilStockout <= 3) {
                        $restockReason = 'Stock is running out soon - reorder now';
                    } elseif ($daysUntilStockout !== null && $daysUntilStockout <= $targetCoverDays) {
                        $restockReason = 'Stock may not last through the next few days';
                    } elseif ($dailyRate > ($quantity / max(1, (float) $lookbackDays / 2))) {
                        $restockReason = 'Recent sales are using stock faster than usual';
                    } else {
                        $restockReason = 'Add stock to keep enough supply on hand';
                    }
                }

                return (object) [
                    'id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'name' => $inventory->product?->name ?? 'Unknown Product',
                    'category' => $inventory->product?->category?->name ?? 'Uncategorized',
                    'current_quantity' => $quantity,
                    'min_stock_level' => $minStockLevel,
                    'total_sold' => $totalSold,
                    'daily_rate' => $dailyRate,
                    'days_until_stockout' => $daysUntilStockout,
                    'projected_stockout_date' => $projectedStockoutDate,
                    'suggested_reorder_qty' => $suggestedReorderQty,
                    'risk_label' => $riskLabel,
                    'restock_reason' => $restockReason,
                ];
            })
            ->sortBy(function ($forecast) {
                return $forecast->days_until_stockout === null ? 99999 : $forecast->days_until_stockout;
            })
            ->values();

        $ingredientConsumptionTotals = $this->getIngredientConsumptionTotals($branchId, $lookbackStart, $lookbackEnd);

        $ingredientForecasts = IngredientInventory::query()
            ->with(['ingredient'])
            ->where('branch_id', $branchId)
            ->whereHas('ingredient', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->map(function (IngredientInventory $inventory) use ($ingredientConsumptionTotals, $lookbackDays, $leadTimeDays, $targetCoverDays) {
                $totalConsumed = (float) ($ingredientConsumptionTotals[$inventory->ingredient_id] ?? 0);
                $dailyRate = $lookbackDays > 0 ? $totalConsumed / $lookbackDays : 0;
                $quantity = (float) $inventory->quantity;
                $minStockLevel = (float) $inventory->min_stock_level;
                $daysUntilStockout = $dailyRate > 0 ? $quantity / $dailyRate : null;
                $projectedStockoutDate = $daysUntilStockout !== null
                    ? Carbon::now()->addDays((int) ceil($daysUntilStockout))
                    : null;
                $reorderPoint = ($dailyRate * ($leadTimeDays + $targetCoverDays)) + $minStockLevel;
                $suggestedReorderQty = max(0, (int) ceil($reorderPoint - $quantity));
                $riskLabel = $this->riskLabel($daysUntilStockout, $quantity, $minStockLevel, $targetCoverDays);

                // Short human-friendly reason for restock recommendations
                $restockReason = '';
                if ($suggestedReorderQty > 0) {
                    if ($dailyRate <= 0) {
                        $restockReason = 'No recent usage data - keep minimum stock available';
                    } elseif ($daysUntilStockout !== null && $daysUntilStockout <= 3) {
                        $restockReason = 'Stock is running out soon - reorder now';
                    } elseif ($daysUntilStockout !== null && $daysUntilStockout <= $targetCoverDays) {
                        $restockReason = 'Stock may not last through the next few days';
                    } elseif ($dailyRate > ($quantity / max(1, (float) $lookbackDays / 2))) {
                        $restockReason = 'Recent sales are using stock faster than usual';
                    } else {
                        $restockReason = 'Add stock to keep enough supply on hand';
                    }
                }

                return (object) [
                    'id' => $inventory->id,
                    'ingredient_id' => $inventory->ingredient_id,
                    'name' => $inventory->ingredient?->name ?? 'Unknown Ingredient',
                    'unit' => $inventory->ingredient?->unit ?? '',
                    'current_quantity' => $quantity,
                    'min_stock_level' => $minStockLevel,
                    'total_consumed' => $totalConsumed,
                    'daily_rate' => $dailyRate,
                    'days_until_stockout' => $daysUntilStockout,
                    'projected_stockout_date' => $projectedStockoutDate,
                    'suggested_reorder_qty' => $suggestedReorderQty,
                    'risk_label' => $riskLabel,
                    'restock_reason' => $restockReason,
                ];
            })
            ->sortBy(function ($forecast) {
                return $forecast->days_until_stockout === null ? 99999 : $forecast->days_until_stockout;
            })
            ->values();

        $summary = [
            'lookback_days' => $lookbackDays,
            'lead_time_days' => $leadTimeDays,
            'target_cover_days' => $targetCoverDays,
            'product_items_at_risk' => $productForecasts->filter(fn ($item) => $item->days_until_stockout !== null && $item->days_until_stockout <= $targetCoverDays)->count(),
            'ingredient_items_at_risk' => $ingredientForecasts->filter(fn ($item) => $item->days_until_stockout !== null && $item->days_until_stockout <= $targetCoverDays)->count(),
            'product_items_with_history' => $productForecasts->filter(fn ($item) => $item->daily_rate > 0)->count(),
            'ingredient_items_with_history' => $ingredientForecasts->filter(fn ($item) => $item->daily_rate > 0)->count(),
        ];

        return [
            'productForecasts' => $productForecasts,
            'ingredientForecasts' => $ingredientForecasts,
            'summary' => $summary,
        ];
    }

    /**
     * Aggregate ingredient usage from sales history and recipe definitions.
     *
     * @return array<int, float>
     */
    private function getIngredientConsumptionTotals(int $branchId, Carbon $lookbackStart, Carbon $lookbackEnd): array
    {
        $ingredientConsumptionTotals = [];

        $salesItems = SalesItem::query()
            ->whereHas('sale', function ($query) use ($branchId, $lookbackStart, $lookbackEnd) {
                $query->where('branch_id', $branchId)
                    ->whereBetween('created_at', [$lookbackStart, $lookbackEnd]);
            })
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('product_type', 'composite');
            })
            ->with(['product.ingredients'])
            ->get();

        foreach ($salesItems as $salesItem) {
            $product = $salesItem->product;

            if (!$product) {
                continue;
            }

            foreach ($product->ingredients as $ingredient) {
                $requiredPerUnit = (float) ($ingredient->pivot->quantity_required ?? 0);
                if ($requiredPerUnit <= 0) {
                    continue;
                }

                $consumed = $ingredient->convertRecipeQuantityToStockUnit(
                    $requiredPerUnit * (int) $salesItem->quantity,
                    $ingredient->pivot->unit ?? null
                );

                if ($consumed === null) {
                    continue;
                }

                $ingredientConsumptionTotals[$ingredient->id] = ($ingredientConsumptionTotals[$ingredient->id] ?? 0) + $consumed;
            }
        }

        return $ingredientConsumptionTotals;
    }

    private function riskLabel(?float $daysUntilStockout, float $quantity, float $minStockLevel, int $targetCoverDays): string
    {
        if ($quantity <= 0) {
            return 'Critical';
        }

        if ($daysUntilStockout !== null && $daysUntilStockout <= 3) {
            return 'Critical';
        }

        if ($daysUntilStockout !== null && $daysUntilStockout <= $targetCoverDays) {
            return 'At Risk';
        }

        if ($quantity <= $minStockLevel) {
            return 'Low Stock';
        }

        return 'Healthy';
    }
}
