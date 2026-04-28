<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-black leading-tight">{{ __('Create Product') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-4 sm:p-6">
                    @include('products.partials.form', ['categories' => $categories])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
