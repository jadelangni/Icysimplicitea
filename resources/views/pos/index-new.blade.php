<x-app-layout>
    <!-- Custom styles for this page -->
    <style>
        .pos-container { display: flex; gap: 1.5rem; height: calc(100vh - 5rem); }
        .pos-left { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .pos-right { width: 380px; display: flex; flex-direction: column; }
        .products-scroll { flex: 1; overflow-y: auto; padding-right: 0.5rem; }
        .products-scroll::-webkit-scrollbar { width: 6px; }
        .products-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        .products-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .cart-scroll { flex: 1; overflow-y: auto; }
        .cart-scroll::-webkit-scrollbar { width: 4px; }
        .cart-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }
        @keyframes slide-in { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        .animate-slide-in { animation: slide-in 0.3s ease forwards; }
        .category-tab.active { background: #166534 !important; color: black !important; }
    </style>

    <div class="py-4 px-4 sm:px-6 lg:px-8">
        <div class="pos-container">
            <!-- LEFT PANEL: Products -->
            <div class="pos-left">
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-black">Point of Sale</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Let's Choose Your Option To Sale!</p>
                </div>

                <!-- Search & Filter Row -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="relative flex-1 max-w-xs">
                        <input type="text" id="product-search" placeholder="Search" 
                               class="w-full pl-4 pr-10 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:text-black">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <button type="button" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters
                    </button>
                    <!-- Cart count badge (mobile) -->
                    <button type="button" id="cart-mobile-toggle" class="lg:hidden relative px-4 py-2.5 bg-green-600 text-black rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-black text-xs rounded-full flex items-center justify-center hidden">0</span>
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-2">
                    <button class="category-tab active px-4 py-2 bg-green-700 text-black rounded-lg text-sm font-medium whitespace-nowrap transition-all hover:bg-green-600" data-category="all">
                        All Products
                    </button>
                    @php
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
                        $usedCategoryIds = [];
                        $tabData = [];
                        foreach($tabGroups as $tabName => $keywords) {
                            $matchingCats = $categories->filter(fn($cat) => collect($keywords)->contains(fn($kw) => str_contains(strtolower($cat->name), $kw)));
                            if($matchingCats->count() > 0) {
                                $ids = $matchingCats->pluck('id')->toArray();
                                $hasProducts = $products->whereIn('category_id', $ids)->count() > 0;
                                if($hasProducts) {
                                    $tabData[] = ['name' => $tabName, 'ids' => $ids];
                                    $usedCategoryIds = array_merge($usedCategoryIds, $ids);
                                }
                            }
                        }
                        foreach($categories->whereNotIn('id', $usedCategoryIds) as $cat) {
                            if($products->where('category_id', $cat->id)->count() > 0) {
                                $tabData[] = ['name' => $cat->name, 'ids' => [$cat->id]];
                            }
                        }
                    @endphp
                    @foreach($tabData as $tab)
                    <button class="category-tab px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium whitespace-nowrap transition-all hover:bg-gray-50 dark:hover:bg-gray-700" data-category="{{ implode(',', $tab['ids']) }}">
                        {{ $tab['name'] }}
                    </button>
                    @endforeach
                    
                    <!-- Ordered Items Button -->
                    <button type="button" id="show-ordered-btn" class="ml-auto px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium whitespace-nowrap flex items-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Ordered
                        <span id="ordered-count" class="bg-green-600 text-black text-xs px-2 py-0.5 rounded-full">0</span>
                    </button>
                </div>

                <!-- Search Results Info -->
                <div id="search-results-info" class="hidden mb-3 text-sm text-gray-500 dark:text-gray-400">
                    <span id="search-results-count">0</span> results for "<span id="search-query-display"></span>"
                </div>

                <!-- Products Grid -->
                <div class="products-scroll">
                    <div id="products-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($products as $product)
                        @php 
                            $qty = $product->inventory->first()->quantity ?? 0;
                            $hasSizes = $product->options && is_array($product->options) && !empty($product->options);
                            $soldCount = $product->salesItems->sum('quantity') ?? 0;
                        @endphp
                        <div class="product-card bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-green-300 dark:hover:border-green-600 transition-all cursor-pointer {{ $qty <= 0 ? 'opacity-50' : '' }}" 
                             data-category="{{ $product->category_id }}"
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-price="{{ $product->price }}"
                             data-stock="{{ $qty }}"
                             data-options='{{ json_encode($product->options ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                            
                            <!-- Product Image -->
                            <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative">
                                @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="w-16 h-20 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                </div>
                                @endif
                                @if($qty <= 0)
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="bg-red-500 text-black text-xs px-2 py-1 rounded">Out of Stock</span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-3">
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-black mb-1 line-clamp-1">{{ $product->name }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    <span>Sold <span class="text-green-600 font-medium">{{ $soldCount }}pcs</span></span>
                                    <span>Avail <span class="text-green-600 font-medium">{{ $qty }}pcs</span></span>
                                </div>
                                
                                @if($hasSizes)
                                <div class="flex items-center gap-2 mb-3">
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Size</span>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-700 dark:text-gray-300">Varies</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Price</span>
                                        <p class="text-lg font-bold text-gray-900 dark:text-black">₱{{ number_format($product->price, 0) }}</p>
                                    </div>
                                    <button type="button" class="add-to-cart-btn p-2 bg-gray-100 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors group">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Cart -->
            <div class="pos-right bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col overflow-hidden">
                <!-- Cashier Info Header -->
                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden">
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Cashier {{ Auth::user()->id }}</span>
                                <span class="flex items-center gap-1 text-xs text-green-600">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Online
                                </span>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-black">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: #{{ str_pad(Auth::user()->id, 7, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Header -->
                <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 dark:text-black">List Order Product</span>
                        <span id="cart-count-badge" class="bg-green-600 text-black text-xs px-2 py-0.5 rounded-full">0</span>
                    </div>
                    <button type="button" id="clear-cart-btn" class="text-sm text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition-colors">
                        Clear All
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="cart-scroll flex-1 p-4">
                    <div id="cart-items" class="space-y-3">
                        <!-- Empty state -->
                        <div id="cart-empty" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No items selected</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Click on products to add them</p>
                        </div>
                        <!-- Cart items will be populated here -->
                        <div id="cart-items-list" class="space-y-3 hidden"></div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h4 class="font-medium text-gray-900 dark:text-black mb-3">Detail Payment</h4>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span id="subtotal" class="font-medium text-gray-900 dark:text-black">₱0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Discount</span>
                            <span id="discount-display" class="text-gray-900 dark:text-black">₱0</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span class="font-medium text-gray-900 dark:text-black">Total Amount</span>
                        <span id="total" class="text-xl font-bold text-gray-900 dark:text-black">₱0</span>
                    </div>

                    <!-- Discount Input -->
                    <div class="flex items-center gap-2 mt-4">
                        <input type="text" id="discount-input" placeholder="Discount code or amount" 
                               class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm dark:text-black">
                        <button type="button" id="apply-discount-btn" class="px-4 py-2 text-sm text-green-600 dark:text-green-400 font-medium hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                            Apply
                        </button>
                    </div>

                    <!-- Payment Method Buttons -->
                    <div class="flex gap-2 mt-4">
                        <button type="button" class="payment-method-btn flex-1 py-2.5 border-2 border-green-600 text-green-600 rounded-lg text-sm font-medium hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" data-method="cash">
                            Cash
                        </button>
                        <button type="button" class="payment-method-btn flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-method="card">
                            Card
                        </button>
                        <button type="button" class="payment-method-btn flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-method="gcash">
                            GCash
                        </button>
                    </div>

                    <!-- Pay Button -->
                    <button type="button" id="pay-btn" disabled class="w-full mt-4 py-3.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 disabled:cursor-not-allowed text-black rounded-xl font-semibold text-lg transition-colors">
                        Pay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Options Modal -->
    <div id="product-options-modal" class="fixed inset-0 z-50 hidden">
        <div id="product-options-backdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden">
                <div class="bg-green-600 text-black px-6 py-4">
                    <h3 id="modal-product-name" class="text-xl font-bold">Select Size</h3>
                    <p class="text-green-100 text-sm mt-1">Choose your preferred size</p>
                </div>
                
                <form id="product-options-form" class="p-6">
                    <div id="options-container" class="space-y-4"></div>
                    
                    <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" id="modal-cancel-btn" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="modal-add-btn" class="flex-1 px-4 py-3 bg-green-600 text-black rounded-xl font-medium hover:bg-green-700 transition-colors">
                            Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Amount Paid Modal -->
    <div id="amount-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60" onclick="document.getElementById('amount-modal').classList.add('hidden')"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden">
                <div class="bg-green-600 text-black px-6 py-4">
                    <h3 class="text-xl font-bold">Enter Amount Paid</h3>
                    <p class="text-green-100 text-sm mt-1">Total: <span id="modal-total-display">₱0</span></p>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Paid</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 text-lg">₱</span>
                            <input type="number" id="amount-paid-input" step="0.01" min="0" placeholder="0.00" 
                                   class="w-full pl-8 pr-4 py-3 text-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    
                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <button type="button" class="quick-amount-btn py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600" data-amount="50">₱50</button>
                        <button type="button" class="quick-amount-btn py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600" data-amount="100">₱100</button>
                        <button type="button" class="quick-amount-btn py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600" data-amount="200">₱200</button>
                        <button type="button" class="quick-amount-btn py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600" data-amount="500">₱500</button>
                        <button type="button" class="quick-amount-btn py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600" data-amount="1000">₱1000</button>
                        <button type="button" class="quick-amount-btn py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-sm font-medium hover:bg-green-200 dark:hover:bg-green-900/50" data-amount="exact">Exact</button>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Change:</span>
                        <span id="change-display" class="text-xl font-bold text-green-600 dark:text-green-400">₱0.00</span>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('amount-modal').classList.add('hidden')" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="confirm-payment-btn" class="flex-1 px-4 py-3 bg-green-600 text-black rounded-xl font-medium hover:bg-green-700 transition-colors">
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <div id="order-confirmation-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/60 dark:bg-gray-900/80 backdrop-blur-sm" id="modal-backdrop"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden">
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-black flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Confirm Order
                    </h3>
                    <p class="text-green-100 text-sm mt-1">Please review before processing</p>
                </div>

                <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
                    <div id="confirm-order-items" class="space-y-2 mb-4"></div>
                    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span id="confirm-subtotal" class="font-medium text-gray-900 dark:text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-green-700 dark:text-green-400 pt-1">
                            <span>Total</span>
                            <span id="confirm-total">₱0.00</span>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Payment Method</span>
                            <span id="confirm-payment-method" class="font-medium text-gray-900 dark:text-black">Cash</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Amount Paid</span>
                            <span id="confirm-amount-paid" class="font-medium text-gray-900 dark:text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Change</span>
                            <span id="confirm-change" class="font-bold text-green-600 dark:text-green-400">₱0.00</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button type="button" id="cancel-order-btn" class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirm-order-btn" class="flex-1 px-4 py-2.5 bg-green-600 text-black rounded-xl font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm & Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cart state
        let cart = [];
        try {
            const savedCart = localStorage.getItem('pos_cart');
            if (savedCart) cart = JSON.parse(savedCart);
        } catch(e) { cart = []; }
        
        let subtotal = 0;
        let discount = 0;
        let currentCategory = 'all';
        let currentSearchQuery = '';
        let selectedPaymentMethod = 'cash';

        const searchInput = document.getElementById('product-search');

        // Search handler
        searchInput.addEventListener('input', function() {
            currentSearchQuery = this.value.toLowerCase().trim();
            filterProducts();
        });

        function filterProducts() {
            let visibleCount = 0;
            const selectedIds = currentCategory === 'all' ? null : currentCategory.split(',');
            
            document.querySelectorAll('.product-card').forEach(card => {
                const productName = card.dataset.productName.toLowerCase();
                const categoryMatch = !selectedIds || selectedIds.includes(card.dataset.category);
                const searchMatch = !currentSearchQuery || productName.includes(currentSearchQuery);
                
                if (categoryMatch && searchMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (currentSearchQuery) {
                document.getElementById('search-results-info').classList.remove('hidden');
                document.getElementById('search-results-count').textContent = visibleCount;
                document.getElementById('search-query-display').textContent = currentSearchQuery;
            } else {
                document.getElementById('search-results-info').classList.add('hidden');
            }
        }

        // Category tabs
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.category-tab').forEach(t => {
                    t.classList.remove('active', 'bg-green-700', 'text-black');
                    t.classList.add('bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
                });
                this.classList.add('active', 'bg-green-700', 'text-black');
                this.classList.remove('bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
                currentCategory = this.dataset.category;
                filterProducts();
            });
        });

        // Product cards click
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.add-to-cart-btn')) return;
                handleProductClick(this);
            });
        });

        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                handleProductClick(this.closest('.product-card'));
            });
        });

        function handleProductClick(card) {
            const productId = card.dataset.productId;
            const productName = card.dataset.productName;
            const productPrice = parseFloat(card.dataset.productPrice);
            const stock = parseInt(card.dataset.stock);
            let options = [];
            try { options = JSON.parse(card.dataset.options || '[]'); } catch(e) { options = []; }

            if (stock <= 0) {
                alert('This product is out of stock!');
                return;
            }

            if (options && options.length > 0) {
                openOptionsModal({ productId, productName, productPrice, stock, options });
                return;
            }

            addOrIncrementCartItem({ productId, productName, productPrice, stock, options: null });
            updateCart();
        }

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

        function openOptionsModal(product) {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const title = document.getElementById('modal-product-name');
            container.innerHTML = '';
            title.textContent = product.productName;

            product.options.forEach((opt, idx) => {
                let values = opt.values || [];
                if (!Array.isArray(values)) values = typeof values === 'string' ? values.split(',').map(v => v.trim()) : [values];
                
                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-3';

                const label = document.createElement('label');
                label.className = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2';
                label.textContent = opt.name || 'Select Size';

                const buttonsDiv = document.createElement('div');
                buttonsDiv.className = 'grid grid-cols-2 gap-3';
                buttonsDiv.setAttribute('data-option-name', opt.name || `Option ${idx+1}`);

                values.forEach((v, vIdx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'size-option-btn p-4 rounded-xl border-2 transition-all text-center hover:border-green-400 ' + 
                                   (vIdx === 0 ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700');
                    
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
                        <div class="font-bold text-gray-900 dark:text-black text-lg">${labelValue}</div>
                        <div class="text-green-600 dark:text-green-400 font-semibold mt-1">₱${priceVal > 0 ? priceVal.toFixed(0) : parseFloat(product.productPrice).toFixed(0)}</div>
                    `;
                    
                    btn.addEventListener('click', function() {
                        buttonsDiv.querySelectorAll('.size-option-btn').forEach(b => {
                            b.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/30');
                            b.classList.add('border-gray-200', 'dark:border-gray-600', 'bg-white', 'dark:bg-gray-700');
                        });
                        this.classList.remove('border-gray-200', 'dark:border-gray-600', 'bg-white', 'dark:bg-gray-700');
                        this.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/30');
                        updateModalPrice();
                    });
                    
                    buttonsDiv.appendChild(btn);
                });

                wrapper.appendChild(label);
                wrapper.appendChild(buttonsDiv);
                container.appendChild(wrapper);
            });

            modal.classList.remove('hidden');
            modal.dataset.currentProduct = JSON.stringify(product);

            const updateModalPrice = () => {
                let computed = parseFloat(product.productPrice);
                const selectedBtn = container.querySelector('.size-option-btn.border-green-500');
                if (selectedBtn && selectedBtn.dataset.price) {
                    const priceVal = parseFloat(selectedBtn.dataset.price);
                    if (priceVal > 0) computed = priceVal;
                }
                let totalHint = container.querySelector('.total-price-hint');
                if (!totalHint) {
                    totalHint = document.createElement('div');
                    totalHint.className = 'total-price-hint text-center text-xl font-bold text-green-600 dark:text-green-400 mt-4 p-3 bg-green-50 dark:bg-green-900/30 rounded-xl';
                    container.appendChild(totalHint);
                }
                totalHint.textContent = `Total: ₱${computed.toFixed(2)}`;
                modal.dataset.computedPrice = computed;
            };
            updateModalPrice();
        }

        document.getElementById('product-options-backdrop').addEventListener('click', () => {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        document.getElementById('modal-cancel-btn').addEventListener('click', () => {
            document.getElementById('product-options-modal').classList.add('hidden');
        });

        document.getElementById('modal-add-btn').addEventListener('click', function() {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const product = modal.dataset.currentProduct ? JSON.parse(modal.dataset.currentProduct) : null;
            if (!product) return;
            
            const selected = {};
            const selectedBtn = container.querySelector('.size-option-btn.border-green-500');
            if (selectedBtn) {
                const optionName = selectedBtn.dataset.optionName || 'Size';
                selected[optionName] = selectedBtn.dataset.label;
            }

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

        function saveCart() {
            try { localStorage.setItem('pos_cart', JSON.stringify(cart)); } catch(e) {}
        }

        function updateCart() {
            const cartEmpty = document.getElementById('cart-empty');
            const cartList = document.getElementById('cart-items-list');
            subtotal = 0;
            saveCart();

            if (cart.length === 0) {
                cartEmpty.classList.remove('hidden');
                cartList.classList.add('hidden');
                cartList.innerHTML = '';
            } else {
                cartEmpty.classList.add('hidden');
                cartList.classList.remove('hidden');
                
                cartList.innerHTML = cart.map((item, index) => {
                    const itemTotal = item.productPrice * item.quantity;
                    subtotal += itemTotal;
                    const optionText = item.options ? Object.values(item.options).join(', ') : '';
                    
                    return `
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 dark:text-black truncate">${item.productName}</p>
                                ${optionText ? `<p class="text-xs text-gray-500 dark:text-gray-400">Size: ${optionText}</p>` : ''}
                                <p class="text-sm font-semibold text-gray-900 dark:text-black mt-1">₱${itemTotal.toFixed(0)}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="updateQuantity(${index}, -1)" class="w-7 h-7 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-500">
                                    <span class="text-gray-600 dark:text-gray-300">−</span>
                                </button>
                                <span class="w-6 text-center font-medium text-gray-900 dark:text-black">${item.quantity}</span>
                                <button onclick="updateQuantity(${index}, 1)" class="w-7 h-7 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-500">
                                    <span class="text-gray-600 dark:text-gray-300">+</span>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            const total = subtotal - discount;
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(0);
            document.getElementById('total').textContent = '₱' + total.toFixed(0);
            document.getElementById('cart-count-badge').textContent = cart.length;
            document.getElementById('ordered-count').textContent = cart.length;
            
            const cartBadge = document.getElementById('cart-badge');
            if (cart.length > 0) {
                cartBadge.textContent = cart.length;
                cartBadge.classList.remove('hidden');
            } else {
                cartBadge.classList.add('hidden');
            }

            document.getElementById('pay-btn').disabled = cart.length === 0;
        }

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

        document.getElementById('clear-cart-btn').addEventListener('click', function() {
            if (cart.length === 0) return;
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = [];
                localStorage.removeItem('pos_cart');
                updateCart();
            }
        });

        // Payment method buttons
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.payment-method-btn').forEach(b => {
                    b.classList.remove('border-green-600', 'text-green-600', 'border-2');
                    b.classList.add('border-gray-200', 'dark:border-gray-600', 'text-gray-700', 'dark:text-gray-300');
                });
                this.classList.remove('border-gray-200', 'dark:border-gray-600', 'text-gray-700', 'dark:text-gray-300');
                this.classList.add('border-green-600', 'text-green-600', 'border-2');
                selectedPaymentMethod = this.dataset.method;
            });
        });

        // Apply discount
        document.getElementById('apply-discount-btn').addEventListener('click', function() {
            const input = document.getElementById('discount-input').value.trim();
            if (input) {
                const num = parseFloat(input);
                if (!isNaN(num) && num > 0) {
                    discount = num;
                    document.getElementById('discount-display').textContent = '₱' + discount.toFixed(0);
                    updateCart();
                }
            }
        });

        // Pay button
        document.getElementById('pay-btn').addEventListener('click', function() {
            if (cart.length === 0) return;
            const total = subtotal - discount;
            document.getElementById('modal-total-display').textContent = '₱' + total.toFixed(2);
            document.getElementById('amount-paid-input').value = '';
            document.getElementById('change-display').textContent = '₱0.00';
            document.getElementById('amount-modal').classList.remove('hidden');
        });

        // Quick amount buttons
        document.querySelectorAll('.quick-amount-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const amount = this.dataset.amount;
                const total = subtotal - discount;
                if (amount === 'exact') {
                    document.getElementById('amount-paid-input').value = total.toFixed(2);
                } else {
                    document.getElementById('amount-paid-input').value = amount;
                }
                updateChangeDisplay();
            });
        });

        document.getElementById('amount-paid-input').addEventListener('input', updateChangeDisplay);

        function updateChangeDisplay() {
            const amountPaid = parseFloat(document.getElementById('amount-paid-input').value) || 0;
            const total = subtotal - discount;
            const change = amountPaid - total;
            document.getElementById('change-display').textContent = '₱' + (change >= 0 ? change.toFixed(2) : '0.00');
        }

        // Confirm payment
        document.getElementById('confirm-payment-btn').addEventListener('click', function() {
            const amountPaid = parseFloat(document.getElementById('amount-paid-input').value) || 0;
            const total = subtotal - discount;

            if (amountPaid < total) {
                alert('Amount paid is less than the total!');
                return;
            }

            document.getElementById('amount-modal').classList.add('hidden');

            // Populate confirmation modal
            const confirmItems = document.getElementById('confirm-order-items');
            confirmItems.innerHTML = cart.map(item => {
                let optionLabel = '';
                if (item.options) {
                    const optVal = Object.values(item.options)[0];
                    if (optVal) optionLabel = `<span class="text-xs text-gray-500 dark:text-gray-400"> (${optVal})</span>`;
                }
                return `
                    <div class="flex justify-between items-center py-1.5">
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-black">${item.productName}</span>${optionLabel}
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">x${item.quantity}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-black ml-3">₱${(item.productPrice * item.quantity).toFixed(2)}</span>
                    </div>
                `;
            }).join('');

            document.getElementById('confirm-subtotal').textContent = '₱' + total.toFixed(2);
            document.getElementById('confirm-total').textContent = '₱' + total.toFixed(2);
            
            const methodLabels = { cash: 'Cash', card: 'Card', gcash: 'GCash' };
            document.getElementById('confirm-payment-method').textContent = methodLabels[selectedPaymentMethod] || selectedPaymentMethod;
            document.getElementById('confirm-amount-paid').textContent = '₱' + amountPaid.toFixed(2);
            document.getElementById('confirm-change').textContent = '₱' + (amountPaid - total).toFixed(2);

            document.getElementById('order-confirmation-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-order-btn').addEventListener('click', () => {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
        });
        document.getElementById('modal-backdrop').addEventListener('click', () => {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
        });

        // Process sale
        document.getElementById('confirm-order-btn').addEventListener('click', function() {
            document.getElementById('order-confirmation-modal').classList.add('hidden');

            const confirmBtn = this;
            const payBtn = document.getElementById('pay-btn');
            confirmBtn.disabled = true;
            payBtn.disabled = true;
            payBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('payment_method', selectedPaymentMethod);
            formData.append('amount_paid', document.getElementById('amount-paid-input').value);
            formData.append('items', JSON.stringify(cart.map(item => ({
                product_id: item.productId,
                quantity: item.quantity,
                options: item.options || null
            }))));

            fetch('{{ route("pos.process-sale") }}', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                let data = null;
                try { data = await response.json(); } catch (e) { data = null; }
                if (!response.ok) {
                    const msg = (data && (data.error || data.message)) || 'An error occurred while processing the sale.';
                    showErrorToast(msg);
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                if (data && data.success) {
                    printReceiptDirect(data.direct_print_url, data.sale_id);
                    cart = [];
                    discount = 0;
                    localStorage.removeItem('pos_cart');
                    updateCart();
                    document.getElementById('discount-input').value = '';
                    document.getElementById('discount-display').textContent = '₱0';
                    showSuccessToast('Sale completed! Receipt sent to printer.');
                } else {
                    showErrorToast((data && (data.error || data.message)) || 'An error occurred');
                }
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast(error?.message || 'An error occurred. Please try again.');
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            });
        });

        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-[100] bg-green-600 text-black px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-slide-in';
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

        function showErrorToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-[100] bg-red-600 text-black px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-slide-in';
            toast.innerHTML = `
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="font-medium">${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function printReceiptDirect(printUrl, saleId) {
            let printFrame = document.getElementById('receipt-print-frame');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'receipt-print-frame';
                printFrame.name = 'receipt-print-frame';
                printFrame.style.cssText = 'position:absolute;width:0;height:0;border:0;left:-9999px;top:-9999px;';
                document.body.appendChild(printFrame);
            }
            printFrame.src = printUrl;
        }

        // Initialize
        updateCart();
    </script>
</x-app-layout>
