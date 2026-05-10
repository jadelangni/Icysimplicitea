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
                    $allForecasts = $ingredientForecasts->concat($productForecasts);
                    $trackedItemsCount = $allForecasts->count();
                    $atRiskItems = $allForecasts->filter(fn($item) => $item->days_until_stockout !== null && $item->days_until_stockout <= $targetCoverDays)->values();
                    $atRiskCount = $atRiskItems->count();
                    $safeCount = max($trackedItemsCount - $atRiskCount, 0);
                    $avgDaysUntilStockout = $atRiskCount > 0 ? $atRiskItems->avg('days_until_stockout') : null;
                    $riskRate = $trackedItemsCount > 0 ? ($atRiskCount / $trackedItemsCount) * 100 : 0;
                    $projectedShortageQty = $allForecasts->sum(function ($item) {
                        if (($item->daily_rate ?? 0) <= 0) {
                            return 0;
                        }

                        $daysUntilStockout = $item->days_until_stockout;
                        if ($daysUntilStockout === null || $daysUntilStockout >= 7) {
                            return 0;
                        }

                        return (7 - $daysUntilStockout) * $item->daily_rate;
                    });
                @endphp

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Forecast Analytics</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Quick health indicators from current product and ingredient forecast signals.</p>
                    </div>
                    <div class="flex items-center gap-3 print:hidden">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                            {{ number_format($trackedItemsCount) }} tracked
                        </span>
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

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Risk Rate</p>
                        <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($riskRate, 1) }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Items likely to stockout within cover window.</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Avg Days To Stockout</p>
                        <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $avgDaysUntilStockout === null ? 'N/A' : number_format($avgDaysUntilStockout, 1) . 'd' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Average for at-risk items only.</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Projected 7-Day Shortage</p>
                        <p class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($projectedShortageQty, 1) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Estimated unmet demand quantity.</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Safe Items</p>
                        <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($safeCount) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Items above immediate risk threshold.</p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <div class="h-2 bg-red-500 dark:bg-red-400" style="width: {{ min(max($riskRate, 0), 100) }}%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Risk exposure</span>
                        <span>{{ $atRiskCount }} at risk / {{ $trackedItemsCount }} total</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Predicted Ingredient Usage (Next 7 Days)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Usage estimate, remaining stock, and stockout timing based on recent sales history.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                            {{ $predictedIngredientUsage->count() }} ingredients
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingredient</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Forecast</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days Left</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($previewIngredients as $item)
                                        @php
                                            $seed = crc32($item->name);
                                            $statusClass = $item->remaining_stock <= 0
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
                                                : ($item->days_left !== null && $item->days_left <= 7
                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300');
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 align-top">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-black truncate">{{ $item->name }}</div>
                                            </td>
                                            <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ number_format($item->forecast_usage, 1) }} {{ $item->unit }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ number_format($item->current_stock, 1) }} {{ $item->unit }}</td>
                                            <td class="px-3 py-2 align-top text-xs {{ $item->remaining_stock <= 0 ? 'text-red-600 dark:text-red-300' : 'text-gray-900 dark:text-black' }}">{{ number_format($item->remaining_stock, 1) }} {{ $item->unit }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ $item->days_left === null ? 'N/A' : number_format($item->days_left, 1) . 'd' }}</td>
                                            <td class="px-3 py-2 align-top text-xs">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $item->remaining_stock <= 0 ? 'At Risk' : ($item->days_left !== null && $item->days_left <= 7 ? 'At Risk' : 'Safe') }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No ingredient usage history found for this branch.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="button" onclick="openIngredientsModal()" class="inline-flex items-center text-sm font-semibold text-sky-600 hover:text-sky-700">
                                View All Ingredients Forecast
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Product Forecast</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Top products approaching stockout or needing reorder.</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 max-h-72 overflow-y-auto overflow-x-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Daily</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stockout</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($previewProducts as $forecast)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 align-top">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-black truncate">{{ $forecast->name }}</div>
                                        </td>
                                        <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ number_format($forecast->current_quantity, 1) }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ number_format($forecast->daily_rate, 1) }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-gray-900 dark:text-black">{{ $forecast->days_until_stockout === null ? 'N/A' : number_format($forecast->days_until_stockout, 1) . 'd' }}</td>
                                        
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No product forecast available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="openProductsModal()" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                            View Full Product Forecast
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Recommended Restock Plan</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('reports.export.restock', ['branch_id' => $selectedBranchId, 'lookback_days' => $lookbackDays, 'lead_time_days' => $leadTimeDays, 'target_cover_days' => $targetCoverDays]) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                            </svg>
                            Export Excel
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                @php
                    $ingredientRestock = $ingredientForecasts->filter(fn($it) => $it->suggested_reorder_qty > 0)->sortByDesc('suggested_reorder_qty')->values();
                    $productRestock = $productForecasts->filter(fn($p) => $p->suggested_reorder_qty > 0)->sortByDesc('suggested_reorder_qty')->values();
                    $ingredientRestockPreview = $ingredientRestock->take(6);
                    $productRestockPreview = $productRestock->take(6);
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ingredient Restock</h4>
                        @if($ingredientRestock->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingredient</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Suggested</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($ingredientRestockPreview as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-3 py-2 align-top text-sm text-gray-900 dark:text-black">{{ $item->name }}</td>
                                                <td class="px-3 py-2 align-top">
                                                    <div class="text-sm font-semibold text-teal-600 dark:text-teal-400">+{{ number_format($item->suggested_reorder_qty, 1) }} {{ $item->unit }}</div>
                                                    @if(!empty($item->restock_reason))
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Reason: {{ $item->restock_reason }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 align-top text-sm text-gray-900 dark:text-black">{{ number_format($item->current_quantity, 1) }} {{ $item->unit }}</td>
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
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Raw Product Restock</h4>
                        @if($productRestock->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Suggested</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($productRestockPreview as $p)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-3 py-2 align-top text-sm text-gray-900 dark:text-black">{{ $p->name }}</td>
                                                <td class="px-3 py-2 align-top">
                                                    <div class="text-sm font-semibold text-teal-600 dark:text-teal-400">+{{ number_format($p->suggested_reorder_qty, 0) }}</div>
                                                    @if(!empty($p->restock_reason))
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Reason: {{ $p->restock_reason }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 align-top text-sm text-gray-900 dark:text-black">{{ number_format($p->current_quantity, 1) }}</td>
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

                @if($ingredientRestock->count() > 0 || $productRestock->count() > 0)
                    <div class="mt-4">
                        <button type="button" onclick="openRestockModal()" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                            Show All
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                @endif
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

            <!-- Ingredients Modal -->
            <div id="ingredientsModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-4xl max-h-96 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black">All Ingredients Forecast</h3>
                        <button type="button" onclick="closeIngredientsModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 px-4 pt-0 pb-2">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingredient</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Forecast (7d)</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days Left</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($predictedIngredientUsage as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($item->forecast_usage, 1) }} {{ $item->unit }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($item->current_stock, 1) }} {{ $item->unit }}</td>
                                        <td class="px-3 py-2 text-xs {{ $item->remaining_stock <= 0 ? 'text-red-600 dark:text-red-300' : 'text-gray-900 dark:text-black' }}">{{ number_format($item->remaining_stock, 1) }} {{ $item->unit }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ $item->days_left === null ? 'N/A' : number_format($item->days_left, 1) . 'd' }}</td>
                                        <td class="px-3 py-2 text-xs">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->remaining_stock <= 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : ($item->days_left !== null && $item->days_left <= 7 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300') }}">{{ $item->remaining_stock <= 0 ? 'At Risk' : ($item->days_left !== null && $item->days_left <= 7 ? 'At Risk' : 'Safe') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Daily</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stockout</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($sortedProductForecasts as $forecast)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-black">{{ $forecast->name }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($forecast->current_quantity, 1) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ number_format($forecast->daily_rate, 1) }}</td>
                                        <td class="px-3 py-2 text-xs text-gray-900 dark:text-black">{{ $forecast->days_until_stockout === null ? 'N/A' : number_format($forecast->days_until_stockout, 1) . 'd' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                const forecastModalIds = ['restockModal', 'ingredientsModal', 'productsModal'];

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

                function openIngredientsModal() {
                    document.getElementById('ingredientsModal').classList.remove('hidden');
                    lockBodyScroll();
                }

                function closeIngredientsModal() {
                    document.getElementById('ingredientsModal').classList.add('hidden');
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

                document.getElementById('ingredientsModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeIngredientsModal();
                    }
                });

                document.getElementById('productsModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeProductsModal();
                    }
                });
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
