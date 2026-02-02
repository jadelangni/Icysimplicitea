<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('activity-logs.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Activity History: ') }} {{ $user->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- User Info Card -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-simplicitea-100 flex items-center justify-center">
                                <span class="text-2xl text-simplicitea-600 font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-simplicitea-100 text-simplicitea-800">
                                    {{ ucfirst($user->role) }}
                                </span>
                                @if($user->branch)
                                    <span class="text-sm text-gray-500">
                                        {{ $user->branch->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Total Logins</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_logins'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Total Logouts</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_logouts'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Last Login</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $stats['last_login'] ? $stats['last_login']->created_at->format('M d, Y h:i A') : 'Never' }}
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Last Logout</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $stats['last_logout'] ? $stats['last_logout']->created_at->format('M d, Y h:i A') : 'Never' }}
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6">
                <form method="GET" action="{{ route('activity-logs.user', $user) }}" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-simplicitea-600 text-white rounded-md hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 text-sm">
                                Filter
                            </button>
                            <a href="{{ route('activity-logs.user', $user) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Activity Timeline</h3>
                </div>
                <div class="p-6">
                    @forelse($logs as $log)
                        <div class="relative pb-8 {{ !$loop->last ? 'border-l-2 border-gray-200 ml-4' : 'ml-4' }}">
                            <div class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full {{ $log->action === 'login' ? 'bg-green-100' : 'bg-red-100' }}">
                                @if($log->action === 'login')
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                @else
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-6 pb-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-gray-900 {{ $log->action === 'login' ? 'text-green-700' : 'text-red-700' }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                        @if($log->branch)
                                            <span class="text-gray-500 text-sm ml-2">at {{ $log->branch->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $log->created_at->format('M d, Y h:i:s A') }}
                                    </div>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    IP: {{ $log->ip_address ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No activity found</h3>
                            <p class="mt-1 text-sm text-gray-500">This user has no recorded activity.</p>
                        </div>
                    @endforelse
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
