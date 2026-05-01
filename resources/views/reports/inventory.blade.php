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

                            <form id="inventory-import-form" method="POST" action="{{ route('reports.inventory.import.preview') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                <div class="inline-flex items-center p-1 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                                    <input
                                        id="inventory_file"
                                        name="inventory_file"
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        required
                                        onchange="this.form.submit()"
                                        class="sr-only"
                                    >
                                    <label for="inventory_file" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-black rounded-md transition-colors cursor-pointer whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"></path>
                                        </svg>
                                        Inventory Import
                                    </label>
                                </div>
                                @error('inventory_file')
                                    <p class="basis-full text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>

                        <div class="ml-auto inline-flex items-center rounded-lg border border-transparent bg-transparent p-1">
                            <a href="{{ route('reports.inventory.import.template') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-simplicitea-600 bg-transparent px-4 py-2 text-simplicitea-700 transition-colors hover:bg-simplicitea-50 dark:text-simplicitea-300 dark:hover:bg-simplicitea-900/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Template
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

            @if($importPreview)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 print:hidden" role="dialog" aria-modal="true" aria-labelledby="inventory-import-preview-title">
                    <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-700 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 id="inventory-import-preview-title" class="text-lg font-medium text-gray-900 dark:text-black">Preview Before Saving</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $importPreview['file_name'] }} -
                                    {{ $importPreview['valid_count'] }} valid row(s),
                                    {{ $importPreview['error_count'] }} error(s)
                                </p>
                            </div>
                            <form method="POST" action="{{ route('reports.inventory.import.cancel') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Close preview">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto p-6">
                            @if($importPreview['error_count'] > 0)
                                <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                                    {{ $importPreview['error_count'] }} error(s) found. Please fix the file before confirming.
                                </div>
                            @endif

                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Row</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item Name</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Branch</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($importPreview['rows'] as $row)
                                            <tr class="{{ $row['valid'] ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-red-50 dark:bg-red-900/20' }}">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $row['row_number'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">{{ $row['item_name'] ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-black">{{ is_numeric($row['qty']) ? number_format((float) $row['qty'], 2) : ($row['qty'] ?: 'N/A') }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $row['unit'] ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $row['branch'] ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row['valid'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $row['status'] }}
                                                    </span>
                                                    @if(!$row['valid'])
                                                        <div class="mt-1 text-xs text-red-700">
                                                            {{ implode(' ', $row['errors']) }}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row sm:justify-end">
                            <form method="POST" action="{{ route('reports.inventory.import.cancel') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md sm:w-auto">
                                    Clear Preview
                                </button>
                            </form>
                            <form method="POST" action="{{ route('reports.inventory.import.confirm') }}">
                                @csrf
                                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                <button
                                    type="submit"
                                    @disabled($importPreview['error_count'] > 0)
                                    class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md sm:w-auto {{ $importPreview['error_count'] > 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 text-black' }}"
                                >
                                    Confirm Import
                                </button>
                            </form>
                        </div>
                    </div>
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
