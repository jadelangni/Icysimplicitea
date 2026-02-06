@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-simplicitea-500 to-simplicitea-600 px-6 py-8 text-center">
                <svg class="mx-auto h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-white">PIN Setup</h2>
                <p class="mt-2 text-simplicitea-100">Set up your quick login PIN for faster access</p>
            </div>

            <!-- Form -->
            <div class="px-6 py-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <p class="ml-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if($hasPin)
                    <div class="mb-6 bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <p class="ml-3 text-sm text-blue-700 dark:text-blue-300">You already have a PIN set up. You can update it below or remove it.</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('pin.save') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Current Password
                        </label>
                        <input type="password" name="current_password" id="current_password" required
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $hasPin ? 'New PIN' : 'PIN' }} (4-6 digits)
                        </label>
                        <input type="password" name="pin" id="pin" required maxlength="6" pattern="[0-9]{4,6}"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest"
                            placeholder="••••••">
                        @error('pin')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirm PIN
                        </label>
                        <input type="password" name="pin_confirmation" id="pin_confirmation" required maxlength="6" pattern="[0-9]{4,6}"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-simplicitea-500 focus:border-simplicitea-500 dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest"
                            placeholder="••••••">
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-simplicitea-600 hover:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-simplicitea-500">
                        {{ $hasPin ? 'Update PIN' : 'Set PIN' }}
                    </button>
                </form>

                @if($hasPin)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('pin.remove') }}" onsubmit="return confirm('Are you sure you want to remove your PIN?');">
                            @csrf
                            @method('DELETE')
                            
                            <div class="mb-4">
                                <label for="remove_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password (to remove PIN)
                                </label>
                                <input type="password" name="current_password" id="remove_password" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-red-300 dark:border-red-600 rounded-lg shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove PIN
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
