<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales Reports') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-white rounded-md hover:bg-simplicitea-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Total Transactions</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTransactions) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Average Transaction</h3>
                                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($averageTransaction, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Selling Products -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Selling Products</h3>
                        @if($topProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach($topProducts as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->total_sold }} units sold</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₱{{ number_format($item->revenue, 2) }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No sales data available for the selected period.</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Sales</h3>
                        @if($sales->count() > 0)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($sales->take(10) as $sale)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $sale->receipt_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $sale->created_at->format('M d, Y h:i A') }}</p>
                                        <p class="text-xs text-gray-400">{{ $sale->user->name ?? 'Staff' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₱{{ number_format($sale->total_amount, 2) }}</p>
                                        <span class="text-xs px-2 py-1 {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }} rounded-full">
                                            {{ ucfirst($sale->payment_method) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No sales found for the selected period.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sales List -->
            @if($sales->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">All Sales</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $sale->receipt_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->user->name ?? 'Staff' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($sale->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱{{ number_format($sale->total_amount, 2) }}
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