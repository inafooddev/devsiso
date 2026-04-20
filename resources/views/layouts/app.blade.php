<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }}</title>

    {{-- Anti-FOUC: set theme from localStorage before render --}}
    <script>
        (function() {
            var t = localStorage.getItem('neon-theme') || 'neon-dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
         [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body x-data="{
    theme: localStorage.getItem('neon-theme') || 'neon-dark',
    get isDark() { return this.theme === 'neon-dark'; },
    toggleTheme() {
        this.theme = this.theme === 'neon-dark' ? 'neon-light' : 'neon-dark';
        localStorage.setItem('neon-theme', this.theme);
        document.documentElement.setAttribute('data-theme', this.theme);
    }
}">
    <div class="drawer lg:drawer-open" x-data="{ sidebarOpen: true }">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        {{-- Main Content Area --}}
        <div class="drawer-content flex flex-col min-h-screen">
            {{-- Navbar --}}
            <x-navbar :title="$title ?? 'Dashboard'" />

            {{-- Page Content --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-base-200 p-6">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <x-footer />
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-40">
            <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <x-sidebar />
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>