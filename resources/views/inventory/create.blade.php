<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add New Ingredient') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full sm:max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('inventory.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-4">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Ingredient Name</span>
                                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Description</span>
                                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">{{ old('description') }}</textarea>
                            </label>

                            <div class="grid grid-cols-2 gap-4">
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Inventory Unit from Supplier</span>
                                    <input type="text" name="unit" list="inventory-unit-options" value="{{ old('unit') }}" placeholder="e.g., bottle, kg, pack, can, tray, gallon" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                    <datalist id="inventory-unit-options">
                                        <option value="bottle"></option>
                                        <option value="bottles"></option>
                                        <option value="can"></option>
                                        <option value="cans"></option>
                                        <option value="tray"></option>
                                        <option value="trays"></option>
                                        <option value="gallon"></option>
                                        <option value="gallons"></option>
                                        <option value="kg"></option>
                                        <option value="g"></option>
                                        <option value="ml"></option>
                                        <option value="l"></option>
                                        <option value="pieces"></option>
                                        <option value="packs"></option>
                                        <option value="pack"></option>
                                    </datalist>
                                </label>
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Recipe Unit</span>
                                    <select name="recipe_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                        <option value="g" {{ old('recipe_unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                        <option value="kg" {{ old('recipe_unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                        <option value="ml" {{ old('recipe_unit') == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                        <option value="l" {{ old('recipe_unit') == 'l' ? 'selected' : '' }}>Liters (L)</option>
                                        <option value="pieces" {{ old('recipe_unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                        <option value="tbsp" {{ old('recipe_unit') == 'tbsp' ? 'selected' : '' }}>Tablespoons (tbsp)</option>
                                        <option value="scoop" {{ old('recipe_unit') == 'scoop' ? 'selected' : '' }}>Scoop</option>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Recipe Units per Inventory Unit</span>
                                    <input type="number" name="recipe_units_per_inventory_unit" list="recipe-conversion-presets" value="{{ old('recipe_units_per_inventory_unit', 1) }}" step="0.0001" min="0.0001" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500">
                                    <datalist id="recipe-conversion-presets">
                                        <option value="250"></option>
                                        <option value="330"></option>
                                        <option value="500"></option>
                                        <option value="750"></option>
                                        <option value="1000"></option>
                                        <option value="1500"></option>
                                        <option value="2000"></option>
                                    </datalist>
                                    <p class="text-xs text-gray-500 mt-1">Common bottle sizes in ml for liquid inventory.</p>
                                </label>

                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">Current Quantity</span>
                                    <input type="number" step="0.1" name="quantity" value="{{ old('quantity', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                </label>
                            </div>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Minimum Stock Level</span>
                                <input type="number" step="0.1" name="min_stock_level" value="{{ old('min_stock_level', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                <p class="text-xs text-gray-500 mt-1">Alert when stock falls below this level</p>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="form-checkbox text-simplicitea-600 border-gray-300 rounded shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>

                            <div class="pt-4 flex justify-between">
                                <a href="{{ route('product-inventory.index', ['tab' => 'ingredients']) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</a>
                                <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500">Add Ingredient</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
