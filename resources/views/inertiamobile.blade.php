<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Sales RWO - Photo Portal</title>
    <meta name="theme-color" content="#ffffff">
    
    <!-- Leaflet CSS and JS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app-inertia.jsx'])
    @inertiaHead
</head>
<body class="bg-slate-50 antialiased overflow-x-hidden selection:bg-primary/30 font-sans">
    @inertia
</body>
</html>
