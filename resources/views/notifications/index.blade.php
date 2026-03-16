<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl transition-colors duration-200">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if($unreadCount > 0)
                                    You have <span class="font-semibold text-simplicitea-600 dark:text-simplicitea-400">{{ $unreadCount }}</span> unread notification{{ $unreadCount > 1 ? 's' : '' }}
                                @else
                                    All caught up!
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-simplicitea-50 dark:bg-simplicitea-900/30 text-simplicitea-700 dark:text-simplicitea-300 rounded-xl text-sm font-medium hover:bg-simplicitea-100 dark:hover:bg-simplicitea-900/50 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Mark All Read
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'all' ? 'bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            All
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'unread' ? 'bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Unread
                            @if($unreadCount > 0)
                                <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'read' ? 'bg-simplicitea-100 dark:bg-simplicitea-900/50 text-simplicitea-700 dark:text-simplicitea-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Read
                        </a>
                    </div>

                    <!-- Notifications List -->
                    @if($notifications->isEmpty())
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🔔</div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No notifications</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                @if($filter === 'unread')
                                    You're all caught up! No unread notifications.
                                @elseif($filter === 'read')
                                    No read notifications yet.
                                @else
                                    No notifications to show.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                            <div class="flex items-start gap-4 p-4 rounded-xl border transition-colors {{ $notification->is_read ? 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700' : 'bg-simplicitea-50/50 dark:bg-simplicitea-900/20 border-simplicitea-200 dark:border-simplicitea-800' }}">
                                <!-- Icon -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $notification->is_read ? 'bg-gray-100 dark:bg-gray-700' : 'bg-simplicitea-100 dark:bg-simplicitea-900/50' }}">
                                    @if($notification->type === 'off_schedule_clock_in')
                                        <span class="text-lg">⏰</span>
                                    @elseif($notification->type === 'late_clock_in')
                                        <span class="text-lg">🕐</span>
                                    @else
                                        <span class="text-lg">🔔</span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-semibold {{ $notification->is_read ? 'text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-white' }}">
                                            {{ $notification->title }}
                                        </h4>
                                        @if(!$notification->is_read)
                                            <span class="inline-flex w-2 h-2 bg-simplicitea-500 rounded-full"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $notification->message }}</p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                        @if($notification->triggeredByUser)
                                            <span class="text-xs text-gray-400 dark:text-gray-500">• {{ $notification->triggeredByUser->name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                @if(!$notification->is_read)
                                <div class="flex-shrink-0">
                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-simplicitea-600 dark:hover:text-simplicitea-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Mark as read">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Live polling: auto-refresh page when new notifications arrive -->
    <script>
        let lastUnreadCount = {{ $unreadCount }};
        setInterval(async () => {
            try {
                const response = await fetch('/notifications/unread-count', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (!response.ok) return;
                const data = await response.json();
                // If unread count changed, reload to show new notifications
                if (data.count !== undefined && data.count !== lastUnreadCount) {
                    window.location.reload();
                }
            } catch (e) {}
        }, 10000);
    </script>
</x-app-layout>
