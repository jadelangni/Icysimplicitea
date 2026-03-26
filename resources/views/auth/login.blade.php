<x-auth-portal-layout
    page-title="Employee Login"
    apple-title="Employee Login"
    hero-title="Employee Access"
    hero-subtitle="Use QR scan as your main sign in method. Email and password are available below if needed."
    hero-note-title="Main Login: QR Scanner"
    hero-note-text="Use your employee QR code for faster sign in."
    chip-label="Employee Portal"
    portal-heading="Welcome back"
    portal-copy="Sign in with your employee account to continue to your branch dashboard."
    divider-label="Simplicitea Crew Access"
    support-title="Need admin monitoring tools?"
    support-text="Switch to the admin login page for reporting, branch overview, and management functions."
    switch-text="Are you an admin?"
    :switch-href="route('admin.login')"
    switch-link-label="Go to Admin Login"
>
    <x-slot:chipIcon>
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    </x-slot:chipIcon>

    <x-slot:heroNoteIcon>
        <svg width="20" height="20" fill="none" stroke="#d6ff4b" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
    </x-slot:heroNoteIcon>

    @if (session('status'))
        <div class="auth-status">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="auth-error">{{ session('error') }}</div>
    @endif

    @php
        $showEmailLogin = $errors->has('email') || $errors->has('password') || old('email');
    @endphp

    <div class="mb-6 rounded-2xl border border-simplicitea-200 bg-simplicitea-50 p-4">
        <p class="text-sm font-semibold text-simplicitea-900 mb-3">Recommended: Login with QR</p>
        <a href="{{ route('qr.scanner') }}" class="qr-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            Open QR Scanner
        </a>
    </div>

    <div class="mb-4 text-center">
        <button
            type="button"
            id="toggleEmailLoginBtn"
            onclick="toggleEmailLoginSection()"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-100"
            aria-controls="emailLoginSection"
            aria-expanded="{{ $showEmailLogin ? 'true' : 'false' }}"
        >
            {{ $showEmailLogin ? 'Hide Email Login' : 'Use Email Login Instead' }}
        </button>
    </div>

    <div id="emailLoginSection" class="{{ $showEmailLogin ? '' : 'hidden' }}">
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field-group">
            <label for="email" class="field-label">Email Address</label>
            <div class="field-shell">
                <span class="field-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="employee@simplicitea.com" required {{ $showEmailLogin ? 'autofocus' : '' }} autocomplete="username">
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
            Sign in as Employee
        </button>

    </form>
    </div>

    <x-slot:scripts>
        <script>
            function toggleEmailLoginSection() {
                const section = document.getElementById('emailLoginSection');
                const button = document.getElementById('toggleEmailLoginBtn');
                const isHidden = section.classList.contains('hidden');

                if (isHidden) {
                    section.classList.remove('hidden');
                    button.textContent = 'Hide Email Login';
                    button.setAttribute('aria-expanded', 'true');
                    const email = document.getElementById('email');
                    if (email) email.focus();
                } else {
                    section.classList.add('hidden');
                    button.textContent = 'Use Email Login Instead';
                    button.setAttribute('aria-expanded', 'false');
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
    </x-slot:scripts>
</x-auth-portal-layout>
