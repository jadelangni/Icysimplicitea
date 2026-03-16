<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Attendance - {{ config('app.name', 'Simplicitea') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow: hidden; }
        .pos-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar { width: 240px; background: #1a1a2e; display: flex; flex-direction: column; color: white; flex-shrink: 0; }
        .main-content { flex: 1; display: flex; flex-direction: column; background: #f8f9fa; overflow: hidden; transition: background-color 0.3s; }
        .content-area { flex: 1; overflow-y: auto; padding: 24px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; color: #9ca3af; text-decoration: none; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: #166534; color: white; }
        @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.2s ease forwards; }
        
        /* Dark Mode Styles - Comprehensive */
        html.dark body { background: #111827 !important; color: #f3f4f6 !important; }
        html.dark .main-content { background: #1f2937 !important; }
        html.dark .content-area { background: #1f2937 !important; }
        
        /* Backgrounds */
        html.dark .bg-white { background: #374151 !important; }
        html.dark .bg-gray-50 { background: #1f2937 !important; }
        html.dark .bg-gray-100 { background: #374151 !important; }
        html.dark .bg-gray-200 { background: #4b5563 !important; }
        html.dark .bg-green-50 { background: #14532d !important; }
        html.dark .bg-green-100 { background: #166534 !important; }
        html.dark .bg-blue-50 { background: #1e3a5f !important; }
        html.dark .bg-yellow-50 { background: #713f12 !important; }
        html.dark .bg-red-50 { background: #7f1d1d !important; }
        
        /* Text Colors */
        html.dark .text-black { color: #f3f4f6 !important; }
        html.dark .text-gray-900 { color: #f3f4f6 !important; }
        html.dark .text-gray-800 { color: #f3f4f6 !important; }
        html.dark .text-gray-700 { color: #d1d5db !important; }
        html.dark .text-gray-600 { color: #d1d5db !important; }
        html.dark .text-gray-500 { color: #9ca3af !important; }
        html.dark .text-gray-400 { color: #9ca3af !important; }
        html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6 { color: #f3f4f6 !important; }
        html.dark p { color: #d1d5db !important; }
        html.dark label { color: #d1d5db !important; }
        
        /* Borders */
        html.dark .border-gray-100 { border-color: #374151 !important; }
        html.dark .border-gray-200 { border-color: #4b5563 !important; }
        html.dark .border-gray-300 { border-color: #4b5563 !important; }
        html.dark .border-b { border-color: #374151 !important; }
        html.dark .border-t { border-color: #374151 !important; }
        
        /* Tables */
        html.dark table { color: #f3f4f6 !important; border-color: #4b5563 !important; }
        html.dark th { color: #d1d5db !important; background: #1f2937 !important; }
        html.dark td { color: #f3f4f6 !important; border-color: #374151 !important; }
        html.dark tr:hover { background: #4b5563 !important; }
        html.dark tbody tr { border-color: #374151 !important; }
        html.dark thead { background: #1f2937 !important; }
        
        /* Forms */
        html.dark input, html.dark select, html.dark textarea { 
            background: #374151 !important; 
            border-color: #4b5563 !important; 
            color: #f3f4f6 !important; 
        }
        html.dark input::placeholder { color: #9ca3af !important; }
        
        /* Status Badges */
        html.dark .bg-green-100 { background: #166534 !important; }
        html.dark .bg-red-100 { background: #7f1d1d !important; }
        html.dark .bg-yellow-100 { background: #854d0e !important; }
        html.dark .text-green-800 { color: #86efac !important; }
        html.dark .text-red-800 { color: #fca5a5 !important; }
        html.dark .text-yellow-800 { color: #fef08a !important; }
        
        /* Cards */
        html.dark .rounded-2xl { background: #374151 !important; border-color: #4b5563 !important; }
        html.dark .rounded-xl { background: #374151 !important; }
        html.dark .shadow-sm { border-color: #4b5563 !important; }
        
        /* Modal */
        html.dark .fixed.inset-0 .bg-white { background: #374151 !important; }
        html.dark [x-show] .bg-white { background: #374151 !important; }
    </style>
    <script>
        // Initialize dark mode before page renders
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased h-full bg-gray-100">
    <div class="pos-wrapper">
        @include('partials.cashier-sidebar')

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Attendance</h1>
                        <p class="text-gray-500 text-sm">Your time is automatically tracked when you login and logout</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="max-w-6xl mx-auto">
                    <!-- Status Card -->
                    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center">
                                <div class="w-14 h-14 {{ $isClockedIn ? 'bg-green-500' : 'bg-gray-400' }} rounded-full flex items-center justify-center">
                                    @if($isClockedIn)
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $isClockedIn ? 'Currently On Duty' : 'Off Duty' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Today's hours: <span id="live-today-hours" class="font-semibold text-gray-700">{{ number_format($todayHours, 2) }} hrs</span>
                                        @if(isset($totalHours))
                                        · Period total: <span id="live-total-hours" class="font-semibold text-green-600">{{ number_format($totalHours, 2) }} hrs</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($isClockedIn)
                            <div class="mt-4 sm:mt-0 flex items-center px-4 py-3 bg-green-100 text-green-800 rounded-lg">
                                <svg class="h-5 w-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="live-running-timer" class="font-bold text-lg font-mono">00:00:00</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <form method="GET" action="{{ route('attendance.my-attendance') }}" class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" value="{{ $startDate }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" name="end_date" value="{{ $endDate }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                                    Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Attendance Sessions Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">Duty Sessions</h2>
                            <p class="text-sm text-gray-500">Your login and logout history with hours worked</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Login Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Logout Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hours Worked</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($sessions ?? [] as $session)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($session['date'])->format('M d, Y') }}
                                                <span class="block text-xs text-gray-500">{{ \Carbon\Carbon::parse($session['date'])->format('l') }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <span class="inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                    </svg>
                                                    {{ $session['clock_in']->format('h:i A') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                @if($session['clock_out'])
                                                    <span class="inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        {{ $session['clock_out']->format('h:i A') }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $hours = floor($session['hours_worked']);
                                                    $minutes = round(($session['hours_worked'] - $hours) * 60);
                                                @endphp
                                                @if($session['is_running'])
                                                    <span id="live-session-hours" class="text-sm font-semibold text-green-600" data-clock-in="{{ $session['clock_in']->toIso8601String() }}">
                                                        {{ $hours }}h {{ $minutes }}m
                                                    </span>
                                                    <span id="live-session-decimal" class="block text-xs text-gray-500">({{ number_format($session['hours_worked'], 2) }} hrs)</span>
                                                @else
                                                    <span class="text-sm font-semibold text-gray-900">
                                                        {{ $hours }}h {{ $minutes }}m
                                                    </span>
                                                    <span class="block text-xs text-gray-500">({{ number_format($session['hours_worked'], 2) }} hrs)</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($session['is_running'])
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                        <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                                        On Duty
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                        Completed
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center">
                                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500">No attendance sessions found for this period.</p>
                                                <p class="text-sm text-gray-400 mt-1">Login to start tracking your duty hours.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($sessions ?? []) > 0)
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">
                                            Total Hours for Period:
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $totalH = floor($totalHours);
                                                $totalM = round(($totalHours - $totalH) * 60);
                                            @endphp
                                            <span class="text-lg font-bold text-green-600">{{ $totalH }}h {{ $totalM }}m</span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



    @if($isClockedIn)
    <script>
    (function() {
        const sessionEl = document.getElementById('live-session-hours');
        if (!sessionEl) return;

        const clockInTime = new Date(sessionEl.getAttribute('data-clock-in'));
        const baseTodayHours = {{ $todayHours }};
        const baseTotalHours = {{ $totalHours ?? 0 }};
        // The running session's initial hours (server-rendered)
        const baseSessionHours = {{ collect($sessions)->first(fn($s) => $s['is_running'])['hours_worked'] ?? 0 }};

        function updateTimer() {
            const now = new Date();
            const diffMs = now - clockInTime;
            const diffSec = Math.floor(diffMs / 1000);
            const h = Math.floor(diffSec / 3600);
            const m = Math.floor((diffSec % 3600) / 60);
            const s = diffSec % 60;

            const pad = n => String(n).padStart(2, '0');
            const decimalHours = diffSec / 3600;

            // Update running timer badge (HH:MM:SS)
            const timerEl = document.getElementById('live-running-timer');
            if (timerEl) {
                timerEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
            }

            // Update session row hours
            if (sessionEl) {
                sessionEl.textContent = h + 'h ' + m + 'm';
            }
            const decimalEl = document.getElementById('live-session-decimal');
            if (decimalEl) {
                decimalEl.textContent = '(' + decimalHours.toFixed(2) + ' hrs)';
            }

            // Update today's hours in status card
            const todayEl = document.getElementById('live-today-hours');
            if (todayEl) {
                // baseTodayHours already includes the running session from server time
                // We need to add the extra seconds elapsed since server render
                const extraHours = decimalHours - baseSessionHours;
                const updatedToday = baseTodayHours + (extraHours > 0 ? extraHours : 0);
                todayEl.textContent = updatedToday.toFixed(2) + ' hrs';
            }

            // Update total hours in status card
            const totalEl = document.getElementById('live-total-hours');
            if (totalEl) {
                const extraHours = decimalHours - baseSessionHours;
                const updatedTotal = baseTotalHours + (extraHours > 0 ? extraHours : 0);
                totalEl.textContent = updatedTotal.toFixed(2) + ' hrs';
            }
        }

        // Run immediately then every second
        updateTimer();
        setInterval(updateTimer, 1000);
    })();
    </script>
    @endif

    <!-- Live polling: auto-refresh attendance data every 60 seconds -->
    <script>
        setInterval(() => {
            window.location.reload();
        }, 60000);
    </script>
</body>
</html>
