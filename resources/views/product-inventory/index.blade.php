<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inventory Management') }}
            </h2>
            <div class="flex items-center gap-3">
                <!-- Sync Status Indicator -->
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-green-700 dark:text-green-400">LIVE SYNC</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Inventory Type Tabs (Segmented Control) -->
            <div class="mb-6 flex items-center justify-between">
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <button onclick="switchTab('products')" id="tab-products"
                        class="tab-btn px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center gap-2 {{ ($activeTab ?? 'products') === 'products' ? 'bg-white dark:bg-gray-700 text-simplicitea-700 dark:text-simplicitea-300 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span class="text-lg">🧋</span>
                        Products
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($activeTab ?? 'products') === 'products' ? 'bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400' }}">{{ $products->count() }}</span>
                    </button>
                    <button onclick="switchTab('ingredients')" id="tab-ingredients"
                        class="tab-btn px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center gap-2 {{ ($activeTab ?? 'products') === 'ingredients' ? 'bg-white dark:bg-gray-700 text-simplicitea-700 dark:text-simplicitea-300 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span class="text-lg">🧪</span>
                        Ingredients
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($activeTab ?? 'products') === 'ingredients' ? 'bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400' }}">{{ $ingredients->count() }}</span>
                        @if($lowStockIngredients > 0)
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">{{ $lowStockIngredients }} low</span>
                        @endif
                    </button>
                </div>
                
                <!-- Export Button -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                        class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                        <a href="{{ route('product-inventory.export', ['type' => 'products']) }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="text-lg">🧋</span>
                            Export Products
                        </a>
                        <a href="{{ route('product-inventory.export', ['type' => 'ingredients']) }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border-t border-gray-100 dark:border-gray-700">
                            <span class="text-lg">🧪</span>
                            Export Ingredients
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
            </div>
            @endif

            <!-- ==================== PRODUCTS TAB ==================== -->
            <div id="content-products" class="tab-content {{ ($activeTab ?? 'products') === 'products' ? '' : 'hidden' }}">

            <!-- Low Stock Alerts Banner -->
            @if(count($lowStockAlerts) > 0)
            <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4" id="lowStockBanner">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center animate-pulse">
                        <span class="text-xl">⚠️</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-red-800 dark:text-red-200 font-semibold">Low Stock Alerts ({{ count($lowStockAlerts) }})</h3>
                        <div class="mt-2 space-y-2 max-h-32 overflow-y-auto">
                            @foreach($lowStockAlerts as $alert)
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-red-100 dark:border-red-900">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $alert->product->name ?? 'Unknown' }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">@ {{ $alert->branch->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button onclick="openRestockModal({{ $alert->product_id }}, {{ $alert->branch_id }})" 
                                        class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">
                                        Restock
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="document.getElementById('lowStockBanner').classList.add('hidden')" class="text-red-400 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🧋</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Products</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $products->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">✅</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fully Synced</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400" id="syncedCount">
                                {{ $products->filter(fn($p) => $p->inventory->count() >= $branches->count())->count() }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">⚠️</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock Items</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="lowStockCount">{{ count($lowStockAlerts) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🏪</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Branches</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $branches->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Inventory Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            Product Inventory Management
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage prices globally and stock per branch</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" id="productSearch" placeholder="Search products..." 
                                class="pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full" id="productTable">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Base Price</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sync Status</th>
                                @foreach($branches as $branch)
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    📍 {{ $branch->name }}
                                </th>
                                @endforeach
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($products as $product)
                            @php
                                $inventoryByBranch = $product->inventory->keyBy('branch_id');
                                $syncedBranches = $product->inventory->count();
                                $totalBranches = $branches->count();
                                $isSynced = $syncedBranches >= $totalBranches;
                                
                                // Check if product has ingredients (composite product)
                                $hasIngredients = $product->ingredients->count() > 0;
                                $isDirectProduct = !$hasIngredients;
                                
                                // For direct products: check product inventory stock
                                $hasLowProductStock = $product->inventory->filter(fn($inv) => $inv->isLowStock())->count() > 0;
                                
                                // For composite products: check ingredient stock per branch
                                $hasLowIngredientStock = false;
                                $lowIngredientBranches = [];
                                if ($hasIngredients) {
                                    foreach ($branches as $branch) {
                                        foreach ($product->ingredients as $ingredient) {
                                            $ingInv = $ingredient->inventories->where('branch_id', $branch->id)->first();
                                            $ingQty = $ingInv ? (float)$ingInv->quantity : 0;
                                            $ingMinStock = $ingInv ? (float)$ingInv->min_stock_level : 10;
                                            if ($ingQty <= $ingMinStock) {
                                                $hasLowIngredientStock = true;
                                                $lowIngredientBranches[$branch->id] = true;
                                            }
                                        }
                                    }
                                }
                                
                                // Determine overall low stock status
                                $hasLowStock = $isDirectProduct ? $hasLowProductStock : $hasLowIngredientStock;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 product-row" data-name="{{ strtolower($product->name) }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-400 to-simplicitea-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $product->id }}</p>
                                                @if($isDirectProduct)
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300" title="Raw/Direct product - stock tracked directly">Raw</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300" title="Composite product - requires ingredients">Recipe</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">₱{{ number_format($product->price, 2) }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($isSynced && !$hasLowStock)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300" title="Price synced across all branches">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Synced
                                        </span>
                                    @elseif($hasLowStock && $hasIngredients)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300 animate-pulse" title="Low ingredient stock in some branches">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                            </svg>
                                            Low Ingredients
                                        </span>
                                    @elseif($hasLowStock)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 animate-pulse" title="Low stock in some branches">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300" title="{{ $syncedBranches }}/{{ $totalBranches }} branches have inventory">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            {{ $syncedBranches }}/{{ $totalBranches }}
                                        </span>
                                    @endif
                                </td>
                                @foreach($branches as $branch)
                                @php
                                    $inv = $inventoryByBranch->get($branch->id);
                                    $qty = $inv ? $inv->quantity : 0;
                                    $minQty = $inv ? $inv->min_stock_level : 10;
                                    $isLow = $inv ? $inv->isLowStock() : false;
                                    
                                    // For composite products, check ingredient stock for this branch
                                    $branchHasLowIngredients = isset($lowIngredientBranches[$branch->id]);
                                @endphp
                                <td class="px-5 py-4 text-center" data-product-branch-stock="{{ $product->id }}-{{ $branch->id }}">
                                    @if($isDirectProduct)
                                        {{-- Direct product: show product inventory stock --}}
                                        <div class="inline-flex flex-col items-center">
                                            <span class="stock-value text-sm font-bold {{ $qty <= 0 ? 'text-red-600' : ($isLow ? 'text-yellow-600' : 'text-gray-900 dark:text-white') }}">
                                                {{ $qty }}
                                            </span>
                                            @if($isLow)
                                                <span class="w-2 h-2 bg-red-500 rounded-full animate-ping mt-1" title="Low Stock"></span>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Composite product: show ingredient status --}}
                                        <div class="inline-flex flex-col items-center">
                                            @if($branchHasLowIngredients)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    Low Ing.
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    OK
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                @endforeach
                                <td class="px-5 py-4 text-center">
                                    <button onclick="openProductModal({{ $product->id }})" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-simplicitea-600 hover:bg-simplicitea-700 text-white text-xs font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Manage
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 5 + $branches->count() }}" class="px-5 py-12 text-center">
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
            </div> <!-- End of Products Tab Content -->

            <!-- ==================== INGREDIENTS TAB ==================== -->
            <div id="content-ingredients" class="tab-content {{ ($activeTab ?? 'products') === 'ingredients' ? '' : 'hidden' }}">
                
                <!-- Ingredients Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🧪</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Ingredients</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ingredients->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">⚠️</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock Alerts</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lowStockIngredients ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">✅</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">In Stock</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $inStockIngredients ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">📦</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Quick Actions</p>
                                <button onclick="document.getElementById('addIngredientModal').classList.remove('hidden')" class="text-sm font-medium text-simplicitea-600 hover:text-simplicitea-700 dark:text-simplicitea-400">+ Add Ingredient</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Ingredients Alert -->
                @if($lowStockIngredients > 0)
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4" id="lowStockIngredientsBanner">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-800 rounded-full flex items-center justify-center animate-pulse">
                            <span class="text-xl">⚠️</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-yellow-800 dark:text-yellow-200 font-semibold">Low Stock Ingredients ({{ $lowStockIngredients }})</h3>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Some ingredients need restocking. Check the table below for details.</p>
                        </div>
                        <button onclick="document.getElementById('lowStockIngredientsBanner').classList.add('hidden')" class="text-yellow-400 hover:text-yellow-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Ingredients Table -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="text-xl">🧪</span>
                                Raw Materials & Ingredients
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage bulk ingredients like tea leaves, pearls, syrups, and more</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <input type="text" id="ingredientSearch" placeholder="Search ingredients..." 
                                    class="pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <button onclick="document.getElementById('addIngredientModal').classList.remove('hidden')" class="px-4 py-2 bg-simplicitea-600 text-white text-sm font-medium rounded-xl hover:bg-simplicitea-700 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Ingredient
                            </button>
                            </a>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full" id="ingredientTable">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ingredient</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($ingredients as $ingredient)
                                @php
                                    $isLowStock = $ingredient->is_low_stock ?? false;
                                    $isOutOfStock = $ingredient->is_out_of_stock ?? false;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 ingredient-row" data-name="{{ strtolower($ingredient->name) }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($ingredient->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $ingredient->name }}</p>
                                                @if($ingredient->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($ingredient->description, 40) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($isOutOfStock)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Out of Stock
                                            </span>
                                        @elseif($isLowStock)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                In Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-lg font-bold {{ $isOutOfStock ? 'text-red-600 dark:text-red-400' : ($isLowStock ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white') }}">
                                            {{ number_format($ingredient->quantity, 1) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $ingredient->unit }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $ingredient->updated_at->format('M d, Y') }}</span>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $ingredient->updated_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openEditIngredientModal({{ $ingredient->id }}, '{{ $ingredient->name }}', '{{ $ingredient->unit }}', {{ json_encode($ingredient->branches) }})" 
                                                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition"
                                                title="Manage Ingredient">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('ingredients.destroy', $ingredient->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ingredient from all branches?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition"
                                                    title="Delete Ingredient">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <div class="text-gray-500 dark:text-gray-400">
                                            <span class="text-4xl">🧪</span>
                                            <p class="mt-2">No ingredients found</p>
                                            <button onclick="document.getElementById('addIngredientModal').classList.remove('hidden')" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-simplicitea-600 text-white text-sm font-medium rounded-xl hover:bg-simplicitea-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Add Your First Ingredient
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- End of Ingredients Tab Content -->

        </div>
    </div>

    {{-- ==================== INGREDIENT MODALS ==================== --}}
    
    {{-- Add New Ingredient Modal --}}
    <div id="addIngredientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Ingredient</h3>
                <button onclick="document.getElementById('addIngredientModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('ingredients.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ingredient Name</label>
                        <input type="text" name="name" required 
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                               placeholder="e.g., Evaporated Milk">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <input type="text" name="description" 
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                               placeholder="e.g., Dairy product">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measure</label>
                        <select name="unit" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="g">Grams (g)</option>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="ml">Milliliters (ml)</option>
                            <option value="L">Liters (L)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="cans">Cans</option>
                            <option value="bottles">Bottles</option>
                            <option value="packs">Packs</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial Quantity (All Branches)</label>
                            <input type="number" name="initial_quantity" step="0.01" min="0" value="0" required 
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock Level</label>
                            <input type="number" name="min_stock_level" step="0.01" min="0" value="10" required 
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addIngredientModal').classList.add('hidden')" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700">
                        Add Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Ingredient (All Branches) Modal --}}
    <div id="editIngredientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Ingredient Inventory</h3>
                <button onclick="document.getElementById('editIngredientModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Editing: <strong id="editIngredientName" class="text-gray-900 dark:text-white"></strong>
                (<span id="editIngredientUnit" class="text-gray-500"></span>)
            </p>
            <form id="editIngredientForm" action="{{ route('inventory.update-ingredient-branches') }}" method="POST">
                @csrf
                <input type="hidden" name="ingredient_id" id="editIngredientId">
                <div class="space-y-4" id="editBranchesContainer">
                    {{-- Dynamically populated --}}
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editIngredientModal').classList.add('hidden')" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700">
                        Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Management Modal - Enhanced UI -->
    <div id="productModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/80 backdrop-blur-sm" onclick="closeProductModal()"></div>
            
            <div class="relative z-10 w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl transform transition-all animate-modal-in">
                <!-- Modal Header with Product Info -->
                <div class="relative px-6 py-5 bg-gradient-to-r from-simplicitea-600 via-simplicitea-500 to-teal-500 rounded-t-3xl overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid)"/>
                        </svg>
                    </div>
                    
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div id="modalProductIcon" class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                <span class="text-2xl">📦</span>
                            </div>
                            <div class="text-left">
                                <h3 id="modalProductName" class="text-xl font-bold text-white">Loading...</h3>
                                <p id="modalProductCategory" class="text-white/70 text-sm mt-1">Category</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                                        <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
                                        Live Sync
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button onclick="closeProductModal()" class="text-white/70 hover:text-white transition p-2 hover:bg-white/10 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 max-h-[70vh] overflow-y-auto" id="modalContent">
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="relative">
                            <div class="w-16 h-16 border-4 border-simplicitea-200 dark:border-simplicitea-800 rounded-full"></div>
                            <div class="absolute top-0 left-0 w-16 h-16 border-4 border-simplicitea-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 mt-4">Loading product data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes modal-in {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .animate-modal-in {
            animation: modal-in 0.3s ease-out;
        }
    </style>

    <script>
        // ==================== MAIN PAGE TAB SWITCHING ====================
        function switchTab(tab) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tab).classList.remove('hidden');
            
            // Update tab button styles
            const activeClasses = ['bg-white', 'dark:bg-gray-700', 'text-simplicitea-700', 'dark:text-simplicitea-300', 'shadow-sm'];
            const inactiveClasses = ['text-gray-600', 'dark:text-gray-400', 'hover:text-gray-900', 'dark:hover:text-white'];
            
            document.getElementById('tab-products').classList.remove(...activeClasses);
            document.getElementById('tab-products').classList.add(...inactiveClasses);
            document.getElementById('tab-ingredients').classList.remove(...activeClasses);
            document.getElementById('tab-ingredients').classList.add(...inactiveClasses);
            
            document.getElementById('tab-' + tab).classList.remove(...inactiveClasses);
            document.getElementById('tab-' + tab).classList.add(...activeClasses);
            
            // Update tab badge styles
            const tabBadges = document.querySelectorAll('#tab-' + tab + ' span.ml-1');
            tabBadges.forEach(badge => {
                if (!badge.classList.contains('bg-red-100')) { // Don't update the "low" warning badge
                    badge.classList.remove('bg-gray-200', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
                    badge.classList.add('bg-simplicitea-100', 'dark:bg-simplicitea-900/50', 'text-simplicitea-700', 'dark:text-simplicitea-300');
                }
            });
            
            // Reset other tab's badge
            const otherTab = tab === 'products' ? 'ingredients' : 'products';
            const otherBadges = document.querySelectorAll('#tab-' + otherTab + ' span.ml-1');
            otherBadges.forEach(badge => {
                if (!badge.classList.contains('bg-red-100')) {
                    badge.classList.remove('bg-simplicitea-100', 'dark:bg-simplicitea-900/50', 'text-simplicitea-700', 'dark:text-simplicitea-300');
                    badge.classList.add('bg-gray-200', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
                }
            });
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }
        
        // ==================== MODAL TAB SWITCHING ====================
        function switchModalTab(tab) {
            document.querySelectorAll('.modal-tab-btn').forEach(btn => {
                btn.classList.remove('border-simplicitea-600', 'text-simplicitea-600', 'dark:text-simplicitea-400');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            document.querySelectorAll('.modal-tab-panel').forEach(panel => panel.classList.add('hidden'));
            
            const tabBtn = document.getElementById(`tabModal${tab.charAt(0).toUpperCase() + tab.slice(1)}`);
            const tabPanel = document.getElementById(`panelModal${tab.charAt(0).toUpperCase() + tab.slice(1)}`);
            
            if (tabBtn) {
                tabBtn.classList.add('border-simplicitea-600', 'text-simplicitea-600', 'dark:text-simplicitea-400');
                tabBtn.classList.remove('border-transparent', 'text-gray-500');
            }
            if (tabPanel) {
                tabPanel.classList.remove('hidden');
            }
        }
        
        // Product search functionality
        document.getElementById('productSearch').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.product-row').forEach(row => {
                const name = row.dataset.name;
                row.style.display = name.includes(search) ? '' : 'none';
            });
        });
        
        // Ingredient search functionality
        const ingredientSearchInput = document.getElementById('ingredientSearch');
        if (ingredientSearchInput) {
            ingredientSearchInput.addEventListener('input', function() {
                const search = this.value.toLowerCase();
                document.querySelectorAll('.ingredient-row').forEach(row => {
                    const name = row.dataset.name;
                    row.style.display = name.includes(search) ? '' : 'none';
                });
            });
        }

        // Modal functions
        function openProductModal(productId) {
            const modal = document.getElementById('productModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Load product data
            loadProductData(productId);
        }

        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openRestockModal(productId, branchId) {
            openProductModal(productId);
            // Will auto-select the branch in the modal
            setTimeout(() => {
                const branchSelect = document.getElementById('selectedBranch');
                if (branchSelect) {
                    branchSelect.value = branchId;
                    branchSelect.dispatchEvent(new Event('change'));
                }
            }, 500);
        }

        async function loadProductData(productId) {
            const modalContent = document.getElementById('modalContent');
            
            try {
                const response = await fetch(`/product-inventory/${productId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    renderModalContent(data);
                } else {
                    modalContent.innerHTML = '<p class="text-red-500 text-center py-8">Failed to load product data</p>';
                }
            } catch (error) {
                console.error('Error loading product:', error);
                modalContent.innerHTML = '<p class="text-red-500 text-center py-8">Error loading product data</p>';
            }
        }

        function renderModalContent(data) {
            const product = data.product;
            const branchInventory = data.branch_inventory;
            
            const branches = @json($branches);
            const categories = @json($categories);
            
            // Update header
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductCategory').textContent = product.category_name || 'Uncategorized';
            document.getElementById('modalProductIcon').innerHTML = `<span class="text-2xl">${product.name.charAt(0).toUpperCase()}</span>`;
            
            // Calculate total stock and stats
            let totalStock = 0;
            let lowStockBranches = 0;
            let outOfStockBranches = 0;
            branchInventory.forEach(inv => {
                totalStock += inv.quantity;
                if (inv.quantity <= 0) outOfStockBranches++;
                else if (inv.quantity <= inv.min_stock_level) lowStockBranches++;
            });
            
            // Build branch cards HTML
            let branchCardsHtml = branchInventory.map(inv => {
                const isLow = inv.quantity > 0 && inv.quantity <= inv.min_stock_level;
                const isOut = inv.quantity <= 0;
                const percentage = Math.min(100, (inv.quantity / Math.max(inv.min_stock_level * 2, 50)) * 100);
                
                const statusColor = isOut ? 'red' : (isLow ? 'yellow' : 'green');
                const statusText = isOut ? 'OUT OF STOCK' : (isLow ? 'LOW STOCK' : 'In Stock');
                const statusIcon = isOut ? '🚫' : (isLow ? '⚠️' : '✅');
                
                return `
                    <div class="bg-white dark:bg-gray-700/50 rounded-2xl border-2 ${isOut ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (isLow ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-100 dark:border-gray-600')} overflow-hidden transition-all hover:shadow-lg">
                        <!-- Branch Header -->
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📍</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">${inv.branch_name}</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${isOut ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : (isLow ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300')}">
                                    ${statusIcon} ${statusText}
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-4 space-y-4">
                            <!-- Stock Level Visual -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Stock Level</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${inv.quantity} units</span>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 ${isOut ? 'bg-red-500' : (isLow ? 'bg-yellow-500' : 'bg-green-500')}" style="width: ${percentage}%"></div>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-xs text-gray-400">0</span>
                                </div>
                            </div>
                            
                            <!-- Stock Input with Quick Buttons -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adjust Stock</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-700">
                                        <button type="button" onclick="adjustStock(${inv.branch_id}, -10)" class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition font-medium text-sm">-10</button>
                                        <button type="button" onclick="adjustStock(${inv.branch_id}, -1)" class="px-3 py-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition font-medium">-</button>
                                        <input type="number" 
                                            id="stock_${inv.branch_id}" 
                                            name="stock_${inv.branch_id}" 
                                            value="${inv.quantity}" 
                                            min="0"
                                            class="w-20 text-center border-0 bg-white dark:bg-gray-800 text-lg font-bold text-gray-900 dark:text-white focus:ring-0 py-2">
                                        <button type="button" onclick="adjustStock(${inv.branch_id}, 1)" class="px-3 py-2 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/30 transition font-medium">+</button>
                                        <button type="button" onclick="adjustStock(${inv.branch_id}, 10)" class="px-3 py-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 transition font-medium text-sm">+10</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Min Stock Setting -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Low Stock Alert Threshold</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔔</span>
                                    <input type="number" 
                                        name="min_${inv.branch_id}" 
                                        value="${inv.min_stock_level}" 
                                        min="0"
                                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-simplicitea-500">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Build category options
            let categoryOptions = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                categoryOptions += `<option value="${cat.id}" ${cat.id == product.category_id ? 'selected' : ''}>${cat.name}</option>`;
            });
            
            const html = `
                <form id="productForm" onsubmit="saveProduct(event, ${product.id})">
                    <!-- Stats Summary -->
                    <div class="grid grid-cols-4 gap-3 mb-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-4 text-center">
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">${totalStock}</p>
                            <p class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-1">Total Stock</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-4 text-center">
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">₱${parseFloat(product.price).toFixed(2)}</p>
                            <p class="text-xs text-green-600/70 dark:text-green-400/70 mt-1">Base Price</p>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-xl p-4 text-center">
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">${lowStockBranches}</p>
                            <p class="text-xs text-yellow-600/70 dark:text-yellow-400/70 mt-1">Low Stock</p>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl p-4 text-center">
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400">${outOfStockBranches}</p>
                            <p class="text-xs text-red-600/70 dark:text-red-400/70 mt-1">Out of Stock</p>
                        </div>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
                        <button type="button" onclick="switchModalInternalTab('global')" id="tabGlobal" class="modal-internal-tab-btn px-4 py-3 text-sm font-medium border-b-2 border-simplicitea-600 text-simplicitea-600 dark:text-simplicitea-400">
                            🌐 Global Settings
                        </button>
                        <button type="button" onclick="switchModalInternalTab('branches')" id="tabBranches" class="modal-internal-tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            🏪 Branch Stock (${branchInventory.length})
                        </button>
                    </div>
                    
                    <!-- Global Settings Tab -->
                    <div id="panelGlobal" class="modal-internal-tab-panel">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                                    <span class="text-white text-lg">🌐</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Global Product Settings</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Changes apply to all branches instantly</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <span class="mr-1">📝</span> Product Name
                                    </label>
                                    <input type="text" name="name" value="${product.name}" required
                                        class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <span class="mr-1">💰</span> Base Price
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
                                        <input type="number" name="price" value="${product.price}" step="0.01" min="0" required
                                            class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 shadow-sm text-lg font-bold">
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <span class="mr-1">📂</span> Category
                                    </label>
                                    <select name="category_id" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 shadow-sm">
                                        ${categoryOptions}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-5 p-4 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-start gap-3">
                                <span class="text-xl">💡</span>
                                <div>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Price Sync Info</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">Updating the base price will automatically update the price across all POS terminals in real-time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Branch Stock Tab -->
                    <div id="panelBranches" class="modal-internal-tab-panel hidden">
                        <!-- Bulk Actions -->
                        <div class="flex items-center justify-between mb-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Quick Actions for All Branches</span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="bulkAdjustStock(-10)" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition">
                                    -10 All
                                </button>
                                <button type="button" onclick="bulkAdjustStock(10)" class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition">
                                    +10 All
                                </button>
                                <button type="button" onclick="bulkAdjustStock(50)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition">
                                    +50 All
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            ${branchCardsHtml}
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeProductModal()" class="px-5 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </button>
                        <button type="submit" id="saveBtn" class="px-8 py-3 bg-gradient-to-r from-simplicitea-600 to-teal-500 hover:from-simplicitea-700 hover:to-teal-600 text-white font-semibold rounded-xl transition shadow-lg shadow-simplicitea-500/30 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save All Changes
                        </button>
                    </div>
                </form>
            `;
            
            document.getElementById('modalContent').innerHTML = html;
        }
        
        // Modal tab switching (for global/branches tabs inside the modal)
        function switchModalInternalTab(tab) {
            document.querySelectorAll('.modal-internal-tab-btn').forEach(btn => {
                btn.classList.remove('border-simplicitea-600', 'text-simplicitea-600', 'dark:text-simplicitea-400');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            document.querySelectorAll('.modal-internal-tab-panel').forEach(panel => panel.classList.add('hidden'));
            
            const tabBtn = document.getElementById(`tab${tab.charAt(0).toUpperCase() + tab.slice(1)}`);
            const tabPanel = document.getElementById(`panel${tab.charAt(0).toUpperCase() + tab.slice(1)}`);
            
            if (tabBtn) {
                tabBtn.classList.add('border-simplicitea-600', 'text-simplicitea-600', 'dark:text-simplicitea-400');
                tabBtn.classList.remove('border-transparent', 'text-gray-500');
            }
            if (tabPanel) {
                tabPanel.classList.remove('hidden');
            }
        }
        
        // Adjust stock for a branch
        function adjustStock(branchId, amount) {
            const input = document.getElementById(`stock_${branchId}`);
            if (input) {
                const newValue = Math.max(0, parseInt(input.value || 0) + amount);
                input.value = newValue;
            }
        }
        
        // Bulk adjust all stocks
        function bulkAdjustStock(amount) {
            const branches = @json($branches);
            branches.forEach(branch => {
                adjustStock(branch.id, amount);
            });
        }

        async function saveProduct(event, productId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Prepare the data
            const branches = @json($branches);
            const stocks = branches.map(branch => ({
                branch_id: branch.id,
                quantity: parseFloat(formData.get(`stock_${branch.id}`)) || 0,
                min_stock_level: parseFloat(formData.get(`min_${branch.id}`)) || 10
            }));
            
            try {
                // Update global price
                const priceResponse = await fetch(`/product-inventory/${productId}/price`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        price: parseFloat(formData.get('price')),
                        category_id: formData.get('category_id')
                    })
                });
                
                // Update branch stocks
                const stockResponse = await fetch(`/product-inventory/${productId}/all-stocks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ stocks })
                });
                
                if (priceResponse.ok && stockResponse.ok) {
                    showToast('Product updated successfully!', 'success');
                    closeProductModal();
                    // Reload page to see changes
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Failed to update product', 'error');
                }
            } catch (error) {
                console.error('Error saving product:', error);
                showToast('Error saving product', 'error');
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-2xl shadow-lg z-50 transform translate-y-full transition-all duration-300 flex items-center gap-3 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
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

        // Poll for low stock alerts every 10 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/product-inventory/low-stock-alerts', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('lowStockCount').textContent = data.count;
                }
            } catch (error) {
                console.error('Error checking low stock:', error);
            }
        }, 10000);

        // ==================== INGREDIENT MODAL FUNCTIONS ====================
        const branchNames = @json($allBranches->pluck('name', 'id'));
        
        function openEditIngredientModal(ingredientId, name, unit, branchesData) {
            document.getElementById('editIngredientId').value = ingredientId;
            document.getElementById('editIngredientName').textContent = name;
            document.getElementById('editIngredientUnit').textContent = unit;
            
            const container = document.getElementById('editBranchesContainer');
            container.innerHTML = '';
            
            // Calculate total quantity across all branches
            let totalQuantity = 0;
            for (const data of Object.values(branchesData)) {
                totalQuantity += parseFloat(data.quantity) || 0;
            }
            
            // Add summary header
            const summaryHtml = `
                <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 mb-2 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Across All Branches</span>
                        </div>
                        <span class="text-lg font-bold text-blue-800 dark:text-blue-200">${totalQuantity.toFixed(2)} ${unit}</span>
                    </div>
                </div>
            `;
            container.innerHTML = summaryHtml;
            
            for (const [branchId, data] of Object.entries(branchesData)) {
                const branchName = branchNames[branchId] || 'Branch ' + branchId;
                const currentQty = parseFloat(data.quantity) || 0;
                const minStock = parseFloat(data.min_stock_level) || 0;
                const statusBadge = data.is_out_of_stock 
                    ? '<span class="text-xs bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400 px-2 py-0.5 rounded-full">Out of Stock</span>' 
                    : (data.is_low_stock 
                        ? '<span class="text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400 px-2 py-0.5 rounded-full">Low Stock</span>' 
                        : '<span class="text-xs bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400 px-2 py-0.5 rounded-full">In Stock</span>');
                
                const html = `
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">${branchName}</span>
                            </div>
                            ${statusBadge}
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Current Quantity</label>
                                <div class="relative">
                                    <input type="number" name="branches[${branchId}][quantity]" value="${currentQty}" step="0.01" min="0" 
                                           class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pr-12">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">${unit}</span>
                                </div>
                                <input type="hidden" name="branches[${branchId}][inventory_id]" value="${data.inventory_id || ''}">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Alert Threshold</label>
                                <div class="relative">
                                    <input type="number" name="branches[${branchId}][min_stock_level]" value="${minStock}" step="0.01" min="0" 
                                           class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pr-12">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">${unit}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            }
            
            document.getElementById('editIngredientModal').classList.remove('hidden');
        }

        // ==================== LIVE INVENTORY POLLING ====================
        // Refresh stock data every 15 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/product-inventory/live-data', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!data.success) return;

                // Update low stock count badge
                const lowStockEl = document.getElementById('lowStockCount');
                if (lowStockEl) lowStockEl.textContent = data.low_stock_count;

                // Update product stock cells in the table
                document.querySelectorAll('[data-product-branch-stock]').forEach(el => {
                    const key = el.dataset.productBranchStock; // format: "productId-branchId"
                    const [productId, branchId] = key.split('-');
                    const stockItem = data.product_stocks.find(s => 
                        String(s.product_id) === productId && String(s.branch_id) === branchId
                    );
                    if (stockItem) {
                        const stockSpan = el.querySelector('.stock-value');
                        if (stockSpan) {
                            const oldVal = stockSpan.textContent.trim();
                            const newVal = String(Math.floor(stockItem.quantity));
                            if (oldVal !== newVal) {
                                stockSpan.textContent = newVal;
                                stockSpan.className = 'stock-value text-sm font-bold ' + 
                                    (stockItem.is_out ? 'text-red-600' : (stockItem.is_low ? 'text-yellow-600' : 'text-gray-900 dark:text-white'));
                                el.style.transition = 'background-color 0.3s';
                                el.style.backgroundColor = 'rgba(34,197,94,0.1)';
                                setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
                            }
                        }
                    }
                });

                // Update ingredient stock cells
                document.querySelectorAll('[data-ingredient-branch-stock]').forEach(el => {
                    const key = el.dataset.ingredientBranchStock; // format: "ingredientId-branchId"
                    const [ingredientId, branchId] = key.split('-');
                    const stockItem = data.ingredient_stocks.find(s => 
                        String(s.ingredient_id) === ingredientId && String(s.branch_id) === branchId
                    );
                    if (stockItem) {
                        const stockSpan = el.querySelector('.stock-value');
                        if (stockSpan) {
                            const oldVal = stockSpan.textContent.trim();
                            const newVal = Number(stockItem.quantity).toFixed(2);
                            if (oldVal !== newVal) {
                                stockSpan.textContent = newVal;
                                stockSpan.className = 'stock-value text-sm font-bold ' + 
                                    (stockItem.is_out ? 'text-red-600' : (stockItem.is_low ? 'text-yellow-600' : 'text-gray-900 dark:text-white'));
                                el.style.transition = 'background-color 0.3s';
                                el.style.backgroundColor = 'rgba(34,197,94,0.1)';
                                setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Inventory live data error:', error);
            }
        }, 15000);
    </script>
</x-app-layout>
