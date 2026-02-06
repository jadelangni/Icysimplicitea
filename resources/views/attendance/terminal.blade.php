<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Terminal - Icy's Simplicitea</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .selfie-container {
            position: relative;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }
        .selfie-container video,
        .selfie-container canvas {
            width: 100%;
            border-radius: 1rem;
            transform: scaleX(-1);
        }
        .selfie-container canvas {
            display: none;
        }
        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 70%;
            border: 3px dashed rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-gray-900 to-gray-800">
    <div id="app" class="min-h-full flex flex-col">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700 py-4 px-6">
            <div class="flex items-center justify-between max-w-4xl mx-auto">
                <div class="flex items-center">
                    <svg class="h-10 w-10 text-simplicitea-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 17h20v2H2zm1.15-4.05L4 11l.85 1.95.66-.35c.52-.28 1.12-.35 1.69-.35.92 0 1.8.13 2.8.13 2.24 0 3-.81 3-1.94 0-.5-.31-1.24-.81-1.74-.5-.5-1.24-.81-1.74-.81-.92 0-1.56.49-2.06.99L6 7.38c.5-.5 1.31-.99 2.44-.99 1.92 0 3.56 1.58 3.56 3.61 0 2.03-1.64 3.61-3.56 3.61-1.14 0-1.94-.49-2.44-.99l1.39-1.81z"/>
                    </svg>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-white">Icy's Simplicitea</h1>
                        <p class="text-sm text-gray-400">Attendance Terminal</p>
                    </div>
                </div>
                <div class="text-right">
                    <p id="currentTime" class="text-2xl font-bold text-white"></p>
                    <p id="currentDate" class="text-sm text-gray-400"></p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <!-- Step 1: Select User -->
                <div id="step1" class="bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700">
                        <h2 class="text-xl font-semibold text-white text-center">Select Your Name</h2>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto space-y-2" id="userList">
                        <!-- Users loaded via JavaScript -->
                        <p class="text-center text-gray-400 py-8">Loading staff...</p>
                    </div>
                </div>

                <!-- Step 2: Enter PIN -->
                <div id="step2" class="hidden bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700">
                        <button onclick="goToStep(1)" class="flex items-center text-gray-400 hover:text-white mb-4">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </button>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-simplicitea-500 rounded-full flex items-center justify-center mx-auto">
                                <span id="userInitial" class="text-2xl text-white font-bold"></span>
                            </div>
                            <h3 id="userName" class="mt-3 text-xl font-semibold text-white"></h3>
                            <p id="userStatus" class="text-sm mt-1"></p>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- PIN Display -->
                        <div class="flex justify-center space-x-3 mb-6">
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-600"></div>
                        </div>
                        
                        <input type="password" id="pinInput" class="sr-only" maxlength="6">
                        <p id="pinError" class="hidden text-center text-sm text-red-400 mb-4"></p>

                        <!-- Number Pad -->
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                                <button type="button" onclick="addDigit('{{ $num }}')"
                                    class="h-14 text-2xl font-semibold text-white bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                                    {{ $num }}
                                </button>
                            @endforeach
                            <button type="button" onclick="clearPin()"
                                class="h-14 text-sm font-medium text-gray-400 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                                Clear
                            </button>
                            <button type="button" onclick="addDigit('0')"
                                class="h-14 text-2xl font-semibold text-white bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                                0
                            </button>
                            <button type="button" onclick="backspace()"
                                class="h-14 flex items-center justify-center text-gray-400 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                                </svg>
                            </button>
                        </div>

                        <button type="button" onclick="verifyAndCapture()" id="nextBtn" disabled
                            class="mt-6 w-full py-3 px-4 bg-simplicitea-600 hover:bg-simplicitea-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                            Next
                        </button>
                    </div>
                </div>

                <!-- Step 3: Capture Selfie -->
                <div id="step3" class="hidden bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700">
                        <button onclick="goToStep(2)" class="flex items-center text-gray-400 hover:text-white mb-4">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </button>
                        <h2 class="text-xl font-semibold text-white text-center">Take a Selfie</h2>
                        <p class="text-sm text-gray-400 text-center mt-1">Position your face in the circle</p>
                    </div>
                    <div class="p-6">
                        <div class="selfie-container">
                            <video id="selfieVideo" autoplay playsinline></video>
                            <canvas id="selfieCanvas"></canvas>
                            <div class="face-guide"></div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <button type="button" onclick="captureAndSubmit()" id="captureBtn"
                                class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span id="actionText">Clock In</span>
                            </button>
                            <button type="button" onclick="skipSelfie()"
                                class="w-full py-3 px-4 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-lg transition-colors">
                                Skip Photo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Message -->
                <div id="resultMessage" class="hidden bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-12 text-center">
                        <div id="successIcon" class="hidden">
                            <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto">
                                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div id="errorIcon" class="hidden">
                            <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto">
                                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </div>
                        <h3 id="resultTitle" class="mt-6 text-2xl font-bold text-white"></h3>
                        <p id="resultText" class="mt-2 text-gray-400"></p>
                        <button onclick="resetTerminal()" class="mt-8 px-6 py-3 bg-simplicitea-600 hover:bg-simplicitea-700 text-white font-medium rounded-lg transition-colors">
                            Done
                        </button>
                    </div>
                </div>

                <!-- Loading -->
                <div id="loadingState" class="hidden bg-gray-800 rounded-2xl shadow-xl p-12">
                    <div class="text-center">
                        <svg class="animate-spin h-12 w-12 mx-auto text-simplicitea-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="mt-4 text-gray-400">Processing...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // State
        let selectedUser = null;
        let currentPin = '';
        let isClockedIn = false;
        let videoStream = null;
        const pinDots = document.querySelectorAll('.pin-dot');

        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Load users
        async function loadUsers() {
            try {
                const response = await fetch('{{ route("attendance.users") }}');
                const data = await response.json();

                if (data.success && data.users.length > 0) {
                    const userList = document.getElementById('userList');
                    userList.innerHTML = data.users.map(user => `
                        <button type="button" onclick='selectUser(${JSON.stringify(user)})'
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 ${user.is_clocked_in ? 'bg-green-500' : 'bg-gray-500'} rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">${user.name.charAt(0)}</span>
                                </div>
                                <div class="ml-3 text-left">
                                    <p class="text-sm font-medium text-white">${user.name}</p>
                                    <p class="text-xs ${user.is_clocked_in ? 'text-green-400' : 'text-gray-400'}">${user.is_clocked_in ? 'Clocked In' : 'Not Clocked In'}</p>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    `).join('');
                } else {
                    document.getElementById('userList').innerHTML = '<p class="text-center text-gray-400 py-8">No staff with PIN configured</p>';
                }
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('userList').innerHTML = '<p class="text-center text-red-400 py-8">Error loading staff list</p>';
            }
        }
        loadUsers();

        function selectUser(user) {
            selectedUser = user;
            isClockedIn = user.is_clocked_in;
            document.getElementById('userInitial').textContent = user.name.charAt(0);
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userStatus').textContent = isClockedIn ? 'Currently Clocked In' : 'Not Clocked In';
            document.getElementById('userStatus').className = `text-sm mt-1 ${isClockedIn ? 'text-green-400' : 'text-gray-400'}`;
            document.getElementById('actionText').textContent = isClockedIn ? 'Clock Out' : 'Clock In';
            goToStep(2);
        }

        function goToStep(step) {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('resultMessage').classList.add('hidden');
            document.getElementById('loadingState').classList.add('hidden');

            if (step === 1) {
                document.getElementById('step1').classList.remove('hidden');
                clearPin();
                stopCamera();
            } else if (step === 2) {
                document.getElementById('step2').classList.remove('hidden');
                clearPin();
            } else if (step === 3) {
                document.getElementById('step3').classList.remove('hidden');
                startCamera();
            }
        }

        // PIN functions
        function addDigit(digit) {
            if (currentPin.length < 6) {
                currentPin += digit;
                updatePinDisplay();
            }
        }

        function backspace() {
            if (currentPin.length > 0) {
                currentPin = currentPin.slice(0, -1);
                updatePinDisplay();
            }
        }

        function clearPin() {
            currentPin = '';
            updatePinDisplay();
            document.getElementById('pinError').classList.add('hidden');
        }

        function updatePinDisplay() {
            pinDots.forEach((dot, index) => {
                if (index < currentPin.length) {
                    dot.classList.remove('bg-gray-600');
                    dot.classList.add('bg-simplicitea-500');
                } else {
                    dot.classList.add('bg-gray-600');
                    dot.classList.remove('bg-simplicitea-500');
                }
            });
            document.getElementById('nextBtn').disabled = currentPin.length < 4;
        }

        function verifyAndCapture() {
            if (currentPin.length >= 4) {
                goToStep(3);
            }
        }

        // Camera functions
        async function startCamera() {
            try {
                const video = document.getElementById('selfieVideo');
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: 640, height: 480 } 
                });
                video.srcObject = videoStream;
            } catch (error) {
                console.error('Error accessing camera:', error);
                // Continue without camera
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function captureImage() {
            const video = document.getElementById('selfieVideo');
            const canvas = document.getElementById('selfieCanvas');
            
            if (!video.srcObject) return null;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            
            return canvas.toDataURL('image/jpeg', 0.8);
        }

        async function captureAndSubmit() {
            const selfie = captureImage();
            await submitAttendance(selfie);
        }

        async function skipSelfie() {
            await submitAttendance(null);
        }

        async function submitAttendance(selfie) {
            showLoading();

            try {
                const endpoint = isClockedIn ? '{{ route("attendance.clock-out") }}' : '{{ route("attendance.clock-in") }}';
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_id: selectedUser.id,
                        pin: currentPin,
                        selfie: selfie
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showResult(true, isClockedIn ? 'Clocked Out!' : 'Clocked In!', data.message);
                } else {
                    showResult(false, 'Error', data.message || 'Something went wrong');
                }
            } catch (error) {
                console.error('Error:', error);
                showResult(false, 'Error', 'Network error. Please try again.');
            }
        }

        function showLoading() {
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('loadingState').classList.remove('hidden');
            stopCamera();
        }

        function showResult(success, title, message) {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('resultMessage').classList.remove('hidden');
            
            document.getElementById('successIcon').classList.toggle('hidden', !success);
            document.getElementById('errorIcon').classList.toggle('hidden', success);
            document.getElementById('resultTitle').textContent = title;
            document.getElementById('resultText').textContent = message;
        }

        function resetTerminal() {
            selectedUser = null;
            currentPin = '';
            isClockedIn = false;
            loadUsers();
            goToStep(1);
        }

        // Keyboard input
        document.addEventListener('keydown', function(e) {
            if (!document.getElementById('step2').classList.contains('hidden')) {
                if (e.key >= '0' && e.key <= '9') {
                    addDigit(e.key);
                } else if (e.key === 'Backspace') {
                    backspace();
                } else if (e.key === 'Enter' && currentPin.length >= 4) {
                    verifyAndCapture();
                } else if (e.key === 'Escape') {
                    goToStep(1);
                }
            }
        });
    </script>
</body>
</html>
