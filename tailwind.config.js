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
                    red:   '#D61B23',
                    gold:  '#A98539',
                    cream: '#FFF8F0',
                    dark:  '#1A1A1A',
                },
            },
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                dinor: '0 8px 24px rgba(214, 27, 35, 0.15)',
            },
        },
    },
};
