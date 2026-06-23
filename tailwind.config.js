import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/@inertiaui/modal-vue/src/**/*.{js,vue}',
    ],

    theme: {
        extend: {
            colors: {
                istaht: {
                    navy: '#1B2D6B',
                    blue: '#00AEEF',
                    cyan: '#00AEEF',
                    turquoise: '#14b8a6',
                    green: '#4CAF50',
                    amber: '#F5A623',
                    orange: '#F5A623',
                    red: '#E53935',
                    ink: '#102a43',
                    mist: '#F8FAFC',
                },
            },
            boxShadow: {
                soft: '0 12px 28px -18px rgba(27, 45, 107, 0.38)',
                panel: '0 20px 50px -32px rgba(15, 23, 42, 0.45)',
                lift: '0 18px 36px -24px rgba(27, 45, 107, 0.45)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'soft-pulse': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '.72' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-700px 0' },
                    '100%': { backgroundPosition: '700px 0' },
                },
                'scale-in': {
                    '0%': { opacity: '0', transform: 'scale(.98)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .35s ease-out both',
                'soft-pulse': 'soft-pulse 1.6s ease-in-out infinite',
                shimmer: 'shimmer 1.8s linear infinite',
                'scale-in': 'scale-in .22s ease-out both',
            },
        },
    },

    plugins: [forms],
};
