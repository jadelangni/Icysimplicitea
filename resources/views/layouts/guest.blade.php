<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Simplicitea') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .bubble {
                position: absolute;
                border-radius: 50%;
                animation: float 8s infinite ease-in-out;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(5deg); }
            }
            .milk-tea-gradient {
                background: linear-gradient(135deg, #8B5A2B 0%, #A0522D 25%, #D2691E 50%, #CD853F 75%, #DEB887 100%);
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
            }
            .dark .glass-card {
                background: rgba(31, 41, 55, 0.95);
            }
        </style>
        
        <!-- Dark mode initialization -->
        <script>
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex relative overflow-hidden">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex lg:w-1/2 milk-tea-gradient relative items-center justify-center p-12">
                <!-- Decorative bubbles -->
                <div class="bubble w-20 h-20 bg-white/10 top-20 left-20" style="animation-delay: 0s;"></div>
                <div class="bubble w-32 h-32 bg-white/10 top-40 right-32" style="animation-delay: 1s;"></div>
                <div class="bubble w-16 h-16 bg-white/10 bottom-32 left-40" style="animation-delay: 2s;"></div>
                <div class="bubble w-24 h-24 bg-white/10 bottom-20 right-20" style="animation-delay: 3s;"></div>
                <div class="bubble w-12 h-12 bg-white/10 top-1/2 left-16" style="animation-delay: 4s;"></div>
                
                <!-- Brand Content -->
                <div class="relative z-10 text-center text-white">
                    <!-- Logo -->
                    <div class="mb-8">
                        <div class="w-40 h-40 mx-auto bg-white/20 backdrop-blur rounded-full flex items-center justify-center shadow-2xl">
                            <span class="text-8xl">🧋</span>
                        </div>
                    </div>
                    
                    <!-- Brand Name -->
                    <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">
                        Icy's Simplicitea
                    </h1>
                    <p class="text-xl text-white/90 mb-8 max-w-md mx-auto">
                        Simplicity in every sip. Your favorite milk tea, made with love.
                    </p>
                    
                    <!-- Features -->
                    <div class="flex flex-wrap justify-center gap-4 mt-8">
                        <div class="bg-white/20 backdrop-blur px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <span>📍</span> Oslob
                        </div>
                        <div class="bg-white/20 backdrop-blur px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <span>📍</span> Santander
                        </div>
                        <div class="bg-white/20 backdrop-blur px-4 py-2 rounded-full text-sm flex items-center gap-2">
                            <span>📍</span> Looc
                        </div>
                    </div>
                </div>
                
                <!-- Bottom decoration -->
                <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black/20 to-transparent"></div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-12 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
                <!-- Mobile Logo (shown on smaller screens) -->
                <div class="lg:hidden mb-8 text-center">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-amber-600 to-amber-800 rounded-full flex items-center justify-center shadow-xl mb-4">
                        <span class="text-5xl">🧋</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Icy's Simplicitea</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Point of Sale System</p>
                </div>
                
                <!-- Login Card -->
                <div class="w-full max-w-md">
                    <div class="glass-card rounded-2xl shadow-xl p-8 border border-gray-200/50 dark:border-gray-700/50">
                        {{ $slot }}
                    </div>
                    
                    <!-- Footer -->
                    <p class="text-center text-gray-400 dark:text-gray-500 text-sm mt-8">
                        © {{ date('Y') }} Icy's Simplicitea. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
