<!-- Top Bar -->
<nav class="bg-white border-b border-gray-200 fixed w-full top-0 z-50 shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Sidebar Toggle -->
                <button @click="sidebarOpen = true" class="p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo (Desktop) -->
                <div class="hidden lg:flex items-center">
                    <svg class="h-8 w-8 text-simplicitea-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 17h20v2H2zm1.15-4.05L4 11l.85 1.95.66-.35c.52-.28 1.12-.35 1.69-.35.92 0 1.8.13 2.8.13 2.24 0 3-.81 3-1.94 0-.5-.31-1.24-.81-1.74-.5-.5-1.24-.81-1.74-.81-.92 0-1.56.49-2.06.99L6 7.38c.5-.5 1.31-.99 2.44-.99 1.92 0 3.56 1.58 3.56 3.61 0 2.03-1.64 3.61-3.56 3.61-1.14 0-1.94-.49-2.44-.99l1.39-1.81z"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Icy's Simplicitea</span>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-simplicitea-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('Profile') }}
                            </div>
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Log Out') }}
                                </div>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>


<!-- Sidebar Overlay -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 lg:hidden" 
     style="display: none;">
    <div @click="sidebarOpen = false" class="absolute inset-0 bg-gray-900 bg-opacity-50"></div>
</div>

<!-- Sidebar -->
<div
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl border-r border-gray-200 transform transition-transform duration-300 ease-out lg:translate-x-0"
      :class="{
          '-translate-x-full': !sidebarOpen,
          'translate-x-0': sidebarOpen
      }"
      @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
      x-cloak>
    
    <!-- Sidebar Header - User Profile Section -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <!-- User Profile Info -->
            <div class="flex items-center min-w-0 flex-1">
                <!-- User Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-500 to-simplicitea-600 rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-lg font-bold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>
                
                <!-- User Details -->
                <div class="ml-3 min-w-0 flex-1">
                    <div class="flex flex-col">
                        <!-- Cashier's Name -->
                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                            {{ Auth::user()->name }}
                        </h3>
                        
                        <!-- Role Label -->
                        <div class="flex items-center mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-simplicitea-100 text-simplicitea-800">
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                            @if(Auth::user()->branch)
                                <span class="ml-2 text-xs text-gray-500 truncate">
                                    {{ Auth::user()->branch->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Close button (mobile only) -->
            <button 
                @click="sidebarOpen = false"
                class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors duration-200 lg:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-4 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4" />
            </svg>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <!-- POS -->
        <a href="{{ route('pos.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('pos.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4" />
            </svg>
            <span class="text-sm font-medium">Point of Sale</span>
        </a>

        @if(auth()->user()->isOwner() || auth()->user()->isSupervisor())
        <!-- Products -->
        <a href="{{ route('products.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-sm font-medium">Products</span>
        </a>

        <!-- Inventory -->
        <a href="{{ route('inventory.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('inventory.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="text-sm font-medium">Inventory</span>
        </a>

        <!-- Reports -->
        <a href="{{ route('reports.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-sm font-medium">Reports</span>
        </a>

        <!-- Activity Logs (Owner Only) -->
        @if(auth()->user()->isOwner())
        <a href="{{ route('activity-logs.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('activity-logs.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium">Activity Logs</span>
        </a>
        @endif
        @endif

        <!-- Divider -->
        <div class="my-3 border-t border-gray-200"></div>

        <!-- My QR Code (for supervisors and cashiers) -->
        @if(auth()->user()->isSupervisor() || auth()->user()->isCashier())
        <a href="{{ route('qr.my-qrcode') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('qr.my-qrcode') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <span class="text-sm font-medium">My QR Code</span>
        </a>
        @endif

        <!-- Settings -->
        <a href="{{ route('profile.edit') }}" 
           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
           @click="if (window.innerWidth < 1024) sidebarOpen = false">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium">Settings</span>
        </a>
    </nav>

    <!-- Sidebar Footer - Optional Status or Additional Info -->
    <div class="border-t border-gray-200 p-3">
        <div class="text-center">
            <p class="text-xs text-gray-500">Icy's Simplicitea POS</p>
            <p class="text-xs text-gray-400">v1.0.0</p>
        </div>
    </div>
</div>
