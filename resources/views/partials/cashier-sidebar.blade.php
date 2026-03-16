{{-- Cashier Sidebar Navigation - Reusable Component --}}
@php
    $currentRoute = request()->route()->getName();
    $sidebarBranchSession = \App\Models\BranchSession::getActiveSessionForUser(Auth::id());
    $sidebarSessionRole = $sidebarBranchSession ? ($sidebarBranchSession->is_cashier ? 'Cashier' : 'Crew') : (Auth::user()->role === 'admin' ? 'Admin' : 'Cashier');
    $sidebarActiveCrew = Auth::user()->branch_id ? \App\Models\BranchSession::getActiveCrew(Auth::user()->branch_id) : collect();
@endphp

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
                <p class="text-xs text-gray-400">
                    {{ $sidebarSessionRole }}
                    @if($sidebarBranchSession && $sidebarBranchSession->is_cashier && $sidebarActiveCrew->count() > 0)
                        <span class="text-yellow-400 ml-1">({{ $sidebarActiveCrew->count() }} crew online)</span>
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
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto hide-scrollbar">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4"/>
            </svg>
            <span class="flex-1">Dashboard</span>
        </a>

        <!-- Point of Sale -->
        <a href="{{ route('pos.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'pos.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"/>
            </svg>
            <span class="flex-1">Point of Sale</span>
        </a>

        @if(Auth::user()->role === 'admin')
        <!-- Products -->
        <a href="{{ route('products.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'products.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="flex-1">Products</span>
        </a>

        <!-- Recipes -->
        <a href="{{ route('recipes.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'recipes.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="flex-1">Recipes</span>
        </a>

        <!-- Inventory -->
        <a href="{{ route('product-inventory.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'product-inventory.') || str_starts_with($currentRoute, 'inventory.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
            <span class="flex-1">Inventory</span>
        </a>

        <!-- Reports -->
        <a href="{{ route('reports.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'reports.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="flex-1">Reports</span>
        </a>

        <!-- Employees -->
        <a href="{{ route('employees.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'employees.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="flex-1">Employees</span>
        </a>

        <!-- Cashier Sales -->
        <a href="{{ route('activity-logs.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'activity-logs.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="flex-1">Cashier Sales</span>
        </a>

        <!-- Staff Attendance -->
        <a href="{{ route('attendance.index') }}" class="nav-item {{ $currentRoute === 'attendance.index' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="flex-1">Staff Attendance</span>
        </a>
        @endif

        <!-- Divider -->
        <div class="my-3 border-t border-gray-700"></div>

        @if(Auth::user()->role === 'cashier')
        <!-- My Attendance -->
        <a href="{{ route('attendance.my-attendance') }}" class="nav-item {{ $currentRoute === 'attendance.my-attendance' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1">My Attendance</span>
        </a>

        <!-- My QR Code -->
        <a href="{{ route('qr.my-qrcode') }}" class="nav-item {{ $currentRoute === 'qr.my-qrcode' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <span class="flex-1">My QR Code</span>
        </a>
        @endif

        <!-- Settings -->
        <a href="{{ route('profile.edit') }}" class="nav-item {{ str_starts_with($currentRoute, 'profile.') ? 'active' : '' }}">
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

        @if($sidebarBranchSession && $sidebarBranchSession->is_cashier && $sidebarActiveCrew->count() > 0)
        <div class="mb-3 p-2 bg-yellow-900/30 border border-yellow-500/30 rounded-lg">
            <p class="text-yellow-300 text-xs font-semibold mb-1">Active Crew Members:</p>
            @foreach($sidebarActiveCrew as $crew)
            <div class="flex items-center justify-between py-1">
                <p class="text-yellow-200 text-xs">• {{ $crew->user->name }}</p>
                <button type="button" onclick="if(typeof handleCrewCheckOut==='function'){handleCrewCheckOut({{ $crew->id }})}else{alert('Use POS page to check out crew.')}" class="text-red-400 hover:text-red-300 text-xs underline">Check Out</button>
            </div>
            @endforeach
        </div>
        @endif

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

<script>
// Dark mode handling for sidebar
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
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => updateDarkModeUI(isDark));
    } else {
        updateDarkModeUI(isDark);
    }
})();
</script>
