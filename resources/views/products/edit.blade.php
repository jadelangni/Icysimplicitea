<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-black leading-tight">{{ __('Edit Product') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        @php $qty = isset($branchStock) ? $branchStock : ($product->inventory->first()->quantity ?? 0); @endphp
                        @if($product->is_active && $qty > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Available</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Unavailable</span>
                        @endif
                    </div>
                    @if($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</span>
                                <select id="category-select" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if($product->category_id == $category->id) selected @endif>{{ $category->name }}</option>
                                    @endforeach
                                    <option value="custom">➕ Add New Category</option>
                                </select>
                            </label>

                            <label id="custom-category-label" class="block hidden">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">New Category Name</span>
                                <input type="text" id="custom-category-input" name="custom_category" placeholder="Enter new category name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This will create a new category and assign it to this product.</p>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</span>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</span>
                                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black">{{ old('description', $product->description) }}</textarea>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Price</span>
                                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Product Image</span>
                                <div class="mt-2">
                                    <div id="image-preview-container" class="mb-3 {{ $product->image ? '' : 'hidden' }}">
                                        <img id="image-preview" src="{{ $product->image ? asset('storage/' . $product->image) : '' }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                                        <button type="button" id="remove-image" class="mt-2 text-sm text-red-600 hover:text-red-800">Change image</button>
                                    </div>
                                    <input type="file" name="image" id="image-input" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-simplicitea-50 file:text-simplicitea-700 hover:file:bg-simplicitea-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 2MB. Leave empty to keep current image.</p>
                                </div>
                            </label>

                            <!-- send explicit false when unchecked, and 1 when checked -->
                            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox" @if($product->is_active) checked @endif>
                <span class="ml-2 text-gray-700 dark:text-gray-300">Active</span>
            </label>                            <!-- Options (variants) -->
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Options / Variants (e.g. Size, Temperature)</span>
                                <div id="options-container" class="space-y-3 mt-2">
                                    <!-- option groups will be added here -->
                                </div>
                                <div class="mt-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    <div class="flex gap-2 mb-2">
                                        <input id="option-name" type="text" placeholder="Option name (e.g. Size)" class="block w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black px-2 py-1">
                                        <button type="button" id="add-option-group" class="px-3 py-1 bg-simplicitea-100 dark:bg-simplicitea-900 text-simplicitea-700 dark:text-simplicitea-300 rounded-md">Create Option Group</button>
                                    </div>
                                    <div id="current-option-values" class="space-y-2 hidden">
                                        <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Add values for this option:</div>
                                        <div class="flex gap-2">
                                            <input id="value-name" type="text" placeholder="Value name" class="block w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black px-2 py-1">
                                            <input id="value-price" type="number" step="0.01" placeholder="Fixed price (0 = base)" class="block w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black px-2 py-1">
                                            <button type="button" id="add-value" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-md">Add Value</button>
                                        </div>
                                        <div id="current-values-list" class="space-y-1"></div>
                                        <button type="button" id="finish-option" class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-md">Finish Option</button>
                                    </div>
                                </div>
                                <input type="hidden" name="options" id="options-input" value='{{ old("options", json_encode($product->options ?? [])) }}'>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Create option groups (like Size) and set fixed prices. Set price to 0 to use base product price.</p>
                            </label>

                            <!-- Recipe / Ingredients Section -->
                            <div class="block border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <span class="text-lg">🧪</span>
                                    Product Recipe (Ingredients)
                                </span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">Define which raw materials are needed to make this product</p>
                                
                                <div id="recipe-container" class="space-y-2 mt-2">
                                    <!-- Recipe items will be added here -->
                                </div>
                                
                                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    <div class="flex gap-2 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Ingredient</label>
                                            <select id="recipe-ingredient" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black text-sm">
                                                <option value="">Select ingredient...</option>
                                                @foreach($ingredients as $ingredient)
                                                    <option value="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}" data-unit="{{ $ingredient->unit }}">
                                                        {{ $ingredient->name }} ({{ $ingredient->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Quantity</label>
                                            <input id="recipe-quantity" type="number" step="0.01" min="0.01" placeholder="0.00" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black text-sm">
                                        </div>
                                        <div class="w-20">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Unit</label>
                                            <input id="recipe-unit" type="text" readonly class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300 text-sm bg-gray-100">
                                        </div>
                                        <button type="button" id="add-recipe-item" class="px-3 py-2 bg-simplicitea-600 text-black text-sm rounded-md hover:bg-simplicitea-700 transition">
                                            Add
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="recipe" id="recipe-input" value='{{ old("recipe", json_encode($recipe ?? [])) }}'>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg">Update</button>
                                <a href="{{ route('products.index') }}" class="ml-3 text-sm text-gray-600 dark:text-gray-400">Cancel</a>
                            </div>
                        </div>
                    </form>
                    <script>
                        (function(){
                            const container = document.getElementById('options-container');
                            const hidden = document.getElementById('options-input');
                            const optionNameEl = document.getElementById('option-name');
                            const addGroupBtn = document.getElementById('add-option-group');
                            const currentValuesDiv = document.getElementById('current-option-values');
                            const valueNameEl = document.getElementById('value-name');
                            const valuePriceEl = document.getElementById('value-price');
                            const addValueBtn = document.getElementById('add-value');
                            const currentValuesList = document.getElementById('current-values-list');
                            const finishOptionBtn = document.getElementById('finish-option');
                            
                            let currentOption = null;
                            let allOptions = [];

                            function rebuildHidden() {
                                hidden.value = JSON.stringify(allOptions);
                            }

                            function renderOptions() {
                                container.innerHTML = '';
                                allOptions.forEach((opt, idx) => {
                                    const optDiv = document.createElement('div');
                                    optDiv.className = 'p-3 bg-gray-50 rounded border';
                                    const valuesList = (opt.values || []).map(v => {
                                        if (v && typeof v === 'object') {
                                            const mod = v.price ? (v.price > 0 ? `+₱${v.price}` : `-₱${Math.abs(v.price)}`) : '₱0';
                                            return `${v.label || v.value || ''} (${mod})`;
                                        }
                                        return v;
                                    }).join(', ');
                                    optDiv.innerHTML = `
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-medium text-gray-900">${opt.name}</div>
                                                <div class="text-sm text-gray-600">${valuesList}</div>
                                            </div>
                                            <button type="button" class="text-red-600 hover:text-red-800" onclick="removeOption(${idx})">Remove</button>
                                        </div>
                                    `;
                                    container.appendChild(optDiv);
                                });
                            }

                            window.removeOption = function(idx) {
                                allOptions.splice(idx, 1);
                                renderOptions();
                                rebuildHidden();
                            };

                            addGroupBtn.addEventListener('click', function() {
                                const name = optionNameEl.value.trim();
                                if (!name) return alert('Please enter option name');
                                currentOption = { name, values: [] };
                                currentValuesDiv.classList.remove('hidden');
                                optionNameEl.disabled = true;
                                addGroupBtn.disabled = true;
                            });

                            addValueBtn.addEventListener('click', function() {
                                const valueName = valueNameEl.value.trim();
                                if (!valueName) return alert('Please enter value name');
                                const priceVal = valuePriceEl.value.trim();
                                const price = priceVal ? parseFloat(priceVal) : null;
                                
                                const valueObj = price !== null ? { label: valueName, price } : valueName;
                                currentOption.values.push(valueObj);
                                
                                const valueDiv = document.createElement('div');
                                valueDiv.className = 'flex justify-between items-center p-2 bg-white border rounded';
                                const priceText = price !== null ? ` (₱${price >= 0 ? '+' : ''}${price})` : '';
                                valueDiv.innerHTML = `
                                    <span>${valueName}${priceText}</span>
                                    <button type="button" class="text-red-600 text-sm" onclick="removeCurrentValue(${currentOption.values.length - 1})">Remove</button>
                                `;
                                currentValuesList.appendChild(valueDiv);
                                
                                valueNameEl.value = '';
                                valuePriceEl.value = '';
                            });

                            window.removeCurrentValue = function(idx) {
                                currentOption.values.splice(idx, 1);
                                currentValuesList.children[idx].remove();
                            };

                            finishOptionBtn.addEventListener('click', function() {
                                if (currentOption.values.length === 0) return alert('Please add at least one value');
                                allOptions.push(currentOption);
                                currentOption = null;
                                currentValuesDiv.classList.add('hidden');
                                currentValuesList.innerHTML = '';
                                optionNameEl.value = '';
                                optionNameEl.disabled = false;
                                addGroupBtn.disabled = false;
                                renderOptions();
                                rebuildHidden();
                            });

                            // Load existing options
                            try {
                                const existing = hidden.value ? JSON.parse(hidden.value) : [];
                                if (Array.isArray(existing)) {
                                    allOptions = existing;
                                    renderOptions();
                                }
                            } catch (err) {}
                        })();

                        // Category selection handler
                        (function() {
                            const categorySelect = document.getElementById('category-select');
                            const customCategoryLabel = document.getElementById('custom-category-label');
                            const customCategoryInput = document.getElementById('custom-category-input');

                            categorySelect.addEventListener('change', function() {
                                if (this.value === 'custom') {
                                    customCategoryLabel.classList.remove('hidden');
                                    customCategoryInput.required = true;
                                    this.name = ''; // Remove name so it won't be submitted
                                } else {
                                    customCategoryLabel.classList.add('hidden');
                                    customCategoryInput.required = false;
                                    customCategoryInput.value = '';
                                    this.name = 'category_id'; // Restore name for submission
                                }
                            });
                        })();

                        // Image preview handler
                        (function() {
                            const imageInput = document.getElementById('image-input');
                            const previewContainer = document.getElementById('image-preview-container');
                            const previewImg = document.getElementById('image-preview');

                            if (imageInput) {
                                imageInput.addEventListener('change', function(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            previewImg.src = e.target.result;
                                            previewContainer.classList.remove('hidden');
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                });
                            }
                        })();

                        // Recipe/Ingredients handler
                        (function() {
                            const recipeContainer = document.getElementById('recipe-container');
                            const recipeHidden = document.getElementById('recipe-input');
                            const ingredientSelect = document.getElementById('recipe-ingredient');
                            const quantityInput = document.getElementById('recipe-quantity');
                            const unitInput = document.getElementById('recipe-unit');
                            const addBtn = document.getElementById('add-recipe-item');
                            
                            let recipeItems = [];

                            function rebuildRecipeHidden() {
                                recipeHidden.value = JSON.stringify(recipeItems);
                            }

                            function renderRecipe() {
                                recipeContainer.innerHTML = '';
                                if (recipeItems.length === 0) {
                                    recipeContainer.innerHTML = '<p class="text-sm text-gray-400 dark:text-gray-500 italic">No ingredients added yet</p>';
                                    return;
                                }
                                
                                recipeItems.forEach((item, idx) => {
                                    const div = document.createElement('div');
                                    div.className = 'flex items-center justify-between p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg';
                                    div.innerHTML = `
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
                                                <span class="text-sm">🧪</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-black text-sm">${item.name}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">${item.quantity_required} ${item.unit}</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="removeRecipeItem(${idx})" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    `;
                                    recipeContainer.appendChild(div);
                                });
                            }

                            window.removeRecipeItem = function(idx) {
                                recipeItems.splice(idx, 1);
                                renderRecipe();
                                rebuildRecipeHidden();
                            };

                            // Update unit when ingredient is selected
                            ingredientSelect.addEventListener('change', function() {
                                const selected = this.options[this.selectedIndex];
                                unitInput.value = selected.dataset.unit || '';
                            });

                            addBtn.addEventListener('click', function() {
                                const ingredientId = ingredientSelect.value;
                                const selected = ingredientSelect.options[ingredientSelect.selectedIndex];
                                const name = selected.dataset.name;
                                const quantity = parseFloat(quantityInput.value);
                                const unit = unitInput.value || selected.dataset.unit;

                                if (!ingredientId) return alert('Please select an ingredient');
                                if (!quantity || quantity <= 0) return alert('Please enter a valid quantity');

                                // Check if ingredient already exists
                                const exists = recipeItems.find(item => item.ingredient_id == ingredientId);
                                if (exists) return alert('This ingredient is already in the recipe');

                                recipeItems.push({
                                    ingredient_id: parseInt(ingredientId),
                                    name: name,
                                    quantity_required: quantity,
                                    unit: unit
                                });

                                // Reset inputs
                                ingredientSelect.value = '';
                                quantityInput.value = '';
                                unitInput.value = '';

                                renderRecipe();
                                rebuildRecipeHidden();
                            });

                            // Load existing recipe
                            try {
                                const existing = recipeHidden.value ? JSON.parse(recipeHidden.value) : [];
                                if (Array.isArray(existing)) {
                                    recipeItems = existing;
                                    renderRecipe();
                                }
                            } catch (err) {
                                console.error('Error loading recipe:', err);
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
