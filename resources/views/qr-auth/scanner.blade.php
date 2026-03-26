<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QR Code Scanner - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-simplicitea-50 to-simplicitea-100 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center p-4" x-data="qrScanner()">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <svg class="h-12 w-12 text-simplicitea-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2 17h20v2H2zm1.15-4.05L4 11l.85 1.95.66-.35c.52-.28 1.12-.35 1.69-.35.92 0 1.8.13 2.8.13 2.24 0 3-.81 3-1.94 0-.5-.31-1.24-.81-1.74-.5-.5-1.24-.81-1.74-.81-.92 0-1.56.49-2.06.99L6 7.38c.5-.5 1.31-.99 2.44-.99 1.92 0 3.56 1.58 3.56 3.61 0 2.03-1.64 3.61-3.56 3.61-1.14 0-1.94-.49-2.44-.99l1.39-1.81z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Icy's Simplicitea</h1>
            <p class="text-gray-600 mt-2">Staff Time Tracking</p>
        </div>

        <!-- Action Toggle -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-6 inline-flex">
            <button @click="action = 'login'" 
                    :class="action === 'login' ? 'bg-green-500 text-black' : 'text-gray-600 hover:bg-gray-100'"
                    class="px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Clock In
            </button>
            <button @click="action = 'logout'" 
                    :class="action === 'logout' ? 'bg-red-500 text-black' : 'text-gray-600 hover:bg-gray-100'"
                    class="px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Clock Out
            </button>
        </div>

        <!-- Scanner Container -->
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <div class="text-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900" x-text="action === 'login' ? 'Scan to Clock In' : 'Scan to Clock Out'"></h2>
                <p class="text-gray-500 text-sm mt-1">Position your QR code within the frame</p>
            </div>

            <!-- QR Scanner -->
            <div id="qr-reader" class="rounded-xl overflow-hidden"></div>

            <!-- Status Messages -->
            <div x-show="message" x-cloak
                 :class="success ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
                 class="mt-4 p-4 rounded-xl border flex items-start">
                <svg x-show="success" class="w-5 h-5 mr-2 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="!success" class="w-5 h-5 mr-2 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="message"></span>
            </div>

            <!-- User Info on Success -->
            <div x-show="userData" x-cloak class="mt-4 p-4 bg-simplicitea-50 rounded-xl">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-simplicitea-500 rounded-full flex items-center justify-center text-black font-bold text-lg">
                        <span x-text="userData?.name?.charAt(0)?.toUpperCase()"></span>
                    </div>
                    <div class="ml-3">
                        <p class="font-semibold text-gray-900" x-text="userData?.name"></p>
                        <p class="text-sm text-gray-600">
                            <span x-text="userData?.role"></span>
                            <span x-show="userData?.branch"> - <span x-text="userData?.branch"></span></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Time -->
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm">Current Time</p>
            <p class="text-2xl font-semibold text-gray-900" x-text="currentTime"></p>
            <p class="text-gray-600" x-text="currentDate"></p>
        </div>

        <!-- Footer Links -->
        <div class="mt-8 flex items-center gap-4">
            <a href="{{ route('login') }}" class="text-simplicitea-600 hover:text-simplicitea-700 text-sm font-medium">
                Use Email Login Instead →
            </a>
        </div>
    </div>

    <script>
        function qrScanner() {
            return {
                action: 'login',
                message: '',
                success: false,
                userData: null,
                currentTime: '',
                currentDate: '',
                scanner: null,
                isProcessing: false,

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    this.initScanner();
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.currentDate = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },

                initScanner() {
                    this.scanner = new Html5Qrcode("qr-reader");
                    
                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1
                    };

                    this.scanner.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => this.onScanSuccess(decodedText),
                        (errorMessage) => {
                            // Ignore scan errors (no QR code in frame)
                        }
                    ).catch((err) => {
                        console.error("Camera error:", err);
                        this.message = "Unable to access camera. Please check permissions.";
                        this.success = false;
                    });
                },

                async onScanSuccess(qrToken) {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    // Pause scanner
                    this.scanner.pause();

                    try {
                        const response = await fetch('{{ route("qr.process") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                qr_token: qrToken,
                                action: this.action
                            })
                        });

                        const data = await response.json();

                        this.message = data.message;
                        this.success = data.success;
                        this.userData = data.user || null;

                        if (data.success && data.redirect && this.action === 'login') {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        } else {
                            // Resume scanner after 3 seconds
                            setTimeout(() => {
                                this.message = '';
                                this.userData = null;
                                this.scanner.resume();
                                this.isProcessing = false;
                            }, 3000);
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        this.message = "Network error. Please try again.";
                        this.success = false;
                        
                        setTimeout(() => {
                            this.message = '';
                            this.scanner.resume();
                            this.isProcessing = false;
                        }, 3000);
                    }
                }
            }
        }
    </script>
</body>
</html>
