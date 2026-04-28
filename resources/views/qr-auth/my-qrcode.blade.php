<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-black leading-tight">My QR Code</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Use this personal QR for attendance scanning.</p>
            </div>
            <button onclick="window.print()" class="hidden sm:inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-black rounded-lg text-sm font-medium print:hidden">
                Print QR Code
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full sm:max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="px-4 py-3 rounded-lg border border-green-200 bg-green-50 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 print:hidden">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="px-4 py-3 rounded-lg border border-red-200 bg-red-50 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 print:hidden">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-3xl border border-slate-300/70 dark:border-gray-700/70 shadow-xl overflow-hidden max-w-md mx-auto" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #111827 100%);">
                <div class="px-6 pt-8 pb-4 text-center text-black">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <span class="text-3xl font-bold text-green-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-black">{{ $user->name }}</h3>
                </div>

                <div class="px-6 pb-8 text-center">
                    <div class="bg-[#b8f2dd] p-4 rounded-2xl inline-block shadow border border-green-200">
                        <img src="{{ route('qr.image') }}" alt="Your QR Code" class="w-64 h-64 sm:w-72 sm:h-72 mx-auto">
                    </div>

                    @if($user->branch)
                        <p class="text-green-200 text-sm mt-4 font-medium">{{ $user->branch->name }}</p>
                    @endif

                    <p class="text-gray-100 text-sm mt-3">
                        Present this code to the scanner when clocking in.
                    </p>

                    @if($user->qr_token_generated_at)
                        <p class="text-gray-200 text-xs mt-2">
                            Generated: {{ $user->qr_token_generated_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                </div>

                <div class="px-6 py-4 bg-black/20 border-t border-gray-600 print:hidden">
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('qr.regenerate') }}"
                               onclick="return confirm('Are you sure you want to regenerate your QR code? Your old QR code will no longer work.')"
                               class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-black rounded-lg hover:bg-green-700 transition-colors">
                                Regenerate QR Code
                            </a>
                        @endif

                        <a href="{{ route('attendance.my-attendance') }}"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-black rounded-lg hover:bg-gray-900 transition-colors">
                            View My Attendance
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
