<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'forecast_date',
        'forecasted_quantity',
        'confidence_level',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'forecasted_quantity' => 'integer',
        'confidence_level' => 'decimal:2',
    ];

    /**
     * Get the product associated with the forecast.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch associated with the forecast.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope to filter forecasts by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('forecast_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by confidence level threshold.
     */
    public function scopeHighConfidence($query, $threshold = 80)
    {
        return $query->where('confidence_level', '>=', $threshold);
    }
}
