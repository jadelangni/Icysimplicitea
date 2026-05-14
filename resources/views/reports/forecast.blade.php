<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Inventory Forecast') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Projected stockouts and reorder guidance based on recent sales history.
                </p>
            </div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors print:hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $usageWindowDays = 7;
                $predictedIngredientUsage = $ingredientForecasts->map(function ($forecast) use ($usageWindowDays) {
                    $forecastUsage = $forecast->daily_rate * $usageWindowDays;
                    $remainingStock = $forecast->current_quantity - $forecastUsage;
                    $daysLeft = $forecast->daily_rate > 0 ? $forecast->current_quantity / $forecast->daily_rate : null;
                    $isAtRisk = $remainingStock <= 0 || ($daysLeft !== null && $daysLeft <= $usageWindowDays);
                    $sortDays = $daysLeft ?? ($isAtRisk ? -1 : 999999);

                    return (object) [
                        'name' => $forecast->name,
                        'unit' => $forecast->unit,
                        'forecast_usage' => $forecastUsage,
                        'current_stock' => $forecast->current_quantity,
                        'remaining_stock' => $remainingStock,
                        'days_left' => $daysLeft,
                        'risk_label' => $forecast->risk_label,
                        'is_at_risk' => $isAtRisk,
                        'sort_days' => $sortDays,
                    ];
                })->sortBy([
                    ['is_at_risk', 'desc'],
                    ['sort_days', 'asc'],
                    ['name', 'asc'],
                ])->values();

                $sortedProductForecasts = $productForecasts->sortBy(function ($forecast) use ($targetCoverDays) {
                    $daysUntilStockout = $forecast->days_until_stockout;
                    $isAtRisk = $daysUntilStockout !== null && $daysUntilStockout <= $targetCoverDays;
                    $sortDays = $daysUntilStockout ?? 999999;

                    return [
                        $isAtRisk ? 0 : 1,
                        $sortDays,
                        strtolower($forecast->name ?? ''),
                    ];
                })->values();

                $riskAlerts = $predictedIngredientUsage->filter(function ($item) use ($usageWindowDays) {
                    return $item->days_left === null || $item->days_left <= $usageWindowDays || $item->remaining_stock <= 0;
                })->take(5);

                $previewIngredients = $predictedIngredientUsage->take(5);
                $previewProducts = $sortedProductForecasts->take(12);
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                @php
                    // Combine product and ingredient forecasts for analytics
                    $allForecasts = $productForecasts->concat($ingredientForecasts)->values();

                    // Count items that are eligible for risk analytics:
                    // - have usage history, OR
                    // - already at zero/negative stock
                    $analyticsItems = $allForecasts->filter(function ($item) {
                        return $item->daily_rate > 0 || $item->current_quantity <= 0;
                    })->values();
                    $analyticsItemsCount = $analyticsItems->count();

                    // Items at risk within target cover window or already out of stock
                    $atRiskItems = $analyticsItems->filter(function ($item) use ($targetCoverDays) {
                        if ($item->current_quantity <= 0) {
                            return true;
                        }

                        return $item->days_until_stockout !== null && $item->days_until_stockout <= $targetCoverDays;
                    })->values();
                    $atRiskCount = $atRiskItems->count();

                    // Calculate Safe Items (eligible items that are NOT at risk)
                    $safeItems = $analyticsItems->filter(function ($item) use ($targetCoverDays) {
                        if ($item->current_quantity <= 0) {
                            return false;
                        }

                        return !($item->days_until_stockout !== null && $item->days_until_stockout <= $targetCoverDays);
                    })->values();
                    $safeCount = $safeItems->count();

                    // Calculate average days to stockout for at-risk items only (where a rate exists)
                    $avgDaysUntilStockout = $atRiskItems->filter(fn($item) => $item->days_until_stockout !== null)->count() > 0
                        ? $atRiskItems->avg('days_until_stockout')
                        : null;

                    // Risk Rate: percentage of eligible items that are at risk
                    $riskRate = $analyticsItemsCount > 0
                        ? ($atRiskCount / $analyticsItemsCount) * 100
                        : 0;

                    // Projected 7-Day Shortage: sum of unmet demand for items that will stockout within 7 days
                    $projectedShortageQty = $allForecasts->sum(function ($item) {
                        if (($item->daily_rate ?? 0) <= 0) {
                            return 0;
                        }

                        $daysUntilStockout = $item->days_until_stockout;
                        if ($daysUntilStockout === null || $daysUntilStockout >= 7) {
                            return 0;
                        }

                        // Calculate shortage: days until stockout multiplied by daily rate
                        return max(0, (7 - $daysUntilStockout) * $item->daily_rate);
                    });

                    // Prepare trend labels and series: show past 7 days (historical) and next 7 days (predicted)
                    $daysPast = 14;
                    $daysFuture = 7;
                    $startPast = \Carbon\Carbon::today()->subDays($daysPast - 1);
                    $pastLabels = [];
                    for ($i = 0; $i < $daysPast; $i++) {
                        $pastLabels[] = $startPast->copy()->addDays($i)->format('M j');
                    }
                    $futureLabels = [];
                    for ($i = 1; $i <= $daysFuture; $i++) {
                        $futureLabels[] = \Carbon\Carbon::today()->addDays($i)->format('M j');
                    }
                    $trendLabels = array_merge($pastLabels, $futureLabels);

                    // Compute historical daily ingredient usage from actual sales (past window)
                    $historicalUsage = [];
                    for ($i = 0; $i < $daysPast; $i++) {
                        $date = $startPast->copy()->addDays($i)->toDateString();

                        $daySales = \App\Models\Sale::with(['salesItems.product.ingredients'])
                            ->whereDate('created_at', $date)
                            ->when(isset($selectedBranchId) && $selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
                            ->get();

                        $dayTotal = 0;
                        foreach ($daySales as $sale) {
                            foreach ($sale->salesItems as $si) {
                                $product = $si->product;
                                if (!$product) continue;
                                // Only composite products consume ingredients via recipe
                                if (method_exists($product, 'isCompositeProduct') && $product->isCompositeProduct()) {
                                    foreach ($product->ingredients as $ing) {
                                        $qtyRequired = $ing->pivot->quantity_required ?? 0;
                                        $dayTotal += ($si->quantity ?? 0) * $qtyRequired;
                                    }
                                }
                            }
                        }

                        $historicalUsage[] = round($dayTotal, 2);
                    }

                    // Predicted daily usage: project the next 7 days from the recent historical trend.
                    // If the trend is flat or empty, add a small growth slope so the forecast remains visible.
                    $predictedUsage = [];
                    $historicalCount = count($historicalUsage);

                    if ($historicalCount > 0) {
                        $trendDelta = 0;
                        for ($i = 1; $i < $historicalCount; $i++) {
                            $trendDelta += $historicalUsage[$i] - $historicalUsage[$i - 1];
                        }

                        $averageDelta = $historicalCount > 1
                            ? $trendDelta / ($historicalCount - 1)
                            : 0;

                        $lastUsage = $historicalUsage[$historicalCount - 1];
                        $baseForecast = max($lastUsage, (float) $ingredientForecasts->sum(fn($f) => $f->daily_rate ?? 0), 1);
                        $forecastSlope = abs($averageDelta) > 0 ? $averageDelta : max(0.25, $baseForecast * 0.05);

                        for ($i = 1; $i <= $daysFuture; $i++) {
                            $predictedUsage[] = round(max(0, $baseForecast + ($forecastSlope * $i)), 2);
                        }
                    } else {
                        $predictedDaily = max((float) $ingredientForecasts->sum(fn($f) => $f->daily_rate ?? 0), 1);
                        for ($i = 1; $i <= $daysFuture; $i++) {
                            $predictedUsage[] = round($predictedDaily + ($predictedDaily * 0.05 * $i), 2);
                        }
                    }

                    // Align series with labels: historical covers past slots, predicted covers future slots
                    $historicalSeries = array_merge($historicalUsage, array_fill(0, $daysFuture, null));
                    $predictedSeries = array_merge(array_fill(0, $daysPast, null), $predictedUsage);
                @endphp

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Ingredient Stock Forecast</h3>
                    </div>
                    <div class="flex items-center gap-3 print:hidden">
                        @if($canSelectBranch)
                            <form method="GET" action="{{ route('reports.forecast') }}">
                                <select name="branch_id" id="branch_id" onchange="this.form.submit()" class="block w-56 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm text-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ (int) $selectedBranchId === (int) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="w-full">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Ingredient Forecast Graph</p>
                        <div class="mt-3 w-full" style="height: 220px; max-height: 220px;">
                            <canvas id="forecastTrendLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Ingredient Forecast Summary</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Quick view of ingredient stock for the selected branch.</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.119-3 2.5S10.343 13 12 13s3 1.119 3 2.5S13.657 18 12 18m0-10V6m0 12v-2" />
                        </svg>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Top ingredients to monitor</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-white dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingredient</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days Left</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($previewIngredients->take(5) as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-black">{{ $item->name }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-900 dark:text-black">{{ number_format($item->current_stock, 1) }} {{ $item->unit }}</td>
                                            <td class="px-4 py-3 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $item->days_left === null ? 'N/A' : number_format($item->days_left, 0) . 'd' }}</td>
                                            <td class="px-4 py-3 text-xs">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $item->current_stock <= 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : ($item->current_stock <= $item->forecast_usage ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300') }}">
                                                    {{ $item->current_stock <= 0 ? 'No Stock' : ($item->current_stock <= $item->forecast_usage ? 'Low Stock' : 'In Stock') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Product Forecast</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current stock, recent demand, and estimated stockout timing for raw products.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Daily ({{ $lookbackDays }}d avg)</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stockout</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($previewProducts->take(6) as $forecast)
                                    @php
                                        $displayDailyRate = ($forecast->daily_rate ?? 0) > 0 ? max(1, (int) ceil($forecast->daily_rate)) : 0;
                                        $displayStockoutDays = $displayDailyRate > 0 ? $forecast->current_quantity / $displayDailyRate : null;
                                        $stockThreshold = $displayDailyRate * $targetCoverDays;
                                        $status = $forecast->current_quantity <= 0
                                            ? 'No Stock'
                                            : ($forecast->current_quantity <= $stockThreshold ? 'Low Stock' : 'In Stock');
                                        $statusClass = $forecast->current_quantity <= 0
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
                                            : ($forecast->current_quantity <= $stockThreshold
                                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'
                                                : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300');
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 text-sm font-semibold text-gray-900 dark:text-black">{{ $forecast->name }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($forecast->current_quantity, 1) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($displayDailyRate, 0) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ $displayStockoutDays === null ? 'N/A' : number_format($displayStockoutDays, 0) . 'd' }}</td>
                                        <td class="px-3 py-2 text-xs">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No product forecast history found for this branch.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" onclick="openProductsModal()" class="inline-flex items-center text-sm font-semibold text-sky-600 hover:text-sky-700">
                            View All Product Forecast
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                @php
                    $ingredientRestock = $ingredientForecasts->filter(fn($it) => $it->suggested_reorder_qty > 0)->sortByDesc('suggested_reorder_qty')->values();
                    $productRestock = $productForecasts->filter(fn($p) => $p->suggested_reorder_qty > 0)->sortByDesc('suggested_reorder_qty')->values();
                    $ingredientRestockPreview = $ingredientRestock->take(6);
                    $productRestockPreview = $productRestock->take(6);
                @endphp

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-black">Recommended Restock Plan</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Suggestions are based on the selected coverage target and recent demand.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        @if($ingredientRestock->count() > 0 || $productRestock->count() > 0)
                            <button type="button" onclick="openRestockModal()" class="inline-flex items-center rounded-full border-2 border-emerald-500 bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 hover:border-emerald-600 transition-colors">
                                Review Restock Plan
                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                        <a href="{{ route('reports.export.restock', ['branch_id' => $selectedBranchId, 'lookback_days' => $lookbackDays, 'lead_time_days' => $leadTimeDays, 'target_cover_days' => $targetCoverDays]) }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                            </svg>
                            Export Excel
                        </a>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase">Ingredient Restock</h4>
                        @if($ingredientRestock->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-900/40">
                                <table class="min-w-full divide-y divide-amber-200 dark:divide-amber-900/40 text-xs">
                                    <thead class="bg-amber-50 dark:bg-amber-900/20">
                                        <tr>
                                            <th class="px-3 py-1.5 text-left font-medium text-amber-700 dark:text-amber-300 uppercase tracking-wider text-xs">Ingredient</th>
                                            <th class="px-3 py-1.5 text-left font-medium text-amber-700 dark:text-amber-300 uppercase tracking-wider text-xs">Suggested</th>
                                            <th class="px-3 py-1.5 text-left font-medium text-amber-700 dark:text-amber-300 uppercase tracking-wider text-xs">Current</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-amber-200 dark:divide-amber-900/40">
                                        @foreach($ingredientRestockPreview as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-3 py-1.5 align-top text-xs text-gray-900 dark:text-black">{{ $item->name }}</td>
                                                <td class="px-3 py-2 align-top">
                                                    <div class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-semibold text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">+{{ number_format($item->suggested_reorder_qty, 1) }} {{ $item->unit }}</div>
                                                    @if(!empty($item->restock_reason))
                                                        <svg class="mt-0.5 inline-block h-3 w-3 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20" title="{{ $item->restock_reason }}"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-1.5 align-top text-xs text-gray-900 dark:text-black">{{ number_format($item->current_quantity, 1) }} {{ $item->unit }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-6 text-center dark:border-green-900/40 dark:bg-green-900/15">
                                <p class="text-sm font-semibold text-green-700 dark:text-green-300">No ingredient restock needed</p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase">Raw Product Restock</h4>
                        @if($productRestock->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-900/40">
                                <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-900/40 text-xs">
                                    <thead class="bg-blue-50 dark:bg-blue-900/20">
                                        <tr>
                                            <th class="px-3 py-1.5 text-left font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider text-xs">Product</th>
                                            <th class="px-3 py-1.5 text-left font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider text-xs">Suggested</th>
                                            <th class="px-3 py-1.5 text-left font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider text-xs">Current</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-blue-200 dark:divide-blue-900/40">
                                        @foreach($productRestockPreview as $p)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-3 py-1.5 align-top text-xs text-gray-900 dark:text-black">{{ $p->name }}</td>
                                                <td class="px-3 py-2 align-top">
                                                    <div class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-semibold text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">+{{ number_format($p->suggested_reorder_qty, 0) }}</div>
                                                    @if(!empty($p->restock_reason))
                                                        <svg class="mt-0.5 inline-block h-3 w-3 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20" title="{{ $p->restock_reason }}"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-1.5 align-top text-xs text-gray-900 dark:text-black">{{ number_format($p->current_quantity, 1) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-6 text-center dark:border-green-900/40 dark:bg-green-900/15">
                                <p class="text-sm font-semibold text-green-700 dark:text-green-300">No raw product restock needed</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Restock Modal -->
            <div id="restockModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-4xl max-h-96 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">All Restock Items Recommended</h3>
                        <button type="button" onclick="closeRestockModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 px-6 py-0">
                        @if($ingredientRestock->count() > 0 || $productRestock->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Suggested</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ingredientRestock as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400">Ingredient</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ $item->name }}</td>
                                            <td class="px-3 py-2 text-sm font-semibold text-teal-600 dark:text-teal-400">+{{ number_format($item->suggested_reorder_qty, 1) }} {{ $item->unit }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ number_format($item->current_quantity, 1) }} {{ $item->unit }}</td>
                                            <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">{{ $item->restock_reason ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                    @foreach($productRestock as $p)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400">Product</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ $p->name }}</td>
                                            <td class="px-3 py-2 text-sm font-semibold text-teal-600 dark:text-teal-400">+{{ number_format($p->suggested_reorder_qty, 0) }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ number_format($p->current_quantity, 1) }}</td>
                                            <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">{{ $p->restock_reason ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="flex items-center justify-center py-12">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No restock needed</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">All items are well stocked</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Products Modal -->
            <div id="productsModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-4xl max-h-96 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">All Product Forecast</h3>
                        <button type="button" onclick="closeProductsModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 px-4 pt-0 pb-2">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Daily ({{ $lookbackDays }}d avg)</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stockout</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($sortedProductForecasts as $forecast)
                                    @php
                                        $displayDailyRate = ($forecast->daily_rate ?? 0) > 0 ? max(1, (int) ceil($forecast->daily_rate)) : 0;
                                        $displayStockoutDays = $displayDailyRate > 0 ? $forecast->current_quantity / $displayDailyRate : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ $forecast->name }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($forecast->current_quantity, 1) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($displayDailyRate, 0) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ $displayStockoutDays === null ? 'N/A' : number_format($displayStockoutDays, 0) . 'd' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const forecastModalIds = ['restockModal', 'productsModal'];

                const forecastTrendLineData = {
                    labels: @json($trendLabels),
                    historical: @json($historicalSeries),
                    predicted: @json($predictedSeries),
                };

                // Ensure modals are attached to <body> so fixed positioning covers the full viewport.
                forecastModalIds.forEach(function(id) {
                    const modal = document.getElementById(id);
                    if (modal && modal.parentElement !== document.body) {
                        document.body.appendChild(modal);
                    }
                });

                function lockBodyScroll() {
                    document.body.classList.add('overflow-hidden');
                }

                function unlockBodyScrollIfNoModalOpen() {
                    const hasOpenModal = forecastModalIds.some(function(id) {
                        const modal = document.getElementById(id);
                        return modal && !modal.classList.contains('hidden');
                    });

                    if (!hasOpenModal) {
                        document.body.classList.remove('overflow-hidden');
                    }
                }

                function openRestockModal() {
                    document.getElementById('restockModal').classList.remove('hidden');
                    lockBodyScroll();
                }

                function closeRestockModal() {
                    document.getElementById('restockModal').classList.add('hidden');
                    unlockBodyScrollIfNoModalOpen();
                }

                function openProductsModal() {
                    document.getElementById('productsModal').classList.remove('hidden');
                    lockBodyScroll();
                }

                function closeProductsModal() {
                    document.getElementById('productsModal').classList.add('hidden');
                    unlockBodyScrollIfNoModalOpen();
                }

                // Close modal when clicking outside
                document.getElementById('restockModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeRestockModal();
                    }
                });

                document.getElementById('productsModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeProductsModal();
                    }
                });

                

                if (document.getElementById('forecastTrendLineChart') && typeof Chart !== 'undefined') {
                    const trendCtx = document.getElementById('forecastTrendLineChart').getContext('2d');
                    const forecastTrendLineChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: forecastTrendLineData.labels,
                            datasets: [
                                {
                                    label: 'Historical Usage',
                                    data: forecastTrendLineData.historical,
                                    borderColor: '#2563eb',
                                    backgroundColor: 'rgba(37,99,235,0.08)',
                                    pointBackgroundColor: '#2563eb',
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 3,
                                    tension: 0.25,
                                    spanGaps: true,
                                    fill: false,
                                },
                                {
                                    label: 'Forecasted Usage',
                                    data: forecastTrendLineData.predicted,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239,68,68,0.06)',
                                    pointBackgroundColor: '#ef4444',
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 3,
                                    borderDash: [6, 6],
                                    tension: 0.25,
                                    spanGaps: true,
                                    fill: false,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 14,
                                        color: '#6b7280',
                                        font: { size: 11 },
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: '#6b7280',
                                        font: { size: 10 },
                                        maxRotation: 0,
                                        autoSkip: true,
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0, color: '#6b7280' },
                                    grid: { color: 'rgba(107,114,128,0.15)' }
                                }
                            }
                        }
                    });

                    try { forecastTrendLineChart.resize(); } catch (e) { /* ignore */ }
                }
            </script>
            </div>

            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 print:hidden">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 print:hidden">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Branch Forecast removed as requested -->
        </div>
    </div>
</x-app-layout>
