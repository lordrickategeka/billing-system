<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Billing System') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <h1 class="text-xl font-semibold text-gray-800">
                    {{ $title ?? 'Dashboard' }}
                </h1>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 max-w-7xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @vite('resources/js/app.js')
</body>
</html>
