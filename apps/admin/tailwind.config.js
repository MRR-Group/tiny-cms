/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#DF0036',
          50: '#FFE5EC',
          100: '#FFCCD9',
          200: '#FF99B3',
          300: '#FF668C',
          400: '#FF3366',
          500: '#DF0036',
          600: '#B3002B',
          700: '#800020',
          800: '#4D0013',
          900: '#1A0006',
        },
        secondary: {
          DEFAULT: '#333333',
          50: '#F5F5F5',
          100: '#E0E0E0',
          200: '#CCCCCC',
          300: '#B3B3B3',
          400: '#999999',
          500: '#808080',
          600: '#666666',
          700: '#4D4D4D',
          800: '#333333',
          900: '#1A1A1A',
        },
      },
      fontFamily: {
        sans: ['Mitr', 'system-ui', 'sans-serif'],
        serif: ['Source Serif 4', 'Georgia', 'serif'],
      },
    },
  },
  plugins: [],
};
