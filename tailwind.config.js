import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                serif: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
                garamond: ['"Cormorant Garamond"', 'serif'],
                script: ['"Great Vibes"', 'cursive'],
                brush: ['"Alex Brush"', 'cursive'],
                cinzel: ['"Cinzel"', 'serif'],
            },
            colors: {
                brand: {
                    50: '#fdf8f6',
                    100: '#f2e8e5',
                    200: '#eaddd7',
                    300: '#e0cec7',
                    400: '#d2bab0',
                    500: '#bfa094',
                    600: '#a18072',
                    700: '#846356',
                    800: '#6d4f43',
                    900: '#5a4137',
                },
                sage: {
                    50: '#f4f6f4',
                    100: '#e5eae5',
                    200: '#ccd7cd',
                    300: '#a7bca9',
                    400: '#7e9c81',
                    500: '#5f8063',
                    600: '#4a654d',
                    700: '#3c513e',
                    800: '#324234',
                    900: '#2b372c',
                },
                gold: {
                    50: '#fbf9f1',
                    100: '#f5f0dd',
                    200: '#ebe0be',
                    300: '#deca96',
                    400: '#d2b16e',
                    500: '#c59a4f',
                    600: '#b07f3f',
                    700: '#8d6034',
                    800: '#744d30',
                    900: '#61402c',
                }
            }
        },
    },
    plugins: [],
};
