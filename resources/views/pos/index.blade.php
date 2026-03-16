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
        body { margin: 0; padding: 0; overflow: hidden; }
        .pos-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: white; }
        .main-content { flex: 1; display: flex; background: #f8f9fa; overflow: hidden; transition: background-color 0.3s; }
        .products-panel { flex: 1; display: flex; flex-direction: column; padding: 24px; overflow: hidden; }
        .cart-panel { width: 300px; background: white; display: flex; flex-direction: column; border-left: 1px solid #e5e7eb; transition: background-color 0.3s, border-color 0.3s; }
        .products-grid { flex: 1; overflow-y: auto; padding-right: 8px; -ms-overflow-style: none; scrollbar-width: none; }
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
        .category-tab.active { background: #1a1a2e !important; color: white !important; border-color: #1a1a2e !important; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: #166534; color: white; }
        .sidebar-section-title { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 16px 8px; display: flex; align-items: center; justify-content: space-between; }
        @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.2s ease forwards; }
        
        /* Dark Mode Styles - Comprehensive */
        html.dark body { background: #111827 !important; color: #f3f4f6 !important; }
        html.dark .main-content { background: #1f2937 !important; }
        html.dark .products-panel { background: #1f2937 !important; }
        html.dark .cart-panel { background: #111827 !important; border-color: #374151 !important; }
        
        /* Product Cards */
        html.dark .product-card { background: #374151 !important; border-color: #4b5563 !important; }
        html.dark .product-card:hover { border-color: #22c55e !important; }
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
        html.dark .category-tab.active { background: #166534 !important; border-color: #166534 !important; color: white !important; }
        
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
        
        /* Size/Add-on selection boxes */
        html.dark .product-card .flex.items-center.justify-between { color: #d1d5db !important; }
        html.dark .size-option, html.dark .addon-option { background: #4b5563 !important; color: #d1d5db !important; border-color: #6b7280 !important; }
        html.dark .size-option:hover, html.dark .addon-option:hover { background: #6b7280 !important; }
        html.dark .size-option.selected, html.dark .addon-option.selected { background: #166534 !important; color: white !important; }
    </style>
    <script>
        // Initialize dark mode before page renders to prevent flash
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased h-full bg-gray-100">
    <div class="pos-wrapper">
        <!-- LEFT SIDEBAR -->
        <div class="sidebar">
            <!-- User Profile -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center overflow-hidden">
                        <span class="text-white font-bold text-lg">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                        @php
                            $branchSession = \App\Models\BranchSession::getActiveSessionForUser(Auth::id());
                            $sessionRole = $branchSession ? ($branchSession->is_cashier ? 'Cashier' : 'Crew') : (Auth::user()->role === 'admin' ? 'Admin' : 'Cashier');
                            $activeCrew = Auth::user()->branch_id ? \App\Models\BranchSession::getActiveCrew(Auth::user()->branch_id) : collect();
                        @endphp
                        <p class="text-xs text-gray-400">
                            {{ $sessionRole }}
                            @if($branchSession && $branchSession->is_cashier && $activeCrew->count() > 0)
                                <span class="text-yellow-400 ml-1">({{ $activeCrew->count() }} crew online)</span>
                            @endif
                        </p>
                    </div>
                    <!-- Dark Mode Toggle -->
                    <button type="button" id="darkModeToggle" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                        <svg id="darkModeIconMoon" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg id="darkModeIconSun" class="w-5 h-5 text-yellow-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                </div>

                {{-- Crew Check-In Button (visible to cashier) --}}
                @if(isset($branchSession) && $branchSession && $branchSession->is_cashier)
                <div class="mt-3">
                    <button type="button" onclick="openCrewCheckInModal()" class="w-full px-3 py-2 bg-green-700 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Crew Check-In
                    </button>
                </div>
                @endif
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 overflow-y-auto hide-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4"/>
                    </svg>
                    <span class="flex-1">Dashboard</span>
                </a>

                <!-- Point of Sale (Active) -->
                <div class="nav-item active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"/>
                    </svg>
                    <span class="flex-1">Point of Sale</span>
                    <span id="nav-cart-count" class="bg-green-600 text-white text-xs px-2 py-0.5 rounded-full">0</span>
                </div>

                @if(Auth::user()->role === 'admin')
                <!-- Products -->
                <a href="{{ route('products.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="flex-1">Products</span>
                </a>

                <!-- Recipes -->
                <a href="{{ route('recipes.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="flex-1">Recipes</span>
                </a>

                <!-- Inventory -->
                <a href="{{ route('product-inventory.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    <span class="flex-1">Inventory</span>
                </a>

                <!-- Reports -->
                <a href="{{ route('reports.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="flex-1">Reports</span>
                </a>

                <!-- Employees -->
                <a href="{{ route('employees.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="flex-1">Employees</span>
                </a>

                <!-- Cashier Sales -->
                <a href="{{ route('activity-logs.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="flex-1">Cashier Sales</span>
                </a>

                <!-- Staff Attendance -->
                <a href="{{ route('attendance.index') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="flex-1">Staff Attendance</span>
                </a>
                @endif

                <!-- Divider -->
                <div class="my-3 border-t border-gray-700"></div>

                @if(Auth::user()->role === 'cashier')
                <!-- My Attendance (for cashiers) -->
                <a href="{{ route('attendance.my-attendance') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">My Attendance</span>
                </a>

                <!-- My QR Code (for cashiers) -->
                <a href="{{ route('qr.my-qrcode') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="flex-1">My QR Code</span>
                </a>
                @endif

                <!-- Settings -->
                <a href="{{ route('profile.edit') }}" class="nav-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="flex-1">Settings</span>
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-700">
                @if(session('error'))
                <div class="mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs">
                    {{ session('error') }}
                </div>
                @endif

                {{-- Show active crew members if current user is cashier --}}
                <div id="crewListContainer">
                @if(isset($branchSession) && $branchSession && $branchSession->is_cashier && $activeCrew->count() > 0)
                <div class="mb-3 p-2 bg-yellow-900/30 border border-yellow-500/30 rounded-lg">
                    <p class="text-yellow-300 text-xs font-semibold mb-1">Active Crew Members:</p>
                    @foreach($activeCrew as $crew)
                    <div class="flex items-center justify-between py-1">
                        <p class="text-yellow-200 text-xs">• {{ $crew->user->name }}</p>
                        <button type="button" onclick="handleCrewCheckOut({{ $crew->id }})" class="text-red-400 hover:text-red-300 text-xs underline">Check Out</button>
                    </div>
                    @endforeach
                </div>
                @endif
                </div>

                <a href="{{ route('logout.prepare') }}" class="nav-item w-full text-red-400 hover:text-red-300 hover:bg-red-900/20" onclick="return confirm('Are you sure you want to logout?')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="flex-1 text-left">Logout</span>
                </a>
                <div class="mt-4 text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Powered by</p>
                    <p class="text-sm font-semibold text-white flex items-center justify-center gap-2 mt-1">
                        <span class="w-5 h-5 bg-green-600 rounded flex items-center justify-center text-xs font-bold">S</span>
                        Simplicitea
                    </p>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- PRODUCTS PANEL -->
            <div class="products-panel">
                <!-- Header -->
                <div class="mb-6">
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
                        <span id="ordered-count" class="bg-gray-800 text-white text-xs px-2 py-0.5 rounded-full min-w-[24px] text-center">0</span>
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-2 mb-5 overflow-x-auto pb-1">
                    <button class="category-tab active px-5 py-2 bg-gray-900 text-white rounded-xl text-sm font-medium whitespace-nowrap transition-all" data-category="all">
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
                                    <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-lg">Missing Ingredients</span>
                                    @else
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-lg">Out of Stock</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Info -->
                            <div class="product-info p-3">
                                <h4 class="font-semibold text-xs text-gray-900 mb-0.5">{{ $product->name }}</h4>
                                <div class="flex items-center gap-3 text-[10px] text-gray-500 mb-2">
                                    <span>Sold <span class="text-green-600 font-medium">{{ $soldCount }}pcs</span></span>
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
                        <span id="cart-count-badge" class="bg-green-600 text-white text-xs px-1.5 py-0.5 rounded-full">0</span>
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
                    <!-- Detail Payment -->
                    <div class="mb-3">
                        <h4 class="font-semibold text-gray-900 text-sm mb-2">Detail Payment</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Subtotal</span>
                                <span id="subtotal" class="font-medium text-gray-900">₱0</span>
                            </div>
                        </div>
                    </div>

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
                        <button type="button" class="payment-method-btn flex-1 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-50 transition-colors" data-method="card">
                            Credit
                        </button>
                        <button type="button" class="payment-method-btn flex-1 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-50 transition-colors" data-method="gcash">
                            Qris
                        </button>
                    </div>

                    <!-- Pay Button -->
                    <button type="button" id="pay-btn" disabled class="w-full py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-bold text-sm transition-colors">
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
            <div class="bg-white rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden animate-fade-in">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h3 id="modal-product-name" class="text-xl font-bold">Select Size</h3>
                    <p class="text-green-100 text-sm mt-1">Choose your preferred size</p>
                </div>
                
                <form id="product-options-form" class="p-6">
                    <div id="options-container" class="space-y-4"></div>
                    
                    <!-- Quantity Selector -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center justify-center gap-4">
                            <button type="button" id="modal-qty-minus" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors">−</button>
                            <input type="number" id="modal-qty-input" value="1" min="1" class="w-16 text-center text-xl font-bold border border-gray-200 rounded-lg py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" readonly>
                            <button type="button" id="modal-qty-plus" class="w-10 h-10 rounded-full bg-green-100 hover:bg-green-200 text-green-700 font-bold text-xl flex items-center justify-center transition-colors">+</button>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" id="modal-cancel-btn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="modal-add-btn" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
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
                <div class="bg-green-600 text-white px-6 py-4">
                    <h3 class="text-xl font-bold">Enter Amount Paid</h3>
                    <p class="text-green-100 text-sm mt-1">Total: <span id="modal-total-display">₱0</span></p>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
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
                        <button type="button" id="confirm-payment-btn" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
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
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Confirm Order
                    </h3>
                    <p class="text-green-100 text-sm mt-1">Please review before processing</p>
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
                    <button type="button" id="confirm-order-btn" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
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
        // Dark mode handling
        function toggleDarkMode() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            const newMode = !isDark;
            localStorage.setItem('darkMode', newMode);
            updateDarkModeUI(newMode);
            
            // Apply dark mode to document
            if (newMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        function updateDarkModeUI(isDark) {
            const moonIcon = document.getElementById('darkModeIconMoon');
            const sunIcon = document.getElementById('darkModeIconSun');
            const text = document.getElementById('darkModeText');
            
            if (moonIcon && sunIcon) {
                if (isDark) {
                    moonIcon.classList.add('hidden');
                    sunIcon.classList.remove('hidden');
                    if (text) text.textContent = 'Light Mode';
                } else {
                    moonIcon.classList.remove('hidden');
                    sunIcon.classList.add('hidden');
                    if (text) text.textContent = 'Dark Mode';
                }
            }
        }

        // Initialize dark mode state on page load
        (function() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
            // Update UI after DOM is ready
            setTimeout(() => updateDarkModeUI(isDark), 0);
        })();

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
                    t.classList.remove('active', 'bg-gray-900', 'text-white');
                    t.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
                });
                this.classList.add('active', 'bg-gray-900', 'text-white');
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

                const label = document.createElement('label');
                label.className = 'block text-sm font-semibold text-gray-700 mb-2';
                label.textContent = opt.name || 'Select Size';

                const buttonsDiv = document.createElement('div');
                buttonsDiv.className = 'grid grid-cols-2 gap-3';
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
                let totalHint = container.querySelector('.total-price-hint');
                if (!totalHint) {
                    totalHint = document.createElement('div');
                    totalHint.className = 'total-price-hint text-center text-xl font-bold text-green-600 mt-4 p-3 bg-green-50 rounded-xl';
                    container.appendChild(totalHint);
                }
                if (qty > 1) {
                    totalHint.textContent = `Total: ₱${(computed * qty).toFixed(2)}`;
                    totalHint.style.display = '';
                } else {
                    totalHint.style.display = 'none';
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
            const totalHint = container.querySelector('.total-price-hint');
            if (totalHint) {
                if (qty > 1) {
                    totalHint.textContent = `Total: ₱${(computed * qty).toFixed(2)}`;
                    totalHint.style.display = '';
                } else {
                    totalHint.style.display = 'none';
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
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(0);
            document.getElementById('total').textContent = '₱' + total.toFixed(1);
            document.getElementById('cart-count-badge').textContent = cart.length;
            document.getElementById('ordered-count').textContent = cart.length;
            document.getElementById('nav-cart-count').textContent = cart.length;
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
            document.getElementById('amount-paid-input').value = '';
            document.getElementById('change-display').textContent = '₱0.00';
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
            
            const methodLabels = { cash: 'Cash', card: 'Credit', gcash: 'Qris/GCash' };
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Open receipt in new tab with auto-print
                    window.open(data.direct_print_url, '_blank');
                    cart = [];
                    localStorage.removeItem('pos_cart');
                    updateCart();
                    showSuccessToast('Sale completed! Receipt opened in new tab.');
                } else {
                    alert(data.error || data.message || 'An error occurred');
                }
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                confirmBtn.disabled = false;
                payBtn.disabled = cart.length === 0;
                payBtn.innerHTML = 'Pay';
            });
        });

        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-[100] bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-fade-in';
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
                            overlay.innerHTML = '<span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-lg">Missing Ingredients</span>';
                        } else {
                            overlay.innerHTML = '<span class="bg-red-500 text-white text-xs px-2 py-1 rounded-lg">Out of Stock</span>';
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
                <h3 class="text-lg font-bold text-white">Crew Check-In</h3>
                <button type="button" onclick="closeCrewCheckInModal()" class="text-gray-400 hover:text-white">
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
                        <label class="block text-xs text-gray-400 mb-1">Email</label>
                        <input type="email" id="crewEmail" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="crew@example.com">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs text-gray-400 mb-1">Password</label>
                        <input type="password" id="crewPassword" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="••••••••">
                    </div>
                    <button type="submit" id="crewCheckInBtn" class="w-full py-2 bg-green-700 hover:bg-green-600 text-white text-sm font-semibold rounded-lg transition-colors">
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
                <h3 class="text-lg font-bold text-white">Crew Check-Out</h3>
                <button type="button" onclick="closeCrewCheckOutModal()" class="text-gray-400 hover:text-white">
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
                        <label class="block text-xs text-gray-400 mb-1">Email</label>
                        <input type="email" id="checkOutEmail" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="crew@example.com">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs text-gray-400 mb-1">Password</label>
                        <input type="password" id="checkOutPassword" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="••••••••">
                    </div>
                    <button type="submit" id="checkOutBtn" class="w-full py-2 bg-red-700 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
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
