<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-black leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <style>
        html:not(.dark) .settings-theme {
            background: linear-gradient(180deg, #9cb7ab 0%, #8ca79a 54%, #7b9689 100%) !important;
        }

        html:not(.dark) .settings-theme .bg-white {
            background: rgba(226, 243, 235, 0.94) !important;
            border: 1px solid rgba(0, 91, 92, 0.22) !important;
            box-shadow: 0 10px 24px rgba(0, 91, 92, 0.12) !important;
        }
    </style>

    <div class="settings-theme py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
