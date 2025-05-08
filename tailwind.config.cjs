/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,vue}',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {
            colors: {
                navy: '#0A2240',
                teal: '#007C91',
            },
        },
    },
    // This ensures default utilities are available
    presets: [
        require('tailwindcss/preset')
    ],
    plugins: [],
};
