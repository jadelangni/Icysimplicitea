<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inventory Reports') }}
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
                <h1 class="text-2xl font-bold text-center">Icy's Simplicitea - Inventory Report</h1>
                <p class="text-center text-gray-600 mt-2">
                    {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Unknown Branch' }}
                </p>
                <p class="text-center text-gray-600">Current Stock Status</p>
                <p class="text-center text-sm text-gray-500">Generated: {{ now()->format('M d, Y h:i A') }}</p>
            </div>
            
            <!-- Branch Filter & Print Button -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 print:hidden">
                <div class="p-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div class="flex flex-wrap items-end gap-4">
                            @if($canSelectBranch)
                                <form method="GET" action="{{ route('reports.inventory') }}">
                                    <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                                    <select name="branch_id" id="branch_id" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ (int) $selectedBranchId === (int) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif

                            <div class="inline-flex items-center p-1 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                                <a href="{{ route('reports.export.inventory', ['branch_id' => $selectedBranchId]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-black rounded-md transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export Excel
                                </a>
                            </div>


                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 print:hidden">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 print:hidden">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Detailed Inventory List -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mt-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-black">Inventory Details</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('product-inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-black rounded-md hover:bg-simplicitea-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Manage Inventory
                            </a>
                            <a href="{{ route('reports.forecast', ['branch_id' => $selectedBranchId]) }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-black rounded-md hover:bg-teal-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5V4.5m0 15h16M7 16l3-4 4 3 5-7"></path>
                                </svg>
                                Predictive Forecast
                            </a>
                        </div>
                    </div>
                    @if($ingredients->count() > 0)
                        <div class="overflow-x-auto">
                            <div style="min-width: 920px; width: max-content;">
                            <table class="w-max divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingredient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ingredients as $ingredient)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-black">{{ $ingredient->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $ingredient->description ?? 'No description' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ingredient->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-black">
                                            {{ number_format($ingredient->branch_quantity, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'In Stock' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                                    'Low Stock' => 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300',
                                                    'No Stock' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300'
                                                ];
                                                $colorClass = $statusColors[$ingredient->branch_status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                                {{ $ingredient->branch_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ingredient->branch_last_updated ? $ingredient->branch_last_updated->format('M d, Y h:i A') : 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">No inventory items found</p>
                            <p class="text-gray-400 text-sm mb-4">Start by adding ingredients to your inventory</p>
                            <a href="{{ route('product-inventory.index', ['tab' => 'ingredients']) }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 text-black rounded-md hover:bg-simplicitea-700">
                                Manage Ingredients
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>


