<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hotspot ISP Billing')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Tailwind CSS is included via Vite/Laravel Mix -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
<body>
    <nav>
        <!-- Navigation bar content -->
    </nav>
    <main class="container mt-4">
        @yield('content')
    </main>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- No Bootstrap JS needed for Tailwind -->
    @livewireScripts
</body>
</html>
