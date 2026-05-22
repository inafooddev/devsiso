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
    <style>
         [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
