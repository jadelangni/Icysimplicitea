<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#005B5C">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Simplicitea POS">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            * { box-sizing: border-box; }
            .app-wrapper { display: flex; min-height: 100vh; width: 100%; }
            .app-sidebar { width: 240px; background: linear-gradient(180deg, #005b5c 0%, #014b4c 55%, #003536 100%); display: flex; flex-direction: column; color: black; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; }
            .app-main {
                flex: 1;
                margin-left: 240px;
                width: calc(100% - 240px);
                max-width: 100vw;
                background:
                    linear-gradient(120deg, rgba(0, 91, 92, 0.16) 0%, rgba(0, 91, 92, 0.08) 36%, rgba(0, 91, 92, 0.03) 72%),
                    linear-gradient(180deg, #95b0a3 0%, #849f92 52%, #769186 100%);
                min-height: 100vh;
                transition: background-color 0.3s;
            }
            .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; text-decoration: none; }
            .nav-item:hover { background: rgba(152, 255, 152, 0.15); color: black; }
            .nav-item.active { background: #00b140; color: black; }

            .app-mobile-header,
            .app-page-header {
                background: linear-gradient(180deg, #167879 0%, #0f6a6b 55%, #0a5758 100%);
                border-color: rgba(178, 232, 216, 0.35);
                color: #000000;
            }

            .app-mobile-header .text-gray-900,
            .app-mobile-header .text-gray-500,
            .app-page-header .text-gray-900,
            .app-page-header .text-gray-800,
            .app-page-header .text-gray-500,
            .app-page-header .text-gray-600,
            .app-page-header .text-gray-700 {
                color: #000000 !important;
            }

            .app-sidebar .sidebar-brand-badge {
                width: 1.25rem;
                height: 1.25rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.25rem;
                background: #00b140;
                color: #000000;
                font-size: 0.75rem;
                font-weight: 700;
                line-height: 1;
            }
            
            /* Hide scrollbar but allow scrolling */
            .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            .hide-scrollbar::-webkit-scrollbar { display: none; }
            
            /* Mobile responsive */
            @media (max-width: 1023px) {
                .app-sidebar { width: min(86vw, 280px); transform: translateX(-100%); transition: transform 0.3s ease; box-shadow: 0 24px 48px rgba(15, 23, 42, 0.32); }
                .app-sidebar.open { transform: translateX(0); }
                .app-main { margin-left: 0; width: 100%; }
            }
        </style>
    </head>
    <body class="simplicitea-theme font-sans antialiased bg-gray-50 transition-colors duration-200 overflow-x-hidden" 
          x-data="{ 
              sidebarOpen: false,
              toggleSidebar() {
                  this.sidebarOpen = !this.sidebarOpen;
              },
              closeSidebar() {
                  this.sidebarOpen = false;
              }
          }" 
          x-init="$watch('sidebarOpen', val => {
              if (window.innerWidth < 1024) {
                  document.body.classList.toggle('overflow-hidden', val);
              }
          })"
          @keydown.escape="closeSidebar()">
        <div class="app-wrapper">
            @include('layouts.navigation', ['pageHeader' => $header ?? null, 'navSearch' => $navSearch ?? null])

            <!-- Main Content Area -->
            <div class="app-main">
                <!-- Mobile Header -->
                <div class="app-mobile-header lg:hidden flex items-center justify-between p-4 bg-white border-b border-gray-200">
                    <button @click.stop="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Open navigation menu" :aria-expanded="sidebarOpen.toString()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="text-lg font-bold text-gray-900">Icy's Simplicitea</span>
                    <div class="w-10"></div>
                </div>

                <!-- Page Content -->
                <main class="min-h-screen">
                    @if (isset($header))
                        <header class="app-page-header bg-white border-b border-gray-200">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
