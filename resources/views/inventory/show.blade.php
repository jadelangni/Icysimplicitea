<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $ingredient->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Ingredient Details -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Ingredient Information</h3>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Name</label>
                                        <div class="text-base text-gray-900">{{ $ingredient->name }}</div>
                                    </div>

                                    @if($ingredient->description)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Description</label>
                                        <div class="text-base text-gray-900">{{ $ingredient->description }}</div>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Unit</label>
                                            <div class="text-base text-gray-900">{{ ucfirst($ingredient->unit) }}</div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Status</label>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ingredient->status_color }}">
                                                    {{ $ingredient->status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Information</h3>
                            
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Current Quantity</span>
                                    <span class="text-2xl font-bold text-gray-900">{{ number_format($ingredient->quantity, 1) }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Unit</span>
                                    <span class="text-lg text-gray-700">{{ $ingredient->unit }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Minimum Level</span>
                                    <span class="text-lg text-gray-700">{{ number_format($ingredient->min_stock_level, 1) }} {{ $ingredient->unit }}</span>
                                </div>

                                @if($ingredient->quantity <= $ingredient->min_stock_level)
                                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Stock Alert</h3>
                                                <div class="mt-1 text-sm text-yellow-700">This ingredient is running low and needs restocking.</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-sm text-gray-500 space-y-1">
                                <div><strong>Created:</strong> {{ $ingredient->created_at->format('F j, Y g:i A') }}</div>
                                <div><strong>Last Updated:</strong> {{ $ingredient->updated_at->format('F j, Y g:i A') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('product-inventory.index', ['tab' => 'ingredients']) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">← Back to Inventory</a>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('inventory.edit', $ingredient) }}" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg hover:bg-simplicitea-700">Edit Ingredient</a>
                            
                            <form action="{{ route('inventory.destroy', $ingredient) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-black rounded-lg hover:bg-red-700" onclick="return confirm('Are you sure you want to delete this ingredient?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>