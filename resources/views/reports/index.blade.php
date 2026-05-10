<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reports Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-black">Monthly Sales Overview</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">A wider view of monthly sales performance</p>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/50 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-black">Sales Reports</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">View detailed sales analytics</p>
                            <a href="{{ route('reports.sales') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                View Reports
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 bg-green-100 dark:bg-green-900/50 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-black">Inventory Reports</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">Monitor stock levels and usage</p>
                            <a href="{{ route('reports.inventory') }}" class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-500">
                                View Reports
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 bg-yellow-100 dark:bg-yellow-900/50 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-black">Daily Reports</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">Today's sales performance</p>
                            <a href="{{ route('reports.daily') }}" class="inline-flex items-center text-sm font-medium text-yellow-600 hover:text-yellow-500">
                                View Reports
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/50 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-black">Monthly Reports</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-6">Monthly sales trends</p>
                            <a href="{{ route('reports.monthly') }}" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-500">
                                View Reports
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Sales Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const formatCompactValue = (value) => {
            const numericValue = Number(value) || 0;
            return Math.abs(numericValue) >= 1000
                ? `${(numericValue / 1000).toFixed(1).replace(/\.0$/, '')}k`
                : numericValue.toLocaleString();
        };

        const monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Sales (₱)',
                    data: {!! json_encode($monthlyData) !!},
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
                            padding: 8
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>