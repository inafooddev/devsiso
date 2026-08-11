/** @type {import('tailwindcss').Config} */
import { createRequire } from 'module'
const require = createRequire(import.meta.url)

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
    "./resources/**/*.tsx",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
    "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        "neon-dark": {
          "primary": "#321fdb",          // CoreUI Primary
          "primary-content": "#ffffff",
          "secondary": "#9da5b1",        // CoreUI Secondary
          "secondary-content": "#ffffff",
          "accent": "#2eb85c",           // CoreUI Success/Accent
          "accent-content": "#ffffff",
          "neutral": "#4f5d73",          // CoreUI Dark
          "neutral-content": "#ffffff",

          // CoreUI Dark Mode Backgrounds
          "base-200": "#181924",         // CoreUI Dark BG
          "base-100": "#212631",         // CoreUI Dark Surface
          "base-300": "#212631",         // CoreUI Dark Sidebar

          "base-content": "#ffffff",     // Text

          "info": "#3399ff",             // CoreUI Info
          "success": "#2eb85c",          // CoreUI Success
          "warning": "#f9b115",          // CoreUI Warning
          "error": "#e55353",            // CoreUI Danger
        },
      },
      {
        "neon-light": {
          "primary": "#321fdb",          // CoreUI Primary
          "primary-content": "#ffffff",
          "secondary": "#9da5b1",        // CoreUI Secondary
          "secondary-content": "#ffffff",
          "accent": "#2eb85c",           // CoreUI Success/Accent
          "accent-content": "#ffffff",
          "neutral": "#4f5d73",          // CoreUI Dark
          "neutral-content": "#ffffff",
          "base-100": "#ffffff",         // Surface
          "base-200": "#ebedef",         // CoreUI Light BG
          "base-300": "#d8dbe0",         // CoreUI Light Border/Hover
          "base-content": "#4f5d73",     // CoreUI Dark text
          "info": "#3399ff",             // CoreUI Info
          "success": "#2eb85c",          // CoreUI Success
          "warning": "#f9b115",          // CoreUI Warning
          "error": "#e55353",            // CoreUI Danger
        },
      },
    ],
    darkTheme: "neon-dark",
    base: true,
    styled: true,
    utils: true,
    logs: false,
  },
}
