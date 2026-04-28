import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './app/**/*.php',
        './resources/js/**/*.js',
        './resources/js/**/*.ts',
        './resources/js/**/*.vue',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        {
            pattern: /(bg|text|border)-(simplicitea|green|blue|red|yellow|purple|gray)-(50|100|200|300|400|500|600|700|800|900)(\/(10|20|30|40|50|60|70|80|90))?/,
            variants: ['dark', 'hover'],
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'simplicitea': {
                    '50': '#f0fdf4',
                    '100': '#dcfce7',
                    '200': '#bbf7d0',
                    '300': '#86efac',
                    '400': '#4ade80',
                    '500': '#22c55e',
                    '600': '#16a34a',
                    '700': '#15803d',
                    '800': '#166534',
                    '900': '#14532d',
                },
            }
        },
    },

    plugins: [forms],
};
