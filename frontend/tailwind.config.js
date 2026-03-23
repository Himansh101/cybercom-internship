/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: '#2563eb', hover: '#1d4ed8', light: '#dbeafe' },
        success: { DEFAULT: '#16a34a', light: '#dcfce7' },
        danger:  { DEFAULT: '#dc2626', light: '#fee2e2' },
      },
    },
  },
  plugins: [],
};
