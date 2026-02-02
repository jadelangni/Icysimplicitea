<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Activity Logs') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Today's Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Logins</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $todayStats['logins'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today's Logouts</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $todayStats['logouts'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Sessions</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $todayStats['active_users'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                </div>
                <form method="GET" action="{{ route('activity-logs.index') }}" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                            <select name="action" id="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                                <option value="">All Actions</option>
                                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                            </select>
                        </div>

                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                            <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-simplicitea-500 focus:ring-simplicitea-500 text-sm">
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-md hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 text-sm">
                            Apply Filters
                        </button>
                        <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Activity Logs Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP Address
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-simplicitea-100 flex items-center justify-center">
                                                    <span class="text-simplicitea-600 font-medium">
                                                        {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $log->user->name ?? 'Unknown User' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ ucfirst($log->user->role ?? 'N/A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->action === 'login')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                                Login
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Logout
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->ip_address ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $log->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i:s A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('activity-logs.user', $log->user) }}" 
                                               class="text-simplicitea-600 hover:text-simplicitea-900">
                                                History
                                            </a>
                                            @if($log->user && ($log->user->isSupervisor() || $log->user->isCashier()))
                                            <a href="{{ route('qr.user-qrcode', $log->user) }}" 
                                               class="text-gray-600 hover:text-gray-900" title="View QR Code">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No activity logs</h3>
                                        <p class="mt-1 text-sm text-gray-500">No activity has been recorded yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
