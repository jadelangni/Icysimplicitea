<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-black leading-tight">{{ __('Create Product') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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

                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 gap-4">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</span>
                                <select id="category-select" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                                    <option value="">Select existing category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black" required>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</span>
                                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black">{{ old('description') }}</textarea>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Price</span>
                                <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black" required>
                            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Product Image (optional)</span>
                <div class="mt-2">
                    <div id="image-preview-container" class="hidden mb-3">
                        <img id="image-preview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                        <button type="button" id="remove-image" class="mt-2 text-sm text-red-600 hover:text-red-800">Remove image</button>
                    </div>
                    <input type="file" name="image" id="image-input" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-simplicitea-50 file:text-simplicitea-700 hover:file:bg-simplicitea-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 2MB</p>
                </div>
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
                                <input type="hidden" name="options" id="options-input" value="{{ old('options') }}">
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Create option groups (like Size) and set fixed prices. Set price to 0 to use base product price.</p>
                            </label>

                            <!-- send explicit false when unchecked, and 1 when checked -->
                            <input type="hidden" name="is_active" value="0">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>

                            <div class="pt-4">
                                <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg">Create</button>
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
                            const removeBtn = document.getElementById('remove-image');

                            imageInput.addEventListener('change', function(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        previewImg.src = e.target.result;
                                        previewContainer.classList.remove('hidden');
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    previewContainer.classList.add('hidden');
                                    previewImg.src = '';
                                }
                            });

                            removeBtn.addEventListener('click', function() {
                                imageInput.value = '';
                                previewContainer.classList.add('hidden');
                                previewImg.src = '';
                            });
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
