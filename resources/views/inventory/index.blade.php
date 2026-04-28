<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Product Inventory') }}</h2>
            @if($view === 'products' && auth()->user()->role === 'admin' && $branches->count() > 1)
                <form method="GET" action="{{ route('inventory.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <label for="branch_id" class="text-sm text-gray-600 dark:text-gray-400">Branch:</label>
                    <select name="branch_id" id="branch_id" onchange="this.form.submit()" 
                            class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-md shadow-sm focus:ring-simplicitea-500 focus:border-simplicitea-500">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <style>
        html:not(.dark) .inventory-theme {
            background: linear-gradient(180deg, #9cb7ab 0%, #8ca79a 54%, #7b9689 100%) !important;
        }

        html:not(.dark) .inventory-theme .bg-white {
            background: rgba(226, 243, 235, 0.94) !important;
            border-color: rgba(0, 91, 92, 0.22) !important;
            box-shadow: 0 10px 24px rgba(0, 91, 92, 0.12) !important;
        }

        html:not(.dark) .inventory-theme .bg-gray-50,
        html:not(.dark) .inventory-theme .bg-gray-100,
        html:not(.dark) .inventory-theme .dark\:bg-gray-700\/50 {
            background: rgba(200, 224, 213, 0.75) !important;
        }

        html:not(.dark) .inventory-theme .text-simplicitea-600,
        html:not(.dark) .inventory-theme .text-green-600,
        html:not(.dark) .inventory-theme .text-green-700 {
            color: #005b5c !important;
        }

        html:not(.dark) .inventory-theme .bg-simplicitea-600,
        html:not(.dark) .inventory-theme .bg-green-600 {
            background-color: #00b140 !important;
        }
    </style>

    <div class="inventory-theme py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Toggle Tabs: Finished Products vs Ingredients -->
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.index', ['view' => 'products', 'branch_id' => $branchId]) }}" 
                       class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                              {{ $view === 'products' 
                                 ? 'bg-simplicitea-600 text-black shadow-md' 
                                 : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Finished Products</span>
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $view === 'products' ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-600' }}">
                            {{ $view === 'products' ? $totalItems : '' }}
                        </span>
                    </a>
                          <a href="{{ route('inventory.index', ['view' => 'ingredients']) }}" 
                       class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                              {{ $view === 'ingredients' 
                                 ? 'bg-simplicitea-600 text-black shadow-md' 
                                 : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span>Raw Materials</span>
                    </a>
                </div>
            </div>

            <!-- Current Branch Info (Products only) / Global Info (Ingredients) -->
            @if($view === 'products')
                <div class="mb-4 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Viewing inventory for: <strong class="text-gray-900 dark:text-black">{{ $currentBranch->name ?? 'Unknown Branch' }}</strong></span>
                </div>
            @else
                <div class="mb-4 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Viewing <strong class="text-gray-900 dark:text-black">Global Inventory</strong> across all {{ $allBranches->count() }} branches</span>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">{{ $view === 'products' ? '📦' : '🧪' }}</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total {{ $view === 'products' ? 'Products' : 'Ingredients' }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-black">{{ $totalItems }}</p>
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
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalItems - $lowStockCount - $outOfStockCount }}</p>
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
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lowStockCount }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🚫</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Out of Stock</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $outOfStockCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Actions -->
            <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <form method="GET" action="{{ route('inventory.index') }}" class="flex-1 max-w-md">
                    <input type="hidden" name="view" value="{{ $view }}">
                    @if($view === 'products')
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif
                    <div class="relative">
                        <input type="text" name="q" value="{{ $search }}" 
                               placeholder="Search {{ $view === 'products' ? 'products' : 'ingredients' }}..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-black placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-simplicitea-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    @if($view === 'ingredients')
                        <button onclick="document.getElementById('addIngredientModal').classList.remove('hidden')" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-simplicitea-600 text-black rounded-xl text-sm font-medium hover:bg-simplicitea-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New Ingredient
                        </button>
                        <a href="{{ route('recipes.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Manage Recipes
                        </a>
                    @endif
                </div>
            </div>

            <!-- Info Banner for Ingredients -->
            @if($view === 'ingredients')
                <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-100 dark:bg-amber-800 rounded-full flex items-center justify-center">
                            <span class="text-xl">💡</span>
                        </div>
                        <div>
                            <h3 class="text-amber-800 dark:text-amber-200 font-semibold">Ingredients Inventory</h3>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                These raw materials are automatically deducted when composite products (like Milk Tea) are sold via POS.
                                For example, selling "Dark Choco Milk Tea" will deduct 30g Dark Choco Powder, 200ml Tea Base, etc. based on the recipe.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Inventory Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                @if($inventoryItems->isEmpty())
                    <div class="text-center py-16">
                        <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <span class="text-3xl">{{ $view === 'products' ? '📦' : '🧪' }}</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-2">No {{ $view === 'products' ? 'products' : 'ingredients' }} found</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            @if($search)
                                No results match your search "{{ $search }}".
                            @else
                                Start by adding {{ $view === 'products' ? 'products' : 'ingredients' }} to your inventory.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        @php
                            $sortedInventoryItems = $inventoryItems->sortByDesc(function ($item) use ($view) {
                                if ($view === 'ingredients') {
                                    $hasOut = false;
                                    $hasLow = false;
                                    foreach (($item->branches ?? []) as $branchData) {
                                        if (!empty($branchData['is_out_of_stock'])) {
                                            $hasOut = true;
                                            break;
                                        }
                                        if (!empty($branchData['is_low_stock'])) {
                                            $hasLow = true;
                                        }
                                    }
                                    return $hasOut ? 2 : ($hasLow ? 1 : 0);
                                }

                                $status = strtolower($item->status ?? '');
                                if (str_contains($status, 'out')) {
                                    return 2;
                                }
                                if (str_contains($status, 'low')) {
                                    return 1;
                                }
                                return 0;
                            });
                        @endphp
                        @if($view === 'ingredients')
                            {{-- Multi-Branch Ingredients Table --}}
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Ingredient
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Unit
                                        </th>
                                        @foreach($allBranches as $branch)
                                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px]">
                                                <div class="flex flex-col items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    </svg>
                                                    {{ $branch->name }}
                                                </div>
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($sortedInventoryItems as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
                                                        <span class="text-lg">🧪</span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-black">{{ $item->name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->unit }}</span>
                                            </td>
                                            @foreach($allBranches as $branch)
                                                @php
                                                    $branchInv = $item->branches[$branch->id] ?? null;
                                                    $qty = $branchInv ? $branchInv['quantity'] : 0;
                                                    $isLow = $branchInv ? $branchInv['is_low_stock'] : false;
                                                    $isOut = $branchInv ? $branchInv['is_out_of_stock'] : true;
                                                    $invId = $branchInv ? $branchInv['inventory_id'] : null;
                                                @endphp
                                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                                    <div class="flex flex-col items-center gap-1">
                                                        <span class="text-sm font-semibold {{ $isOut ? 'text-red-600 dark:text-red-400' : ($isLow ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-black') }}">
                                                            {{ number_format($qty, 1) }}
                                                        </span>
                                                        @if($isOut)
                                                            <span>Out</span>
                                                        @elseif($isLow)
                                                            <span>Low</span>
                                                        @endif
                                                        @if($invId)
                                                            <button onclick="openBranchRestockModal({{ $invId }}, '{{ $item->name }}', '{{ $item->unit }}', '{{ $branch->name }}')" 
                                                                    class="text-xs text-green-600 hover:text-green-800 dark:text-green-400 hover:underline">
                                                                +Restock
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->status_color }}">
                                                    {{ Str::limit($item->status, 30) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <button onclick="openEditIngredientModal({{ $item->ingredient_id }}, '{{ $item->name }}', '{{ $item->unit }}', {{ json_encode($item->branches) }})" 
                                                        class="text-simplicitea-600 hover:text-simplicitea-800 dark:text-simplicitea-400 dark:hover:text-simplicitea-300 font-medium text-sm">
                                                    Edit All
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            {{-- Single-Branch Products Table --}}
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Min Stock</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($sortedInventoryItems as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                        <span class="text-lg">🧋</span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-black">{{ $item->name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->status_color }}">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-black">
                                                    {{ number_format($item->quantity, 0) }} {{ $item->unit }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ number_format($item->min_stock_level, 0) }} {{ $item->unit }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->updated_at->diffForHumans() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                <a href="{{ route('inventory.edit', ['inventory' => $item->id, 'view' => $view]) }}" 
                                                   class="text-simplicitea-600 hover:text-simplicitea-800 dark:text-simplicitea-400 dark:hover:text-simplicitea-300 font-medium">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sales Chart (Products view only) -->
            @if($view === 'products' && $chartLabels->isNotEmpty())
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-black mb-4">Top Products (Last 30 Days)</h3>
                    <div class="w-full" style="height:280px">
                        <canvas id="salesChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Ingredient Modal -->
    @if($view === 'ingredients')
    {{-- Add New Ingredient to System Modal --}}
    <div id="addIngredientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Add New Ingredient</h3>
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
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg"
                               placeholder="e.g., Evaporated Milk">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <input type="text" name="description" 
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg"
                               placeholder="e.g., Dairy product">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measure</label>
                        <select name="unit" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg">
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
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock Level</label>
                            <input type="number" name="min_stock_level" step="0.01" min="0" value="10" required 
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addIngredientModal').classList.add('hidden')" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg hover:bg-simplicitea-700">
                        Add Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Branch-Specific Restock Modal --}}
    <div id="branchRestockModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Restock Ingredient</h3>
                <button onclick="document.getElementById('branchRestockModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="branchRestockForm" method="POST">
                @csrf
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    Ingredient: <strong id="branchRestockIngredientName" class="text-gray-900 dark:text-black"></strong>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Branch: <strong id="branchRestockBranchName" class="text-gray-900 dark:text-black"></strong>
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount to Add</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="amount" step="0.01" min="0.01" required 
                               class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg">
                        <span id="branchRestockUnit" class="text-gray-500 dark:text-gray-400"></span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('branchRestockModal').classList.add('hidden')" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-black rounded-lg hover:bg-green-700">
                        Restock
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Ingredient (All Branches) Modal --}}
    <div id="editIngredientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Edit Ingredient Inventory</h3>
                <button onclick="document.getElementById('editIngredientModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Editing: <strong id="editIngredientName" class="text-gray-900 dark:text-black"></strong>
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
                    <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-black rounded-lg hover:bg-simplicitea-700">
                        Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

