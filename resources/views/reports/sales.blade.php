<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sales Reports') }}
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
                <h1 class="text-2xl font-bold text-center">Icy's Simplicitea - Sales Report</h1>
                <p class="text-center text-gray-600 mt-2">
                    @if($isAll)
                        All Branches
                    @else
                        {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Unknown Branch' }}
                    @endif
                </p>
                <p class="text-center text-gray-600">Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                <p class="text-center text-sm text-gray-500">Generated: {{ now()->format('M d, Y h:i A') }}</p>
            </div>
            
            <!-- Date Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-end gap-4">
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
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" onchange="this.form.submit()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" onchange="this.form.submit()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                        </div>
                        <div class="inline-flex items-center p-1 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg print:hidden">
                            <a href="{{ route('reports.export.sales', ['start_date' => request('start_date', $startDate->format('Y-m-d')), 'end_date' => request('end_date', $endDate->format('Y-m-d')), 'branch_id' => $selectedBranchId]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-black rounded-md transition-colors">
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
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</h3>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Selling Products -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-4">Top Selling Products</h3>
                        @if($topProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach($topProducts as $item)
                                @if($item->product)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-black">{{ $item->product->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $item->total_sold }} units sold</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-black">&#8369;{{ number_format($item->revenue, 2) }}</p>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No sales data available for the selected period.</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-4">Recent Sales</h3>
                        @if($sales->count() > 0)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($sales->take(10) as $sale)
                                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-black">{{ $sale->receipt_number }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $sale->created_at->format('M d, Y h:i A') }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $sale->user->name ?? 'Staff' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-black">&#8369;{{ number_format($sale->total_amount, 2) }}</p>
                                        <span class="text-xs px-2 py-1 {{ $sale->payment_method === 'cash' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300' }} rounded-full">
                                            {{ ucfirst($sale->payment_method) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No sales found for the selected period.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sales by Staff -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-4">Sales by Staff</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    @if($salesByUser->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Staff Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transactions</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Sales</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Avg per Transaction</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($salesByUser as $userSales)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">
                                            {{ $userSales->user->name ?? 'Unknown Staff' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($userSales->transaction_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                            &#8369;{{ number_format($userSales->total_sales, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            &#8369;{{ number_format($userSales->transaction_count > 0 ? $userSales->total_sales / $userSales->transaction_count : 0, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <td class="px-6 py-3 text-sm font-bold text-gray-900 dark:text-black">Total</td>
                                        <td class="px-6 py-3 text-sm font-bold text-gray-900 dark:text-black">{{ number_format($salesByUser->sum('transaction_count')) }}</td>
                                        <td class="px-6 py-3 text-sm font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($salesByUser->sum('total_sales'), 2) }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No staff sales data available for the selected period.</p>
                    @endif
                </div>
            </div>

            <!-- Sales List -->
            @if($sales->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-4">All Sales</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Receipt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Staff</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($sales as $sale)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">
                                        {{ $sale->receipt_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $sale->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $sale->user->name ?? 'Staff' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sale->payment_method === 'cash' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300' }}">
                                            {{ ucfirst($sale->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">
                                        &#8369;{{ number_format($sale->total_amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>