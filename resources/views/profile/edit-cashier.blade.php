<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - {{ config('app.name', 'Simplicitea') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow: hidden; }
        .pos-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: black; flex-shrink: 0; }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background:
                linear-gradient(120deg, rgba(0, 91, 92, 0.24) 0%, rgba(0, 91, 92, 0.14) 36%, rgba(0, 91, 92, 0.07) 72%),
                linear-gradient(180deg, #97b2a5 0%, #87a294 54%, #789284 100%);
            overflow: hidden;
            transition: background-color 0.3s;
        }
        .content-area { flex: 1; overflow-y: auto; padding: 24px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; text-decoration: none; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: black; }
        .nav-item.active { background: #00B140; color: black; }

        .main-content .bg-white {
            background: rgba(226, 243, 235, 0.94) !important;
            border-color: rgba(0, 91, 92, 0.22) !important;
            box-shadow: 0 10px 24px rgba(0, 91, 92, 0.12) !important;
        }

        .main-content .bg-gray-50,
        .main-content .bg-gray-100 {
            background: rgba(200, 224, 213, 0.75) !important;
        }

        .main-content .text-green-600,
        .main-content .text-green-700,
        .main-content .text-gray-900 {
            color: #005b5c !important;
        }
        @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.2s ease forwards; }
        
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
        html.dark .bg-red-50 { background: #7f1d1d !important; }
        
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
        html.dark .border-t { border-color: #374151 !important; }
        
        /* Forms */
        html.dark input, html.dark select, html.dark textarea { 
            background: #374151 !important; 
            border-color: #4b5563 !important; 
            color: #f3f4f6 !important; 
        }
        html.dark input::placeholder { color: #9ca3af !important; }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus { 
            border-color: #22c55e !important; 
            ring-color: #22c55e !important;
        }
        
        /* Cards */
        html.dark .rounded-2xl { background: #374151 !important; border-color: #4b5563 !important; }
        html.dark .rounded-xl { background: #374151 !important; }
        html.dark .shadow-sm { border-color: #4b5563 !important; }
        
        /* Validation/Error Messages */
        html.dark .text-red-600 { color: #fca5a5 !important; }
        html.dark .text-green-600 { color: #86efac !important; }
        
        /* Alert/Status Messages */
        html.dark .bg-green-100 { background: #166534 !important; }
        html.dark .text-green-800 { color: #86efac !important; }
        html.dark .bg-red-100 { background: #7f1d1d !important; }
        html.dark .text-red-800 { color: #fca5a5 !important; }
    </style>
</head>
<body class="font-sans antialiased h-full bg-gray-100" x-data="{ show: true }">
    <div class="pos-wrapper">
        @include('partials.cashier-sidebar')

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 pl-16 pr-6 py-4 lg:px-6" style="background: linear-gradient(180deg, #167879 0%, #0f6a6b 55%, #0a5758 100%); border-color: rgba(178, 232, 216, 0.35);">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-black">Settings</h1>
                        <p class="text-[#d3f3e8] text-sm">Manage your account settings and preferences</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="max-w-2xl mx-auto space-y-6">
                    <!-- Profile Information Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in">
                        <div class="p-6 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                            <p class="mt-1 text-sm text-gray-500">Update your account's profile information.</p>
                        </div>
                        <div class="p-6">
                            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                                @csrf
                            </form>

                            <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
                                @csrf
                                @method('patch')

                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                    <input id="name" name="name" type="text" 
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                           value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Username (Login Email)</label>
                                    <input id="email" name="email" type="email" 
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                           value="{{ old('email', $user->email) }}" required autocomplete="username">
                                    <p class="mt-1 text-xs text-gray-500">This email is used to log in to the system.</p>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-black rounded-lg font-medium transition-colors">
                                        Save Changes
                                    </button>
                                    @if (session('status') === 'profile-updated')
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                           class="text-sm text-green-600 font-medium">
                                            Saved successfully!
                                        </p>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Update Password Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in" style="animation-delay: 0.1s">
                        <div class="p-6 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">Update Password</h2>
                            <p class="mt-1 text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
                        </div>
                        <div class="p-6">
                            <form method="post" action="{{ route('password.update') }}" class="space-y-5">
                                @csrf
                                @method('put')

                                <div>
                                    <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input id="update_password_current_password" name="current_password" type="password" 
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                           autocomplete="current-password">
                                    @error('current_password', 'updatePassword')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input id="update_password_password" name="password" type="password" 
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                           autocomplete="new-password">
                                    @error('password', 'updatePassword')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                           autocomplete="new-password">
                                    @error('password_confirmation', 'updatePassword')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-black rounded-lg font-medium transition-colors">
                                        Update Password
                                    </button>
                                    @if (session('status') === 'password-updated')
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                           class="text-sm text-green-600 font-medium">
                                            Password updated!
                                        </p>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Links Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in" style="animation-delay: 0.2s">
                        <div class="p-6 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">Quick Links</h2>
                            <p class="mt-1 text-sm text-gray-500">Access commonly used features.</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <a href="{{ route('qr.my-qrcode') }}" 
                                   class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-green-200 transition-colors">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">My QR Code</p>
                                        <p class="text-sm text-gray-500">View or print your attendance QR code</p>
                                    </div>
                                </a>

                                <a href="{{ route('attendance.my-attendance') }}" 
                                   class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-200 transition-colors">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">My Attendance</p>
                                        <p class="text-sm text-gray-500">View your attendance history</p>
                                    </div>
                                </a>

                                <a href="{{ route('pos.index') }}" 
                                   class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-purple-200 transition-colors">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Point of Sale</p>
                                        <p class="text-sm text-gray-500">Go to the sales terminal</p>
                                    </div>
                                </a>

                                <a href="{{ route('dashboard') }}" 
                                   class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-orange-200 transition-colors">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Dashboard</p>
                                        <p class="text-sm text-gray-500">View your sales summary</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
