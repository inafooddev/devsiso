<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RWO Mobile Update' }}</title>

    {{-- Anti-FOUC: set theme from localStorage before render --}}
    <script>
        (function() {
            var t = localStorage.getItem('neon-theme') || 'neon-dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <!-- DaisyUI CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {}
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }

        /* Custom HSL theme variables for DaisyUI components */
        [data-theme="neon-dark"] {
            --p: 239 84% 67%; /* #6366f1 */
            --pc: 0 0% 100%;
            --s: 199 89% 60%; /* #38bdf8 */
            --sc: 0 0% 100%;
            --a: 161 84% 40%; /* #10b981 */
            --ac: 0 0% 100%;
            --n: 215 19% 35%; /* #475569 */
            --nc: 210 40% 98%; /* #f8fafc */
            --b1: 217 33% 17%; /* #1e293b */
            --b2: 222 47% 11%; /* #0f172a */
            --b3: 215 25% 27%; /* #334155 */
            --bc: 210 40% 98%; /* #f8fafc */
            --su: 142 71% 45%; /* #22c55e */
            --er: 0 84% 60%; /* #ef4444 */
        }

        [data-theme="neon-light"] {
            --p: 262 83% 58%; /* #7c3aed */
            --pc: 0 0% 100%;
            --s: 200 98% 39%; /* #0284c7 */
            --sc: 0 0% 100%;
            --a: 162 94% 30%; /* #059669 */
            --ac: 0 0% 100%;
            --n: 210 40% 96%; /* #f1f5f9 */
            --nc: 222 47% 11%; /* #0f172a */
            --b1: 0 0% 100%; /* #ffffff */
            --b2: 210 40% 98%; /* #f8fafc */
            --b3: 214 32% 91%; /* #e2e8f0 */
            --bc: 222 47% 11%; /* #0f172a */
            --su: 142 76% 36%; /* #16a34a */
            --er: 0 74% 50%; /* #dc2626 */
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-base-200 text-base-content min-h-screen antialiased" x-data="{
    theme: localStorage.getItem('neon-theme') || 'neon-dark',
    get isDark() { return this.theme === 'neon-dark'; },
    toggleTheme() {
        this.theme = this.theme === 'neon-dark' ? 'neon-light' : 'neon-dark';
        localStorage.setItem('neon-theme', this.theme);
        document.documentElement.setAttribute('data-theme', this.theme);
    }
}">
    
    <div class="min-h-screen flex flex-col">
        {{ $slot }}
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
