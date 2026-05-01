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

    <style>
        .attendance-qr-layout {
            display: grid;
            grid-template-columns: max-content minmax(280px, 1fr);
            align-items: start;
            gap: 1.5rem;
        }

        @media (max-width: 700px) {
            .attendance-qr-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

            <div class="rounded-3xl border border-slate-300/70 dark:border-gray-700/70 shadow-xl overflow-hidden mx-auto" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #111827 100%);">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 sm:gap-5 mb-6">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center shadow-lg shrink-0">
                            <span class="text-2xl sm:text-3xl font-bold text-green-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-green-200 font-semibold">Attendance QR</p>
                            <h3 class="text-xl sm:text-2xl font-bold text-white leading-tight truncate">{{ $user->name }}</h3>
                        </div>
                    </div>

                    <div class="attendance-qr-layout">
                        <section class="flex justify-center md:justify-start">
                            <div class="bg-[#b8f2dd] p-4 sm:p-5 rounded-3xl shadow border border-green-200/80">
                                <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-inner">
                                    <img src="{{ route('qr.image') }}" alt="Your QR Code" class="w-56 h-56 sm:w-72 sm:h-72 mx-auto">
                                </div>
                            </div>
                        </section>

                        <aside class="min-w-0 flex flex-col">
                            <div class="rounded-3xl border border-white/40 bg-white/5 p-5 text-white">
                                <p class="text-xs uppercase tracking-[0.24em] text-green-200 font-semibold mb-4">Details</p>

                                <div class="space-y-3 text-sm leading-6">
                                    @if($user->branch)
                                        <div class="flex items-start justify-between gap-4 rounded-2xl bg-black/20 border border-white/30 px-4 py-3">
                                            <span class="text-slate-200">Branch</span>
                                            <span class="text-right font-semibold text-white">{{ $user->branch->name }}</span>
                                        </div>
                                    @endif

                                    @if($user->qr_token_generated_at)
                                        <div class="flex items-start justify-between gap-4 rounded-2xl bg-black/20 border border-white/30 px-4 py-3">
                                            <span class="text-slate-200 whitespace-nowrap">Generated</span>
                                            <span class="text-right font-semibold text-white">{{ $user->qr_token_generated_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                    @endif

                                </div>
                            </div>

                            <div class="mt-5 print:hidden">
                                <div class="flex flex-col gap-3">
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('qr.regenerate') }}"
                                           onclick="return confirm('Are you sure you want to regenerate your QR code? Your old QR code will no longer work.')"
                                           class="inline-flex items-center justify-center px-4 py-3 bg-green-500 text-black rounded-xl hover:bg-green-400 transition-colors font-semibold shadow-sm">
                                            Regenerate QR Code
                                        </a>
                                    @endif

                                    <a href="{{ route('attendance.my-attendance') }}"
                                       class="inline-flex items-center justify-center px-4 py-3 bg-slate-200 text-slate-900 rounded-xl hover:bg-white transition-colors font-semibold shadow-sm">
                                        View My Attendance
                                    </a>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
