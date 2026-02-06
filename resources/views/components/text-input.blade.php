@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-simplicitea-500 dark:focus:border-simplicitea-500 focus:ring-simplicitea-500 dark:focus:ring-simplicitea-500 rounded-md shadow-sm']) }}>
