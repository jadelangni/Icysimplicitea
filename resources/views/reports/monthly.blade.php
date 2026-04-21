<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Monthly Reports') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors print:hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Print Header (visible only when printing) -->
            <div class="hidden print:block mb-6 border-b-2 border-gray-800 pb-4">
                <h1 class="text-2xl font-bold text-center">Icy's Simplicitea - Monthly Report</h1>
                <p class="text-center text-gray-600 mt-2">
                    @if($isAll)
                        All Branches
                    @else
                        {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Unknown Branch' }}
                    @endif
                </p>
                <p class="text-center text-gray-600">{{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }} {{ $selectedYear }}</p>
                <p class="text-center text-sm text-gray-500">Generated: {{ now()->format('M d, Y h:i A') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8 print:hidden">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.monthly') }}" class="flex flex-wrap items-end gap-4">
                        @if($canSelectBranch)
                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                            <select name="branch_id" id="branch_id" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                <option value="all" {{ $isAll ? 'selected' : '' }}>All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id && !$isAll ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Month</label>
                            <select name="month" id="month" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                            <select name="year" id="year" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="inline-flex items-center p-1 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg print:hidden">
                            <a href="{{ route('reports.export.monthly', ['month' => $selectedMonth, 'year' => $selectedYear, 'branch_id' => $selectedBranchId]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-black rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</h3>
                                <p class="text-2xl font-bold text-gray-900 dark:text-black">&#8369;{{ number_format($totalRevenue, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</h3>
                                <p class="text-2xl font-bold text-gray-900 dark:text-black">{{ number_format($totalTransactions) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Transaction</h3>
                                <p class="text-2xl font-bold text-gray-900 dark:text-black">&#8369;{{ number_format($averageTransaction, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Daily Performance Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daily Performance</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monthly revenue trend overview</p>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Product Performance -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bestsellers this month</p>
                    </div>
                    <div class="p-4">
                        @if($productPerformance->count() > 0)
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($productPerformance->take(8) as $item)
                                @if($item->product)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->total_sold }} units sold</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-white">&#8369;{{ number_format($item->revenue, 2) }}</p>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <span class="text-4xl">📊</span>
                                <p class="text-gray-500 dark:text-gray-400 mt-2">No product data available for selected month.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown Table -->
            @if(count($dailyBreakdown) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-4">
                        Daily Breakdown - {{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }} {{ $selectedYear }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Day</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Performance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($dailyBreakdown as $day)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $day['revenue'] == 0 ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">
                                        {{ $day['date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $day['day'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-black">
                                        @if($day['revenue'] > 0)
                                            &#8369;{{ number_format($day['revenue'], 2) }}
                                        @else
                                            <span class="text-gray-400">No sales</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($totalRevenue > 0)
                                            @php
                                                $percentage = ($day['revenue'] / $totalRevenue) * 100;
                                                $barWidth = min($percentage * 2, 100); // Scale for visual representation
                                            @endphp
                                            <div class="flex items-center">
                                                <div class="flex-1 max-w-xs">
                                                    <div class="bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                        <div class="bg-simplicitea-600 h-2 rounded-full" style="width: {{ $barWidth }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ number_format($percentage, 1) }}%</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mt-8">
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">No data found</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm">
                        No transactions were made in {{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }} {{ $selectedYear }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daily Performance Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const formatCompactValue = (value) => {
            const numericValue = Number(value) || 0;
            return Math.abs(numericValue) >= 1000
                ? `${(numericValue / 1000).toFixed(1).replace(/\.0$/, '')}k`
                : numericValue.toLocaleString();
        };

        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(collect($dailyBreakdown)->pluck('date')->toArray()) !!},
                datasets: [{
                    label: 'Daily Revenue (₱)',
                    data: {!! json_encode(collect($dailyBreakdown)->pluck('revenue')->toArray()) !!},
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
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>