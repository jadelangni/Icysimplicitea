<x-app-layout>
    @php
        $branchSession = \App\Models\BranchSession::getActiveSessionForUser(Auth::id());
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-black leading-tight">My Attendance</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your time is tracked automatically on login/logout.</p>
            </div>
        </div>
    </x-slot>

    <style>
        html:not(.dark) .my-attendance-theme {
            background:
                linear-gradient(120deg, rgba(0, 91, 92, 0.10) 0%, rgba(0, 91, 92, 0.05) 36%, rgba(0, 91, 92, 0.02) 72%),
                linear-gradient(180deg, #eaf4ef 0%, #deece5 52%, #d4e5dc 100%) !important;
        }

        html:not(.dark) .my-attendance-theme .bg-white {
            background: rgba(243, 250, 247, 0.95) !important;
            border-color: rgba(0, 91, 92, 0.16) !important;
            box-shadow: 0 10px 24px rgba(0, 91, 92, 0.08) !important;
        }

        html:not(.dark) .my-attendance-theme .bg-gray-50,
        html:not(.dark) .my-attendance-theme .bg-gray-100,
        html:not(.dark) .my-attendance-theme .dark\:bg-gray-700\/40 {
            background: rgba(223, 237, 229, 0.7) !important;
        }

        html:not(.dark) .my-attendance-theme .text-green-600,
        html:not(.dark) .my-attendance-theme .text-green-700 {
            color: #005b5c !important;
        }
    </style>

    <div class="my-attendance-theme py-6 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-black">{{ $isClockedIn ? 'Currently On Duty' : 'Off Duty' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Today: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($todayHours, 2) }} hrs</span>
                            <span class="mx-2">•</span>
                            Period Total: <span class="font-semibold text-green-600 dark:text-green-400">{{ number_format($totalHours, 2) }} hrs</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isClockedIn)
                            <div class="inline-flex items-center px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                <span id="live-running-timer" class="font-mono font-bold">00:00:00</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <form method="GET" action="{{ route('attendance.my-attendance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Start Date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">End Date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full md:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-black rounded-lg font-medium">Apply</button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-black">Duty Sessions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/40">
                                <th class="px-5 py-3 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300">Date</th>
                                <th class="px-5 py-3 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300">Clock In</th>
                                <th class="px-5 py-3 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300">Clock Out</th>
                                <th class="px-5 py-3 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300">Hours</th>
                                <th class="px-5 py-3 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($sessions ?? [] as $session)
                                <tr>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-black">{{ \Carbon\Carbon::parse($session['date'])->format('M d, Y') }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $session['clock_in']->format('h:i A') }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $session['clock_out'] ? $session['clock_out']->format('h:i A') : '-' }}</td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-black">{{ number_format($session['hours_worked'], 2) }} hrs</td>
                                    <td class="px-5 py-4">
                                        @if($session['is_running'])
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">On Duty</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">No attendance sessions found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($isClockedIn)
    <script>
        (function() {
            const timerEl = document.getElementById('live-running-timer');
            if (!timerEl) return;

            const runningSession = @json(collect($sessions ?? [])->firstWhere('is_running', true));
            if (!runningSession || !runningSession.clock_in) return;

            const start = new Date(runningSession.clock_in);
            const tick = () => {
                const now = new Date();
                const diff = Math.max(0, Math.floor((now - start) / 1000));
                const h = String(Math.floor(diff / 3600)).padStart(2, '0');
                const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');
                timerEl.textContent = `${h}:${m}:${s}`;
            };

            tick();
            setInterval(tick, 1000);
        })();
    </script>
    @endif
</x-app-layout>
