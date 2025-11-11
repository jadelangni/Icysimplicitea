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
    </head>
    <body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <div class="flex" x-data="{ collapsed: false }">
                <!-- Main Content Area -->
                <div class="flex-1 pt-16 transition-all duration-500 ease-out"
                     :class="{
                        'lg:ml-64': !collapsed,
                        'lg:ml-16': collapsed
                     }"
                     @resize.window="if (window.innerWidth < 1024) { collapsed = false }"">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow-sm border-b border-gray-200">
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
