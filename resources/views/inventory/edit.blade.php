<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Ingredient') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('inventory.update', $ingredient) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Ingredient Name</span>
                                <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Description</span>
                                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">{{ old('description', $ingredient->description) }}</textarea>
                            </label>

                            <div class="grid grid-cols-2 gap-4">
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Unit</span>
                                    <select name="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                        <option value="kg" {{ old('unit', $ingredient->unit) == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                        <option value="liters" {{ old('unit', $ingredient->unit) == 'liters' ? 'selected' : '' }}>Liters</option>
                                        <option value="pieces" {{ old('unit', $ingredient->unit) == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                        <option value="grams" {{ old('unit', $ingredient->unit) == 'grams' ? 'selected' : '' }}>Grams</option>
                                        <option value="cups" {{ old('unit', $ingredient->unit) == 'cups' ? 'selected' : '' }}>Cups</option>
                                        <option value="bottles" {{ old('unit', $ingredient->unit) == 'bottles' ? 'selected' : '' }}>Bottles</option>
                                        <option value="packs" {{ old('unit', $ingredient->unit) == 'packs' ? 'selected' : '' }}>Packs</option>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Current Quantity</span>
                                    <input type="number" step="0.1" name="quantity" value="{{ old('quantity', $ingredient->quantity) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                </label>
                            </div>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Minimum Stock Level</span>
                                <input type="number" step="0.1" name="min_stock_level" value="{{ old('min_stock_level', $ingredient->min_stock_level) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                <p class="text-xs text-gray-500 mt-1">Alert when stock falls below this level</p>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="form-checkbox text-simplicitea-600 border-gray-300 rounded shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" {{ old('is_active', $ingredient->is_active) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>

                            <div class="pt-4 flex justify-between">
                                <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</a>
                                <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500">Update Ingredient</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <div><strong>Created:</strong> {{ $ingredient->created_at->format('Y-m-d H:i') }}</div>
                            <div><strong>Last Updated:</strong> {{ $ingredient->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>