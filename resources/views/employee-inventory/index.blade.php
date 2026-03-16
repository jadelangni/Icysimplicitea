<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inventory Overview') }}
            </h2>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <span class="text-sm">📍</span>
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">{{ $branch->name }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Low Stock Alerts Banner --}}
            @if($lowStockAlerts->count() > 0)
            <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4" id="lowStockBanner">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center animate-pulse">
                        <span class="text-xl">⚠️</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-red-800 dark:text-red-200 font-semibold">Low Stock Alerts ({{ $lowStockAlerts->count() }})</h3>
                        <div class="mt-2 space-y-2 max-h-40 overflow-y-auto">
                            @foreach($lowStockAlerts as $alert)
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-red-100 dark:border-red-900">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">{{ $alert->is_out ? '🔴' : '🟡' }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $alert->name }}</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $alert->status_color }}">
                                    {{ $alert->status }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="document.getElementById('lowStockBanner').classList.add('hidden')" class="text-red-400 hover:text-red-600 dark:hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- Tabs --}}
            <div class="mb-6">
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <button onclick="switchTab('products')" id="tab-products"
                        class="tab-btn px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center gap-2 {{ ($activeTab ?? 'products') === 'products' ? 'bg-white dark:bg-gray-700 text-green-700 dark:text-green-300 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span class="text-lg">🧋</span>
                        Products
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($activeTab ?? 'products') === 'products' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400' }}">{{ $totalProducts }}</span>
                    </button>
                    <button onclick="switchTab('ingredients')" id="tab-ingredients"
                        class="tab-btn px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center gap-2 {{ ($activeTab ?? 'products') === 'ingredients' ? 'bg-white dark:bg-gray-700 text-green-700 dark:text-green-300 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span class="text-lg">🧪</span>
                        Ingredients
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($activeTab ?? 'products') === 'ingredients' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400' }}">{{ $totalIngredients }}</span>
                        @if($lowStockIngredients + $outOfStockIngredients > 0)
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">{{ $lowStockIngredients + $outOfStockIngredients }} low</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- ==================== PRODUCTS TAB ==================== --}}
            <div id="content-products" class="tab-content {{ ($activeTab ?? 'products') === 'products' ? '' : 'hidden' }}">

                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🧋</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Products</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalProducts }}</p>
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
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalProducts - $lowStockProducts - $outOfStockProducts }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">⚠️</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lowStockProducts }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🔴</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Out of Stock</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $outOfStockProducts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="text-xl">📦</span>
                                Product Stock Overview
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stock levels at {{ $branch->name }}</p>
                        </div>
                        <div class="relative">
                            <input type="text" id="productSearch" placeholder="Search products..." 
                                class="pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Min Stock</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($products as $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 product-row" data-name="{{ strtolower($product->name) }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                            </div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $product->category }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($product->is_direct)
                                            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">Raw</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">Recipe</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-bold text-gray-900 dark:text-white">₱{{ number_format($product->price, 2) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-lg font-bold {{ $product->is_out ? 'text-red-600 dark:text-red-400' : ($product->is_low ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white') }}">
                                            {{ $product->is_direct ? $product->quantity : '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product->is_direct ? $product->min_stock : '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $product->status_color }}">
                                            @if($product->is_out)
                                                🔴
                                            @elseif($product->is_low)
                                                ⚠️
                                            @else
                                                ✅
                                            @endif
                                            {{ $product->status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="text-4xl">📦</span>
                                            <p class="font-medium">No products found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ==================== INGREDIENTS TAB ==================== --}}
            <div id="content-ingredients" class="tab-content {{ ($activeTab ?? 'products') === 'ingredients' ? '' : 'hidden' }}">

                {{-- Ingredient Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🧪</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Ingredients</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalIngredients }}</p>
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
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalIngredients - $lowStockIngredients - $outOfStockIngredients }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">⚠️</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lowStockIngredients }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🔴</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Out of Stock</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $outOfStockIngredients }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ingredients Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="text-xl">🧪</span>
                                Ingredient Stock Overview
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ingredient levels at {{ $branch->name }}</p>
                        </div>
                        <div class="relative">
                            <input type="text" id="ingredientSearch" placeholder="Search ingredients..." 
                                class="pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ingredient</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Min Stock</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Level</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($ingredients as $ingredient)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 ingredient-row" data-name="{{ strtolower($ingredient->name) }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($ingredient->name, 0, 2)) }}
                                            </div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $ingredient->name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $ingredient->unit }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-lg font-bold {{ $ingredient->is_out ? 'text-red-600 dark:text-red-400' : ($ingredient->is_low ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white') }}">
                                            {{ number_format($ingredient->quantity, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($ingredient->min_stock, 2) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $percent = $ingredient->min_stock > 0 ? min(($ingredient->quantity / $ingredient->min_stock) * 100, 100) : 100;
                                            $barColor = $ingredient->is_out ? 'bg-red-500' : ($ingredient->is_low ? 'bg-yellow-500' : 'bg-green-500');
                                        @endphp
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 max-w-[120px] mx-auto">
                                            <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $ingredient->status_color }}">
                                            @if($ingredient->is_out)
                                                🔴
                                            @elseif($ingredient->is_low)
                                                ⚠️
                                            @else
                                                ✅
                                            @endif
                                            {{ $ingredient->status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="text-4xl">🧪</span>
                                            <p class="font-medium">No ingredients found</p>
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

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('content-' + tab).classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-gray-700', 'text-green-700', 'dark:text-green-300', 'shadow-sm');
                b.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            const activeBtn = document.getElementById('tab-' + tab);
            activeBtn.classList.add('bg-white', 'dark:bg-gray-700', 'text-green-700', 'dark:text-green-300', 'shadow-sm');
            activeBtn.classList.remove('text-gray-600', 'dark:text-gray-400');

            // Update tab pill colors
            document.querySelectorAll('.tab-btn .ml-1').forEach(pill => {
                pill.classList.remove('bg-green-100', 'dark:bg-green-900/50', 'text-green-700', 'dark:text-green-300');
                pill.classList.add('bg-gray-200', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
            });
            activeBtn.querySelectorAll('.ml-1').forEach(pill => {
                if (!pill.classList.contains('bg-red-100')) {
                    pill.classList.remove('bg-gray-200', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
                    pill.classList.add('bg-green-100', 'dark:bg-green-900/50', 'text-green-700', 'dark:text-green-300');
                }
            });
        }

        // Product search
        document.getElementById('productSearch')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.product-row').forEach(row => {
                row.style.display = row.dataset.name.includes(q) ? '' : 'none';
            });
        });

        // Ingredient search
        document.getElementById('ingredientSearch')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.ingredient-row').forEach(row => {
                row.style.display = row.dataset.name.includes(q) ? '' : 'none';
            });
        });
    </script>
</x-app-layout>
