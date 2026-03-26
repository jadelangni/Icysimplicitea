<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Employee Management') }}
            </h2>
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-sm font-medium rounded-xl transition-colors duration-200 shadow-lg shadow-simplicitea-500/30">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Employee
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Employees</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-black">{{ \App\Models\User::count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ \App\Models\User::where('is_active', true)->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Admins</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-simplicitea-100 dark:bg-simplicitea-900/50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-simplicitea-600 dark:text-simplicitea-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cashiers</p>
                            <p class="text-2xl font-bold text-simplicitea-600 dark:text-simplicitea-400">{{ \App\Models\User::where('role', 'cashier')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-black flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters
                    </h3>
                </div>
                <div class="p-5">
                    <form id="employeesFiltersForm" action="{{ route('employees.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." 
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                            </div>
                        </div>
                        
                        <!-- Branch Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Branch</label>
                            <select name="branch_id" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Role Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                            <select name="role" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                                <option value="">All Roles</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        
                    </form>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-black flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Employee List
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">{{ $employees->total() }} total</span>
                    </h3>
                </div>
                
                @if($employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branch</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-400 to-simplicitea-600 rounded-full flex items-center justify-center text-black font-semibold text-sm">
                                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-black">{{ $employee->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($employee->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Cashier
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="inline-branch-selector" data-employee-id="{{ $employee->id }}">
                                        <select onchange="updateBranch({{ $employee->id }}, this.value)" 
                                            class="branch-select text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 cursor-pointer min-w-[140px]">
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $employee->branch_id == $branch->id ? 'selected' : '' }}>
                                                    📍 {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <button onclick="toggleStatus({{ $employee->id }})" 
                                        class="status-badge-{{ $employee->id }} inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-200 cursor-pointer hover:opacity-80
                                        {{ $employee->is_active ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' }}">
                                        <span class="w-2 h-2 rounded-full mr-1.5 {{ $employee->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        <span class="status-text">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('employees.edit', $employee) }}" 
                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg transition-colors duration-200" 
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <!-- QR Code Management -->
                                        <a href="{{ route('qr.user-qrcode', $employee) }}" 
                                            class="inline-flex items-center justify-center w-8 h-8 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 text-purple-600 dark:text-purple-400 rounded-lg transition-colors duration-200" 
                                            title="View QR Code">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('qr.user-regenerate', $employee) }}" 
                                            class="inline-flex items-center justify-center w-8 h-8 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 rounded-lg transition-colors duration-200" 
                                            title="Regenerate QR Code"
                                            onclick="return confirm('Are you sure you want to regenerate the QR code for {{ $employee->name }}? The old QR code will no longer work.');">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </a>
                                        @if($employee->id !== auth()->id())
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center justify-center w-8 h-8 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg transition-colors duration-200" 
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($employees->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $employees->links() }}
                </div>
                @endif
                @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-black mb-1">No employees found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your filters or add a new employee.</p>
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-sm font-medium rounded-xl transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Employee
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3 min-w-[300px]">
            <div id="toastIcon" class="w-8 h-8 rounded-full flex items-center justify-center"></div>
            <p id="toastMessage" class="text-gray-800 dark:text-gray-200 font-medium"></p>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            
            if (type === 'success') {
                toastIcon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50';
                toastIcon.innerHTML = '<svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            } else {
                toastIcon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50';
                toastIcon.innerHTML = '<svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            }
            
            toast.classList.remove('hidden');
            toast.classList.add('animate-slide-up');
            
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function updateBranch(employeeId, branchId) {
            fetch(`/employees/${employeeId}/branch`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ branch_id: branchId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast('Failed to update branch', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }

        function toggleStatus(employeeId) {
            fetch(`/employees/${employeeId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector(`.status-badge-${employeeId}`);
                    const statusText = badge.querySelector('.status-text');
                    const dot = badge.querySelector('span:first-child');
                    
                    if (data.is_active) {
                        badge.className = `status-badge-${employeeId} inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-200 cursor-pointer hover:opacity-80 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300`;
                        dot.className = 'w-2 h-2 rounded-full mr-1.5 bg-green-500';
                        statusText.textContent = 'Active';
                    } else {
                        badge.className = `status-badge-${employeeId} inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-200 cursor-pointer hover:opacity-80 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300`;
                        dot.className = 'w-2 h-2 rounded-full mr-1.5 bg-red-500';
                        statusText.textContent = 'Inactive';
                    }
                    
                    showToast(data.message, 'success');
                } else {
                    showToast('Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filtersForm = document.getElementById('employeesFiltersForm');
            if (!filtersForm) return;

            const searchInput = filtersForm.querySelector('input[name="search"]');
            const autoSubmitFields = filtersForm.querySelectorAll('select');
            let searchDebounce = null;

            autoSubmitFields.forEach(field => {
                field.addEventListener('change', () => filtersForm.requestSubmit());
            });

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(() => filtersForm.requestSubmit(), 450);
                });
            }
        });
    </script>

    <style>
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(1rem);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
    </style>
</x-app-layout>
