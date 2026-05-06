<x-app-layout>
    @php($showAddBranchModal = $errors->addBranch->any() || request('open') === 'add-branch')
    @php($showBranchManagerModal = request('open') === 'manage-branches')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Employee Management') }}
        </h2>
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
                    <form id="employeesFiltersForm" action="{{ route('employees.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or ID number..." 
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
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-black flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Employee List
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">{{ $employees->total() }} total</span>
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button id="openManageBranchesBtn" class="inline-flex w-full sm:w-auto justify-center items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            </svg>
                            Manage Branches
                        </button>
                        <a href="{{ route('employees.create') }}" class="inline-flex w-full sm:w-auto justify-center items-center px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-sm font-medium rounded-xl transition-colors duration-200 shadow-lg shadow-simplicitea-500/30">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Employee Account
                        </a>
                    </div>
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
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-10 h-10 bg-gradient-to-br from-simplicitea-400 to-simplicitea-600 rounded-full flex items-center justify-center text-black font-semibold text-sm flex-shrink-0">
                                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-black truncate">{{ $employee->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $employee->email }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">ID: {{ $employee->id_number ?? 'Pending' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 min-w-[80px]">
                                    @if($employee->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 whitespace-nowrap">
                                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300 whitespace-nowrap">
                                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Cashier
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 min-w-[120px]">
                                    <div class="inline-branch-selector" data-employee-id="{{ $employee->id }}">
                                        <select onchange="updateBranch({{ $employee->id }}, this.value)" 
                                            class="branch-select text-xs sm:text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-2.5 sm:px-3 py-1.5 text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 cursor-pointer max-w-[130px] truncate">
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $employee->branch_id == $branch->id ? 'selected' : '' }}>
                                                    📍 {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="px-5 py-4 min-w-[90px]">
                                    <button onclick="toggleStatus({{ $employee->id }})" 
                                        class="status-badge-{{ $employee->id }} inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-200 cursor-pointer hover:opacity-80 whitespace-nowrap
                                        {{ $employee->is_active ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' }}">
                                        <span class="w-2 h-2 rounded-full mr-1.5 flex-shrink-0 {{ $employee->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        <span class="status-text">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 min-w-[200px]">
                                    <div class="flex items-center gap-1.5 flex-wrap">
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
                        Create Employee Account
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Branch Modal -->
    <div id="addBranchModal" class="fixed inset-0 z-50 hidden">
        <div id="addBranchModalBackdrop" class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-black">Add New Branch</h4>
                    <button type="button" id="closeAddBranchModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('branches.store') }}" class="px-6 py-5 space-y-4">
                    @csrf

                    <div>
                        <label for="branch_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Name <span class="text-red-500">*</span></label>
                        <input type="text" id="branch_name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border {{ $errors->addBranch->has('name') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500"
                               placeholder="e.g., Oslob Main">
                        @if($errors->addBranch->has('name'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->addBranch->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="branch_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address <span class="text-red-500">*</span></label>
                        <input type="text" id="branch_address" name="address" value="{{ old('address') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border {{ $errors->addBranch->has('address') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500"
                               placeholder="Branch location">
                        @if($errors->addBranch->has('address'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->addBranch->first('address') }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="branch_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text" id="branch_phone" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border {{ $errors->addBranch->has('phone') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500"
                                   placeholder="Optional">
                            @if($errors->addBranch->has('phone'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->addBranch->first('phone') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="branch_manager_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manager Name</label>
                            <input type="text" id="branch_manager_name" name="manager_name" value="{{ old('manager_name') }}"
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border {{ $errors->addBranch->has('manager_name') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500"
                                   placeholder="Optional">
                            @if($errors->addBranch->has('manager_name'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->addBranch->first('manager_name') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="branch_is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-simplicitea-600 shadow-sm focus:ring-simplicitea-500">
                        <label for="branch_is_active" class="text-sm text-gray-700 dark:text-gray-300">Set as active branch</label>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button type="button" id="cancelAddBranchModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-black bg-simplicitea-600 hover:bg-simplicitea-700 rounded-xl">
                            Save Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Branch Manager Modal -->
    <div id="branchManagerModal" class="fixed inset-0 z-50 hidden">
        <div id="branchManagerModalBackdrop" class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-black">Branch Manager</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add, archive, or delete branches from a single place.</p>
                    </div>
                    <button type="button" id="closeBranchManagerModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $managedBranches->count() }} branches total</p>
                        <button type="button" id="openAddBranchFromManager" class="px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-sm font-medium rounded-xl transition-colors duration-200">
                            Add Branch
                        </button>
                    </div>
                    <div class="space-y-3">
                        @foreach($managedBranches as $branch)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-black">{{ $branch->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $branch->address }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $branch->manager_name ?? 'No manager assigned' }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="archiveBranch({{ $branch->id }})" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                        {{ $branch->is_active ? 'Archive' : 'Restore' }}
                                    </button>
                                    <button type="button" onclick="if(confirm('Delete this branch? Employees will be unassigned from it.')) { deleteBranch({{ $branch->id }}); }" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors border border-red-200 dark:border-red-800">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
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
        function archiveBranch(branchId) {
            fetch(`/branches/${branchId}/archive`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Branch updated successfully', 'success');
                    // Reload branch manager content without page refresh
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showToast(data.message || 'Failed to update branch', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }

        function deleteBranch(branchId) {
            fetch(`/branches/${branchId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Branch deleted successfully', 'success');
                    // Reload branch manager content without page refresh
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showToast(data.message || 'Failed to delete branch', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }

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
            const addBranchModal = document.getElementById('addBranchModal');
            const branchManagerModal = document.getElementById('branchManagerModal');
            const openAddBranchModalBtn = document.getElementById('openAddBranchModal');
            const openAddBranchFromManagerBtn = document.getElementById('openAddBranchFromManager');
            const closeAddBranchModalBtn = document.getElementById('closeAddBranchModal');
            const closeBranchManagerModalBtn = document.getElementById('closeBranchManagerModal');
            const cancelAddBranchModalBtn = document.getElementById('cancelAddBranchModal');
            const addBranchModalBackdrop = document.getElementById('addBranchModalBackdrop');
            const branchManagerModalBackdrop = document.getElementById('branchManagerModalBackdrop');

            const openAddBranchModal = () => {
                if (!addBranchModal) return;
                addBranchModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeAddBranchModal = () => {
                if (!addBranchModal) return;
                addBranchModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const openBranchManagerModal = () => {
                if (!branchManagerModal) return;
                branchManagerModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeBranchManagerModal = () => {
                if (!branchManagerModal) return;
                branchManagerModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            if (openAddBranchModalBtn) openAddBranchModalBtn.addEventListener('click', openAddBranchModal);
            if (openAddBranchFromManagerBtn) openAddBranchFromManagerBtn.addEventListener('click', () => {
                closeBranchManagerModal();
                openAddBranchModal();
            });
            const openManageBranchesBtn = document.getElementById('openManageBranchesBtn');
            if (openManageBranchesBtn) openManageBranchesBtn.addEventListener('click', openBranchManagerModal);
            if (closeAddBranchModalBtn) closeAddBranchModalBtn.addEventListener('click', closeAddBranchModal);
            if (cancelAddBranchModalBtn) cancelAddBranchModalBtn.addEventListener('click', closeAddBranchModal);
            if (addBranchModalBackdrop) addBranchModalBackdrop.addEventListener('click', closeAddBranchModal);
            if (closeBranchManagerModalBtn) closeBranchManagerModalBtn.addEventListener('click', closeBranchManagerModal);
            if (branchManagerModalBackdrop) branchManagerModalBackdrop.addEventListener('click', closeBranchManagerModal);

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && addBranchModal && !addBranchModal.classList.contains('hidden')) {
                    closeAddBranchModal();
                }
                if (event.key === 'Escape' && branchManagerModal && !branchManagerModal.classList.contains('hidden')) {
                    closeBranchManagerModal();
                }
            });

            if ({{ $showAddBranchModal ? 'true' : 'false' }}) {
                openAddBranchModal();
            }

            if ({{ $showBranchManagerModal ? 'true' : 'false' }}) {
                openBranchManagerModal();
            }

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
