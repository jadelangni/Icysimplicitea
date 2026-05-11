<x-auth-portal-layout
    page-title="Admin Login"
    apple-title="Admin Login"
    hero-title="Admin Access"
    hero-subtitle="Monitor sales, inventory, attendance, and branch performance anywhere through the Simplicitea management portal."
    hero-note-title="Admin-only sign in"
    hero-note-text="Non-admin accounts are rejected on this page and should use the employee login flow."
    chip-label="Admin Portal"
    portal-heading="Welcome back"
    portal-copy="Use your admin credentials to continue to the monitoring dashboard."
    theme-align="left"
    divider-label="Simplicitea Control Center"
    support-title="Management access only"
    support-text="Review sales, branch activity, inventory, and staff reports from a phone browser without switching to the employee login page."
    switch-text="Not an admin?"
    :switch-href="route('login')"
    switch-link-label="Go to Employee Login"
>
    <x-slot:chipIcon>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
    </x-slot:chipIcon>

    <x-slot:heroNoteIcon>
        <svg width="20" height="20" fill="none" stroke="#d6ff4b" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </x-slot:heroNoteIcon>

    @if (session('status'))
        <div class="auth-status">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="auth-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="field-group">
            <label for="email" class="field-label">Email Address</label>
            <div class="field-shell">
                <span class="field-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="admin@simplicitea" required autofocus autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" class="input-error" />
        </div>

        <div class="field-group">
            <label for="password" class="field-label">Password</label>
            <div class="field-shell">
                <span class="field-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password">
                <button type="button" class="field-action" onclick="togglePassword()" aria-label="Toggle password visibility">
                    <svg id="eye-open" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-closed" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="input-error" />
        </div>

        <div class="meta-row">
            <label class="remember-check" for="remember">
                <input id="remember" type="checkbox" name="remember" value="1" checked>
                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="meta-link">Forgot Password?</a>
            @endif
        </div>

        <button type="submit" class="submit-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6" />
            </svg>
            Sign in as Admin
        </button>
    </form>

    <x-slot:scripts>
        <script>
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
    </x-slot:scripts>
</x-auth-portal-layout>
