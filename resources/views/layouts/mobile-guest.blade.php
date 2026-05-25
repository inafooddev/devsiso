<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RWO Mobile Update' }}</title>

    {{-- Anti-FOUC: set theme to light --}}
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'light');
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
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen antialiased" x-data="{
    theme: 'light',
    get isDark() { return false; },
    toggleTheme() {
        // Strictly light theme
    }
}">
    
    <div class="min-h-screen flex flex-col">
        {{ $slot }}
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                    .catch(err => console.error('Service Worker registration failed:', err));
            });
        }
    </script>
</body>
</html>
