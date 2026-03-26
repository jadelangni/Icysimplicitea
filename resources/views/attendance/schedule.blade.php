<x-app-layout>
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('attendance.index') }}" class="text-sm text-simplicitea-600 dark:text-simplicitea-400 hover:underline mb-2 inline-block">&larr; Back to Attendance</a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-black">Duty Schedule</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Set the weekly work schedule for <strong>{{ $user->name }}</strong></p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg relative">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Schedule Form -->
        <form method="POST" action="{{ route('duty-schedules.update', $user) }}">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $dayNames = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
                    @endphp

                    @foreach($dayNames as $dayNum => $dayName)
                        @php
                            $schedule = $schedules->get($dayNum);
                            $isDayOff = $schedule ? $schedule->is_day_off : false;
                            $startTime = $schedule && !$isDayOff ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('H:i') : '06:00';
                            $endTime = $schedule && !$isDayOff ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('H:i') : '14:00';
                        @endphp
                        <div class="p-4 sm:p-6" x-data="{ isDayOff: {{ $isDayOff ? 'true' : 'false' }} }">
                            <input type="hidden" name="schedules[{{ $dayNum }}][day_of_week]" value="{{ $dayNum }}">
                            
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <!-- Day Name -->
                                <div class="w-full sm:w-32 flex-shrink-0">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-black">{{ $dayName }}</span>
                                </div>

                                <!-- Day Off Toggle -->
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="schedules[{{ $dayNum }}][is_day_off]" 
                                               value="1"
                                               x-model="isDayOff"
                                               class="sr-only peer"
                                               {{ $isDayOff ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-simplicitea-500 dark:peer-focus:ring-simplicitea-400 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:after:border-gray-500 peer-checked:bg-red-500"></div>
                                    </label>
                                    <span class="text-xs font-medium" :class="isDayOff ? 'text-red-500' : 'text-gray-400 dark:text-gray-500'">Day Off</span>
                                </div>

                                <!-- Time Inputs -->
                                <div class="flex items-center gap-2 flex-1" x-show="!isDayOff" x-transition>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Start</label>
                                        <input type="time" 
                                               name="schedules[{{ $dayNum }}][start_time]" 
                                               value="{{ $startTime }}"
                                               class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-black w-full">
                                    </div>
                                    <span class="text-gray-400 mt-5">to</span>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">End</label>
                                        <input type="time" 
                                               name="schedules[{{ $dayNum }}][end_time]" 
                                               value="{{ $endTime }}"
                                               class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-black w-full">
                                    </div>
                                </div>

                                <!-- Day Off Message -->
                                <div x-show="isDayOff" x-transition class="flex-1">
                                    <span class="text-sm text-red-500 dark:text-red-400 italic">Rest Day — No duty</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit -->
                <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="px-6 py-2.5 bg-simplicitea-600 hover:bg-simplicitea-700 text-black rounded-lg font-medium transition-colors">
                        Save Schedule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
