{{-- Cashier Sidebar Navigation - Reusable Component --}}
@php
    $currentRoute = request()->route()->getName();
@endphp

<button type="button" id="cashierSidebarToggle" class="cashier-sidebar-toggle no-print p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 bg-white dark:bg-gray-800" aria-label="Open navigation menu" aria-expanded="false" data-hide-toggle-on-open="{{ str_starts_with($currentRoute, 'pos.') ? 'true' : 'false' }}">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<div id="cashierSidebarOverlay" class="cashier-sidebar-overlay"></div>

<!-- LEFT SIDEBAR -->
<div id="cashierSidebar" class="sidebar cashier-sidebar">
    <!-- Panel Branding -->
    <div class="p-4 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden bg-white/90">
                <img src="{{ asset('images/logo.png') }}" alt="Icy's Simplicitea logo" class="w-9 h-9 object-contain">
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-black text-sm truncate">Icy's Simplicitea</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto hide-scrollbar" id="cashierSidebarNav">
        @if(Auth::user()->role === 'admin')
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m4-4v4m4-4v4"/>
            </svg>
            <span class="flex-1">Dashboard</span>
        </a>
        @endif

        <!-- Point of Sale -->
        <a href="{{ route('pos.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'pos.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7.5m-7.5 0H4"/>
            </svg>
            <span class="flex-1">Point of Sale</span>
        </a>

        @if(Auth::user()->role === 'cashier')
        <!-- Inventory (employee view) -->
        <a href="{{ route('employee-inventory.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'employee-inventory.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
            <span class="flex-1">Inventory</span>
        </a>
        @endif

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

        <!-- Employee -->
        <a href="{{ route('employees.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'employees.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="flex-1">Employee List</span>
        </a>

        <!-- Cashier Sales -->
        <a href="{{ route('activity-logs.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'activity-logs.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="flex-1">Cashier Sales</span>
        </a>

        <!-- Staff Attendance -->
        <a href="{{ route('attendance.index') }}" class="nav-item {{ $currentRoute === 'attendance.index' ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="flex-1">Staff Attendance</span>
        </a>
        @endif

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

    </nav>

    @if(isset($sidebarBranchSession) && $sidebarBranchSession && $sidebarBranchSession->is_cashier && isset($sidebarActiveCrew) && $sidebarActiveCrew->count() > 0)
    <div class="px-4 pb-3">
        <div class="p-2 bg-yellow-900/30 border border-yellow-500/30 rounded-lg">
            <p class="text-yellow-300 text-xs font-semibold mb-1">Active Crew Members:</p>
            @foreach($sidebarActiveCrew as $crew)
            <div class="flex items-center justify-between py-1">
                <p class="text-yellow-200 text-xs">• {{ $crew->user->name }}</p>
                <button type="button" onclick="if(typeof handleCrewCheckOut==='function'){handleCrewCheckOut({{ $crew->id }})}else{alert('Use POS page to check out crew.')}" class="text-red-400 hover:text-red-300 text-xs underline">Check Out</button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="p-4 border-t border-gray-700">
        @if(session('error'))
        <div class="mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs">
            {{ session('error') }}
        </div>
        @endif

        <a href="{{ route('logout.prepare') }}" class="nav-item w-full text-red-400 hover:text-red-300 hover:bg-red-900/20" onclick="return confirm('Are you sure you want to logout?')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="flex-1 text-left">Logout</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="nav-item w-full {{ str_starts_with($currentRoute, 'profile.') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="flex-1 text-left">Settings</span>
        </a>
        <div class="mt-4 text-center">
            <p class="text-[10px] text-black uppercase tracking-wider">Powered by</p>
            <p class="text-sm font-semibold text-black flex items-center justify-center gap-2 mt-1">
                <span class="sidebar-brand-badge">S</span>
                Simplicitea
            </p>
        </div>
    </div>
</div>

<style>
#cashierSidebar {
    background: linear-gradient(180deg, #157476 0%, #0f6264 52%, #0a5152 100%);
}

#cashierSidebar .nav-item {
    color: #e5fff8;
}

#cashierSidebar .nav-item.active {
    background: #00B140;
    color: #000000;
}

#cashierSidebar .nav-item:hover {
    background: rgba(178, 232, 216, 0.18);
    color: #ffffff;
}

#cashierSidebar .employee-parent {
    width: 100%;
}

#cashierSidebar .employee-chevron {
    transition: transform 0.2s ease;
}

#cashierSidebar .employee-submenu {
    margin-left: 2rem;
    margin-top: 0.25rem;
    margin-bottom: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

#cashierSidebar .employee-subitem {
    display: block;
    padding: 0.45rem 0.75rem;
    border-radius: 0.5rem;
    color: #b2e8d8;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

#cashierSidebar .employee-subitem:hover {
    background: rgba(152, 255, 152, 0.12);
    color: #ffffff;
}

