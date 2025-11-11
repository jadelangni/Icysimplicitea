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
                <!-- User Info -->
                <div class="text-gray-700 text-sm">
                    <div class="font-medium">{{ Auth::user()->name }}</div>
                    <div class="text-gray-500 text-xs">
                        {{ ucfirst(Auth::user()->role) }}
                        @if(Auth::user()->branch)
                            • {{ Auth::user()->branch->name }}
                        @endif
                    </div>
                </div>

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


<!-- Sidebar Overlay with Blur Effect -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-all ease-out duration-500"
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-all ease-in duration-300"
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40" 
     style="display: none;">
    <div @click="sidebarOpen = false" class="absolute inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
</div>

<!-- Sidebar -->
<div x-data="{ collapsed: false }"
     class="fixed inset-y-0 left-0 z-50 bg-white shadow-xl border-r border-gray-200 transform transition-all duration-500 ease-out"
     :class="{
        'w-64': !collapsed && sidebarOpen,
        'w-16': collapsed,
        '-translate-x-full': !sidebarOpen && window.innerWidth < 1024,
        'translate-x-0': sidebarOpen || window.innerWidth >= 1024,
        'lg:w-64': !collapsed,
        'lg:w-16': collapsed,
        'lg:translate-x-0': true
     }"
     @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
     @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true"
     x-init="if (window.innerWidth >= 1024) sidebarOpen = true"
     x-cloak>
    
    <!-- Sidebar Header - User Profile Section -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <!-- User Profile Info -->
            <div class="flex items-center min-w-0 flex-1" :class="{ 'justify-center': collapsed }">
                <!-- User Avatar -->
                <div class="flex-shrink-0 relative group">
                    <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-500 to-simplicitea-600 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:shadow-xl">
                        <span class="text-lg font-bold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    
                    <!-- Tooltip for collapsed state -->
                    <div class="absolute left-14 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                         :class="{ 'group-hover:opacity-100': collapsed }"
                         x-show="collapsed">
                        {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                    </div>
                </div>
                
                <!-- User Details (hidden when collapsed) -->
                <div class="ml-3 min-w-0 flex-1 transition-opacity duration-300" 
                     :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                     x-show="!collapsed">
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

            <!-- Action Buttons: unified toggle -> collapse on desktop, open/close on mobile -->
            <div class="flex items-center space-x-1 ml-2">
                <button 
                    @click="if (window.innerWidth >= 1024) { collapsed = !collapsed } else { sidebarOpen = !sidebarOpen }"
                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors duration-200"
                    :title="(window.innerWidth >= 1024) ? (collapsed ? 'Expand Sidebar' : 'Collapse Sidebar') : (sidebarOpen ? 'Close Menu' : 'Open Menu')">

                    <!-- Desktop: collapse/expand icon -->
                    <svg class="h-4 w-4 transform transition-transform duration-300 hidden lg:block" :class="{ 'rotate-180': collapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>

                    <!-- Mobile: close (X) icon when sidebar is open -->
                    <svg class="h-4 w-4 block lg:hidden" x-show="sidebarOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>

                </button>
            </div>
        </div>

        <!-- Company Branding (collapsed state) -->
        <div class="mt-3 text-center transition-opacity duration-300" 
             :class="{ 'opacity-100': collapsed, 'opacity-0': !collapsed }"
             x-show="collapsed">
            <svg class="h-6 w-6 text-simplicitea-600 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                <path d="M2 17h20v2H2zm1.15-4.05L4 11l.85 1.95.66-.35c.52-.28 1.12-.35 1.69-.35.92 0 1.8.13 2.8.13 2.24 0 3-.81 3-1.94 0-.5-.31-1.24-.81-1.74-.5-.5-1.24-.81-1.74-.81-.92 0-1.56.49-2.06.99L6 7.38c.5-.5 1.31-.99 2.44-.99 1.92 0 3.56 1.58 3.56 3.61 0 2.03-1.64 3.61-3.56 3.61-1.14 0-1.94-.49-2.44-.99l1.39-1.81z"/>
            </svg>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-2 py-4 space-y-1" :class="{ 'px-4': !collapsed }">
        <!-- Dashboard -->
        <div class="relative group">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('dashboard') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Dashboard' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Dashboard</span>
            </a>
            <!-- Tooltip for collapsed state -->
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Dashboard
            </div>
        </div>

        <!-- POS -->
        <div class="relative group">
            <a href="{{ route('pos.index') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('pos.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Point of Sale' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Point of Sale</span>
            </a>
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Point of Sale
            </div>
        </div>

        @if(auth()->user()->isOwner() || auth()->user()->isSupervisor())
        <!-- Products -->
        <div class="relative group">
            <a href="{{ route('products.index') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('products.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Products' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Products</span>
            </a>
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Products
            </div>
        </div>

        <!-- Inventory -->
        <div class="relative group">
            <a href="{{ route('inventory.index') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('inventory.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Inventory' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Inventory</span>
            </a>
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Inventory
            </div>
        </div>

        <!-- Reports -->
        <div class="relative group">
            <a href="{{ route('reports.index') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('reports.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Reports' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Reports</span>
            </a>
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Reports
            </div>
        </div>
        @endif

        <!-- Divider -->
        <div class="my-3 border-t border-gray-200" x-show="!collapsed"></div>

        <!-- Settings -->
        <div class="relative group">
            <a href="{{ route('profile.edit') }}" 
               class="flex items-center rounded-lg transition-all duration-300 ease-in-out {{ request()->routeIs('profile.*') ? 'bg-simplicitea-50 text-simplicitea-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
               :class="{ 'px-3 py-3 justify-center': collapsed, 'px-4 py-3': !collapsed }"
               @click="if (window.innerWidth < 1024) sidebarOpen = false"
               :title="collapsed ? 'Settings' : ''">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                     :class="{ 'mr-0': collapsed, 'mr-3': !collapsed }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm font-medium transition-opacity duration-300" 
                      :class="{ 'opacity-0 w-0': collapsed, 'opacity-100': !collapsed }"
                      x-show="!collapsed">Settings</span>
            </a>
            <div class="absolute left-16 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50"
                 :class="{ 'group-hover:opacity-100': collapsed }"
                 x-show="collapsed">
                Settings
            </div>
        </div>
    </nav>

    <!-- Sidebar Footer - Optional Status or Additional Info -->
    <div class="border-t border-gray-200 p-3" x-show="!collapsed">
        <div class="text-center">
            <p class="text-xs text-gray-500">Icy's Simplicitea POS</p>
            <p class="text-xs text-gray-400">v1.0.0</p>
        </div>
    </div>
</div>
