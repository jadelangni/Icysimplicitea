<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300 font-medium">
        {{ session('error') }}
    </div>
    @endif

    <!-- Primary Login: QR Scan -->
    <div id="qr-login-section">
        <div class="text-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Scan QR Code to Login</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Quick and secure access using your employee QR code</p>
        </div>

        <a href="{{ route('qr.scanner') }}" class="btn-mint w-full flex items-center justify-center gap-3 py-4 text-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <span>Open QR Scanner</span>
        </a>
    </div>

    <!-- Divider -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-100 dark:border-gray-700"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-4 text-xs font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-[#0f172a] uppercase tracking-wider">
                Or
            </span>
        </div>
    </div>

    <!-- Toggle for Email Login -->
    <div class="text-center">
        <button type="button" 
                onclick="toggleEmailLogin()" 
                id="toggle-email-btn"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors font-medium flex items-center justify-center gap-2 mx-auto">
            <svg id="toggle-icon-down" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg id="toggle-icon-up" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            <span id="toggle-text">Sign in with Email</span>
        </button>
    </div>

    <!-- Email Login Form (Hidden by default) -->
    <div id="email-login-section" class="hidden mt-6">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email/Username Field -->
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" style="padding-left: 14px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="mint-input block w-full pr-4 text-gray-800 dark:text-white placeholder-gray-400"
                           style="padding-left: 44px;"
                           placeholder="Email Address"
                           required 
                           autocomplete="username">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" style="padding-left: 14px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" 
                           type="password" 
                           name="password"
                           class="mint-input block w-full pr-12 text-gray-800 dark:text-white placeholder-gray-400"
                           style="padding-left: 44px;"
                           placeholder="Password"
                           required 
                           autocomplete="current-password">
                    <button type="button" 
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <svg id="eye-open" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye-closed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me (hidden but functional) -->
            <input type="hidden" name="remember" value="1">

            <!-- Login Button -->
            <button type="submit" class="btn-mint w-full py-3.5 text-sm mt-2">
                Sign In
            </button>
        </form>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="text-center mt-5">
                <a class="text-sm text-gray-400 dark:text-gray-500 hover:text-green-600 dark:hover:text-green-400 transition-colors font-medium" 
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            </div>
        @endif
    </div>

    <script>
        // Auto-show email form if there are validation errors
        @if($errors->any())
            document.getElementById('email-login-section').classList.remove('hidden');
            document.getElementById('toggle-icon-down').classList.add('hidden');
            document.getElementById('toggle-icon-up').classList.remove('hidden');
            document.getElementById('toggle-text').textContent = 'Hide Email Login';
        @endif

        function toggleEmailLogin() {
            const emailSection = document.getElementById('email-login-section');
            const toggleIconDown = document.getElementById('toggle-icon-down');
            const toggleIconUp = document.getElementById('toggle-icon-up');
            const toggleText = document.getElementById('toggle-text');
            
            if (emailSection.classList.contains('hidden')) {
                emailSection.classList.remove('hidden');
                toggleIconDown.classList.add('hidden');
                toggleIconUp.classList.remove('hidden');
                toggleText.textContent = 'Hide Email Login';
                document.getElementById('email').focus();
            } else {
                emailSection.classList.add('hidden');
                toggleIconDown.classList.remove('hidden');
                toggleIconUp.classList.add('hidden');
                toggleText.textContent = 'Sign in with Email';
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            }
        }
    </script>

    <!-- Admin Login Link -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-100 dark:border-gray-700"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-4 text-xs font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-[#0f172a] uppercase tracking-wider">
                Admin?
            </span>
        </div>
    </div>
    <div class="text-center">
        <a href="{{ route('admin.login') }}" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors font-semibold">
            Go to Admin Login →
        </a>
    </div>
</x-guest-layout>
