/** @type {import('tailwindcss').Config} */
import { createRequire } from 'module'
const require = createRequire(import.meta.url)

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        "neon-dark": {
          "primary": "#6366f1",          // Indigo 500 (premium SaaS primary)
          "primary-content": "#ffffff",
          "secondary": "#38bdf8",        // Sky 400
          "secondary-content": "#ffffff",
          "accent": "#10b981",           // Emerald 500
          "accent-content": "#ffffff",
          "neutral": "#475569",          // Slate 600
          "neutral-content": "#f8fafc",  // Slate 50

          // Depth Hierarchy (Darkest to Lightest)
          "base-200": "#0f172a",         // Slate 900 (Main Canvas Background)
          "base-100": "#1e293b",         // Slate 800 (Card / Container Surface)
          "base-300": "#334155",         // Slate 700 (Borders, Headers, Hover States)

          "base-content": "#f8fafc",     // Slate 50 (Text)

          "info": "#0ea5e9",             // Sky 500
          "success": "#22c55e",          // Green 500
          "warning": "#f59e0b",          // Amber 500
          "error": "#ef4444",            // Red 500
        },
      },
      {
        "neon-light": {
          "primary": "#7c3aed",
          "primary-content": "#ffffff",
          "secondary": "#0284c7",
          "secondary-content": "#ffffff",
          "accent": "#059669",
          "accent-content": "#ffffff",
          "neutral": "#f1f5f9",
          "neutral-content": "#0f172a",
          "base-100": "#ffffff",
          "base-200": "#f8fafc",
          "base-300": "#e2e8f0",
          "base-content": "#0f172a",
          "info": "#0ea5e9",
          "success": "#16a34a",
          "warning": "#d97706",
          "error": "#dc2626",
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