@if($view === 'products' && $chartLabels->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const labels = @json($chartLabels);
            const data = @json($chartData);

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Quantity sold',
                        data: data,
                        backgroundColor: 'rgba(34,197,94,0.7)',
                        borderColor: 'rgba(34,197,94,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })();
    </script>
@endif

@if($view === 'ingredients')
    <script>
        const branchNames = @json($allBranches->pluck('name', 'id'));
        
        function openBranchRestockModal(invId, name, unit, branchName) {
            document.getElementById('branchRestockForm').action = '/inventory/' + invId + '/restock';
            document.getElementById('branchRestockIngredientName').textContent = name;
            document.getElementById('branchRestockBranchName').textContent = branchName;
            document.getElementById('branchRestockUnit').textContent = unit;
            document.getElementById('branchRestockModal').classList.remove('hidden');
        }
        
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
                                <span class="font-medium text-gray-900 dark:text-black">${branchName}</span>
                            </div>
                            ${statusBadge}
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Current Quantity</label>
                                <div class="relative">
                                    <input type="number" name="branches[${branchId}][quantity]" value="${currentQty}" step="0.01" min="0" 
                                           class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg pr-12">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">${unit}</span>
                                </div>
                                <input type="hidden" name="branches[${branchId}][inventory_id]" value="${data.inventory_id || ''}">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Alert Threshold</label>
                                <div class="relative">
                                    <input type="number" name="branches[${branchId}][min_stock_level]" value="${minStock}" step="0.01" min="0" 
                                           class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-black rounded-lg pr-12">
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
    </script>
@endif
</x-app-layout>
