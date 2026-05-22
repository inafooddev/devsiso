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
            var t = localStorage.getItem('rwo-mobile-theme') || 'light';
            if (t === 'neon-dark') t = 'dark';
            if (t === 'neon-light') t = 'light';
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
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-base-200 text-base-content min-h-screen antialiased" x-data="{
    theme: (function() {
        var t = localStorage.getItem('rwo-mobile-theme') || 'light';
        if (t === 'neon-dark') t = 'dark';
        if (t === 'neon-light') t = 'light';
        return t;
    })(),
    get isDark() { return this.theme === 'dark'; },
    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('rwo-mobile-theme', this.theme);
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
