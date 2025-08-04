/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'wp-blue': '#0073aa',
        'wp-blue-dark': '#005177',
        'wp-gray': '#f1f1f1',
        'wp-gray-dark': '#ddd',
      }
    },
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  }
}