import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: { DEFAULT: '#285BC5', hover: '#214CA3', soft: '#EBF1FC' },
                ink: { DEFAULT: '#192B43', soft: '#748095', muted: '#97A0AF' },
                body: '#526279',
                navy: '#19365E',
                line: '#E6EBF2',
                canvas: '#F4F6F9',
                wash: '#F8FAFC',
                success: { DEFAULT: '#277A62', soft: '#EAF5EF' },
                warning: { DEFAULT: '#A76B1F', soft: '#FFF5E5' },
                danger: { DEFAULT: '#BA5151', soft: '#FCEEEE' },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};