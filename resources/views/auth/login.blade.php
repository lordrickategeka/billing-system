<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-orange-50 via-white to-blue-50">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-2xl font-bold text-gray-900">BASH</h1>
                        <p class="text-sm text-orange-500 font-medium">Digitalizing Tech</p>
                    </div>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">Sign in to your account</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('register') }}" class="font-medium text-orange-600 hover:text-orange-500">
                    create a new account
                </a>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                        <div class="mt-2">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-500 sm:text-sm sm:leading-6 @error('email') ring-red-300 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mt-6">
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                        <div class="mt-2">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-500 sm:text-sm sm:leading-6 @error('password') ring-red-300 @enderror">
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between mt-6">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" 
                                   class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                            <label for="remember_me" class="ml-3 block text-sm leading-6 text-gray-700">Remember me</label>
                        </div>

                        <div class="text-sm leading-6">
                            <a href="#" class="font-semibold text-orange-600 hover:text-orange-500">Forgot password?</a>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="flex w-full justify-center rounded-md bg-orange-500 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors">
                            Sign in
                        </button>
                    </div>
                </form>

                <!-- Additional Options -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm font-medium leading-6">
                            <span class="bg-white px-6 text-gray-900">Need help?</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center space-x-4 text-sm">
                        <a href="#" class="text-orange-600 hover:text-orange-500 font-medium">Contact Support</a>
                        <span class="text-gray-300">|</span>
                        <a href="#" class="text-orange-600 hover:text-orange-500 font-medium">Documentation</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} BASH Digitalizing Tech. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
