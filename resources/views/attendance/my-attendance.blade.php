@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Attendance</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View your clock-in and clock-out history</p>
        </div>

        <!-- Status Card -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 {{ $isClockedIn ? 'bg-green-500' : 'bg-gray-400' }} rounded-full flex items-center justify-center">
                        @if($isClockedIn)
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $isClockedIn ? 'Currently Clocked In' : 'Not Clocked In' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Today's hours: {{ number_format($todayHours, 2) }} hrs
                        </p>
                    </div>
                </div>
                <a href="{{ route('attendance.terminal') }}" target="_blank"
                    class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Open Attendance Terminal
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <form method="GET" action="{{ route('attendance.my-attendance') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-simplicitea-600 hover:bg-simplicitea-700 text-white rounded-lg transition-colors">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Photo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($attendance as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $record->recorded_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $record->recorded_at->format('h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $record->type === 'clock_in' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' }}">
                                        {{ $record->type === 'clock_in' ? 'Clock In' : 'Clock Out' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($record->selfie_path)
                                        <a href="{{ route('attendance.selfie', $record) }}" target="_blank"
                                            class="text-simplicitea-600 hover:text-simplicitea-700 dark:text-simplicitea-400">
                                            View Photo
                                        </a>
                                    @else
                                        <span class="text-gray-400">No photo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $record->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No attendance records found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendance->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $attendance->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
