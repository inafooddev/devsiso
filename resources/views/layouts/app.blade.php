<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }}</title>

    {{-- Anti-FOUC: set theme from localStorage before render --}}
    <script>
        (function() {
            var applyTheme = function() {
                var t = localStorage.getItem('neon-theme') || 'neon-light';
                document.documentElement.setAttribute('data-theme', t);
            };
            applyTheme();
            document.addEventListener('livewire:navigated', applyTheme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
         /* Responsive UI Scaling */
         html { font-size: 100%; } /* 1rem = 16px at >=1536px */
         @media (max-width: 1536px) { html { font-size: 87.5%; } } /* 1rem = 14px at <1536px */
         @media (max-width: 1280px) { html { font-size: 75%; } } /* 1rem = 12px at <1280px */
         
         [x-cloak] { display: none !important; }
         /* Fix konflik CSS transition antara Tailwind/DaisyUI dan Leaflet saat zoom/pan/cluster */
         .leaflet-container * { transition: none; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body x-data="{
    theme: localStorage.getItem('neon-theme') || 'neon-light',
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
        <div class="drawer-content flex flex-col h-screen overflow-hidden">
            {{-- Navbar --}}
            <x-navbar :title="$title ?? 'Dashboard'" />

            {{-- Page Content --}}
            <main class="flex-1 overflow-hidden bg-base-200 p-3 md:p-4 lg:p-6 flex flex-col relative">
                <div class="w-full flex-1 flex flex-col min-h-0 min-w-0 h-full">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer --}}
            {{-- <x-footer /> --}}
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-40">
            <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <x-sidebar />
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
    <x-global-loading />
</body>
</html>