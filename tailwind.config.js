/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // WordPress admin colors (dynamic via CSS variables)
        'wp-admin': {
          'primary': 'var(--wp-admin-primary, #2271b1)',
          'secondary': 'var(--wp-admin-secondary, #135e96)',
          'highlight': 'var(--wp-admin-highlight, #043f54)',
          'accent': 'var(--wp-admin-accent, #0a4b78)',
        },
        // Legacy colors for backward compatibility
        'wp-blue': '#0073aa',
        'wp-blue-dark': '#005177',
        'wp-gray': '#f1f1f1',
        'wp-gray-dark': '#ddd',
        // Additional admin-compatible colors
        'blue': {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: 'var(--wp-admin-primary, #3b82f6)',
          600: 'var(--wp-admin-secondary, #2563eb)',
          700: 'var(--wp-admin-highlight, #1d4ed8)',
          800: 'var(--wp-admin-accent, #1e40af)',
          900: '#1e3a8a',
        },
      }
    },
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  }
}