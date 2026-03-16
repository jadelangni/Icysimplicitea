<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Point of Sale') }}
        </h2>
    </x-slot>



    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg onclick="this.parentElement.parentElement.style.display='none'" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Left Panel: Product Categories and Item List -->
                <div class="w-full lg:flex-[2]">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white whitespace-nowrap">Products</h3>
                                <!-- Search Bar -->
                                <div class="relative w-56">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" id="product-search" placeholder="Search products..." 
                                           class="w-full pl-9 pr-9 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm transition-colors">
                                    <button type="button" id="clear-search-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                                        <svg class="h-4 w-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div id="search-results-info" class="hidden mb-3 text-sm text-gray-500 dark:text-gray-400">
                                <span id="search-results-count">0</span> results for "<span id="search-query-display"></span>"
                            </div>
                            
                            <!-- Category Tabs (Grouped) -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Product Categories</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button class="category-tab active px-4 py-2 bg-simplicitea-600 text-white rounded-lg font-medium text-sm hover:bg-simplicitea-700 transition-colors" data-category="all">
                                        🔍 All Products
                                    </button>
                                    @php
                                        // Build grouped category tabs matching the product grouping logic
                                        $tabGroups = [
                                            'Milk Tea' => ['milk tea'],
                                            'Frappe' => ['frappe'],
                                            'Fruit Tea' => ['fruit'],
                                            'Coffee' => ['coffee'],
                                            'Burgers' => ['burger'],
                                            'Chicken Wings' => ['chicken', 'wing'],
                                            'Rice Meals' => ['rice'],
                                            'Snacks' => ['snack'],
                                        ];
                                        $tabEmojis = [
                                            'Milk Tea' => '🧋', 'Frappe' => '🥤', 'Fruit Tea' => '🍹',
                                            'Coffee' => '☕', 'Burgers' => '🍔', 'Chicken Wings' => '🍗',
                                            'Rice Meals' => '🍚', 'Snacks' => '🍿',
                                        ];
                                        $usedTabCategoryIds = [];
                                        $tabData = [];
                                        foreach($tabGroups as $tabName => $keywords) {
                                            $matchingCats = $categories->filter(function($cat) use ($keywords) {
                                                foreach($keywords as $kw) {
                                                    if(str_contains(strtolower($cat->name), $kw)) return true;
                                                }
                                                return false;
                                            });
                                            if($matchingCats->count() > 0) {
                                                $ids = $matchingCats->pluck('id')->toArray();
                                                // Only show tab if there are products in these categories
                                                $hasProducts = $products->whereIn('category_id', $ids)->count() > 0;
                                                if($hasProducts) {
                                                    $tabData[] = ['name' => $tabName, 'ids' => $ids, 'emoji' => $tabEmojis[$tabName] ?? '📦'];
                                                    $usedTabCategoryIds = array_merge($usedTabCategoryIds, $ids);
                                                }
                                            }
                                        }
                                        // Add remaining ungrouped categories
                                        foreach($categories->whereNotIn('id', $usedTabCategoryIds) as $cat) {
                                            $hasProducts = $products->where('category_id', $cat->id)->count() > 0;
                                            if($hasProducts) {
                                                $tabData[] = ['name' => $cat->name, 'ids' => [$cat->id], 'emoji' => '📦'];
                                            }
                                        }
                                    @endphp
                                    @foreach($tabData as $tab)
                                    <button class="category-tab px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" data-category="{{ implode(',', $tab['ids']) }}">
                                        {{ $tab['emoji'] }} {{ $tab['name'] }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Products Grid - Grouped by Category -->
                            <div id="products-container" class="space-y-8">
                                @php
                                    // Define category groups to merge similar categories
                                    $categoryGroups = [
                                        'Milk Tea' => ['milk tea'],
                                        'Frappe' => ['frappe'],
                                        'Fruit Tea' => ['fruit'],
                                        'Coffee' => ['coffee'],
                                        'Burgers' => ['burger'],
                                        'Chicken Wings' => ['chicken', 'wing'],
                                        'Rice Meals' => ['rice'],
                                        'Snacks' => ['snack'],
                                    ];
                                    
                                    $processedCategoryIds = [];
                                    $groupedProducts = [];
                                    
                                    // Group categories
                                    foreach($categoryGroups as $groupName => $keywords) {
                                        $matchingCategories = $categories->filter(function($cat) use ($keywords) {
                                            foreach($keywords as $keyword) {
                                                if(str_contains(strtolower($cat->name), $keyword)) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        });
                                        
                                        if($matchingCategories->count() > 0) {
                                            $categoryIds = $matchingCategories->pluck('id')->toArray();
                                            $groupProducts = $products->whereIn('category_id', $categoryIds);
                                            
                                            if($groupProducts->count() > 0) {
                                                $groupedProducts[$groupName] = [
                                                    'products' => $groupProducts,
                                                    'category_ids' => $categoryIds,
                                                ];
                                                $processedCategoryIds = array_merge($processedCategoryIds, $categoryIds);
                                            }
                                        }
                                    }
                                    
                                    // Add any remaining categories not matched
                                    $remainingCategories = $categories->whereNotIn('id', $processedCategoryIds);
                                    foreach($remainingCategories as $cat) {
                                        $catProducts = $products->where('category_id', $cat->id);
                                        if($catProducts->count() > 0) {
                                            $groupedProducts[$cat->name] = [
                                                'products' => $catProducts,
                                                'category_ids' => [$cat->id],
                                            ];
                                        }
                                    }
                                    
                                    // Get emoji for group
                                    function getGroupEmoji($groupName) {
                                        $name = strtolower($groupName);
                                        if(str_contains($name, 'milk tea')) return '🧋';
                                        if(str_contains($name, 'frappe')) return '🥤';
                                        if(str_contains($name, 'fruit')) return '🍹';
                                        if(str_contains($name, 'coffee')) return '☕';
                                        if(str_contains($name, 'burger')) return '🍔';
                                        if(str_contains($name, 'chicken') || str_contains($name, 'wing')) return '🍗';
                                        if(str_contains($name, 'rice')) return '🍚';
                                        if(str_contains($name, 'snack')) return '🍿';
                                        return '📦';
                                    }
                                @endphp
                                
                                @foreach($groupedProducts as $groupName => $groupData)
                                <div class="category-section" data-category-ids="{{ implode(',', $groupData['category_ids']) }}">
                                    <!-- Category Header -->
                                    <div class="flex items-center gap-3 mb-4 pb-2 border-b-2 border-simplicitea-200 dark:border-simplicitea-700">
                                        <span class="text-2xl">{{ getGroupEmoji($groupName) }}</span>
                                        <h4 class="text-lg font-bold text-gray-800 dark:text-white">{{ $groupName }}</h4>
                                        <span class="ml-auto text-sm text-gray-500 dark:text-gray-400">{{ $groupData['products']->count() }} items</span>
                                    </div>
                                    
                                    <!-- Products Grid for this Category -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                        @foreach($groupData['products'] as $product)
                                        @php 
                                            $qty = $product->inventory->first()->quantity ?? 0;
                                            $hasSizes = $product->options && is_array($product->options) && !empty($product->options);
                                        @endphp
                                        <div class="product-card bg-white dark:bg-gray-700 rounded-xl p-4 cursor-pointer hover:shadow-lg hover:scale-[1.02] transition-all duration-200 border-2 {{ $qty > 0 ? 'border-gray-100 dark:border-gray-600 hover:border-simplicitea-300' : 'border-red-200 opacity-60' }}" 
                                             data-category="{{ $product->category_id }}"
                                             data-product-id="{{ $product->id }}"
                                             data-product-name="{{ $product->name }}"
                                             data-product-price="{{ $product->price }}"
                                             data-stock="{{ $qty }}"
                                             data-options='{{ json_encode($product->options ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                                            
                                            {{-- Product Image --}}
                                            @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded-lg mb-3">
                                            @else
                                            <div class="w-full h-24 bg-gradient-to-br from-simplicitea-100 to-simplicitea-200 dark:from-gray-600 dark:to-gray-700 rounded-lg mb-3 flex items-center justify-center">
                                                <span class="text-3xl">🧋</span>
                                            </div>
                                            @endif
                                            
                                            {{-- Product Name --}}
                                            <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2">
                                                {{ $product->name }}
                                            </h4>
                                            
                                            {{-- Price Display --}}
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg font-bold text-simplicitea-600 dark:text-simplicitea-400">
                                                    ₱{{ number_format($product->price, 0) }}
                                                </span>
                                                @if($hasSizes)
                                                    <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                                        Sizes
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            {{-- Stock Status --}}
                                            @if($qty <= 0)
                                                <div class="mt-2 text-center">
                                                    <span class="text-xs font-medium text-red-600 dark:text-red-400">Out of Stock</span>
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Current Customer's Order Summary -->
                <div class="w-full lg:flex-[1]">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer's Order</h3>
                            
                            <!-- Cart Items -->
                            <div id="cart-items" class="space-y-2 mb-6 min-h-[300px] max-h-[400px] overflow-y-auto bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-gray-500 dark:text-gray-400 text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"></path>
                                    </svg>
                                    <p class="text-sm">No items selected</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Click on products to add them</p>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="bg-simplicitea-50 dark:bg-simplicitea-900/30 rounded-lg p-4 mb-6">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Order Summary</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span id="subtotal" class="font-medium dark:text-white">₱0.00</span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                                        <div class="flex justify-between text-lg font-bold text-simplicitea-700 dark:text-simplicitea-400">
                                            <span>Total:</span>
                                            <span id="total">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <form id="checkout-form" class="space-y-4">
                                @csrf
                                <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Payment Details</h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                                            <select name="payment_method" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                                <option value="cash">💵 Cash</option>
                                                <option value="card">💳 Card</option>
                                                <option value="gcash">📱 GCash</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Paid</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">₱</span>
                                                <input type="number" name="amount_paid" step="0.01" min="0" placeholder="0.00" class="w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <button type="submit" id="process-sale-btn" class="w-full bg-simplicitea-600 text-white py-3 px-4 rounded-lg hover:bg-simplicitea-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors" disabled>
                                        🛒 Process Sale
                                    </button>
                                    
                                    <button type="button" id="clear-cart-btn" class="w-full bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors">
                                        🗑️ Clear Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Order Confirmation Modal -->
    <div id="order-confirmation-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/60 dark:bg-gray-900/80 backdrop-blur-sm" id="modal-backdrop"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden transform transition-all">
                <!-- Modal Header -->
                <div class="bg-simplicitea-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Confirm Order
                    </h3>
                    <p class="text-simplicitea-100 text-sm mt-1">Please review before processing</p>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
                    <!-- Order Items -->
                    <div id="confirm-order-items" class="space-y-2 mb-4">
                        <!-- Items will be populated by JS -->
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

                    <!-- Order Totals -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span id="confirm-subtotal" class="font-medium text-gray-900 dark:text-white">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-simplicitea-700 dark:text-simplicitea-400 pt-1">
                            <span>Total</span>
                            <span id="confirm-total">₱0.00</span>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Payment Method</span>
                            <span id="confirm-payment-method" class="font-medium text-gray-900 dark:text-white">Cash</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Amount Paid</span>
                            <span id="confirm-amount-paid" class="font-medium text-gray-900 dark:text-white">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Change</span>
                            <span id="confirm-change" class="font-bold text-green-600 dark:text-green-400">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button type="button" id="cancel-order-btn" class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirm-order-btn" class="flex-1 px-4 py-2.5 bg-simplicitea-600 text-white rounded-xl font-medium hover:bg-simplicitea-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm & Process
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- JavaScript for POS functionality -->
    <!-- Product Options Modal -->
    <div id="product-options-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div id="product-options-backdrop" class="absolute inset-0 bg-black/60 z-40"></div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl relative z-50 w-full max-w-md mx-4 overflow-hidden">
            {{-- Modal Header --}}
            <div class="bg-simplicitea-600 text-white px-6 py-4">
                <h3 id="modal-product-name" class="text-xl font-bold">Select Size</h3>
                <p class="text-simplicitea-100 text-sm mt-1">Choose your preferred size</p>
            </div>
            
            {{-- Modal Body --}}
            <form id="product-options-form" class="p-6">
                <div id="options-container" class="space-y-4"></div>
                
                {{-- Action Buttons --}}
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" id="modal-cancel-btn" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="modal-add-btn" class="flex-1 px-4 py-3 bg-simplicitea-600 text-white rounded-xl font-medium hover:bg-simplicitea-700 transition-colors flex items-center justify-center gap-2">
                        <span>🛒</span>
                        <span>Add to Cart</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Load cart from localStorage (persist across refreshes)
        let cart = [];
        try {
            const savedCart = localStorage.getItem('pos_cart');
            if (savedCart) {
                cart = JSON.parse(savedCart);
            }
        } catch(e) {
            console.error('Error loading cart from localStorage:', e);
            cart = [];
        }
        let subtotal = 0;
        let currentCategory = 'all';
        let currentSearchQuery = '';
        
        const searchInput = document.getElementById('product-search');
        const clearSearchBtn = document.getElementById('clear-search-btn');
        const searchResultsInfo = document.getElementById('search-results-info');
        const searchResultsCount = document.getElementById('search-results-count');
        const searchQueryDisplay = document.getElementById('search-query-display');

        // Search input handler
        searchInput.addEventListener('input', function() {
            currentSearchQuery = this.value.toLowerCase().trim();
            clearSearchBtn.classList.toggle('hidden', !currentSearchQuery);
            filterProducts();
        });

        // Clear search button
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            currentSearchQuery = '';
            this.classList.add('hidden');
            searchResultsInfo.classList.add('hidden');
            filterProducts();
            searchInput.focus();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                currentSearchQuery = '';
                clearSearchBtn.classList.add('hidden');
                searchResultsInfo.classList.add('hidden');
                filterProducts();
                searchInput.blur();
            }
        });

        function filterProducts() {
            let visibleCount = 0;
            // Parse selected category IDs (supports comma-separated groups)
            const selectedIds = currentCategory === 'all' ? null : currentCategory.split(',');
            
            // First, handle category sections visibility
            document.querySelectorAll('.category-section').forEach(section => {
                const sectionCategoryIds = section.dataset.categoryIds.split(',');
                const categoryMatch = !selectedIds || sectionCategoryIds.some(id => selectedIds.includes(id));
                
                if (categoryMatch) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
            
            // Then filter individual product cards within visible sections
            document.querySelectorAll('.product-card').forEach(card => {
                const productName = card.dataset.productName.toLowerCase();
                const categoryMatch = !selectedIds || selectedIds.includes(card.dataset.category);
                const searchMatch = !currentSearchQuery || productName.includes(currentSearchQuery);
                
                if (categoryMatch && searchMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                    
                    if (currentSearchQuery) {
                        highlightText(card);
                    } else {
                        removeHighlight(card);
                    }
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Hide category sections that have no visible products (when searching)
            if (currentSearchQuery) {
                document.querySelectorAll('.category-section').forEach(section => {
                    const visibleProducts = section.querySelectorAll('.product-card[style*="display: block"]').length;
                    if (visibleProducts === 0) {
                        section.style.display = 'none';
                    }
                });
            }

            // Show/hide search results info
            if (currentSearchQuery) {
                searchResultsInfo.classList.remove('hidden');
                searchResultsCount.textContent = visibleCount;
                searchQueryDisplay.textContent = currentSearchQuery;
            } else {
                searchResultsInfo.classList.add('hidden');
            }
        }

        function highlightText(card) {
            const nameEl = card.querySelector('h4');
            if (!nameEl) return;
            const originalName = card.dataset.productName;
            const regex = new RegExp(`(${currentSearchQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            nameEl.innerHTML = originalName.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-600 rounded px-0.5">$1</mark>');
        }

        function removeHighlight(card) {
            const nameEl = card.querySelector('h4');
            if (!nameEl) return;
            nameEl.textContent = card.dataset.productName;
        }

        // Category filtering (supports comma-separated grouped IDs)
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.category-tab').forEach(t => {
                    t.classList.remove('active', 'bg-simplicitea-600', 'text-white');
                    t.classList.add('bg-gray-100', 'text-gray-700');
                });
                this.classList.add('active', 'bg-simplicitea-600', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-700');

                // Update current category (may be comma-separated group)
                currentCategory = this.dataset.category;
                filterProducts();
            });
        });

        // Add to cart (supports product options/variants)
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productPrice = parseFloat(this.dataset.productPrice);
                const stock = parseInt(this.dataset.stock);
                const optionsData = this.getAttribute('data-options') || '[]';
                let options = [];
                try { 
                    options = JSON.parse(optionsData); 
                    if (!Array.isArray(options)) options = [];
                } catch(e) { 
                    console.error('Error parsing options for product:', productName, e);
                    options = []; 
                }

                if (stock <= 0) {
                    alert('This product is out of stock!');
                    return;
                }

                // If product has options, open modal to select
                if (options && options.length > 0) {
                    openOptionsModal({ productId, productName, productPrice, stock, options });
                    return;
                }

                // No options - default add behavior
                addOrIncrementCartItem({ productId, productName, productPrice, stock, options: null });
                updateCart();
            });
        });

        // Helper to find existing cart item by productId + options
        function sameOptions(a, b) {
            try { return JSON.stringify(a || {}) === JSON.stringify(b || {}); }
            catch(e) { return false; }
        }

        function addOrIncrementCartItem(item) {

            const existingItem = cart.find(i => i.productId === item.productId && sameOptions(i.options, item.options));
            if (existingItem) {
                if (existingItem.quantity >= item.stock) {
                    alert('Cannot add more items than available in stock!');
                    return;
                }
                // Update price in case it changed
                existingItem.productPrice = item.productPrice;
                existingItem.quantity += 1;

            } else {
                cart.push({
                    productId: item.productId,
                    productName: item.productName,
                    productPrice: item.productPrice,
                    quantity: 1,
                    stock: item.stock,
                    options: item.options || null
                });

            }
        }

        // Open modal to select product options
        function openOptionsModal(product) {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const title = document.getElementById('modal-product-name');
            container.innerHTML = '';
            title.textContent = product.productName;

            // Build size buttons for each option
            product.options.forEach((opt, idx) => {
                let values = opt.values || [];
                if (!Array.isArray(values)) {
                    values = typeof values === 'string' ? values.split(',').map(v => v.trim()) : [values];
                }
                
                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-3';

                const label = document.createElement('label');
                label.className = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2';
                label.textContent = opt.name || 'Select Size';

                const buttonsDiv = document.createElement('div');
                buttonsDiv.className = 'grid grid-cols-2 gap-3';
                buttonsDiv.setAttribute('data-option-name', opt.name || `Option ${idx+1}`);

                // Create size buttons
                values.forEach((v, vIdx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'size-option-btn p-4 rounded-xl border-2 transition-all text-center hover:border-simplicitea-400 ' + 
                                   (vIdx === 0 ? 'border-simplicitea-500 bg-simplicitea-50 dark:bg-simplicitea-900/30' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700');
                    
                    let labelValue = '';
                    let priceVal = 0;
                    
                    if (v && typeof v === 'object' && (v.label || v.value || v.name)) {
                        labelValue = v.label || v.value || v.name || '';
                        priceVal = (v.price !== undefined && v.price !== null) ? parseFloat(v.price) : 0;
                    } else {
                        labelValue = String(v);
                    }
                    
                    btn.dataset.label = labelValue;
                    btn.dataset.price = priceVal;
                    btn.dataset.optionName = opt.name || `Option ${idx+1}`;
                    
                    btn.innerHTML = `
                        <div class="font-bold text-gray-900 dark:text-white text-lg">${labelValue}</div>
                        <div class="text-simplicitea-600 dark:text-simplicitea-400 font-semibold mt-1">₱${priceVal > 0 ? priceVal.toFixed(0) : parseFloat(product.productPrice).toFixed(0)}</div>
                    `;
                    
                    btn.addEventListener('click', function() {
                        // Remove selection from siblings
                        buttonsDiv.querySelectorAll('.size-option-btn').forEach(b => {
                            b.classList.remove('border-simplicitea-500', 'bg-simplicitea-50', 'dark:bg-simplicitea-900/30');
                            b.classList.add('border-gray-200', 'dark:border-gray-600', 'bg-white', 'dark:bg-gray-700');
                        });
                        // Select this button
                        this.classList.remove('border-gray-200', 'dark:border-gray-600', 'bg-white', 'dark:bg-gray-700');
                        this.classList.add('border-simplicitea-500', 'bg-simplicitea-50', 'dark:bg-simplicitea-900/30');
                        updateModalPrice();
                    });
                    
                    buttonsDiv.appendChild(btn);
                });

                wrapper.appendChild(label);
                wrapper.appendChild(buttonsDiv);
                container.appendChild(wrapper);
            });

            // Show modal
            modal.classList.remove('hidden');
            modal.dataset.currentProduct = JSON.stringify(product);

            // Update price display
            const updateModalPrice = () => {
                let computed = parseFloat(product.productPrice);
                
                // Get selected size price
                const selectedBtn = container.querySelector('.size-option-btn.border-simplicitea-500');
                if (selectedBtn && selectedBtn.dataset.price) {
                    const priceVal = parseFloat(selectedBtn.dataset.price);
                    if (priceVal > 0) {
                        computed = priceVal;
                    }
                }
                
                // Show total price
                let totalHint = container.querySelector('.total-price-hint');
                if (!totalHint) {
                    totalHint = document.createElement('div');
                    totalHint.className = 'total-price-hint text-center text-xl font-bold text-simplicitea-600 dark:text-simplicitea-400 mt-4 p-3 bg-simplicitea-50 dark:bg-simplicitea-900/30 rounded-xl';
                    container.appendChild(totalHint);
                }
                totalHint.textContent = `Total: ₱${computed.toFixed(2)}`;
                modal.dataset.computedPrice = computed;
            };

            updateModalPrice();
        }

        // Close modal when clicking backdrop
        document.getElementById('product-options-backdrop').addEventListener('click', function() {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        // Global cancel button handler
        document.getElementById('modal-cancel-btn').addEventListener('click', function() {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        // Global add button handler
        document.getElementById('modal-add-btn').addEventListener('click', function() {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const product = modal.dataset.currentProduct ? JSON.parse(modal.dataset.currentProduct) : null;
            if (!product) {
                alert('No product selected');
                return;
            }
            
            // Get selected options from buttons
            const selected = {};
            const selectedBtn = container.querySelector('.size-option-btn.border-simplicitea-500');
            if (selectedBtn) {
                const optionName = selectedBtn.dataset.optionName || 'Size';
                selected[optionName] = selectedBtn.dataset.label;
            }

            // Get computed price from modal dataset
            let computedPrice = modal.dataset.computedPrice ? parseFloat(modal.dataset.computedPrice) : parseFloat(product.productPrice);
            
            addOrIncrementCartItem({
                productId: product.productId,
                productName: product.productName,
                productPrice: computedPrice,
                stock: product.stock,
                options: selected
            });

            modal.classList.add('hidden');
            updateCart();
        });

        // Save cart to localStorage
        function saveCart() {
            try {
                localStorage.setItem('pos_cart', JSON.stringify(cart));
            } catch(e) {
                console.error('Error saving cart to localStorage:', e);
            }
        }

        // Update cart display
        function updateCart() {
            const cartContainer = document.getElementById('cart-items');
            subtotal = 0;

            // Persist cart to localStorage
            saveCart();

            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-gray-500 text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"></path>
                        </svg>
                        <p class="text-sm">No items selected</p>
                        <p class="text-xs text-gray-400">Click on products to add them</p>
                    </div>
                `;
            } else {
                cartContainer.innerHTML = cart.map((item, index) => {
                    const itemTotal = item.productPrice * item.quantity;
                    subtotal += itemTotal;
                    const optionsHtml = item.options ? Object.keys(item.options).map(k => `<div class="text-xs text-gray-500">${k}: ${item.options[k]}</div>`).join('') : '';
                    return `
                        <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">${item.productName}</p>
                                    <p class="text-xs text-gray-500">₱${item.productPrice.toFixed(2)} each</p>
                                    ${optionsHtml}
                                </div>
                                <button onclick="removeItem(${index})" class="text-red-500 hover:text-red-700 text-lg leading-none" title="Remove item">
                                    ×
                                </button>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center space-x-2">
                                    <button onclick="updateQuantity(${index}, -1)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">-</button>
                                    <span class="w-8 text-center font-medium">${item.quantity}</span>
                                    <button onclick="updateQuantity(${index}, 1)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-simplicitea-600">₱${itemTotal.toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Update totals
            const tax = 0; // You can implement tax calculation here
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
            document.getElementById('total').textContent = '₱' + total.toFixed(2);

            // Enable/disable checkout button
            document.getElementById('process-sale-btn').disabled = cart.length === 0;
        }

        // Update quantity
        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                cart.splice(index, 1);
            } else if (newQuantity <= item.stock) {
                item.quantity = newQuantity;
            } else {
                alert('Cannot add more items than available in stock!');
                return;
            }
            
            updateCart();
        }

        // Remove item
        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }

        // Clear cart
        document.getElementById('clear-cart-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = [];
                localStorage.removeItem('pos_cart');
                updateCart();
            }
        });

        // Process sale - show confirmation modal first
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            const amountPaid = parseFloat(document.querySelector('[name=amount_paid]').value) || 0;
            const paymentMethod = document.querySelector('[name=payment_method]').value;
            const total = subtotal;

            if (amountPaid < total) {
                alert('Amount paid is less than the total!');
                return;
            }

            // Populate confirmation modal
            const confirmItems = document.getElementById('confirm-order-items');
            confirmItems.innerHTML = cart.map(item => {
                let optionLabel = '';
                if (item.options && item.options.Size) {
                    optionLabel = `<span class="text-xs text-gray-500 dark:text-gray-400"> (${item.options.Size})</span>`;
                } else if (item.options) {
                    const optVal = Object.values(item.options)[0];
                    if (optVal) optionLabel = `<span class="text-xs text-gray-500 dark:text-gray-400"> (${optVal})</span>`;
                }
                return `
                    <div class="flex justify-between items-center py-1.5">
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">${item.name}</span>${optionLabel}
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">x${item.quantity}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white ml-3">₱${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `;
            }).join('');

            document.getElementById('confirm-subtotal').textContent = '₱' + total.toFixed(2);
            document.getElementById('confirm-total').textContent = '₱' + total.toFixed(2);
            
            const methodLabels = { cash: '💵 Cash', card: '💳 Card', gcash: '📱 GCash' };
            document.getElementById('confirm-payment-method').textContent = methodLabels[paymentMethod] || paymentMethod;
            document.getElementById('confirm-amount-paid').textContent = '₱' + amountPaid.toFixed(2);
            document.getElementById('confirm-change').textContent = '₱' + (amountPaid - total).toFixed(2);

            // Show modal
            document.getElementById('order-confirmation-modal').classList.remove('hidden');
        });

        // Cancel confirmation
        document.getElementById('cancel-order-btn').addEventListener('click', function() {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
        });
        document.getElementById('modal-backdrop').addEventListener('click', function() {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
        });

        // Confirm and process sale
        document.getElementById('confirm-order-btn').addEventListener('click', function() {
            // Hide modal
            document.getElementById('order-confirmation-modal').classList.add('hidden');

            // Disable submit button to prevent double-submission
            const submitBtn = document.getElementById('process-sale-btn');
            const confirmBtn = document.getElementById('confirm-order-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            confirmBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';

            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            const items = cart.map(item => ({
                product_id: item.productId,
                quantity: item.quantity,
                options: item.options || null
            }));

            // Add cart items to form data
            formData.append('items', JSON.stringify(items));

            // Submit to server
            fetch('{{ route("pos.process-sale") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Direct print receipt - silent if QZ Tray available
                    printReceiptDirect(data.direct_print_url, data.sale_id);

                    // Clear cart and reset POS for new order
                    cart = [];
                    subtotal = 0;
                    localStorage.removeItem('pos_cart');
                    updateCart();
                    document.getElementById('checkout-form').reset();

                    // Re-enable buttons for next order
                    submitBtn.disabled = false;
                    confirmBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;

                    // Show success notification
                    showSuccessToast('Sale completed! Receipt sent to printer.');
                } else {
                    // Show the error message from server
                    alert(data.error || data.message || 'An error occurred while processing the sale');
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    confirmBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the sale. Please try again.');
                // Re-enable submit button
                submitBtn.disabled = false;
                confirmBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });

        // Success toast notification
        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-[100] bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-slide-in';
            toast.innerHTML = `
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        /**
         * Direct print receipt using hidden iframe
         * The receipt page auto-triggers print when loaded
         */
        function printReceiptDirect(printUrl, saleId) {
            let printFrame = document.getElementById('receipt-print-frame');
            
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'receipt-print-frame';
                printFrame.name = 'receipt-print-frame';
                printFrame.style.cssText = 'position:absolute;width:0;height:0;border:0;left:-9999px;top:-9999px;';
                document.body.appendChild(printFrame);
            }
            
            // Load receipt - it auto-prints when loaded
            printFrame.src = printUrl;
        }

        // Listen for print completion from iframe
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'printComplete') {
                console.log('Print completed for sale:', event.data.saleId);
            }
        });

        // Set printer status to green (ready)
        document.addEventListener('DOMContentLoaded', function() {
            const statusEl = document.getElementById('printer-status-dot');
            if (statusEl) {
                statusEl.className = 'w-2 h-2 bg-green-500 rounded-full';
                statusEl.title = 'Printer Ready';
            }
        });

        // Render saved cart on page load
        updateCart();
    </script>
    <style>
        @keyframes slide-in { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        .animate-slide-in { animation: slide-in 0.3s ease forwards; }
    </style>
</x-app-layout>