<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
     <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         [x-cloak] { display: none !important; }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-animated min-h-screen flex items-center justify-center p-4 font-sans">
    {{ $slot }}

 <script>
        // Simple script to toggle checkbox icon for preview
        const checkbox = document.getElementById('remember');
        const checkIcon = document.getElementById('check-icon');
        checkbox.addEventListener('change', () => {
            if(checkbox.checked) checkIcon.classList.remove('hidden');
            else checkIcon.classList.add('hidden');
        });
    </script>
</body>
</html>