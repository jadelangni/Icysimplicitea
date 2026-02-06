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
        <div class="min-h-screen">
            @include('layouts.navigation')

            <div class="flex">
                <!-- Main Content Area -->
                <div class="flex-1 pt-16 ml-0 lg:ml-64">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="min-h-screen">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
