<x-auth-portal-layout
    :pageTitle="__('Set Password')"
    :appleTitle="__('Set Password')"
    :heroTitle="__('Account Security')"
    :heroSubtitle="__('Please set a new password to continue')"
    :portalHeading="__('Welcome!')"
    :portalCopy="__('Please set a new password for your account')"
>
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-black">Welcome!</h2>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">
            Please set a new password for your account
        </p>
    </div>

    @if (session('warning'))
        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl">
            <p class="text-sm text-amber-700 dark:text-amber-300">{{ session('warning') }}</p>
        </div>
    @endif

    <!-- Current User Info -->
    <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-600">
        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-1">Logged in as</p>
        <p class="font-semibold text-gray-900 dark:text-black">{{ auth()->user()->name }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
    </div>

    <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
        @csrf

        <!-- New Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">
                New Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                    class="mint-input block w-full pl-12 pr-4 py-3.5 text-gray-800 dark:text-black placeholder-gray-400"
                    placeholder="Enter new password">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="mt-2 text-xs text-gray-400">
                At least 8 characters with letters and numbers
            </p>
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">
                Confirm Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="mint-input block w-full pl-12 pr-4 py-3.5 text-gray-800 dark:text-black placeholder-gray-400"
                    placeholder="Confirm new password">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-mint w-full py-4 text-base mt-2">
            Set Password & Continue
        </button>
    </form>

    <!-- Logout Option -->
    <div class="text-center mt-5">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors font-medium">
                Log out instead
            </button>
        </form>
    </div>
</x-auth-portal-layout>
