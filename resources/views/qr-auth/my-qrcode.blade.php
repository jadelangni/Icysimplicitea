<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My QR Code - {{ config('app.name', 'Simplicitea') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow: hidden; }
        .pos-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: white; flex-shrink: 0; }
        .main-content { flex: 1; display: flex; flex-direction: column; background: #f8f9fa; overflow: hidden; transition: background-color 0.3s; }
        .content-area { flex: 1; overflow-y: auto; padding: 24px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; text-decoration: none; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: #166534; color: white; }
        @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.2s ease forwards; }
        @media print {
            .sidebar { display: none !important; }
            .main-content { margin: 0; width: 100%; }
            .print-only { display: block !important; }
            .no-print { display: none !important; }
        }
        
        /* Dark Mode Styles - Comprehensive */
        html.dark body { background: #111827 !important; color: #f3f4f6 !important; }
        html.dark .main-content { background: #1f2937 !important; }
        html.dark .content-area { background: #1f2937 !important; }
        
        /* Backgrounds */
        html.dark .bg-white { background: #374151 !important; }
        html.dark .bg-gray-50 { background: #1f2937 !important; }
        html.dark .bg-gray-100 { background: #374151 !important; }
        html.dark .bg-gray-200 { background: #4b5563 !important; }
        html.dark .bg-green-50 { background: #14532d !important; }
        html.dark .bg-blue-50 { background: #1e3a5f !important; }
        
        /* Text Colors */
        html.dark .text-black { color: #f3f4f6 !important; }
        html.dark .text-gray-900 { color: #f3f4f6 !important; }
        html.dark .text-gray-800 { color: #f3f4f6 !important; }
        html.dark .text-gray-700 { color: #d1d5db !important; }
        html.dark .text-gray-600 { color: #d1d5db !important; }
        html.dark .text-gray-500 { color: #9ca3af !important; }
        html.dark .text-gray-400 { color: #9ca3af !important; }
        html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6 { color: #f3f4f6 !important; }
        html.dark p { color: #d1d5db !important; }
        html.dark label { color: #d1d5db !important; }
        html.dark span { color: inherit; }
        
        /* Borders */
        html.dark .border-gray-100 { border-color: #374151 !important; }
        html.dark .border-gray-200 { border-color: #4b5563 !important; }
        html.dark .border-gray-300 { border-color: #4b5563 !important; }
        html.dark .border-b { border-color: #374151 !important; }
        
        /* Cards */
        html.dark .rounded-2xl { background: #374151 !important; border-color: #4b5563 !important; }
        html.dark .rounded-xl { background: #374151 !important; }
        html.dark .shadow-lg { border-color: #4b5563 !important; }
        
        /* QR Code Card - keep white background for visibility */
        html.dark .qr-code-container { background: #ffffff !important; }
    </style>
    <script>
        // Initialize dark mode before page renders
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased h-full bg-gray-100">
    <div class="pos-wrapper">
        @include('partials.cashier-sidebar')

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 no-print">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My QR Code</h1>
                        <p class="text-gray-500 text-sm">Your personal attendance QR code</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="window.print()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print QR Code
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="max-w-2xl mx-auto">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center no-print">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center no-print">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- QR Card -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden animate-fade-in">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-8 text-center text-white">
                            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-3xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <h3 class="text-2xl font-bold">{{ $user->name }}</h3>
                            <p class="text-green-100 mt-1">{{ ucfirst($user->role) }}</p>
                            @if($user->branch)
                                <p class="text-green-200 text-sm mt-1">{{ $user->branch->name }}</p>
                            @endif
                        </div>

                        <!-- QR Code -->
                        <div class="p-8 text-center">
                            <div class="bg-white p-4 rounded-xl inline-block shadow-lg border-4 border-green-100">
                                <img src="{{ route('qr.image') }}" alt="Your QR Code" class="w-64 h-64 mx-auto">
                            </div>

                            <p class="text-gray-500 text-sm mt-6">
                                Use this QR code to clock in and out at the scanner station.
                            </p>

                            @if($user->qr_token_generated_at)
                                <p class="text-gray-400 text-xs mt-2">
                                    Generated: {{ $user->qr_token_generated_at->format('M d, Y h:i A') }}
                                </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 no-print">
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('qr.regenerate') }}" 
                                   onclick="return confirm('Are you sure you want to regenerate your QR code? Your old QR code will no longer work.')"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Regenerate QR Code
                                </a>
                                @endif

                                <a href="{{ route('attendance.my-attendance') }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    View My Attendance
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 bg-blue-50 rounded-xl p-6 no-print">
                        <h4 class="font-semibold text-blue-900 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            How to use your QR Code
                        </h4>
                        <ul class="mt-3 space-y-2 text-blue-800 text-sm">
                            <li class="flex items-start">
                                <span class="font-bold mr-2">1.</span>
                                Go to the QR scanner station or visit <a href="{{ route('qr.scanner') }}" class="underline hover:text-blue-900">the scanner page</a>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">2.</span>
                                Select "Clock In" when starting your shift or "Clock Out" when ending
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">3.</span>
                                Hold your QR code in front of the camera
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">4.</span>
                                Wait for the confirmation message
                            </li>
                        </ul>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-4 bg-yellow-50 rounded-xl p-4 no-print">
                        <p class="text-yellow-800 text-sm flex items-start">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>
                                <strong>Security Notice:</strong> Keep your QR code private. Do not share it with others. 
                                @if(auth()->user()->isAdmin())
                                    If you believe your QR code has been compromised, regenerate it immediately.
                                @else
                                    If you believe your QR code has been compromised, contact your administrator to regenerate it.
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
