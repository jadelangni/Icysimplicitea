<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('activity-logs.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('QR Code for: ') }} {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-simplicitea-500 to-simplicitea-600 px-6 py-8 text-center text-white">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <h3 class="text-2xl font-bold">{{ $user->name }}</h3>
                    <p class="text-simplicitea-100 mt-1">{{ ucfirst($user->role) }}</p>
                    @if($user->branch)
                        <p class="text-simplicitea-200 text-sm mt-1">{{ $user->branch->name }}</p>
                    @endif
                    <p class="text-simplicitea-200 text-sm mt-1">{{ $user->email }}</p>
                </div>

                <!-- QR Code -->
                <div class="p-8 text-center">
                    <div class="bg-white p-4 rounded-xl inline-block shadow-lg border-4 border-simplicitea-100">
                        {!! QrCode::size(256)->generate($user->qr_token) !!}
                    </div>

                    <p class="text-gray-500 text-sm mt-6">
                        This QR code can be used by {{ $user->name }} to clock in and out.
                    </p>

                    @if($user->qr_token_generated_at)
                        <p class="text-gray-400 text-xs mt-2">
                            Generated: {{ $user->qr_token_generated_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('qr.user-regenerate', $user) }}" 
                           onclick="return confirm('Are you sure you want to regenerate the QR code for {{ $user->name }}? Their old QR code will no longer work.')"
                           class="inline-flex items-center justify-center px-4 py-2 bg-simplicitea-600 text-white rounded-lg hover:bg-simplicitea-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Regenerate QR Code
                        </a>

                        <button onclick="window.print()" 
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print QR Code
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Activity Summary -->
            <div class="mt-6 bg-white rounded-xl shadow p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Recent Activity</h4>
                @php
                    $recentLogs = $user->activityLogs()->latest()->take(5)->get();
                @endphp
                @forelse($recentLogs as $log)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="flex items-center">
                            @if($log->action === 'login')
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>
                                <span class="text-green-700">Clock In</span>
                            @else
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>
                                <span class="text-red-700">Clock Out</span>
                            @endif
                        </div>
                        <span class="text-gray-500 text-sm">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No activity recorded yet.</p>
                @endforelse

                @if($recentLogs->count() > 0)
                    <a href="{{ route('activity-logs.index') }}" class="text-simplicitea-600 hover:text-simplicitea-700 text-sm mt-4 inline-block">
                        View cashier sales →
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .bg-white.rounded-2xl, .bg-white.rounded-2xl * {
                visibility: visible;
            }
            .bg-white.rounded-2xl {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
            }
            .bg-gray-50, button, a {
                display: none !important;
            }
        }
    </style>
</x-app-layout>