#cashierSidebar .employee-subitem.active {
    background: #00B140;
    color: #000000;
    font-weight: 600;
}

#cashierSidebar .sidebar-role-text {
    color: #b2e8d8 !important;
}

#cashierSidebar .border-gray-700 {
    border-color: rgba(178, 232, 216, 0.24) !important;
}

#cashierSidebar .sidebar-brand-badge {
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

@media (max-width: 1023px) {
    .cashier-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: min(86vw, 280px);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.32);
        z-index: 60;
    }

    .cashier-sidebar.open {
        transform: translateX(0);
    }

    .cashier-sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(1px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
        z-index: 55;
    }

    .cashier-sidebar-overlay.show {
        opacity: 1;
        pointer-events: auto;
    }

    .cashier-sidebar-toggle {
        position: fixed;
        top: 0.75rem;
        left: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 65;
    }

    .cashier-sidebar-toggle.is-hidden {
        display: none !important;
    }

    body.cashier-sidebar-open {
        overflow: hidden;
    }
}

@media (min-width: 1024px) {
    .cashier-sidebar-toggle,
    .cashier-sidebar-overlay {
        display: none;
    }
}

body.cashier-pos-layout .cashier-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: min(86vw, 280px);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 0 24px 48px rgba(15, 23, 42, 0.32);
    z-index: 60;
}

body.cashier-pos-layout .cashier-sidebar.open {
    transform: translateX(0);
}

body.cashier-pos-layout .cashier-sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.6);
    backdrop-filter: blur(1px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
    z-index: 55;
}

body.cashier-pos-layout .cashier-sidebar-overlay.show {
    opacity: 1;
    pointer-events: auto;
}

body.cashier-pos-layout .cashier-sidebar-toggle {
    position: fixed;
    top: 0.75rem;
    left: 0.75rem;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    z-index: 65;
}

body.cashier-pos-layout .cashier-sidebar-toggle.is-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

body.cashier-pos-layout .mobile-sidebar-safe-start {
    padding-left: 4rem;
    padding-top: 0.25rem;
}

@media (min-width: 1024px) {
    body.cashier-pos-layout .cashier-sidebar-toggle,
    body.cashier-pos-layout .cashier-sidebar-overlay {
        display: block;
    }
}
</style>

<script>
function setCashierSidebar(open) {
    const sidebar = document.getElementById('cashierSidebar');
    const overlay = document.getElementById('cashierSidebarOverlay');
    const toggle = document.getElementById('cashierSidebarToggle');
    const isMobile = window.innerWidth < 1024;
    const isPosDrawerLayout = document.body.classList.contains('cashier-pos-layout');

    if (!sidebar || !overlay || !toggle || (!isMobile && !isPosDrawerLayout)) return;

    sidebar.classList.toggle('open', open);
    overlay.classList.toggle('show', open);
    document.body.classList.toggle('cashier-sidebar-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

    const shouldHideToggleOnOpen = toggle.dataset.hideToggleOnOpen === 'true';
    if (shouldHideToggleOnOpen) {
        toggle.classList.toggle('is-hidden', open);
    }
}

function openCashierSidebar() {
    setCashierSidebar(true);
}

function closeCashierSidebar() {
    setCashierSidebar(false);
}

document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('cashierSidebarToggle');
    const overlay = document.getElementById('cashierSidebarOverlay');
    const nav = document.getElementById('cashierSidebarNav');
    const employeeToggle = document.querySelector('[data-employee-toggle]');
    const employeeSubmenu = document.querySelector('[data-employee-submenu]');

    if (employeeToggle && employeeSubmenu) {
        employeeToggle.addEventListener('click', function () {
            const isExpanded = employeeToggle.getAttribute('aria-expanded') === 'true';
            const shouldExpand = !isExpanded;
            employeeToggle.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');
            employeeSubmenu.classList.toggle('hidden', !shouldExpand);

            const chevron = employeeToggle.querySelector('.employee-chevron');
            if (chevron) {
                chevron.classList.toggle('rotate-180', shouldExpand);
            }
        });
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            const sidebar = document.getElementById('cashierSidebar');
            const isOpen = sidebar ? sidebar.classList.contains('open') : false;
            setCashierSidebar(!isOpen);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeCashierSidebar);
    }

    if (nav) {
        nav.addEventListener('click', function (event) {
            if (window.innerWidth < 1024 && event.target.closest('a.nav-item, a.employee-subitem')) {
                closeCashierSidebar();
            }
        });
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024 && !document.body.classList.contains('cashier-pos-layout')) {
            const sidebar = document.getElementById('cashierSidebar');
            const overlayEl = document.getElementById('cashierSidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlayEl) overlayEl.classList.remove('show');
            document.body.classList.remove('cashier-sidebar-open');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCashierSidebar();
        }
    });
});
</script>
