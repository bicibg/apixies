/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,vue}',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {
        },
    },
    presets: [
        require('tailwindcss/preset')
    ],
    plugins: [],
};
