import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './public/spotter/js/*.js',
    ],

    safelist: [
        'xl:ml-[80px]',
        'xl:ml-[280px]',
        'xl:w-[64px]',
        'xl:w-[280px]',
        'w-[280px]',
        // Spotter PWA dynamic classes (Alpine x-bind)
        'bg-yellow-400', 'bg-green-400', 'bg-red-400', 'bg-red-500', 'bg-orange-400', 'bg-gray-700', 'bg-gray-600',
        'text-yellow-400', 'text-green-400', 'text-red-400', 'text-orange-400', 'text-gray-300', 'text-gray-400', 'text-gray-500', 'text-gray-600',
        'border-yellow-400', 'border-green-400', 'border-red-400', 'border-gray-600', 'border-gray-700',
        'bg-yellow-400/10', 'bg-green-400/10', 'bg-red-400/10', 'bg-orange-400/10', 'bg-gray-700/50',
        'border-yellow-400/30', 'border-green-400/30', 'border-red-400/30', 'border-red-400/50', 'border-green-400/50',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
