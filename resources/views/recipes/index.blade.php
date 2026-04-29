<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Recipe Management (BOM)') }}
            </h2>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <span class="text-lg">📋</span>
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">Bill of Materials</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Info Banner -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                        <span class="text-xl">💡</span>
                    </div>
                    <div>
                        <h3 class="text-blue-800 dark:text-blue-200 font-semibold">How Recipe Management Works</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Define the ingredients required for each product. When a sale is made:
                        </p>
                        <ul class="text-sm text-blue-600 dark:text-blue-400 mt-2 list-disc list-inside space-y-1">
                            <li><strong>Direct Products</strong> (cookies, bottled drinks): Deduct 1 unit from finished goods inventory</li>
                            <li><strong>Composite Products</strong> (milk tea): Deduct ingredients based on the recipe defined here</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🧋</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Products</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-black">{{ $products->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">📋</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">With Recipes</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $products->filter(fn($p) => $p->ingredients->count() > 0)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">⚠️</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No Recipe</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $products->filter(fn($p) => $p->product_type === 'composite' && $p->ingredients->count() === 0)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🧪</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ingredients Available</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $ingredients->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-black flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            Product Recipes
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Click on a product to manage its recipe</p>
                    </div>
                    <div class="relative">
                        <input type="text" id="productSearch" placeholder="Search products..." 
                            class="pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <div style="min-width: 1000px; width: max-content;">
                    <table class="w-full" id="productTable">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Product</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Type</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Ingredients</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Recipe Status</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($products as $product)
                            @php
                                $hasRecipe = $product->ingredients->count() > 0;
                                $isComposite = $product->product_type === 'composite';
                                $needsRecipe = $isComposite && !$hasRecipe;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 product-row cursor-pointer" 
                                data-name="{{ strtolower($product->name) }}"
                                onclick="openRecipeModal({{ $product->id }})">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-400 to-simplicitea-600 rounded-xl flex items-center justify-center text-black font-semibold text-sm">
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-black">{{ $product->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($product->product_type === 'direct')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                            📦 Direct
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                            🧋 Composite
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($hasRecipe)
                                        <span class="text-sm font-medium text-gray-900 dark:text-black">{{ $product->ingredients->count() }} ingredient(s)</span>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($product->product_type === 'direct')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                            N/A
                                        </span>
                                    @elseif($hasRecipe)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Configured
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 animate-pulse">
                                            ⚠️ Needs Recipe
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <button onclick="event.stopPropagation(); openRecipeModal({{ $product->id }})" 
                                        class="px-3 py-1.5 bg-simplicitea-600 text-black text-xs font-medium rounded-lg hover:bg-simplicitea-700 transition">
                                        {{ $hasRecipe ? 'Edit Recipe' : 'Add Recipe' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <span class="text-4xl">📦</span>
                                        <p class="mt-2">No products found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Modal -->
    <div id="recipeModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/80 backdrop-blur-sm" onclick="closeRecipeModal()"></div>
            
            <div class="relative z-10 w-full max-w-full sm:max-w-3xl lg:max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl transform transition-all">
                <!-- Modal Header -->
                <div class="relative px-6 py-5 bg-gradient-to-r from-simplicitea-600 via-simplicitea-500 to-teal-500 rounded-t-3xl">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div id="modalProductIcon" class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-black font-bold text-lg shadow-lg">
                                <span class="text-2xl">📋</span>
                            </div>
                            <div class="text-left">
                                <h3 id="modalProductName" class="text-xl font-bold text-black">Loading...</h3>
                                <p id="modalProductCategory" class="text-black/70 text-sm mt-1">Category</p>
                            </div>
                        </div>
                        <button onclick="closeRecipeModal()" class="text-black/70 hover:text-black transition p-2 hover:bg-white/10 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 max-h-[70vh] overflow-y-auto" id="recipeModalContent">
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="relative">
                            <div class="w-16 h-16 border-4 border-simplicitea-200 dark:border-simplicitea-800 rounded-full"></div>
                            <div class="absolute top-0 left-0 w-16 h-16 border-4 border-simplicitea-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 mt-4">Loading recipe...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentProductId = null;
        const allIngredients = @json($ingredients);
        const recipeUnitOptions = [
            { value: 'mg', label: 'mg' },
            { value: 'g', label: 'g' },
            { value: 'kg', label: 'kg' },
            { value: 'ml', label: 'ml' },
            { value: 'l', label: 'L' },
            { value: 'pieces', label: 'pieces' },
        ];

        // Product search functionality
        document.getElementById('productSearch').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.product-row').forEach(row => {
                const name = row.dataset.name;
                row.style.display = name.includes(search) ? '' : 'none';
            });
        });

        function openRecipeModal(productId) {
            currentProductId = productId;
            document.getElementById('recipeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadRecipeData(productId);
        }

        function closeRecipeModal() {
            document.getElementById('recipeModal').classList.add('hidden');
            document.body.style.overflow = '';
            currentProductId = null;
        }

        async function loadRecipeData(productId) {
            try {
                const response = await fetch(`/recipes/${productId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    renderRecipeForm(data.product, data.recipe);
                } else {
                    showToast('Failed to load recipe', 'error');
                }
            } catch (error) {
                console.error('Error loading recipe:', error);
                showToast('Error loading recipe data', 'error');
            }
        }

        function renderRecipeForm(product, recipe) {
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductCategory').textContent = product.category;
            document.getElementById('modalProductIcon').innerHTML = `<span class="text-xl">${product.product_type === 'direct' ? '📦' : '🧋'}</span>`;

            let ingredientRows = '';
            recipe.forEach((item, index) => {
                ingredientRows += createIngredientRow(item, index);
            });

            const html = `
                <form id="recipeForm" onsubmit="saveRecipe(event)">
                    <!-- Product Type Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Product Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all ${product.product_type === 'direct' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'}" onclick="selectProductType('direct')">
                                <input type="radio" name="product_type" value="direct" ${product.product_type === 'direct' ? 'checked' : ''} class="hidden" id="typeDirect">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-xl flex items-center justify-center">
                                    <span class="text-xl">📦</span>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900 dark:text-black">Direct Product</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Finished goods (deduct from product inventory)</p>
                                </div>
                            </label>
                            
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all ${product.product_type === 'composite' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'}" onclick="selectProductType('composite')">
                                <input type="radio" name="product_type" value="composite" ${product.product_type === 'composite' ? 'checked' : ''} class="hidden" id="typeComposite">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800 rounded-xl flex items-center justify-center">
                                    <span class="text-xl">🧋</span>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900 dark:text-black">Composite Product</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Made-to-order (deduct from ingredients)</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Ingredients Section (only for composite) -->
                    <div id="ingredientsSection" class="${product.product_type === 'composite' ? '' : 'hidden'}">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Required Ingredients</label>
                            <button type="button" onclick="addIngredientRow()" class="px-3 py-1.5 bg-green-600 text-black text-xs font-medium rounded-lg hover:bg-green-700 transition flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Ingredient
                            </button>
                        </div>
                        
                        <div id="ingredientsList" class="space-y-3">
                            ${ingredientRows || '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No ingredients added yet. Click "Add Ingredient" to start.</p>'}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeRecipeModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-black transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-simplicitea-600 text-black font-medium rounded-xl hover:bg-simplicitea-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Recipe
                        </button>
                    </div>
                </form>
            `;

            document.getElementById('recipeModalContent').innerHTML = html;
        }

        function createIngredientRow(item = null, index = null) {
            const rowIndex = index ?? document.querySelectorAll('.ingredient-row').length;
            const selectedId = item?.ingredient_id || '';
            const quantity = item?.quantity_required || '';
            const unit = item?.unit || '';

            let options = '<option value="">Select Ingredient</option>';
            allIngredients.forEach(ing => {
                options += `<option value="${ing.id}" data-unit="${ing.unit}" ${ing.id == selectedId ? 'selected' : ''}>${ing.name} (${ing.quantity} ${ing.unit} available)</option>`;
            });

            let unitOptions = '<option value="">Select unit</option>';
            recipeUnitOptions.forEach(option => {
                unitOptions += `<option value="${option.value}" ${option.value === unit ? 'selected' : ''}>${option.label}</option>`;
            });

            return `
                <div class="ingredient-row flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div class="flex-1">
                        <select name="ingredients[${rowIndex}][ingredient_id]" required class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500" onchange="updateUnit(this, ${rowIndex})">
                            ${options}
                        </select>
                    </div>
                    <div class="w-full sm:w-28">
                        <input type="number" name="ingredients[${rowIndex}][quantity_required]" value="${quantity}" step="0.01" min="0.01" required placeholder="Qty" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500">
                    </div>
                    <div class="w-full sm:w-32">
                        <select name="ingredients[${rowIndex}][unit]" id="unit_${rowIndex}" required class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500">
                            ${unitOptions}
                        </select>
                    </div>
                    <button type="button" onclick="removeIngredientRow(this)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `;
        }

        function addIngredientRow() {
            const list = document.getElementById('ingredientsList');
            // Remove the "no ingredients" message if present
            const noIngredientsMsg = list.querySelector('p');
            if (noIngredientsMsg) noIngredientsMsg.remove();
            
            list.insertAdjacentHTML('beforeend', createIngredientRow());
        }

        function removeIngredientRow(button) {
            button.closest('.ingredient-row').remove();
        }

        function updateUnit(select, index) {
            const option = select.options[select.selectedIndex];
            const ingredientUnit = option.dataset.unit || '';
            const unitSelect = document.getElementById(`unit_${index}`);

            if (unitSelect && !unitSelect.value) {
                unitSelect.value = ingredientUnit;
            }
        }

        function selectProductType(type) {
            document.getElementById('typeDirect').checked = (type === 'direct');
            document.getElementById('typeComposite').checked = (type === 'composite');
            
            // Update visual selection
            document.querySelectorAll('label[onclick^="selectProductType"]').forEach(label => {
                label.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30', 'border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/30');
                label.classList.add('border-gray-200', 'dark:border-gray-600');
            });
            
            if (type === 'direct') {
                document.querySelector('label[onclick="selectProductType(\'direct\')"]').classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30');
                document.getElementById('ingredientsSection').classList.add('hidden');
            } else {
                document.querySelector('label[onclick="selectProductType(\'composite\')"]').classList.add('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/30');
                document.getElementById('ingredientsSection').classList.remove('hidden');
            }
        }

        async function saveRecipe(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const productType = formData.get('product_type');
            
            // Build ingredients array
            const ingredients = [];
            document.querySelectorAll('.ingredient-row').forEach((row, index) => {
                const ingredientId = row.querySelector(`[name="ingredients[${index}][ingredient_id]"]`)?.value;
                const quantity = row.querySelector(`[name="ingredients[${index}][quantity_required]"]`)?.value;
                const unit = row.querySelector(`[name="ingredients[${index}][unit]"]`)?.value;
                
                if (ingredientId && quantity) {
                    ingredients.push({
                        ingredient_id: parseInt(ingredientId),
                        quantity_required: parseFloat(quantity),
                        unit: unit || null
                    });
                }
            });

            if (productType === 'composite' && ingredients.length === 0) {
                showToast('Please add at least one ingredient for composite products', 'error');
                return;
            }

            try {
                const response = await fetch(`/recipes/${currentProductId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_type: productType,
                        ingredients: ingredients
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Recipe saved successfully!', 'success');
                    closeRecipeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Failed to save recipe', 'error');
                }
            } catch (error) {
                console.error('Error saving recipe:', error);
                showToast('Error saving recipe', 'error');
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-2xl shadow-lg z-[60] transform translate-y-full transition-all duration-300 flex items-center gap-3 ${
                type === 'success' ? 'bg-green-500 text-black' : 'bg-red-500 text-black'
            }`;
            toast.innerHTML = `
                <span class="text-lg">${type === 'success' ? '✅' : '❌'}</span>
                <span class="font-medium">${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-y-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-y-full');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
</x-app-layout>
