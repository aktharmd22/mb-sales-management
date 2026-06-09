import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    // Stage colours are applied dynamically (funnel, kanban, badges, charts),
    // so make sure every variant survives the production purge.
    safelist: [
        ...['cold', 'warm', 'qualified', 'opportunity', 'proposal', 'won', 'lost'].flatMap((s) => [
            `bg-stage-${s}`,
            `bg-stage-${s}/10`,
            `bg-stage-${s}/20`,
            `text-stage-${s}`,
            `border-stage-${s}`,
            `ring-stage-${s}`,
            `from-stage-${s}`,
            `to-stage-${s}`,
        ]),
    ],

    theme: {
        extend: {
            fontFamily: {
                // DM Sans everywhere — display, body, and UI.
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    DEFAULT: '#0F1E2E',
                    50: '#F3F5F7',
                    100: '#E2E7EC',
                    700: '#1C3047',
                    800: '#152538',
                    900: '#0F1E2E',
                    950: '#0A1622',
                },
                canvas: '#F4F6F9',
                surface: '#FFFFFF',
                // Brand primary — bluish (matches the login). Swapped from teal.
                primary: {
                    DEFAULT: '#2563EB',
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#1D4ED8',
                    700: '#1E40AF',
                    800: '#1E3A8A',
                    900: '#172554',
                },
                accent: {
                    DEFAULT: '#F5A524',
                    50: '#FEF6E7',
                    100: '#FDE9C2',
                    200: '#FBD587',
                    300: '#F9C04D',
                    400: '#F7B033',
                    500: '#F5A524',
                    600: '#D6850A',
                    700: '#A8680A',
                    800: '#7C4D0C',
                },
                danger: {
                    DEFAULT: '#E5484D',
                    50: '#FDECEC',
                    100: '#FAD1D2',
                    400: '#EE6B6F',
                    500: '#E5484D',
                    600: '#CC2F34',
                    700: '#A8262A',
                },
                // Light-blue -> deep-indigo progression across the pipeline stages
                // (bluish theme); won = green and lost = red as semantic endpoints.
                stage: {
                    cold: '#60A5FA',        // Cold Visit        — light blue
                    warm: '#3B82F6',        // Warm Visit        — blue
                    qualified: '#2563EB',   // Qualified Meeting — blue-600
                    opportunity: '#4F46E5', // Opportunity       — indigo
                    proposal: '#4338CA',    // Proposal Stage    — deep indigo
                    won: '#16A34A',         // Won               — green
                    lost: '#E5484D',        // Lost              — red
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(15, 30, 46, 0.04), 0 4px 16px rgba(15, 30, 46, 0.06)',
                'card-hover': '0 2px 4px rgba(15, 30, 46, 0.06), 0 12px 28px rgba(15, 30, 46, 0.10)',
                pop: '0 8px 32px rgba(15, 30, 46, 0.14)',
            },
            borderRadius: {
                // Reduced roundedness across the whole UI (cards, inputs, buttons, modals).
                lg: '0.375rem',
                xl: '0.5rem',
                '2xl': '0.625rem',
                '3xl': '0.875rem',
            },
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(6px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'toast-in': {
                    '0%': { opacity: '0', transform: 'translateY(8px) scale(0.98)' },
                    '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
            },
            animation: {
                'fade-in-up': 'fade-in-up 0.35s ease-out both',
                'toast-in': 'toast-in 0.25s ease-out both',
            },
        },
    },

    plugins: [forms],
};
