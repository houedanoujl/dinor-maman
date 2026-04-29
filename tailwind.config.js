/**
 * Tailwind v4 utilise une configuration CSS-first (voir resources/css/app.css).
 * Ce fichier est conservé pour les outils tiers et la rétro-compatibilité.
 */
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                dinor: {
                    red:   '#CE1126',
                    gold:  '#AA812A',
                    cream: '#F9FAFB',
                    dark:  '#1A1A1A',
                },
            },
            fontFamily: {
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
            },
            boxShadow: {
                dinor: '0 10px 30px rgba(206, 17, 38, 0.15)',
            },
        },
    },
};
