<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

<style>
    .stat-icon-box {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(0, 0, 0, 0.2);
    }
    .stat-icon-green { background-color: rgba(34, 197, 94, 0.35); }
    .stat-icon-blue { background-color: rgba(59, 130, 246, 0.35); }
    .stat-icon-purple { background-color: rgba(168, 85, 247, 0.35); }
    .stat-icon-yellow { background-color: rgba(234, 179, 8, 0.35); }
    .stat-icon-red { background-color: rgba(239, 68, 68, 0.35); }
    html.dark .stat-icon-box { border-color: rgba(255, 255, 255, 0.2); }
</style>

<div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome {{ Auth::user()->name }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Here's what's happening at <span class="font-semibold text-simplicitea-600 dark:text-simplicitea-400">{{ $selectedBranch->name ?? 'your branch' }}</span> today.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Live Indicator -->
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl" id="liveIndicator">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-green-700 dark:text-green-400">LIVE</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
                        <p class="text-lg font-semibold text-simplicitea-600 dark:text-simplicitea-400" id="currentTime">{{ now()->format('h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Today's Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="stat-icon-box stat-icon-blue dark:bg-blue-900/50">
                        <span class="text-xl">📊</span>
                    </div>
                    <span id="salesChangeBadge" class="text-xs font-medium px-2 py-1 rounded-full {{ ($salesChange ?? 0) >= 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400' }}">
                        {{ ($salesChange ?? 0) >= 0 ? '↑' : '↓' }} <span id="salesChangeValue">{{ abs($salesChange ?? 0) }}</span>%
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Today's Sales</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" id="todaysSalesValue">₱{{ number_format($todaysSales ?? 0, 0) }}</p>
            </div>

            <!-- Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="stat-icon-box stat-icon-purple dark:bg-purple-900/50">
                        <span class="text-xl">🧾</span>
                    </div>
                    <span id="transactionsChangeBadge" class="text-xs font-medium px-2 py-1 rounded-full {{ ($transactionsChange ?? 0) >= 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400' }}">
                        {{ ($transactionsChange ?? 0) >= 0 ? '↑' : '↓' }} <span id="transactionsChangeValue">{{ abs($transactionsChange ?? 0) }}</span>%
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Today's Orders</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" id="todaysTransactionsValue">{{ $todaysTransactions ?? 0 }}</p>
            </div>

            <!-- Low Stock -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div id="lowStockIcon" class="stat-icon-box {{ ($lowStockCount ?? 0) > 0 ? 'stat-icon-red dark:bg-red-900/50' : 'stat-icon-green dark:bg-green-900/50' }}">
                        <span class="text-xl" id="lowStockEmoji">{{ ($lowStockCount ?? 0) > 0 ? '⚠️' : '✅' }}</span>
                    </div>
                </div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Low Stock</p>
                <p class="text-2xl font-bold mt-1" id="lowStockValue">{{ $lowStockCount ?? 0 }} Items</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Sales Chart - Takes 2 columns -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sales Statistics</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Last 7 days overview</p>
                        </div>
                        <div class="flex items-center gap-4">
                            @if(isset($categorySales))
                                @foreach($categorySales as $cat => $amount)
                                    @if($amount > 0)
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full 
                                            @if($loop->index == 0) bg-simplicitea-500
                                            @elseif($loop->index == 1) bg-blue-500
                                            @elseif($loop->index == 2) bg-yellow-500
                                            @else bg-purple-500
                                            @endif"></span>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $cat }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products - 1 column -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🏆 Top Products</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">This week's bestsellers</p>
                </div>
                <div id="topProductsContainer" class="p-4">
                    @if(isset($topProducts) && count($topProducts) > 0)
                        <div class="space-y-3">
                            @foreach($topProducts as $index => $item)
                            <div class="flex items-center gap-3 p-3 rounded-xl {{ $index === 0 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 dark:bg-yellow-900/50' : ($index === 1 ? 'bg-gray-200 dark:bg-gray-600' : 'bg-orange-100 dark:bg-orange-900/50') }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $index === 0 ? 'text-yellow-600' : ($index === 1 ? 'text-gray-600 dark:text-gray-300' : 'text-orange-600') }}">
                                        {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : ($index + 1))) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item->product->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->total_qty }} sold</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">₱{{ number_format($item->total_sales, 0) }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="text-4xl">📊</span>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">No sales this week yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Your latest orders</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="lastUpdated">{{ now()->format('h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div id="recentTransactionsContainer" class="p-4">
                    @if(isset($recentSales) && $recentSales->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <th class="pb-3 pl-3">Receipt</th>
                                        <th class="pb-3">Items</th>
                                        <th class="pb-3">Payment</th>
                                        <th class="pb-3">Amount</th>
                                        <th class="pb-3 pr-3">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($recentSales as $index => $sale)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="py-3 pl-3">
                                            <div class="flex items-center gap-2">
                                                @if($index === 0)
                                                    <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                                @endif
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $sale->receipt_number }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @php $itemCount = $sale->salesItems->count(); @endphp
                                                @foreach($sale->salesItems->take(2) as $item)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                        {{ $item->product->name ?? 'Item' }}
                                                    </span>
                                                @endforeach
                                                @if($itemCount > 2)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                                        +{{ $itemCount - 2 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                                {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 
                                                   ($sale->payment_method === 'card' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : 
                                                   'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400') }}">
                                                {{ ucfirst($sale->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">₱{{ number_format($sale->total_amount, 0) }}</span>
                                        </td>
                                        <td class="py-3 pr-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $sale->created_at->diffForHumans() }}</span>
                                                <a href="{{ route('pos.receipt', $sale->id) }}" class="text-simplicitea-600 dark:text-simplicitea-400 hover:text-simplicitea-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <span class="text-5xl">🧋</span>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mt-3 mb-1">No sales yet today</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mb-4">Start processing orders to see them here</p>
                            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-simplicitea-600 text-white rounded-xl hover:bg-simplicitea-700 font-medium transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Sale
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">⚡ Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <a href="{{ route('pos.index') }}" class="group flex flex-col items-center p-5 bg-gradient-to-br from-simplicitea-50 to-simplicitea-100 dark:from-simplicitea-900/30 dark:to-simplicitea-900/50 rounded-2xl hover:shadow-lg hover:scale-105 transition-all duration-200 border border-simplicitea-200 dark:border-simplicitea-800">
                        <div class="w-14 h-14 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="text-2xl">🛒</span>
                        </div>
                        <span class="text-sm font-semibold text-simplicitea-900 dark:text-simplicitea-300">New Sale</span>
                        <span class="text-xs text-simplicitea-600 dark:text-simplicitea-400">Process order</span>
                    </a>

                    <a href="{{ route('reports.sales') }}" class="group flex flex-col items-center p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/50 rounded-2xl hover:shadow-lg hover:scale-105 transition-all duration-200 border border-blue-200 dark:border-blue-800">
                        <div class="w-14 h-14 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="text-2xl">📋</span>
                        </div>
                        <span class="text-sm font-semibold text-blue-900 dark:text-blue-300">Sales History</span>
                        <span class="text-xs text-blue-600 dark:text-blue-400">View orders</span>
                    </a>

                    <a href="{{ route('product-inventory.index') }}" class="group flex flex-col items-center p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-900/50 rounded-2xl hover:shadow-lg hover:scale-105 transition-all duration-200 border border-yellow-200 dark:border-yellow-800">
                        <div class="w-14 h-14 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="text-2xl">📦</span>
                        </div>
                        <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-300">Inventory</span>
                        <span class="text-xs text-yellow-600 dark:text-yellow-400">Check stock</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const formatCompactValue = (value, withCurrency = false) => {
        const numericValue = Number(value) || 0;
        const compactValue = Math.abs(numericValue) >= 1000
            ? `${(numericValue / 1000).toFixed(1).replace(/\.0$/, '')}k`
            : numericValue.toLocaleString();

        return withCurrency ? `₱${compactValue}` : compactValue;
    };

    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels ?? []) !!},
            datasets: [{
                label: 'Sales (₱)',
                data: {!! json_encode($dailySales ?? []) !!},
                borderColor: '#2DD4BF',
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) {
                        return 'rgba(45, 212, 191, 0.22)';
                    }

                    const areaGradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    areaGradient.addColorStop(0, 'rgba(45, 212, 191, 0.38)');
                    areaGradient.addColorStop(0.58, 'rgba(45, 212, 191, 0.12)');
                    areaGradient.addColorStop(1, 'rgba(45, 212, 191, 0.02)');
                    return areaGradient;
                },
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHitRadius: 18,
                pointHoverBackgroundColor: '#FFFFFF',
                pointHoverBorderColor: '#2DD4BF',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(148, 163, 184, 0.2)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 12,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return 'Value ' + Number(context.raw || 0).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(15, 23, 42, 0.08)',
                        borderDash: [4, 4],
                        drawBorder: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCompactValue(value);
                        },
                        maxTicksLimit: 5,
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        padding: 10
                    }
                }
            }
        }
    });

    // Auto-refresh for dashboard
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('from_pos') === '1' && urlParams.get('success') === '1') {
            showNotification('🎉 Sale processed successfully!', 'success');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Update current time every minute
        setInterval(updateCurrentTime, 60000);

        // Auto-refresh data every 30 seconds
        setInterval(() => {
            loadCashierData(true);
        }, 30000);
    });

    function updateCurrentTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        document.getElementById('currentTime').textContent = timeStr;
    }

    async function loadCashierData(silent = false) {
        try {
            const response = await fetch(`/dashboard/data`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                updateDashboard(data);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    function updateDashboard(data) {
        // Update Today's Sales
        document.getElementById('todaysSalesValue').textContent = '₱' + data.todaysSales.toLocaleString();
        updateChangeBadge('salesChangeBadge', 'salesChangeValue', data.salesChange);

        // Update Today's Orders
        document.getElementById('todaysTransactionsValue').textContent = data.todaysTransactions;
        updateChangeBadge('transactionsChangeBadge', 'transactionsChangeValue', data.transactionsChange);

        // Update Low Stock
        const lowStock = data.lowStockCount;
        document.getElementById('lowStockValue').textContent = lowStock + ' Items';
        document.getElementById('lowStockValue').className = `text-2xl font-bold mt-1 ${lowStock > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'}`;
        document.getElementById('lowStockEmoji').textContent = lowStock > 0 ? '⚠️' : '✅';
        const iconEl = document.getElementById('lowStockIcon');
        iconEl.className = `stat-icon-box ${lowStock > 0 ? 'stat-icon-red dark:bg-red-900/50' : 'stat-icon-green dark:bg-green-900/50'}`;

        // Update Chart
        salesChart.data.labels = data.dailyLabels;
        salesChart.data.datasets[0].data = data.dailySales;
        if (salesChart.data.datasets[1]) {
            salesChart.data.datasets[1].data = data.dailySales;
        }
        salesChart.update('none');

        // Update Top Products
        updateTopProducts(data.topProducts);

        // Update last updated time
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function updateTopProducts(products) {
        const container = document.getElementById('topProductsContainer');
        if (!container || !products) return;

        if (products.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <span class="text-4xl">📊</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">No sales this week yet</p>
                </div>
            `;
            return;
        }

        let html = '<div class="space-y-3">';
        products.forEach((item, index) => {
            const bgClass = index === 0 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-700/50';
            const medalBgClass = index === 0 ? 'bg-yellow-100 dark:bg-yellow-900/50' : (index === 1 ? 'bg-gray-200 dark:bg-gray-600' : 'bg-orange-100 dark:bg-orange-900/50');
            const medal = index === 0 ? '🥇' : (index === 1 ? '🥈' : (index === 2 ? '🥉' : (index + 1)));

            html += `
                <div class="flex items-center gap-3 p-3 rounded-xl ${bgClass}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full ${medalBgClass} flex items-center justify-center">
                        <span class="text-sm font-bold">${medal}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${item.product?.name || item.name || 'Unknown'}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${item.total_qty} sold</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">₱${parseFloat(item.total_sales).toLocaleString()}</p>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function updateChangeBadge(badgeId, valueId, change) {
        const badge = document.getElementById(badgeId);
        const value = document.getElementById(valueId);
        
        value.textContent = Math.abs(change);
        
        const arrow = change >= 0 ? '↑' : '↓';
        badge.innerHTML = `${arrow} <span id="${valueId}">${Math.abs(change)}</span>%`;
        
        if (change >= 0) {
            badge.className = 'text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400';
        } else {
            badge.className = 'text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400';
        }
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-4 rounded-2xl shadow-lg z-50 transform translate-x-full transition-all duration-300 flex items-center gap-3 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.innerHTML = `<span class="text-lg">${message}</span>`;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // ========================================
    // LIVE SALES NOTIFICATION SYSTEM (Cashier)
    // ========================================
    
    let lastSaleTimestamp = new Date().toISOString();
    
    // CountUp Animation Function
    function animateCountUp(element, start, end, duration = 800, prefix = '', suffix = '') {
        const startTime = performance.now();
        const diff = end - start;
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = start + (diff * easeOut);
            element.textContent = prefix + Math.round(current).toLocaleString() + suffix;
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        requestAnimationFrame(update);
    }
    
    function parseCurrencyValue(element) {
        const text = element.textContent.replace(/[₱,]/g, '');
        return parseFloat(text) || 0;
    }
    
    function addPulseEffect(element) {
        element.classList.add('live-update-pulse');
        setTimeout(() => element.classList.remove('live-update-pulse'), 2000);
    }
    
    function showLiveSaleToast(sale) {
        const toastContainer = document.getElementById('liveToastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = 'live-sale-toast transform translate-x-full opacity-0 transition-all duration-500';
        toast.innerHTML = `
            <div class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-green-200 dark:border-green-800 p-4 min-w-[320px] max-w-md">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center animate-pulse">
                    <span class="text-2xl">🧋</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-ping"></span>
                            LIVE
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${sale.time_ago || 'Just now'}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mb-0.5">New Sale!</p>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">${sale.formatted_amount}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        ${sale.items_count} item${sale.items_count !== 1 ? 's' : ''} • ${sale.payment_method}
                    </p>
                </div>
                <button onclick="this.closest('.live-sale-toast').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        toastContainer.prepend(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });
        
        playNotificationSound();
        
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 500);
        }, 6000);
        
        const toasts = toastContainer.querySelectorAll('.live-sale-toast');
        if (toasts.length > 3) {
            toasts[toasts.length - 1].remove();
        }
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'liveToastContainer';
        container.className = 'fixed top-20 right-4 z-50 space-y-3';
        document.body.appendChild(container);
        return container;
    }
    
    function playNotificationSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {}
    }
    
    function handleLiveSaleUpdate(sale) {
        const todaysSalesEl = document.getElementById('todaysSalesValue');
        if (todaysSalesEl) {
            const currentValue = parseCurrencyValue(todaysSalesEl);
            const newValue = currentValue + sale.amount;
            animateCountUp(todaysSalesEl, currentValue, newValue, 1000, '₱');
            addPulseEffect(todaysSalesEl.closest('.bg-white, .dark\\:bg-gray-800'));
        }
        
        const ordersEl = document.getElementById('todaysTransactionsValue');
        if (ordersEl) {
            const currentOrders = parseInt(ordersEl.textContent) || 0;
            animateCountUp(ordersEl, currentOrders, currentOrders + 1, 500);
            addPulseEffect(ordersEl.closest('.bg-white, .dark\\:bg-gray-800'));
        }
        
        if (typeof salesChart !== 'undefined') {
            const todayIndex = salesChart.data.labels.length - 1;
            if (todayIndex >= 0) {
                salesChart.data.datasets[0].data[todayIndex] += sale.amount;
                if (salesChart.data.datasets[1]) {
                    salesChart.data.datasets[1].data[todayIndex] += sale.amount;
                }
                salesChart.update('none');
            }
        }
    }
    
    async function checkForNewSales() {
        try {
            const response = await fetch(`/dashboard/live-sales?since=${encodeURIComponent(lastSaleTimestamp)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.sales && data.sales.length > 0) {
                    data.sales.reverse().forEach(sale => {
                        showLiveSaleToast(sale);
                        handleLiveSaleUpdate(sale);
                    });
                    
                    lastSaleTimestamp = data.server_time;
                    loadCashierData(true);
                }
            }
        } catch (error) {
            console.error('Error checking for new sales:', error);
        }
    }
    
    // Poll for new sales every 3 seconds
    setInterval(checkForNewSales, 3000);
</script>

<style>
    .live-update-pulse {
        animation: liveUpdatePulse 0.5s ease-out;
    }
    
    @keyframes liveUpdatePulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        50% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    .live-sale-toast {
        animation: slideInRight 0.5s ease-out;
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

</x-app-layout>
