<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('product-inventory.index', ['tab' => 'ingredients']) }}" 
               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Ingredient Inventory
            </h2>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                
                <!-- Ingredient Info -->
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/50 rounded-xl flex items-center justify-center">
                        <span class="text-3xl">🧪</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-black">
                            {{ $inventoryItem->ingredient->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $inventoryItem->ingredient->description }} • Unit: {{ $inventoryItem->ingredient->unit }}
                        </p>
                    </div>
                </div>

                <form action="{{ route('inventory.update', ['inventory' => $inventoryItem->id, 'view' => 'ingredients']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Current Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Current Quantity
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity" 
                                       value="{{ old('quantity', $inventoryItem->quantity) }}"
                                       step="0.01" 
                                       min="0" 
                                       required
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-xl shadow-sm focus:ring-2 focus:ring-simplicitea-500 focus:border-transparent pr-16">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">
                                    {{ $inventoryItem->ingredient->unit }}
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
                                       step="0.01" 
                                       min="0" 
                                       required
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-xl shadow-sm focus:ring-2 focus:ring-simplicitea-500 focus:border-transparent pr-16">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">
                                    {{ $inventoryItem->ingredient->unit }}
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
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $inventoryItem->status_color }}">
                                    {{ $inventoryItem->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 flex items-center justify-between">
                        <a href="{{ route('product-inventory.index', ['tab' => 'ingredients']) }}" 
                           class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-simplicitea-600 text-black rounded-xl hover:bg-simplicitea-700 transition-colors font-medium">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
