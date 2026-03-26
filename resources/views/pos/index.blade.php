<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Icy's Simplicitea POS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/simplicitea-logo.png') }}">
    <style>
        * { box-sizing: border-box; }
        :root {
            --cashier-mint-100: #98ff98;
            --cashier-mint-500: #00b140;
            --cashier-mint-300: #b2e8d8;
            --cashier-mint-050: #e0fff4;
            --cashier-mint-900: #005b5c;
        }
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: linear-gradient(180deg, #92aea1 0%, #839f92 52%, #749185 100%);
        }
        .pos-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: black; flex-shrink: 0; }
        .main-content {
            flex: 1;
            display: flex;
            background:
                linear-gradient(120deg, rgba(0, 91, 92, 0.26) 0%, rgba(0, 91, 92, 0.16) 38%, rgba(0, 91, 92, 0.09) 72%),
                linear-gradient(180deg, #8ba79a 0%, #7c9789 54%, #6d877a 100%);
            padding: 10px 10px 0 10px;
            gap: 10px;
            overflow: hidden;
            transition: background-color 0.3s;
        }
        .products-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 24px 24px 0 24px;
            overflow: hidden;
            background: rgba(146, 180, 161, 0.99);
            border: 1px solid rgba(0, 91, 92, 0.46);
            border-bottom: none;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 10px 20px rgba(0, 91, 92, 0.18);
        }
        .cart-panel {
            width: 300px;
            background: rgba(142, 180, 162, 0.99);
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 91, 92, 0.5);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 14px 28px rgba(0, 91, 92, 0.24);
            transition: background-color 0.3s, border-color 0.3s;
        }
        .products-grid { flex: 1; overflow-y: auto; padding-right: 8px; padding-bottom: 24px; -ms-overflow-style: none; scrollbar-width: none; }
        .products-grid::-webkit-scrollbar { display: none; }
        .product-card { display: flex; flex-direction: column; height: 100%; }
        .product-card .product-image-container { height: 160px; flex-shrink: 0; }
        .product-card .product-info { flex: 1; display: flex; flex-direction: column; }
        .product-card .product-footer { margin-top: auto; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cart-items-scroll { flex: 1; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none; }
        .cart-items-scroll::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .category-tab.active { background: var(--cashier-mint-900) !important; color: black !important; border-color: var(--cashier-mint-900) !important; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: black; }
        .nav-item.active { background: var(--cashier-mint-500); color: black; }
        .sidebar-section-title { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 16px 8px; display: flex; align-items: center; justify-content: space-between; }
        .pos-modal-header {
            background: linear-gradient(180deg, #0b6d6e 0%, #055b5c 55%, #024647 100%);
            color: #000000;
        }
        .pos-modal-header-subtitle {
            color: #d3f3e8;
        }
        .pos-sidebar-toggle { display: none; }
        .pos-sidebar-overlay { display: none; }
        .mobile-sidebar-safe-start { padding-left: 0; }

        body.cashier-mint-theme #ordered-count,
        body.cashier-mint-theme #cart-count-badge {
            background: var(--cashier-mint-900) !important;
            color: #000000 !important;
        }

        body.cashier-mint-theme .product-card:hover {
            border-color: var(--cashier-mint-500) !important;
        }

        body.cashier-mint-theme .product-card {
            background: rgba(150, 185, 167, 0.98) !important;
            border-color: rgba(0, 91, 92, 0.42) !important;
            box-shadow: 0 8px 16px rgba(0, 91, 92, 0.14) !important;
        }

        body.cashier-mint-theme .product-card .product-image-container {
            background: rgba(134, 170, 153, 0.95) !important;
        }

        /* Improve readability in light mode by replacing low-contrast gray text */
        html:not(.dark) body.cashier-mint-theme .text-gray-400,
        html:not(.dark) body.cashier-mint-theme .text-gray-500,
        html:not(.dark) body.cashier-mint-theme .text-gray-600,
        html:not(.dark) body.cashier-mint-theme .text-gray-700,
        html:not(.dark) body.cashier-mint-theme .text-gray-800,
        html:not(.dark) body.cashier-mint-theme .text-gray-900 {
            color: #111111 !important;
        }

        /* Lightly darken all white containers in POS */
        body.cashier-mint-theme .bg-white {
            background-color: rgba(188, 213, 200, 0.94) !important;
            border-color: rgba(0, 91, 92, 0.24) !important;
        }

        body.cashier-mint-theme .payment-method-btn.border-green-600,
        body.cashier-mint-theme .payment-method-btn.text-green-600 {
            border-color: var(--cashier-mint-500) !important;
            color: var(--cashier-mint-900) !important;
            background: rgba(224, 255, 244, 0.72) !important;
        }

        body.cashier-mint-theme .size-option-btn.border-green-500,
        body.cashier-mint-theme .bg-green-50 {
            background-color: var(--cashier-mint-050) !important;
        }

        body.cashier-mint-theme .text-green-600,
        body.cashier-mint-theme .text-green-700,
        body.cashier-mint-theme .text-green-800 {
            color: var(--cashier-mint-900) !important;
        }

        body.cashier-mint-theme .bg-green-500,
        body.cashier-mint-theme .bg-green-600,
        body.cashier-mint-theme .hover\:bg-green-600:hover,
        body.cashier-mint-theme .hover\:bg-green-700:hover {
            background-color: var(--cashier-mint-500) !important;
        }

        body.cashier-mint-theme .border-green-500,
        body.cashier-mint-theme .border-green-600 {
            border-color: var(--cashier-mint-500) !important;
        }

        @media (max-width: 1023px) {
            .sidebar.pos-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(86vw, 280px);
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.32);
                z-index: 60;
            }

            .sidebar.pos-sidebar.open {
                transform: translateX(0);
            }

            .pos-sidebar-toggle {
                position: fixed;
                top: 0.75rem;
                left: 0.75rem;
                z-index: 65;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .pos-sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.6);
                backdrop-filter: blur(1px);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
                z-index: 55;
                display: block;
            }

            .pos-sidebar-overlay.show {
                opacity: 1;
                pointer-events: auto;
            }

            body.pos-sidebar-open {
                overflow: hidden;
            }

            .mobile-sidebar-safe-start {
                padding-left: 3.75rem;
            }
        }

        @media (min-width: 1024px) {
            .pos-sidebar-toggle,
            .pos-sidebar-overlay {
                display: none !important;
            }
        }
        @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.2s ease forwards; }
        
        /* Dark Mode Styles - Comprehensive */
        html.dark body { background: #111827 !important; color: #f3f4f6 !important; }
        html.dark .main-content { background: #1f2937 !important; }
        html.dark .products-panel { background: #1f2937 !important; }
        html.dark .cart-panel { background: #111827 !important; border-color: #374151 !important; }
        
        /* Product Cards */
        html.dark .product-card { background: #374151 !important; border-color: #4b5563 !important; }
        html.dark .product-card:hover { border-color: var(--cashier-mint-500) !important; }
        html.dark .product-card h4, html.dark .product-card h3 { color: #f3f4f6 !important; }
        html.dark .product-card span { color: #d1d5db !important; }
        html.dark .product-card p { color: #9ca3af !important; }
        html.dark .product-card .text-gray-300 { color: #6b7280 !important; }
        html.dark .product-card .text-gray-400 { color: #9ca3af !important; }
        html.dark .product-card .text-gray-500 { color: #9ca3af !important; }
        html.dark .product-card .text-gray-600 { color: #d1d5db !important; }
        html.dark .product-card .text-gray-700 { color: #d1d5db !important; }
        html.dark .product-card .text-gray-800 { color: #e5e7eb !important; }
        html.dark .product-card .text-gray-900 { color: #f3f4f6 !important; }
        html.dark .product-card .bg-gray-50 { background: #4b5563 !important; }
        html.dark .product-card .bg-gray-100 { background: #4b5563 !important; }
        html.dark .product-card .bg-gray-200 { background: #374151 !important; }
        html.dark .product-image-container { background: #4b5563 !important; }
        html.dark .product-info { color: #f3f4f6 !important; }
        
        /* Category Tabs */
        html.dark .category-tab { background: #374151 !important; border-color: #4b5563 !important; color: #d1d5db !important; }
        html.dark .category-tab:hover { background: #4b5563 !important; }
        html.dark .category-tab.active { background: var(--cashier-mint-900) !important; border-color: var(--cashier-mint-900) !important; color: black !important; }
        
        /* Search & Filter */
        html.dark #product-search { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark #product-search::placeholder { color: #9ca3af !important; }
        html.dark .products-panel h1 { color: #f3f4f6 !important; }
        html.dark .products-panel p { color: #9ca3af !important; }
        html.dark button[type="button"] { color: #d1d5db !important; }
        html.dark .btn-filter, html.dark #show-ordered-btn { background: #374151 !important; border-color: #4b5563 !important; color: #d1d5db !important; }
        html.dark .btn-filter:hover, html.dark #show-ordered-btn:hover { background: #4b5563 !important; }
        

        
        /* Cart Panel */
        html.dark .cart-panel h2 { color: #f3f4f6 !important; }
        html.dark .cart-panel h3 { color: #f3f4f6 !important; }
        html.dark .cart-panel h4 { color: #f3f4f6 !important; }
        html.dark .cart-panel p { color: #d1d5db !important; }
        html.dark .cart-panel span { color: #d1d5db !important; }
        html.dark .cart-panel label { color: #d1d5db !important; }
        html.dark .cart-panel .border-gray-100 { border-color: #374151 !important; }
        html.dark .cart-panel .border-gray-200 { border-color: #374151 !important; }
        html.dark .cart-panel .border-b { border-color: #374151 !important; }
        html.dark .cart-panel .border-t { border-color: #374151 !important; }
        html.dark .cart-panel .bg-gray-50 { background: #1f2937 !important; }
        html.dark .cart-panel .bg-gray-100 { background: #374151 !important; }
        html.dark .cart-panel .bg-white { background: #1f2937 !important; }
        html.dark .cart-panel .text-gray-300 { color: #6b7280 !important; }
        html.dark .cart-panel .text-gray-400 { color: #9ca3af !important; }
        html.dark .cart-panel .text-gray-500 { color: #9ca3af !important; }
        html.dark .cart-panel .text-gray-600 { color: #d1d5db !important; }
        html.dark .cart-panel .text-gray-700 { color: #d1d5db !important; }
        html.dark .cart-panel .text-gray-800 { color: #e5e7eb !important; }
        html.dark .cart-panel .text-gray-900 { color: #f3f4f6 !important; }
        html.dark .cart-panel input { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark .cart-panel input::placeholder { color: #9ca3af !important; }
        html.dark .cart-panel select { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark .cart-panel textarea { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        
        /* Global Text Colors */
        html.dark .text-gray-300 { color: #d1d5db !important; }
        html.dark .text-gray-400 { color: #9ca3af !important; }
        html.dark .text-gray-500 { color: #9ca3af !important; }
        html.dark .text-gray-600 { color: #d1d5db !important; }
        html.dark .text-gray-700 { color: #d1d5db !important; }
        html.dark .text-gray-800 { color: #e5e7eb !important; }
        html.dark .text-gray-900 { color: #f3f4f6 !important; }
        html.dark .text-black { color: #f3f4f6 !important; }
        
        /* Global Backgrounds */
        html.dark .bg-white { background: #374151 !important; }
        html.dark .bg-gray-50 { background: #1f2937 !important; }
        html.dark .bg-gray-100 { background: #374151 !important; }
        html.dark .bg-gray-200 { background: #4b5563 !important; }
        
        /* Borders */
        html.dark .border-gray-100 { border-color: #374151 !important; }
        html.dark .border-gray-200 { border-color: #4b5563 !important; }
        html.dark .border-gray-300 { border-color: #4b5563 !important; }
        
        /* Modals and Dropdowns */
        html.dark [x-show] .bg-white { background: #374151 !important; }
        html.dark .modal-content { background: #374151 !important; }
        html.dark .dropdown-menu { background: #374151 !important; }
        
        /* Form Elements */
        html.dark input[type="text"], html.dark input[type="number"], html.dark input[type="email"], html.dark input[type="password"] {
            background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important;
        }
        html.dark select { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark textarea { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark ::placeholder { color: #9ca3af !important; }
        
        /* Size option buttons in modal */
        html.dark .size-option-btn {
            background: #374151 !important;
            border-color: #4b5563 !important;
        }
        html.dark .size-option-btn div {
            color: #e5e7eb !important;
        }
        html.dark .size-option-btn.border-green-500 {
            background: #0b3f40 !important;
            border-color: var(--cashier-mint-500) !important;
        }
        html.dark .size-option-btn.border-green-500 div {
            color: #f0fdf4 !important;
        }
        html.dark .size-option-btn.border-green-500 .text-green-600 {
            color: #bbf7d0 !important;
        }

        /* POS control contrast fixes in dark mode */
        html.dark #modal-qty-minus {
            background: #4b5563 !important;
            color: #f3f4f6 !important;
            border: 1px solid #6b7280 !important;
        }
        html.dark #modal-qty-minus:hover {
            background: #6b7280 !important;
            color: #000000 !important;
        }
        html.dark #modal-qty-plus {
            background: var(--cashier-mint-900) !important;
            color: #f0fdf4 !important;
            border: 1px solid var(--cashier-mint-500) !important;
        }
        html.dark #modal-qty-plus:hover {
            background: var(--cashier-mint-500) !important;
            color: #000000 !important;
        }
        html.dark #pay-btn {
            background: var(--cashier-mint-500) !important;
            color: #f8fafc !important;
        }
        html.dark #pay-btn:disabled {
            background: #374151 !important;
            color: #d1d5db !important;
            border: 1px solid #4b5563 !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body class="cashier-mint-theme font-sans antialiased h-full">
    <div class="pos-wrapper">
        @include('partials.cashier-sidebar')

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- PRODUCTS PANEL -->
            <div class="products-panel">
                <!-- Header -->
                <div class="mb-6 mobile-sidebar-safe-start">
                    <h1 class="text-2xl font-bold text-gray-900">My Stuff -</h1>
                    <p class="text-gray-500 text-sm">Let's Choose Your Option To Sale!</p>
                </div>

                <!-- Search & Filter Row -->
                <div class="flex items-center gap-3 mb-5">
                    <div class="relative flex-1 max-w-sm">
                        <input type="text" id="product-search" placeholder="Search" 
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <button type="button" class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        Filters
                    </button>
                    <!-- Ordered button -->
                    <button type="button" id="show-ordered-btn" class="ml-auto px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        Ordered
                        <span id="ordered-count" class="bg-gray-800 text-black text-xs px-2 py-0.5 rounded-full min-w-[24px] text-center">0</span>
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-2 mb-5 overflow-x-auto pb-1">
                    <button class="category-tab active px-5 py-2 bg-gray-900 text-black rounded-xl text-sm font-medium whitespace-nowrap transition-all" data-category="all">
                        All Items
                    </button>
                    @php
                        $tabGroups = [
                            'Milk Tea' => ['milk tea'],
                            'Frappe' => ['frappe'],
                            'Fruit Tea' => ['fruit'],
                            'Coffee' => ['coffee'],
                            'Burgers' => ['burger'],
                            'Chicken' => ['chicken', 'wing'],
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
                    <button class="category-tab px-5 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-medium whitespace-nowrap transition-all hover:bg-gray-50" data-category="{{ implode(',', $tab['ids']) }}">
                        {{ $tab['name'] }}
                    </button>
                    @endforeach
                </div>

                <!-- Products Grid -->
                <div class="products-grid">
                    <div id="products-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($products as $product)
                        @php 
                            $isComposite = $product->product_type === 'composite';
                            $qty = $isComposite ? ($product->is_available ? 9999 : 0) : ($product->direct_stock ?? 0);
                            $hasSizes = $product->options && is_array($product->options) && !empty($product->options);
                            $soldCount = $product->salesItems->sum('quantity') ?? 0;
                        @endphp
                        <div class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg hover:border-green-300 transition-all cursor-pointer {{ !$product->is_available ? 'opacity-50' : '' }}" 
                             data-category="{{ $product->category_id }}"
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-price="{{ $product->price }}"
                             data-stock="{{ $qty }}"
                             data-product-type="{{ $product->product_type }}"
                             data-product-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                             data-options='{{ json_encode($product->options ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                            
                            <!-- Product Image -->
                            <div class="product-image-container bg-gray-50 relative flex items-center justify-center overflow-hidden">
                                @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                <div class="flex flex-col items-center justify-center text-gray-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs mt-1">No Image</span>
                                </div>
                                @endif
                                @if(!$product->is_available)
                                <div class="stock-overlay absolute inset-0 bg-black/40 flex items-center justify-center">
                                    @if($isComposite)
                                    <span class="bg-orange-500 text-black text-xs px-2 py-1 rounded-lg">Missing Ingredients</span>
                                    @else
                                    <span class="bg-red-500 text-black text-xs px-2 py-1 rounded-lg">Out of Stock</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Info -->
                            <div class="product-info p-3">
                                <h4 class="font-semibold text-xs text-gray-900 mb-0.5">{{ $product->name }}</h4>
                                <div class="flex items-center gap-3 text-[10px] text-gray-500 mb-2">
                                    @if($isComposite)
                                        @if($product->is_available)
                                        <span>Status <span class="text-green-600 font-medium">Available</span></span>
                                        @else
                                        <span>Status <span class="text-red-500 font-medium">Unavailable</span></span>
                                        @endif
                                    @else
                                    <span>Avail <span class="{{ $qty > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">{{ $qty }}pcs</span></span>
                                    @endif
                                </div>
                                
                                @if($hasSizes)
                                <div class="mb-2">
                                    <span class="text-[8px] text-gray-400 block mb-0.5">Sizes</span>
                                    <div class="flex gap-1 flex-wrap">
                                        @foreach($product->options as $option)
                                            @if(isset($option['values']) && is_array($option['values']))
                                                @foreach($option['values'] as $value)
                                                    @php
                                                        $sizeLabel = $value['label'] ?? $value['value'] ?? $value['name'] ?? '';
                                                        $sizePrice = $value['price'] ?? $product->price;
                                                    @endphp
                                                    <span class="text-xs text-gray-600 bg-gray-100 px-1 py-0.5 rounded font-medium whitespace-nowrap">{{ $sizeLabel }} - ₱{{ number_format($sizePrice, 0) }}</span>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="product-footer flex items-center justify-between pt-2">
                                    <div>
                                        @if($hasSizes)
                                            @php
                                                $lowestPrice = $product->price;
                                                foreach($product->options as $opt) {
                                                    if(isset($opt['values']) && is_array($opt['values'])) {
                                                        foreach($opt['values'] as $val) {
                                                            $p = $val['price'] ?? $product->price;
                                                            if($p < $lowestPrice) $lowestPrice = $p;
                                                        }
                                                        // Use first size price as lowest if base price doesn't match
                                                        if(!empty($opt['values'])) {
                                                            $lowestPrice = $opt['values'][0]['price'] ?? $product->price;
                                                            foreach($opt['values'] as $val) {
                                                                $p = $val['price'] ?? $product->price;
                                                                if($p < $lowestPrice) $lowestPrice = $p;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <span class="text-[8px] text-gray-400 block">From</span>
                                            <p class="text-sm font-bold text-gray-900">₱{{ number_format($lowestPrice, 0) }}</p>
                                        @else
                                            <span class="text-[8px] text-gray-400 block">Price</span>
                                            <p class="text-sm font-bold text-gray-900">₱{{ number_format($product->price, 0) }}</p>
                                        @endif
                                    </div>
                                    <button type="button" class="add-to-cart-btn flex items-center gap-1 px-2 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-[10px] font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Add to cart
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- CART PANEL (RIGHT SIDEBAR) -->
            <div class="cart-panel">
                <!-- Cashier Info Header -->
                <div class="p-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="text-[10px] text-gray-400">Cashier {{ Auth::user()->id }}</span>
                                <span class="flex items-center gap-1 text-[10px] text-green-600 font-medium">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    Online
                                </span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400">ID : #{{ str_pad(Auth::user()->id, 7, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-gray-400">{{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Header -->
                <div class="px-3 py-2 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900 text-sm">List Order Product</span>
                        <span id="cart-count-badge" class="bg-green-600 text-black text-xs px-1.5 py-0.5 rounded-full">0</span>
                    </div>
                    <button type="button" id="clear-cart-btn" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                        Clear All
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="cart-items-scroll flex-1 p-3">
                    <div id="cart-items">
                        <!-- Empty state -->
                        <div id="cart-empty" class="text-center py-10">
                            <svg class="w-14 h-14 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-400 text-xs">No items selected</p>
                            <p class="text-gray-300 text-[10px] mt-1">Click on products to add them</p>
                        </div>
                        <!-- Cart items list -->
                        <div id="cart-items-list" class="space-y-2 hidden"></div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="p-3 border-t border-gray-100 bg-white">
                    <!-- Total -->
                    <div class="flex justify-between items-center py-2 border-y border-gray-100 mb-3">
                        <span class="font-semibold text-gray-900 text-sm">Total Amount</span>
                        <span id="total" class="text-base font-bold text-gray-900">₱0.0</span>
                    </div>

                    <!-- Payment Method Buttons -->
                    <div class="flex gap-1.5 mb-3">
                        <button type="button" class="payment-method-btn flex-1 py-2 border-2 border-green-600 text-green-600 rounded-lg text-xs font-semibold hover:bg-green-50 transition-colors" data-method="cash">
                            Cash
                        </button>
                        <button type="button" id="gcash-method-btn" class="payment-method-btn flex-1 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-50 transition-colors {{ ($paymongoConfigured ?? false) ? '' : 'opacity-50 cursor-not-allowed' }}" data-method="gcash" data-configured="{{ ($paymongoConfigured ?? false) ? 'true' : 'false' }}" {{ ($paymongoConfigured ?? false) ? '' : 'title=GCash is not configured in this device yet' }}>
                            GCash
                        </button>
                    </div>

                    <!-- Pay Button -->
                    <button type="button" id="pay-btn" disabled class="w-full py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-black rounded-lg font-bold text-sm transition-colors">
                        Pay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Options Modal -->
    <div id="product-options-modal" class="fixed inset-0 z-50 hidden">
        <div id="product-options-backdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="flex items-center justify-center min-h-screen p-3 sm:p-4">
            <div class="bg-white rounded-2xl shadow-2xl relative z-10 w-full max-w-md sm:max-w-lg md:max-w-2xl mx-2 sm:mx-4 overflow-hidden animate-fade-in max-h-[90vh] md:max-h-[95vh] flex flex-col">
                <div class="pos-modal-header px-4 sm:px-6 py-4">
                    <h3 id="modal-product-name" class="text-xl font-bold">Select Size</h3>
                    <p class="pos-modal-header-subtitle text-sm mt-1">Choose your preferred size</p>
                </div>
                
                <form id="product-options-form" class="p-4 sm:p-6 flex flex-col min-h-0">
                    <div id="options-container" class="space-y-4 overflow-y-auto max-h-[32vh] sm:max-h-[38vh] md:max-h-none md:overflow-visible pr-1"></div>
                    
                    <!-- Quantity Selector -->
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <label for="modal-qty-input" class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center justify-center gap-4">
                            <button type="button" id="modal-qty-minus" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors">−</button>
                            <input type="number" id="modal-qty-input" value="1" min="1" class="w-16 text-center text-xl font-bold border border-gray-200 rounded-lg py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" readonly>
                            <button type="button" id="modal-qty-plus" class="w-10 h-10 rounded-full bg-green-100 hover:bg-green-200 text-green-700 font-bold text-xl flex items-center justify-center transition-colors">+</button>
                        </div>
                    </div>

                    <div id="modal-total-price-hint" class="text-center text-lg sm:text-xl font-bold text-green-600 mt-3 px-3 bg-green-50 rounded-xl h-12 sm:h-[60px] flex items-center justify-center invisible">Total: ₱0.00</div>

                    <div class="flex gap-3 mt-3 pt-3 border-t border-gray-200 bg-white">
                        <button type="button" id="modal-cancel-btn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
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
            <div class="bg-white rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden animate-fade-in">
                <div class="pos-modal-header px-6 py-4">
                    <h3 class="text-xl font-bold">Enter Amount Paid</h3>
                    <p class="pos-modal-header-subtitle text-sm mt-1">Total: <span id="modal-total-display">₱0</span></p>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <label for="amount-paid-input" class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg font-medium">₱</span>
                            <input type="number" id="amount-paid-input" step="0.01" min="0" placeholder="0.00" 
                                   class="w-full pl-10 pr-4 py-4 text-2xl font-bold border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    
                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <button type="button" class="quick-amount-btn py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200" data-amount="50">₱50</button>
                        <button type="button" class="quick-amount-btn py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200" data-amount="100">₱100</button>
                        <button type="button" class="quick-amount-btn py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200" data-amount="200">₱200</button>
                        <button type="button" class="quick-amount-btn py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200" data-amount="500">₱500</button>
                        <button type="button" class="quick-amount-btn py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200" data-amount="1000">₱1000</button>
                        <button type="button" class="quick-amount-btn py-3 bg-green-100 text-green-700 rounded-xl text-sm font-semibold hover:bg-green-200" data-amount="exact">Exact</button>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-4">
                        <span class="text-gray-500">Change:</span>
                        <span id="change-display" class="text-2xl font-bold text-green-600">₱0.00</span>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('amount-modal').classList.add('hidden')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
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
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" id="modal-backdrop"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden animate-fade-in">
                <div class="pos-modal-header px-6 py-4">
                    <h3 class="text-lg font-bold text-black flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Confirm Order
                    </h3>
                    <p class="pos-modal-header-subtitle text-sm mt-1">Please review before processing</p>
                </div>

                <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
                    <div id="confirm-order-items" class="space-y-2 mb-4"></div>
                    <div class="border-t border-gray-200 my-3"></div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span id="confirm-subtotal" class="font-medium text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-green-700 pt-1">
                            <span>Total</span>
                            <span id="confirm-total">₱0.00</span>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Payment Method</span>
                            <span id="confirm-payment-method" class="font-medium text-gray-900">Cash</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Amount Paid</span>
                            <span id="confirm-amount-paid" class="font-medium text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Change</span>
                            <span id="confirm-change" class="font-bold text-green-600">₱0.00</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                    <button type="button" id="cancel-order-btn" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirm-order-btn" class="flex-1 px-4 py-3 bg-green-600 text-black rounded-xl font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm & Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GCash QR Modal -->
    <div id="gcash-qr-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60" id="gcash-modal-backdrop"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden animate-fade-in">
                <div class="bg-green-600 text-black px-6 py-4">
                    <h3 class="text-xl font-bold">GCash Payment</h3>
                    <p class="text-green-100 text-sm mt-1">Scan to pay before processing receipt</p>
                </div>

                <div class="p-6">
                    <div class="text-center mb-4">
                        <img id="gcash-qr-image" alt="GCash QR" class="w-64 h-64 mx-auto rounded-xl border border-gray-200 p-2 bg-white">
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 mb-4 text-sm">
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-500">Amount</span>
                            <span id="gcash-amount-display" class="font-semibold text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span id="gcash-status-display" class="font-semibold text-amber-600">Waiting for payment...</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" id="gcash-cancel-btn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="gcash-check-btn" class="flex-1 px-4 py-3 bg-green-600 text-black rounded-xl font-medium hover:bg-green-700 transition-colors">
                            I've Paid, Check
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setPosSidebar(open) {
            const sidebar = document.getElementById('posSidebar');
            const overlay = document.getElementById('posSidebarOverlay');
            const toggle = document.getElementById('posSidebarToggle');
            const isMobile = window.innerWidth < 1024;

            if (!sidebar || !overlay || !toggle || !isMobile) return;

            sidebar.classList.toggle('open', open);
            overlay.classList.toggle('show', open);
            document.body.classList.toggle('pos-sidebar-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function closePosSidebar() {
            setPosSidebar(false);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('posSidebarToggle');
            const overlay = document.getElementById('posSidebarOverlay');
            const nav = document.getElementById('posSidebarNav');

            if (toggle) {
                toggle.addEventListener('click', function() {
                    const sidebar = document.getElementById('posSidebar');
                    const isOpen = sidebar ? sidebar.classList.contains('open') : false;
                    setPosSidebar(!isOpen);
                });
            }

            if (overlay) {
                overlay.addEventListener('click', closePosSidebar);
            }

            if (nav) {
                nav.addEventListener('click', function(event) {
                    if (window.innerWidth < 1024 && event.target.closest('a.nav-item')) {
                        closePosSidebar();
                    }
                });
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    const sidebar = document.getElementById('posSidebar');
                    const overlayEl = document.getElementById('posSidebarOverlay');
                    if (sidebar) sidebar.classList.remove('open');
                    if (overlayEl) overlayEl.classList.remove('show');
                    document.body.classList.remove('pos-sidebar-open');
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closePosSidebar();
                }
            });
        });

        // Cart state
        let cart = [];
        try {
            const savedCart = localStorage.getItem('pos_cart');
            if (savedCart) cart = JSON.parse(savedCart);
        } catch(e) { cart = []; }
        
        let subtotal = 0;
        let currentCategory = 'all';
        let currentSearchQuery = '';
        let selectedPaymentMethod = 'cash';
        let gcashContext = null;
        let gcashPollingTimer = null;

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
        }

        // Category tabs
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.category-tab').forEach(t => {
                    t.classList.remove('active', 'bg-gray-900', 'text-black');
                    t.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
                });
                this.classList.add('active', 'bg-gray-900', 'text-black');
                this.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-200');
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
            const productType = card.dataset.productType || 'direct';
            const productImage = card.dataset.productImage || '';
            let options = [];
            try { options = JSON.parse(card.dataset.options || '[]'); } catch(e) { options = []; }

            if (stock <= 0) {
                if (productType === 'composite') {
                    alert('This product has missing ingredients!');
                } else {
                    alert('This product is out of stock!');
                }
                return;
            }

            if (options && options.length > 0) {
                openOptionsModal({ productId, productName, productPrice, stock, options, productType, productImage });
                return;
            }

            addOrIncrementCartItem({ productId, productName, productPrice, stock, options: null, productType, productImage });
            updateCart();
        }

        function sameOptions(a, b) {
            try { return JSON.stringify(a || {}) === JSON.stringify(b || {}); }
            catch(e) { return false; }
        }

        function addOrIncrementCartItem(item) {
            const isComposite = item.productType === 'composite';
            const existingItem = cart.find(i => i.productId === item.productId && sameOptions(i.options, item.options));
            if (existingItem) {
                if (!isComposite && existingItem.quantity >= item.stock) {
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
                    productImage: item.productImage || '',
                    quantity: 1,
                    stock: item.stock,
                    productType: item.productType || 'direct',
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

                const label = document.createElement('p');
                label.className = 'block text-sm font-semibold text-gray-700 mb-2';
                label.textContent = opt.name || 'Select Size';

                const buttonsDiv = document.createElement('div');
                buttonsDiv.className = 'grid grid-cols-2 md:grid-cols-3 gap-3';
                buttonsDiv.setAttribute('data-option-name', opt.name || `Option ${idx+1}`);

                values.forEach((v, vIdx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'size-option-btn p-4 rounded-xl border-2 transition-all text-center hover:border-green-400 ' + 
                                   (vIdx === 0 ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white');
                    
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
                        <div class="font-bold text-gray-900 text-lg">${labelValue}</div>
                        <div class="text-green-600 font-semibold mt-1">₱${priceVal > 0 ? priceVal.toFixed(0) : parseFloat(product.productPrice).toFixed(0)}</div>
                    `;
                    
                    btn.addEventListener('click', function() {
                        buttonsDiv.querySelectorAll('.size-option-btn').forEach(b => {
                            b.classList.remove('border-green-500', 'bg-green-50');
                            b.classList.add('border-gray-200', 'bg-white');
                        });
                        this.classList.remove('border-gray-200', 'bg-white');
                        this.classList.add('border-green-500', 'bg-green-50');
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

            // Reset quantity to 1
            document.getElementById('modal-qty-input').value = 1;

            const updateModalPrice = () => {
                let computed = parseFloat(product.productPrice);
                const selectedBtn = container.querySelector('.size-option-btn.border-green-500');
                if (selectedBtn && selectedBtn.dataset.price) {
                    const priceVal = parseFloat(selectedBtn.dataset.price);
                    if (priceVal > 0) computed = priceVal;
                }
                const qty = parseInt(document.getElementById('modal-qty-input').value) || 1;
                const totalHint = document.getElementById('modal-total-price-hint');
                if (qty > 1) {
                    totalHint.textContent = `Total: ₱${(computed * qty).toFixed(2)}`;
                    totalHint.classList.remove('invisible');
                } else {
                    totalHint.textContent = 'Total: ₱0.00';
                    totalHint.classList.add('invisible');
                }
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

        // Live update total hint based on quantity
        function updateModalTotal() {
            const modal = document.getElementById('product-options-modal');
            const container = document.getElementById('options-container');
            const qty = parseInt(document.getElementById('modal-qty-input').value) || 1;
            const product = modal.dataset.currentProduct ? JSON.parse(modal.dataset.currentProduct) : null;
            if (!product) return;
            const computed = parseFloat(modal.dataset.computedPrice) || parseFloat(product.productPrice);
            const totalHint = document.getElementById('modal-total-price-hint');
            if (totalHint) {
                if (qty > 1) {
                    totalHint.textContent = `Total: ₱${(computed * qty).toFixed(2)}`;
                    totalHint.classList.remove('invisible');
                } else {
                    totalHint.textContent = 'Total: ₱0.00';
                    totalHint.classList.add('invisible');
                }
            }
        }

        // Quantity controls in modal
        document.getElementById('modal-qty-minus').addEventListener('click', () => {
            const input = document.getElementById('modal-qty-input');
            let val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
            updateModalTotal();
        });

        document.getElementById('modal-qty-plus').addEventListener('click', () => {
            const input = document.getElementById('modal-qty-input');
            let val = parseInt(input.value) || 1;
            const modal = document.getElementById('product-options-modal');
            const product = modal.dataset.currentProduct ? JSON.parse(modal.dataset.currentProduct) : null;
            if (product && product.productType !== 'composite' && val >= product.stock) {
                alert('Cannot add more items than available in stock!');
                return;
            }
            input.value = val + 1;
            updateModalTotal();
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
            const modalQty = parseInt(document.getElementById('modal-qty-input').value) || 1;
            
            for (let i = 0; i < modalQty; i++) {
                addOrIncrementCartItem({
                    productId: product.productId,
                    productName: product.productName,
                    productPrice: computedPrice,
                    productImage: product.productImage || '',
                    stock: product.stock,
                    productType: product.productType || 'direct',
                    options: selected
                });
            }

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
                    const optionText = item.options ? Object.entries(item.options).map(([k,v]) => `${k}: ${v}`).join(', ') : '';
                    const imageHtml = item.productImage 
                        ? `<img src="${item.productImage}" alt="${item.productName}" class="w-full h-full object-cover rounded-lg">`
                        : `<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
                    
                    return `
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex-shrink-0 flex items-center justify-center overflow-hidden">
                                ${imageHtml}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-900 truncate">${item.productName}</p>
                                ${optionText ? `<p class="text-xs text-gray-500">${optionText}</p>` : ''}
                                <p class="text-sm font-bold text-gray-900 mt-0.5">₱${item.productPrice.toFixed(1)}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                    <button onclick="updateQuantity(${index}, -1)" class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-600 font-bold text-sm">
                                        -
                                    </button>
                                    <span class="w-6 text-center font-semibold text-gray-900 text-sm">${item.quantity}</span>
                                    <button onclick="updateQuantity(${index}, 1)" class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-600 font-bold text-sm">
                                        +
                                    </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            const total = subtotal;
            const subtotalEl = document.getElementById('subtotal');
            if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(0);
            document.getElementById('total').textContent = '₱' + total.toFixed(1);
            document.getElementById('cart-count-badge').textContent = cart.length;
            document.getElementById('ordered-count').textContent = cart.length;
            const navCartCount = document.getElementById('nav-cart-count');
            if (navCartCount) navCartCount.textContent = cart.length;
            document.getElementById('pay-btn').disabled = cart.length === 0;
        }

        function updateQuantity(index, change) {
            const item = cart[index];
            const isComposite = item.productType === 'composite';
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                cart.splice(index, 1);
            } else if (isComposite || newQuantity <= item.stock) {
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
                if (this.dataset.method === 'gcash' && this.dataset.configured === 'false') {
                    alert('GCash is not configured yet on this device. Add PAYMONGO_SECRET_KEY in .env and run php artisan config:clear.');
                    return;
                }

                document.querySelectorAll('.payment-method-btn').forEach(b => {
                    b.classList.remove('border-green-600', 'text-green-600', 'border-2');
                    b.classList.add('border-gray-200', 'text-gray-600');
                });
                this.classList.remove('border-gray-200', 'text-gray-600');
                this.classList.add('border-green-600', 'text-green-600', 'border-2');
                selectedPaymentMethod = this.dataset.method;
            });
        });

        // Pay button
        document.getElementById('pay-btn').addEventListener('click', function() {
            if (cart.length === 0) return;
            const total = subtotal;
            document.getElementById('modal-total-display').textContent = '₱' + total.toFixed(2);
            if (selectedPaymentMethod === 'gcash') {
                document.getElementById('amount-paid-input').value = '0.00';
                document.getElementById('change-display').textContent = '₱0.00';
            } else {
                document.getElementById('amount-paid-input').value = '0.00';
                document.getElementById('change-display').textContent = '₱0.00';
            }
            document.getElementById('amount-modal').classList.remove('hidden');
        });

        // Quick amount buttons
        document.querySelectorAll('.quick-amount-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const amount = this.dataset.amount;
                const total = subtotal;
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
            const total = subtotal;
            const change = amountPaid - total;
            document.getElementById('change-display').textContent = '₱' + (change >= 0 ? change.toFixed(2) : '0.00');
        }

        // Confirm payment
        document.getElementById('confirm-payment-btn').addEventListener('click', function() {
            const amountPaid = parseFloat(document.getElementById('amount-paid-input').value) || 0;
            const total = subtotal;

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
                    if (optVal) optionLabel = `<span class="text-xs text-gray-500"> (${optVal})</span>`;
                }
                return `
                    <div class="flex justify-between items-center py-1.5">
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-900">${item.productName}</span>${optionLabel}
                            <span class="text-xs text-gray-500 ml-1">x${item.quantity}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 ml-3">₱${(item.productPrice * item.quantity).toFixed(2)}</span>
                    </div>
                `;
            }).join('');

            document.getElementById('confirm-subtotal').textContent = '₱' + total.toFixed(2);
            document.getElementById('confirm-total').textContent = '₱' + total.toFixed(2);
            
            const methodLabels = { cash: 'Cash', gcash: 'GCash' };
            document.getElementById('confirm-payment-method').textContent = methodLabels[selectedPaymentMethod] || selectedPaymentMethod;
            document.getElementById('confirm-amount-paid').textContent = '₱' + amountPaid.toFixed(2);
            document.getElementById('confirm-change').textContent = '₱' + (amountPaid - total).toFixed(2);

            document.getElementById('order-confirmation-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-order-btn').addEventListener('click', () => {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
            document.getElementById('amount-modal').classList.remove('hidden');
        });
        document.getElementById('modal-backdrop').addEventListener('click', () => {
            document.getElementById('order-confirmation-modal').classList.add('hidden');
            document.getElementById('amount-modal').classList.remove('hidden');
        });

        // Process sale
        document.getElementById('confirm-order-btn').addEventListener('click', async function() {
            document.getElementById('order-confirmation-modal').classList.add('hidden');

            const confirmBtn = this;
            const payBtn = document.getElementById('pay-btn');
            const amountPaid = parseFloat(document.getElementById('amount-paid-input').value) || 0;

            confirmBtn.disabled = true;
            payBtn.disabled = true;
            payBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            if (selectedPaymentMethod === 'gcash') {
                await startGcashFlow(amountPaid, confirmBtn, payBtn);
                return;
            }

            await processSaleRequest({
                paymentMethod: selectedPaymentMethod,
                amountPaid,
                confirmBtn,
                payBtn,
            });
        });

        function getCartPayload() {
            return cart.map(item => ({
                product_id: item.productId,
                quantity: item.quantity,
                options: item.options || null
            }));
        }

        async function processSaleRequest({ paymentMethod, amountPaid, confirmBtn, payBtn }) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('payment_method', paymentMethod);
            formData.append('amount_paid', amountPaid);
            formData.append('items', JSON.stringify(getCartPayload()));

            try {
                const response = await fetch('{{ route("pos.process-sale") }}', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.error || data.message || 'An error occurred');
                    return;
                }

                window.open(data.direct_print_url, '_blank');
                cart = [];
                localStorage.removeItem('pos_cart');
                updateCart();
                showSuccessToast('Sale completed! Receipt opened in new tab.');
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            }
        }

        async function startGcashFlow(amountPaid, confirmBtn, payBtn) {
            const createForm = new FormData();
            createForm.append('_token', '{{ csrf_token() }}');
            createForm.append('items', JSON.stringify(getCartPayload()));

            try {
                const createResp = await fetch('{{ route("pos.gcash.create-qr") }}', {
                    method: 'POST',
                    body: createForm,
                });
                const createData = await createResp.json();
                if (!createData.success) {
                    alert(createData.error || createData.message || 'Failed to generate GCash QR code.');
                    confirmBtn.disabled = false;
                    payBtn.disabled = cart.length === 0;
                    payBtn.innerHTML = 'Pay';
                    return;
                }

                gcashContext = {
                    sourceId: createData.source_id,
                    amountPaid,
                };

                const qrImg = document.getElementById('gcash-qr-image');
                const qrData = encodeURIComponent(createData.checkout_url);
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' + qrData;
                document.getElementById('gcash-amount-display').textContent = '₱' + Number(createData.amount || subtotal).toFixed(2);
                document.getElementById('gcash-status-display').textContent = 'Waiting for payment...';
                document.getElementById('gcash-status-display').className = 'font-semibold text-amber-600';
                document.getElementById('gcash-qr-modal').classList.remove('hidden');

                startGcashPolling(confirmBtn, payBtn);
            } catch (error) {
                console.error(error);
                alert('Failed to generate GCash QR code. Please try again.');
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            }
        }

        function startGcashPolling(confirmBtn, payBtn) {
            stopGcashPolling();
            gcashPollingTimer = setInterval(() => {
                checkGcashStatus(confirmBtn, payBtn, false);
            }, 4000);
        }

        function stopGcashPolling() {
            if (gcashPollingTimer) {
                clearInterval(gcashPollingTimer);
                gcashPollingTimer = null;
            }
        }

        async function checkGcashStatus(confirmBtn, payBtn, manualCheck) {
            if (!gcashContext || !gcashContext.sourceId) return;

            const checkForm = new FormData();
            checkForm.append('_token', '{{ csrf_token() }}');
            checkForm.append('source_id', gcashContext.sourceId);
            checkForm.append('items', JSON.stringify(getCartPayload()));

            try {
                const response = await fetch('{{ route("pos.gcash.check-status") }}', {
                    method: 'POST',
                    body: checkForm,
                });
                const data = await response.json();

                if (data.success) {
                    stopGcashPolling();
                    document.getElementById('gcash-status-display').textContent = 'Paid';
                    document.getElementById('gcash-status-display').className = 'font-semibold text-green-600';
                    setTimeout(() => {
                        document.getElementById('gcash-qr-modal').classList.add('hidden');
                    }, 400);

                    window.open(data.direct_print_url, '_blank');
                    cart = [];
                    localStorage.removeItem('pos_cart');
                    updateCart();
                    showSuccessToast('GCash payment completed! Receipt opened in new tab.');

                    confirmBtn.disabled = false;
                    payBtn.disabled = cart.length === 0;
                    payBtn.innerHTML = 'Pay';
                    gcashContext = null;
                    return;
                }

                if (data.status === 'pending' || data.status === 'processing') {
                    document.getElementById('gcash-status-display').textContent = data.message || 'Waiting for payment...';
                    document.getElementById('gcash-status-display').className = 'font-semibold text-amber-600';
                    return;
                }

                if (data.status) {
                    stopGcashPolling();
                    alert(data.error || data.message || 'GCash payment was not completed. Please try again.');
                    closeGcashModal(confirmBtn, payBtn);
                    return;
                }

                if (manualCheck) {
                    alert(data.error || data.message || 'Payment is not completed yet.');
                }
            } catch (error) {
                console.error(error);
                if (manualCheck) {
                    alert('Unable to check payment status right now. Please try again.');
                }
            }
        }

        function closeGcashModal(confirmBtn, payBtn) {
            stopGcashPolling();
            gcashContext = null;
            document.getElementById('gcash-qr-modal').classList.add('hidden');
            document.getElementById('gcash-status-display').textContent = 'Waiting for payment...';
            document.getElementById('gcash-status-display').className = 'font-semibold text-amber-600';
            confirmBtn.disabled = false;
            payBtn.disabled = cart.length === 0;
            payBtn.innerHTML = 'Pay';
        }

        document.getElementById('gcash-check-btn').addEventListener('click', function() {
            const confirmBtn = document.getElementById('confirm-order-btn');
            const payBtn = document.getElementById('pay-btn');
            checkGcashStatus(confirmBtn, payBtn, true);
        });

        document.getElementById('gcash-cancel-btn').addEventListener('click', function() {
            const confirmBtn = document.getElementById('confirm-order-btn');
            const payBtn = document.getElementById('pay-btn');
            closeGcashModal(confirmBtn, payBtn);
        });

        document.getElementById('gcash-modal-backdrop').addEventListener('click', function() {
            const confirmBtn = document.getElementById('confirm-order-btn');
            const payBtn = document.getElementById('pay-btn');
            closeGcashModal(confirmBtn, payBtn);
        });

        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-[100] bg-green-600 text-black px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-fade-in';
            toast.innerHTML = `
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'scale(0.95)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Initialize
        updateCart();

        // ==================== LIVE DATA POLLING ====================
        // Poll for product availability/stock updates every 10 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/pos/live-data', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!data.success || !data.products) return;

                document.querySelectorAll('.product-card').forEach(card => {
                    const productId = card.dataset.productId;
                    const productInfo = data.products[productId];
                    if (!productInfo) return;

                    const oldStock = parseInt(card.dataset.stock);
                    const newStock = productInfo.stock;
                    const isComposite = productInfo.product_type === 'composite';

                    // Update data attributes
                    card.dataset.stock = newStock;
                    card.dataset.productType = productInfo.product_type;

                    // Update opacity
                    if (!productInfo.is_available) {
                        card.classList.add('opacity-50');
                    } else {
                        card.classList.remove('opacity-50');
                    }

                    // Update stock overlay
                    const imageContainer = card.querySelector('.product-image-container');
                    let overlay = imageContainer.querySelector('.stock-overlay');
                    
                    if (!productInfo.is_available) {
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'stock-overlay absolute inset-0 bg-black/40 flex items-center justify-center';
                            imageContainer.appendChild(overlay);
                        }
                        if (isComposite) {
                            overlay.innerHTML = '<span class="bg-orange-500 text-black text-xs px-2 py-1 rounded-lg">Missing Ingredients</span>';
                        } else {
                            overlay.innerHTML = '<span class="bg-red-500 text-black text-xs px-2 py-1 rounded-lg">Out of Stock</span>';
                        }
                    } else if (overlay) {
                        overlay.remove();
                    }

                    // Update stock display text
                    const productInfo_el = card.querySelector('.product-info');
                    if (productInfo_el) {
                        const stockSpans = productInfo_el.querySelectorAll('.text-xs.text-gray-500 span');
                        // Find the Avail/Status span (second one in the flex container)
                        const availContainer = productInfo_el.querySelector('.flex.items-center.gap-4.text-xs');
                        if (availContainer) {
                            const spans = availContainer.querySelectorAll(':scope > span');
                            if (spans.length >= 2) {
                                const statusSpan = spans[1];
                                if (isComposite) {
                                    if (productInfo.is_available) {
                                        statusSpan.innerHTML = 'Status <span class="text-green-600 font-medium">Available</span>';
                                    } else {
                                        statusSpan.innerHTML = 'Status <span class="text-red-500 font-medium">Unavailable</span>';
                                    }
                                } else {
                                    const colorClass = newStock > 0 ? 'text-green-600' : 'text-red-500';
                                    statusSpan.innerHTML = `Avail <span class="${colorClass} font-medium">${newStock}pcs</span>`;
                                }
                            }
                        }
                    }

                    // Update cart items stock references
                    cart.forEach(item => {
                        if (item.productId === productId) {
                            item.stock = newStock;
                            item.productType = productInfo.product_type;
                        }
                    });

                    // Flash animation if stock changed
                    if (oldStock !== newStock) {
                        card.style.transition = 'box-shadow 0.3s ease';
                        card.style.boxShadow = '0 0 0 2px rgba(34, 197, 94, 0.5)';
                        setTimeout(() => { card.style.boxShadow = ''; }, 1500);
                    }
                });
            } catch (error) {
                console.error('Live data polling error:', error);
            }
        }, 10000);
    </script>

    {{-- Crew Check-In Modal --}}
    @if(isset($branchSession) && $branchSession && $branchSession->is_cashier)
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <div id="crewCheckInModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-black">Crew Check-In</h3>
                <button type="button" onclick="closeCrewCheckInModal()" class="text-gray-400 hover:text-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Status messages --}}
            <div id="crewCheckInError" class="hidden mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs"></div>
            <div id="crewCheckInSuccess" class="hidden mb-3 p-2 bg-green-900/40 border border-green-500/50 rounded-lg text-green-300 text-xs"></div>

            {{-- QR Scanner (Primary) --}}
            <div id="crewQrSection">
                <p class="text-gray-400 text-xs mb-3 text-center">Scan crew member's QR code to check in.</p>
                <div class="flex justify-center">
                    <div id="crewQrReader" class="rounded-xl overflow-hidden mb-3" style="width: 250px; height: 250px;"></div>
                </div>
                <p id="crewQrStatus" class="text-gray-500 text-xs text-center mb-3">Initializing camera...</p>
            </div>

            {{-- Email/Password Fallback (Hidden by default) --}}
            <div id="crewEmailSection" class="hidden">
                <p class="text-gray-400 text-xs mb-3">Enter crew member credentials to check them in.</p>
                <form id="crewCheckInForm" onsubmit="handleCrewEmailCheckIn(event)">
                    <div class="mb-3">
                        <label for="crewEmail" class="block text-xs text-gray-400 mb-1">Email</label>
                        <input type="email" id="crewEmail" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="crew@example.com">
                    </div>
                    <div class="mb-4">
                        <label for="crewPassword" class="block text-xs text-gray-400 mb-1">Password</label>
                        <input type="password" id="crewPassword" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="••••••••">
                    </div>
                    <button type="submit" id="crewCheckInBtn" class="w-full py-2 bg-green-700 hover:bg-green-600 text-black text-sm font-semibold rounded-lg transition-colors">
                        Check In
                    </button>
                </form>
            </div>

            {{-- Toggle between QR and Email --}}
            <div class="mt-3 text-center">
                <button type="button" id="crewToggleMethod" onclick="toggleCrewCheckInMethod()" class="text-gray-500 hover:text-gray-300 text-xs underline">
                    Use email &amp; password instead
                </button>
            </div>
        </div>
    </div>

    {{-- Crew Check-Out Confirmation Modal --}}
    <div id="crewCheckOutModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-black">Crew Check-Out</h3>
                <button type="button" onclick="closeCrewCheckOutModal()" class="text-gray-400 hover:text-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Status messages --}}
            <div id="checkOutError" class="hidden mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs"></div>
            <div id="checkOutSuccess" class="hidden mb-3 p-2 bg-green-900/40 border border-green-500/50 rounded-lg text-green-300 text-xs"></div>

            {{-- QR Scanner (Primary) --}}
            <div id="checkOutQrSection">
                <p class="text-gray-400 text-xs mb-3 text-center">Scan crew member's QR code to confirm check-out.</p>
                <div class="flex justify-center">
                    <div id="checkOutQrReader" class="rounded-xl overflow-hidden mb-3" style="width: 250px; height: 250px;"></div>
                </div>
                <p id="checkOutQrStatus" class="text-gray-500 text-xs text-center mb-3">Initializing camera...</p>
            </div>

            {{-- Email/Password Fallback (Hidden by default) --}}
            <div id="checkOutEmailSection" class="hidden">
                <p class="text-gray-400 text-xs mb-3">Enter crew member credentials to confirm check-out.</p>
                <form id="checkOutForm" onsubmit="handleCheckOutEmailSubmit(event)">
                    <div class="mb-3">
                        <label for="checkOutEmail" class="block text-xs text-gray-400 mb-1">Email</label>
                        <input type="email" id="checkOutEmail" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="crew@example.com">
                    </div>
                    <div class="mb-4">
                        <label for="checkOutPassword" class="block text-xs text-gray-400 mb-1">Password</label>
                        <input type="password" id="checkOutPassword" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="••••••••">
                    </div>
                    <button type="submit" id="checkOutBtn" class="w-full py-2 bg-red-700 hover:bg-red-600 text-black text-sm font-semibold rounded-lg transition-colors">
                        Confirm Check Out
                    </button>
                </form>
            </div>

            {{-- Toggle between QR and Email --}}
            <div class="mt-3 text-center">
                <button type="button" id="checkOutToggleMethod" onclick="toggleCheckOutMethod()" class="text-gray-500 hover:text-gray-300 text-xs underline">
                    Use email &amp; password instead
                </button>
            </div>
        </div>
    </div>

    <script>
        let crewQrScanner = null;
        let crewQrIsProcessing = false;
        let crewCheckInMode = 'qr'; // 'qr' or 'email'

        function openCrewCheckInModal() {
            const modal = document.getElementById('crewCheckInModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('crewCheckInError').classList.add('hidden');
            document.getElementById('crewCheckInSuccess').classList.add('hidden');
            // Reset to QR mode
            crewCheckInMode = 'qr';
            document.getElementById('crewQrSection').classList.remove('hidden');
            document.getElementById('crewEmailSection').classList.add('hidden');
            document.getElementById('crewToggleMethod').textContent = 'Use email & password instead';
            startCrewQrScanner();
        }

        function closeCrewCheckInModal() {
            const modal = document.getElementById('crewCheckInModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            stopCrewQrScanner();
        }

        function toggleCrewCheckInMethod() {
            const qrSection = document.getElementById('crewQrSection');
            const emailSection = document.getElementById('crewEmailSection');
            const toggleBtn = document.getElementById('crewToggleMethod');
            document.getElementById('crewCheckInError').classList.add('hidden');
            document.getElementById('crewCheckInSuccess').classList.add('hidden');

            if (crewCheckInMode === 'qr') {
                stopCrewQrScanner();
                qrSection.classList.add('hidden');
                emailSection.classList.remove('hidden');
                toggleBtn.textContent = 'Use QR scan instead';
                crewCheckInMode = 'email';
                document.getElementById('crewEmail').value = '';
                document.getElementById('crewPassword').value = '';
                document.getElementById('crewEmail').focus();
            } else {
                emailSection.classList.add('hidden');
                qrSection.classList.remove('hidden');
                toggleBtn.textContent = 'Use email & password instead';
                crewCheckInMode = 'qr';
                startCrewQrScanner();
            }
        }

        function startCrewQrScanner() {
            const statusEl = document.getElementById('crewQrStatus');
            statusEl.textContent = 'Initializing camera...';
            statusEl.className = 'text-gray-500 text-xs text-center mb-3';
            crewQrIsProcessing = false;

            if (crewQrScanner) {
                try { crewQrScanner.stop().catch(() => {}); } catch(e) {}
                crewQrScanner = null;
            }

            crewQrScanner = new Html5Qrcode('crewQrReader');

            crewQrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1, disableFlip: false },
                (decodedText) => onCrewQrScanSuccess(decodedText),
                () => {}
            ).then(() => {
                statusEl.textContent = 'Point camera at crew member\'s QR code';
            }).catch((err) => {
                statusEl.textContent = 'Camera not available. Use email login below.';
                statusEl.className = 'text-red-400 text-xs text-center mb-3';
                console.error('QR scanner error:', err);
            });
        }

        function stopCrewQrScanner() {
            if (crewQrScanner) {
                try {
                    crewQrScanner.stop().catch(() => {});
                } catch(e) {}
                crewQrScanner = null;
            }
            crewQrIsProcessing = false;
        }

        async function onCrewQrScanSuccess(qrToken) {
            if (crewQrIsProcessing) return;
            crewQrIsProcessing = true;

            const errorDiv = document.getElementById('crewCheckInError');
            const successDiv = document.getElementById('crewCheckInSuccess');
            const statusEl = document.getElementById('crewQrStatus');
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            statusEl.textContent = 'Processing...';

            try {
                if (crewQrScanner) crewQrScanner.pause();
            } catch(e) {}

            try {
                const response = await fetch('{{ route("crew-session.check-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_token: qrToken })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successDiv.textContent = data.message || 'Crew member checked in!';
                    successDiv.classList.remove('hidden');
                    statusEl.textContent = 'Check-in successful!';
                    statusEl.className = 'text-green-400 text-xs text-center mb-3';
                    refreshCrewList();
                    setTimeout(() => closeCrewCheckInModal(), 1500);
                } else {
                    errorDiv.textContent = data.message || 'Invalid QR code.';
                    errorDiv.classList.remove('hidden');
                    statusEl.textContent = 'Scan failed. Try again.';
                    setTimeout(() => {
                        crewQrIsProcessing = false;
                        try { if (crewQrScanner) crewQrScanner.resume(); } catch(e) {}
                        statusEl.textContent = 'Point camera at crew member\'s QR code';
                        statusEl.className = 'text-gray-500 text-xs text-center mb-3';
                    }, 2500);
                    return;
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.remove('hidden');
                setTimeout(() => {
                    crewQrIsProcessing = false;
                    try { if (crewQrScanner) crewQrScanner.resume(); } catch(e) {}
                }, 2500);
                return;
            }
        }

        async function handleCrewEmailCheckIn(e) {
            e.preventDefault();
            const btn = document.getElementById('crewCheckInBtn');
            const errorDiv = document.getElementById('crewCheckInError');
            const successDiv = document.getElementById('crewCheckInSuccess');
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Checking in...';

            try {
                const response = await fetch('{{ route("crew-session.check-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: document.getElementById('crewEmail').value,
                        password: document.getElementById('crewPassword').value
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successDiv.textContent = data.message || 'Crew member checked in!';
                    successDiv.classList.remove('hidden');
                    document.getElementById('crewEmail').value = '';
                    document.getElementById('crewPassword').value = '';
                    refreshCrewList();
                    setTimeout(() => closeCrewCheckInModal(), 1500);
                } else {
                    errorDiv.textContent = data.message || 'Check-in failed.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Check In';
            }
        }

        let crewCheckOutSessionId = null;
        let checkOutQrScanner = null;
        let checkOutQrIsProcessing = false;
        let checkOutMode = 'qr';

        function handleCrewCheckOut(sessionId) {
            crewCheckOutSessionId = sessionId;
            openCrewCheckOutModal();
        }

        function openCrewCheckOutModal() {
            const modal = document.getElementById('crewCheckOutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('checkOutError').classList.add('hidden');
            document.getElementById('checkOutSuccess').classList.add('hidden');
            checkOutMode = 'qr';
            document.getElementById('checkOutQrSection').classList.remove('hidden');
            document.getElementById('checkOutEmailSection').classList.add('hidden');
            document.getElementById('checkOutToggleMethod').textContent = 'Use email & password instead';
            startCheckOutQrScanner();
        }

        function closeCrewCheckOutModal() {
            const modal = document.getElementById('crewCheckOutModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            stopCheckOutQrScanner();
            crewCheckOutSessionId = null;
        }

        function toggleCheckOutMethod() {
            const qrSection = document.getElementById('checkOutQrSection');
            const emailSection = document.getElementById('checkOutEmailSection');
            const toggleBtn = document.getElementById('checkOutToggleMethod');
            document.getElementById('checkOutError').classList.add('hidden');
            document.getElementById('checkOutSuccess').classList.add('hidden');

            if (checkOutMode === 'qr') {
                stopCheckOutQrScanner();
                qrSection.classList.add('hidden');
                emailSection.classList.remove('hidden');
                toggleBtn.textContent = 'Use QR scan instead';
                checkOutMode = 'email';
                document.getElementById('checkOutEmail').value = '';
                document.getElementById('checkOutPassword').value = '';
                document.getElementById('checkOutEmail').focus();
            } else {
                emailSection.classList.add('hidden');
                qrSection.classList.remove('hidden');
                toggleBtn.textContent = 'Use email & password instead';
                checkOutMode = 'qr';
                startCheckOutQrScanner();
            }
        }

        function startCheckOutQrScanner() {
            const statusEl = document.getElementById('checkOutQrStatus');
            statusEl.textContent = 'Initializing camera...';
            statusEl.className = 'text-gray-500 text-xs text-center mb-3';
            checkOutQrIsProcessing = false;

            if (checkOutQrScanner) {
                try { checkOutQrScanner.stop().catch(() => {}); } catch(e) {}
                checkOutQrScanner = null;
            }

            checkOutQrScanner = new Html5Qrcode('checkOutQrReader');

            checkOutQrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1, disableFlip: false },
                (decodedText) => onCheckOutQrScanSuccess(decodedText),
                () => {}
            ).then(() => {
                statusEl.textContent = 'Scan crew member\'s QR code to confirm check-out';
            }).catch((err) => {
                statusEl.textContent = 'Camera not available. Use email option below.';
                statusEl.className = 'text-red-400 text-xs text-center mb-3';
            });
        }

        function stopCheckOutQrScanner() {
            if (checkOutQrScanner) {
                try { checkOutQrScanner.stop().catch(() => {}); } catch(e) {}
                checkOutQrScanner = null;
            }
            checkOutQrIsProcessing = false;
        }

        async function onCheckOutQrScanSuccess(qrToken) {
            if (checkOutQrIsProcessing) return;
            checkOutQrIsProcessing = true;

            const errorDiv = document.getElementById('checkOutError');
            const successDiv = document.getElementById('checkOutSuccess');
            const statusEl = document.getElementById('checkOutQrStatus');
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            statusEl.textContent = 'Processing...';

            try { if (checkOutQrScanner) checkOutQrScanner.pause(); } catch(e) {}

            try {
                const response = await fetch('{{ route("crew-session.check-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ session_id: crewCheckOutSessionId, qr_token: qrToken })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successDiv.textContent = data.message || 'Crew member checked out!';
                    successDiv.classList.remove('hidden');
                    statusEl.textContent = 'Check-out confirmed!';
                    statusEl.className = 'text-green-400 text-xs text-center mb-3';
                    refreshCrewList();
                    setTimeout(() => closeCrewCheckOutModal(), 1500);
                } else {
                    errorDiv.textContent = data.message || 'QR does not match.';
                    errorDiv.classList.remove('hidden');
                    setTimeout(() => {
                        checkOutQrIsProcessing = false;
                        try { if (checkOutQrScanner) checkOutQrScanner.resume(); } catch(e) {}
                        statusEl.textContent = 'Scan crew member\'s QR code to confirm check-out';
                        statusEl.className = 'text-gray-500 text-xs text-center mb-3';
                    }, 2500);
                    return;
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.remove('hidden');
                setTimeout(() => {
                    checkOutQrIsProcessing = false;
                    try { if (checkOutQrScanner) checkOutQrScanner.resume(); } catch(e) {}
                }, 2500);
                return;
            }
        }

        async function handleCheckOutEmailSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('checkOutBtn');
            const errorDiv = document.getElementById('checkOutError');
            const successDiv = document.getElementById('checkOutSuccess');
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Checking out...';

            try {
                const response = await fetch('{{ route("crew-session.check-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_id: crewCheckOutSessionId,
                        email: document.getElementById('checkOutEmail').value,
                        password: document.getElementById('checkOutPassword').value
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successDiv.textContent = data.message || 'Crew member checked out!';
                    successDiv.classList.remove('hidden');
                    refreshCrewList();
                    setTimeout(() => closeCrewCheckOutModal(), 1500);
                } else {
                    errorDiv.textContent = data.message || 'Check-out failed.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Confirm Check Out';
            }
        }

        async function refreshCrewList() {
            try {
                const response = await fetch('{{ route("crew-session.active") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                const container = document.getElementById('crewListContainer');

                if (data.crew && data.crew.length > 0) {
                    let html = '<div class="mb-3 p-2 bg-yellow-900/30 border border-yellow-500/30 rounded-lg">';
                    html += '<p class="text-yellow-300 text-xs font-semibold mb-1">Active Crew Members:</p>';
                    data.crew.forEach(member => {
                        html += '<div class="flex items-center justify-between py-1">';
                        html += '<p class="text-yellow-200 text-xs">• ' + member.name + '</p>';
                        html += '<button type="button" onclick="handleCrewCheckOut(' + member.session_id + ')" class="text-red-400 hover:text-red-300 text-xs underline">Check Out</button>';
                        html += '</div>';
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '';
                }

                // Update crew count in header
                const crewCountSpan = document.querySelector('.text-yellow-400');
                if (crewCountSpan && crewCountSpan.textContent.includes('crew online')) {
                    if (data.crew && data.crew.length > 0) {
                        crewCountSpan.textContent = '(' + data.crew.length + ' crew online)';
                    } else {
                        crewCountSpan.style.display = 'none';
                    }
                }
            } catch (err) {
                console.error('Failed to refresh crew list:', err);
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCrewCheckInModal();
                closeCrewCheckOutModal();
            }
        });

        // Close modal on backdrop click
        document.getElementById('crewCheckInModal').addEventListener('click', function(e) {
            if (e.target === this) closeCrewCheckInModal();
        });
        document.getElementById('crewCheckOutModal').addEventListener('click', function(e) {
            if (e.target === this) closeCrewCheckOutModal();
        });
    </script>
    @endif

</body>
</html>
