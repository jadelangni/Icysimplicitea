<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\IngredientInventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class InventorySyncService
{
    /**
     * Process inventory deductions for a completed sale.
     * Uses database transactions and row locking to prevent race conditions.
     *
     * @param Sale $sale The completed sale
     * @return array Result with success status, deductions made, and any alerts
     */
    public function processSaleDeductions(Sale $sale): array
    {
        $result = [
            'success' => false,
            'deductions' => [],
            'low_stock_alerts' => [],
            'errors' => []
        ];

        // Skip if already synced
        if ($sale->inventory_synced) {
            $result['success'] = true;
            $result['message'] = 'Inventory already synced for this sale';
            return $result;
        }

        try {
            DB::transaction(function () use ($sale, &$result) {
                $sale->load('salesItems.product.ingredients', 'salesItems.product.category');
                
                foreach ($sale->salesItems as $saleItem) {
                    $product = $saleItem->product;
                    $quantity = $saleItem->quantity;
                    $branchId = $sale->branch_id;

                    if ($product->product_type === 'direct') {
                        // Direct product (finished goods): Deduct from product inventory
                        $deduction = $this->deductProductInventory($product, $branchId, $quantity, $sale);
                        if ($deduction['success']) {
                            $result['deductions'][] = $deduction;
                            if ($deduction['is_low_stock']) {
                                $result['low_stock_alerts'][] = [
                                    'type' => 'product',
                                    'name' => $product->name,
                                    'branch_id' => $branchId,
                                    'current_quantity' => $deduction['new_quantity'],
                                    'min_stock_level' => $deduction['min_stock_level']
                                ];
                            }
                        } else {
                            $result['errors'][] = $deduction['error'];
                        }
                    } else {
                        // Composite product (milk tea): Deduct from ingredients
                        $ingredientDeductions = $this->deductIngredients($product, $branchId, $quantity, $sale);
                        foreach ($ingredientDeductions as $deduction) {
                            if ($deduction['success']) {
                                $result['deductions'][] = $deduction;
                                if ($deduction['is_low_stock']) {
                                    $result['low_stock_alerts'][] = [
                                        'type' => 'ingredient',
                                        'name' => $deduction['ingredient_name'],
                                        'branch_id' => $branchId,
                                        'current_quantity' => $deduction['new_quantity'],
                                        'min_stock_level' => $deduction['min_stock_level'],
                                        'unit' => $deduction['unit']
                                    ];
                                }
                            } else {
                                $result['errors'][] = $deduction['error'];
                            }
                        }
                    }
                }

                // Mark sale as synced
                $sale->inventory_synced = true;
                $sale->save();

                $result['success'] = true;
            }, 5); // 5 attempts for deadlock retries

            // Store low stock alerts in cache for dashboard notifications
            if (!empty($result['low_stock_alerts'])) {
                $this->storeLowStockAlerts($result['low_stock_alerts'], $sale->branch_id);
            }

        } catch (\Exception $e) {
            Log::error('Inventory sync failed for sale #' . $sale->id . ': ' . $e->getMessage());
            $result['errors'][] = 'Failed to sync inventory: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Deduct from product inventory (for direct/finished goods).
     * Uses pessimistic locking to prevent race conditions.
     * Records stock movement for audit trail.
     */
    private function deductProductInventory(Product $product, int $branchId, int $quantity, ?Sale $sale = null): array
    {
        $inventory = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();

        if (!$inventory) {
            return [
                'success' => false,
                'error' => "No inventory record for {$product->name} at branch {$branchId}"
            ];
        }

        $quantityBefore = $inventory->quantity;
        $newQuantity = max(0, $inventory->quantity - $quantity);
        $inventory->quantity = $newQuantity;
        $inventory->save();

        // Record stock movement for audit trail
        StockMovement::record([
            'branch_id' => $branchId,
            'inventory_type' => StockMovement::INVENTORY_PRODUCT,
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_SALE,
            'quantity_before' => $quantityBefore,
            'quantity_change' => -$quantity,
            'quantity_after' => $newQuantity,
            'unit' => 'pcs',
            'reference_type' => $sale ? Sale::class : null,
            'reference_id' => $sale?->id,
            'user_id' => Auth::id(),
        ]);

        return [
            'success' => true,
            'type' => 'product',
            'product_name' => $product->name,
            'deducted' => $quantity,
            'new_quantity' => $newQuantity,
            'min_stock_level' => $inventory->min_stock_level,
            'is_low_stock' => $newQuantity <= $inventory->min_stock_level
        ];
    }

    /**
     * Deduct from ingredients based on product recipe (BOM).
     * Uses pessimistic locking to prevent race conditions.
     * Records stock movements for audit trail.
     */
    private function deductIngredients(Product $product, int $branchId, int $productQuantity, ?Sale $sale = null): array
    {
        $deductions = [];
        
        // Get the recipe for this product
        $recipe = $product->ingredients;

        if ($recipe->isEmpty()) {
            Log::warning("No recipe found for composite product: {$product->name} (ID: {$product->id})");
            return [[
                'success' => false,
                'error' => "No recipe defined for {$product->name}. Please set up ingredient requirements."
            ]];
        }

        foreach ($recipe as $ingredientPivot) {
            // Get the branch-specific inventory for this ingredient
            $ingredientInventory = IngredientInventory::where('ingredient_id', $ingredientPivot->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$ingredientInventory) {
                $deductions[] = [
                    'success' => false,
                    'error' => "Ingredient {$ingredientPivot->name} not found in inventory for branch {$branchId}"
                ];
                continue;
            }

            $amountToDeduct = $ingredientPivot->pivot->quantity_required * $productQuantity;
            $quantityBefore = $ingredientInventory->quantity;
            $newQuantity = max(0, $ingredientInventory->quantity - $amountToDeduct);
            $ingredientInventory->quantity = $newQuantity;
            $ingredientInventory->save();

            // Record stock movement for audit trail
            StockMovement::record([
                'branch_id' => $branchId,
                'inventory_type' => StockMovement::INVENTORY_INGREDIENT,
                'ingredient_id' => $ingredientPivot->id,
                'movement_type' => StockMovement::TYPE_SALE,
                'quantity_before' => $quantityBefore,
                'quantity_change' => -$amountToDeduct,
                'quantity_after' => $newQuantity,
                'unit' => $ingredientPivot->unit,
                'reference_type' => $sale ? Sale::class : null,
                'reference_id' => $sale?->id,
                'user_id' => Auth::id(),
            ]);

            $deductions[] = [
                'success' => true,
                'type' => 'ingredient',
                'ingredient_name' => $ingredientPivot->name,
                'ingredient_id' => $ingredientPivot->id,
                'deducted' => $amountToDeduct,
                'unit' => $ingredientPivot->unit,
                'new_quantity' => $newQuantity,
                'min_stock_level' => $ingredientInventory->min_stock_level,
                'is_low_stock' => $newQuantity <= $ingredientInventory->min_stock_level
            ];
        }

        return $deductions;
    }

    /**
     * Restore inventory for a voided sale.
     * Reverses all deductions made during the original sale.
     */
    public function restoreVoidedSale(Sale $sale): array
    {
        $result = [
            'success' => false,
            'restorations' => [],
            'errors' => []
        ];

        if (!$sale->inventory_synced) {
            $result['success'] = true;
            $result['message'] = 'Sale was not synced, no inventory to restore';
            return $result;
        }

        try {
            DB::transaction(function () use ($sale, &$result) {
                $sale->load('salesItems.product.ingredients');
                
                foreach ($sale->salesItems as $saleItem) {
                    $product = $saleItem->product;
                    $quantity = $saleItem->quantity;
                    $branchId = $sale->branch_id;

                    if ($product->product_type === 'direct') {
                        // Restore product inventory
                        $restoration = $this->restoreProductInventory($product, $branchId, $quantity, $sale);
                        $result['restorations'][] = $restoration;
                    } else {
                        // Restore ingredients
                        $ingredientRestorations = $this->restoreIngredients($product, $branchId, $quantity, $sale);
                        $result['restorations'] = array_merge($result['restorations'], $ingredientRestorations);
                    }
                }

                // Mark sale as not synced (voided)
                $sale->inventory_synced = false;
                $sale->status = 'voided';
                $sale->save();

                $result['success'] = true;
            }, 5);

        } catch (\Exception $e) {
            Log::error('Inventory restoration failed for voided sale #' . $sale->id . ': ' . $e->getMessage());
            $result['errors'][] = 'Failed to restore inventory: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Restore product inventory (add back units).
     * Records stock movement for audit trail.
     */
    private function restoreProductInventory(Product $product, int $branchId, int $quantity, ?Sale $sale = null): array
    {
        $inventory = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();

        if (!$inventory) {
            return [
                'success' => false,
                'error' => "No inventory record for {$product->name}"
            ];
        }

        $quantityBefore = $inventory->quantity;
        $inventory->quantity += $quantity;
        $inventory->save();

        // Record stock movement for audit trail
        StockMovement::record([
            'branch_id' => $branchId,
            'inventory_type' => StockMovement::INVENTORY_PRODUCT,
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_VOID,
            'quantity_before' => $quantityBefore,
            'quantity_change' => $quantity,
            'quantity_after' => $inventory->quantity,
            'unit' => 'pcs',
            'reference_type' => $sale ? Sale::class : null,
            'reference_id' => $sale?->id,
            'notes' => 'Restored from voided sale',
            'user_id' => Auth::id(),
        ]);

        return [
            'success' => true,
            'type' => 'product',
            'product_name' => $product->name,
            'restored' => $quantity,
            'new_quantity' => $inventory->quantity
        ];
    }

    /**
     * Restore ingredients based on product recipe (add back amounts).
     * Records stock movements for audit trail.
     */
    private function restoreIngredients(Product $product, int $branchId, int $productQuantity, ?Sale $sale = null): array
    {
        $restorations = [];
        $recipe = $product->ingredients;

        foreach ($recipe as $ingredientPivot) {
            // Get the branch-specific inventory for this ingredient
            $ingredientInventory = IngredientInventory::where('ingredient_id', $ingredientPivot->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$ingredientInventory) {
                $restorations[] = [
                    'success' => false,
                    'error' => "Ingredient {$ingredientPivot->name} not found in inventory"
                ];
                continue;
            }

            $amountToRestore = $ingredientPivot->pivot->quantity_required * $productQuantity;
            $quantityBefore = $ingredientInventory->quantity;
            $ingredientInventory->quantity += $amountToRestore;
            $ingredientInventory->save();

            // Get ingredient for unit info
            $ingredient = Ingredient::find($ingredientPivot->id);

            // Record stock movement for audit trail
            StockMovement::record([
                'branch_id' => $branchId,
                'inventory_type' => StockMovement::INVENTORY_INGREDIENT,
                'ingredient_id' => $ingredientPivot->id,
                'movement_type' => StockMovement::TYPE_VOID,
                'quantity_before' => $quantityBefore,
                'quantity_change' => $amountToRestore,
                'quantity_after' => $ingredientInventory->quantity,
                'unit' => $ingredient?->unit ?? '',
                'reference_type' => $sale ? Sale::class : null,
                'reference_id' => $sale?->id,
                'notes' => 'Restored from voided sale',
                'user_id' => Auth::id(),
            ]);

            $restorations[] = [
                'success' => true,
                'type' => 'ingredient',
                'ingredient_name' => $ingredient ? $ingredient->name : $ingredientPivot->name,
                'restored' => $amountToRestore,
                'unit' => $ingredient ? $ingredient->unit : '',
                'new_quantity' => $ingredientInventory->quantity
            ];
        }

        return $restorations;
    }

    /**
     * Store low stock alerts in cache for real-time dashboard notifications.
     */
    private function storeLowStockAlerts(array $alerts, int $branchId): void
    {
        $cacheKey = "low_stock_alerts_{$branchId}";
        $existingAlerts = Cache::get($cacheKey, []);
        
        foreach ($alerts as $alert) {
            $alertKey = $alert['type'] . '_' . ($alert['name'] ?? 'unknown');
            $existingAlerts[$alertKey] = array_merge($alert, [
                'timestamp' => now()->toIso8601String(),
                'branch_id' => $branchId
            ]);
        }

        // Store for 1 hour (alerts can be dismissed)
        Cache::put($cacheKey, $existingAlerts, 3600);

        // Also store a global alert counter for all branches
        $globalKey = 'low_stock_alerts_global';
        $globalAlerts = Cache::get($globalKey, []);
        $globalAlerts[$branchId] = count($existingAlerts);
        Cache::put($globalKey, $globalAlerts, 3600);
    }

    /**
     * Get pending low stock alerts for a branch (or all branches for admin).
     */
    public static function getLowStockAlerts(?int $branchId = null): array
    {
        if ($branchId) {
            return Cache::get("low_stock_alerts_{$branchId}", []);
        }

        // Get alerts for all branches
        $allAlerts = [];
        $globalAlerts = Cache::get('low_stock_alerts_global', []);
        
        foreach (array_keys($globalAlerts) as $branch) {
            $branchAlerts = Cache::get("low_stock_alerts_{$branch}", []);
            $allAlerts = array_merge($allAlerts, $branchAlerts);
        }

        return $allAlerts;
    }

    /**
     * Clear low stock alert for a specific item.
     */
    public static function dismissAlert(string $type, string $name, int $branchId): void
    {
        $cacheKey = "low_stock_alerts_{$branchId}";
        $alerts = Cache::get($cacheKey, []);
        
        $alertKey = $type . '_' . $name;
        unset($alerts[$alertKey]);
        
        Cache::put($cacheKey, $alerts, 3600);
    }

    /**
     * Check if a product can be sold (has sufficient inventory).
     * Returns availability status for POS display.
     */
    public function checkProductAvailability(Product $product, int $branchId, int $quantity = 1): array
    {
        if ($product->product_type === 'direct') {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();

            if (!$inventory || $inventory->quantity < $quantity) {
                return [
                    'available' => false,
                    'reason' => 'Insufficient stock',
                    'current_stock' => $inventory ? $inventory->quantity : 0,
                    'requested' => $quantity
                ];
            }

            return [
                'available' => true,
                'current_stock' => $inventory->quantity,
                'is_low' => $inventory->isLowStock()
            ];
        }

        // Composite product: check all ingredients
        $recipe = $product->ingredients;
        
        if ($recipe->isEmpty()) {
            return [
                'available' => false,
                'reason' => 'No recipe defined for this product'
            ];
        }

        $insufficientIngredients = [];
        
        foreach ($recipe as $ingredientPivot) {
            // Check branch-specific inventory
            $ingredientInventory = IngredientInventory::where('ingredient_id', $ingredientPivot->id)
                ->where('branch_id', $branchId)
                ->first();

            $requiredAmount = $ingredientPivot->pivot->quantity_required * $quantity;
            $ingredient = Ingredient::find($ingredientPivot->id);

            if (!$ingredientInventory || $ingredientInventory->quantity < $requiredAmount) {
                $insufficientIngredients[] = [
                    'name' => $ingredient ? $ingredient->name : $ingredientPivot->name,
                    'required' => $requiredAmount,
                    'available' => $ingredientInventory ? $ingredientInventory->quantity : 0,
                    'unit' => $ingredient ? $ingredient->unit : ''
                ];
            }
        }

        if (!empty($insufficientIngredients)) {
            return [
                'available' => false,
                'reason' => 'Insufficient ingredients',
                'missing_ingredients' => $insufficientIngredients
            ];
        }

        return ['available' => true];
    }
}
