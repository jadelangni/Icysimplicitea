<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Dark mode initialization to prevent flash -->
        <script>
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        </script>
        
        <style>
            * { box-sizing: border-box; }
            .app-wrapper { display: flex; min-height: 100vh; width: 100%; }
            .app-sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: white; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; }
            .app-main { flex: 1; margin-left: 240px; background: #f8f9fa; min-height: 100vh; transition: background-color 0.3s; }
            .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; text-decoration: none; }
            .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
            .nav-item.active { background: #166534; color: white; }
            
            /* Dark Mode for main content */
            html.dark .app-main { background: #1f2937 !important; }
            
            /* Hide scrollbar but allow scrolling */
            .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            .hide-scrollbar::-webkit-scrollbar { display: none; }
            
            /* Mobile responsive */
            @media (max-width: 1023px) {
                .app-sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
                .app-sidebar.open { transform: translateX(0); }
                .app-main { margin-left: 0; }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-200" 
          x-data="{ 
              sidebarOpen: false,
              darkMode: localStorage.getItem('darkMode') === 'true',
              toggleDarkMode() {
                  this.darkMode = !this.darkMode;
                  localStorage.setItem('darkMode', this.darkMode);
                  if (this.darkMode) {
                      document.documentElement.classList.add('dark');
                  } else {
                      document.documentElement.classList.remove('dark');
                  }
              }
          }" 
          x-init="$watch('darkMode', val => { 
              localStorage.setItem('darkMode', val); 
              if (val) { 
                  document.documentElement.classList.add('dark'); 
              } else { 
                  document.documentElement.classList.remove('dark'); 
              } 
          })"
          @keydown.escape="sidebarOpen = false">
        <div class="app-wrapper">
            @include('layouts.navigation', ['pageHeader' => $header ?? null, 'navSearch' => $navSearch ?? null])

            <!-- Main Content Area -->
            <div class="app-main">
                <!-- Mobile Header -->
                <div class="lg:hidden flex items-center justify-between p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Icy's Simplicitea</span>
                    <div class="w-10"></div>
                </div>

                <!-- Page Content -->
                <main class="min-h-screen">
                    @if (isset($header))
                        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Global Live Polling for Navigation Badges -->
        @auth
        @if(auth()->user()->isAdmin())
        <script>
            // Poll notification count every 15 seconds for admin users
            setInterval(async () => {
                try {
                    const response = await fetch('/notifications/unread-count', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        const count = data.count || 0;
                        if (count > 0) {
                            badge.textContent = count > 9 ? '9+' : count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                } catch (e) {}
            }, 15000);
        </script>
        @endif
        @endauth
    </body>
</html>
