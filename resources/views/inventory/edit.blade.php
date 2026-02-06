<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('product-inventory.index') }}" 
               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Product Inventory
            </h2>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                
                <!-- Product Info -->
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 bg-simplicitea-100 dark:bg-simplicitea-900/50 rounded-xl flex items-center justify-center">
                        <span class="text-3xl">🧋</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $inventoryItem->product->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $inventoryItem->product->category->name ?? 'Uncategorized' }} • 
                            ₱{{ number_format($inventoryItem->product->price, 2) }}
                        </p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/50 rounded-xl text-red-600 dark:text-red-400">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inventory.update', ['inventory' => $inventoryItem->id, 'view' => 'products']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Current Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Current Stock Quantity
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity" 
                                       value="{{ old('quantity', $inventoryItem->quantity) }}"
                                       step="1" 
                                       min="0" 
                                       required
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-2 focus:ring-simplicitea-500 focus:border-transparent pr-16">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">
                                    pcs
                                </div>
                            </div>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Stock Level -->
                        <div>
                            <label for="min_stock_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Minimum Stock Level (Alert Threshold)
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="min_stock_level" 
                                       id="min_stock_level" 
                                       value="{{ old('min_stock_level', $inventoryItem->min_stock_level) }}"
                                       step="1" 
                                       min="0" 
                                       required
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-2 focus:ring-simplicitea-500 focus:border-transparent pr-16">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">
                                    pcs
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                You'll receive alerts when stock falls below this level.
                            </p>
                            @error('min_stock_level')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Status -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Current Status</span>
                                @php
                                    $isLowStock = $inventoryItem->quantity <= $inventoryItem->min_stock_level;
                                    $isOutOfStock = $inventoryItem->quantity <= 0;
                                    $status = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? 'Low Stock' : 'In Stock');
                                    $statusColor = $isOutOfStock 
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' 
                                        : ($isLowStock 
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' 
                                            : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400');
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Type Info -->
                        @if($inventoryItem->product->product_type === 'composite')
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Made-to-Order Product</p>
                                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                            This product has a recipe/BOM. When sold, ingredients will be auto-deducted from the Ingredients inventory.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 flex items-center justify-between">
                        <a href="{{ route('product-inventory.index') }}" 
                           class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-simplicitea-600 text-white rounded-xl hover:bg-simplicitea-700 transition-colors font-medium">
                            Save Changes
                        </button>
                    </div>
                </form>

                <!-- Last Updated -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <div><strong>Last Updated:</strong> {{ $inventoryItem->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>