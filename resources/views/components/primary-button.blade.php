<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-simplicitea-600 dark:bg-simplicitea-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-simplicitea-700 dark:hover:bg-simplicitea-600 focus:bg-simplicitea-700 dark:focus:bg-simplicitea-600 active:bg-simplicitea-800 dark:active:bg-simplicitea-700 focus:outline-none focus:ring-2 focus:ring-simplicitea-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
