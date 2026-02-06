<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quick Login - Icy's Simplicitea</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-simplicitea-50 to-simplicitea-100 dark:from-gray-900 dark:to-gray-800">
    <div id="app" class="min-h-full flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <svg class="mx-auto h-16 w-16 text-simplicitea-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M2 17h20v2H2zm1.15-4.05L4 11l.85 1.95.66-.35c.52-.28 1.12-.35 1.69-.35.92 0 1.8.13 2.8.13 2.24 0 3-.81 3-1.94 0-.5-.31-1.24-.81-1.74-.5-.5-1.24-.81-1.74-.81-.92 0-1.56.49-2.06.99L6 7.38c.5-.5 1.31-.99 2.44-.99 1.92 0 3.56 1.58 3.56 3.61 0 2.03-1.64 3.61-3.56 3.61-1.14 0-1.94-.49-2.44-.99l1.39-1.81z"/>
            </svg>
            <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">Quick PIN Login</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Select your name and enter your PIN</p>
        </div>

        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <!-- User Selection -->
            <div id="userSelection" class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Who's working?</h2>
                <div class="space-y-2" id="userList">
                    @forelse($users as $user)
                        <button type="button" 
                            onclick="selectUser({{ $user->id }}, '{{ $user->name }}')"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700 hover:bg-simplicitea-50 dark:hover:bg-simplicitea-900/30 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-simplicitea-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3 text-left">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($user->role) }}</p>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                            No users with PIN set up.<br>
                            <a href="{{ route('login') }}" class="text-simplicitea-600 hover:underline">Use email login</a>
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- PIN Entry -->
            <div id="pinEntry" class="hidden p-6">
                <button type="button" onclick="backToUserList()" class="mb-4 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>

                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-simplicitea-500 rounded-full flex items-center justify-center mx-auto">
                        <span id="selectedUserInitial" class="text-2xl text-white font-bold"></span>
                    </div>
                    <h3 id="selectedUserName" class="mt-3 text-xl font-semibold text-gray-900 dark:text-white"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter your PIN</p>
                </div>

                <form id="pinForm" onsubmit="submitPin(event)">
                    <input type="hidden" id="selectedUserId" name="user_id">
                    
                    <!-- PIN Display -->
                    <div class="flex justify-center space-x-3 mb-6">
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                        <div class="pin-dot w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    </div>

                    <!-- Hidden PIN input -->
                    <input type="password" id="pinInput" name="pin" class="sr-only" maxlength="6" autofocus>

                    <!-- Error message -->
                    <p id="pinError" class="hidden text-center text-sm text-red-600 dark:text-red-400 mb-4"></p>

                    <!-- Number Pad -->
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                            <button type="button" onclick="addDigit('{{ $num }}')"
                                class="h-14 text-2xl font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                {{ $num }}
                            </button>
                        @endforeach
                        <button type="button" onclick="clearPin()"
                            class="h-14 text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            Clear
                        </button>
                        <button type="button" onclick="addDigit('0')"
                            class="h-14 text-2xl font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            0
                        </button>
                        <button type="button" onclick="backspace()"
                            class="h-14 flex items-center justify-center text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" disabled
                        class="mt-6 w-full py-3 px-4 bg-simplicitea-600 hover:bg-simplicitea-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                        Login
                    </button>
                </form>
            </div>

            <!-- Loading -->
            <div id="loadingState" class="hidden p-12 text-center">
                <svg class="animate-spin h-12 w-12 mx-auto text-simplicitea-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Logging in...</p>
            </div>
        </div>

        <!-- Alternative login link -->
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Or <a href="{{ route('login') }}" class="font-medium text-simplicitea-600 hover:text-simplicitea-500">login with email</a>
        </p>
    </div>

    <script>
        let currentPin = '';
        const pinDots = document.querySelectorAll('.pin-dot');

        function selectUser(userId, userName) {
            document.getElementById('selectedUserId').value = userId;
            document.getElementById('selectedUserName').textContent = userName;
            document.getElementById('selectedUserInitial').textContent = userName.charAt(0);
            
            document.getElementById('userSelection').classList.add('hidden');
            document.getElementById('pinEntry').classList.remove('hidden');
            
            clearPin();
            document.getElementById('pinInput').focus();
        }

        function backToUserList() {
            document.getElementById('pinEntry').classList.add('hidden');
            document.getElementById('userSelection').classList.remove('hidden');
            clearPin();
        }

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
            document.getElementById('pinInput').value = currentPin;
            
            pinDots.forEach((dot, index) => {
                if (index < currentPin.length) {
                    dot.classList.remove('bg-gray-300', 'dark:bg-gray-600');
                    dot.classList.add('bg-simplicitea-500');
                } else {
                    dot.classList.add('bg-gray-300', 'dark:bg-gray-600');
                    dot.classList.remove('bg-simplicitea-500');
                }
            });

            document.getElementById('submitBtn').disabled = currentPin.length < 4;
        }

        async function submitPin(event) {
            event.preventDefault();

            if (currentPin.length < 4) return;

            document.getElementById('pinEntry').classList.add('hidden');
            document.getElementById('loadingState').classList.remove('hidden');

            try {
                const response = await fetch('{{ route("pin.authenticate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_id: document.getElementById('selectedUserId').value,
                        pin: currentPin
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect_url;
                } else {
                    document.getElementById('loadingState').classList.add('hidden');
                    document.getElementById('pinEntry').classList.remove('hidden');
                    document.getElementById('pinError').textContent = data.message || 'Invalid PIN';
                    document.getElementById('pinError').classList.remove('hidden');
                    clearPin();
                }
            } catch (error) {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('pinEntry').classList.remove('hidden');
                document.getElementById('pinError').textContent = 'An error occurred. Please try again.';
                document.getElementById('pinError').classList.remove('hidden');
                clearPin();
            }
        }

        // Handle keyboard input
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('pinEntry').classList.contains('hidden')) return;

            if (e.key >= '0' && e.key <= '9') {
                addDigit(e.key);
            } else if (e.key === 'Backspace') {
                backspace();
            } else if (e.key === 'Enter' && currentPin.length >= 4) {
                submitPin(e);
            } else if (e.key === 'Escape') {
                backToUserList();
            }
        });
    </script>
</body>
</html>
