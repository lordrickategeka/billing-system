<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Billing System') }}</title>

    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-900">

    <div class="flex min-h-screen bg-gray-50" x-data="{ sidebarOpen: false }">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="lg:hidden fixed inset-0 z-40 bg-gray-600 bg-opacity-75"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r p-6 flex flex-col transform lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               @click.away="sidebarOpen = false">
            <!-- Company Logo/Brand -->
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-gray-900">BASH</h1>
                        <p class="text-xs text-orange-500 font-medium">Digitalizing Tech</p>
                    </div>
                </div>
                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <nav class="space-y-2">
                <!-- User Management Dropdown -->

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- ISP Management Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('isp.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 rounded-lg {{ request()->routeIs('isp.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50' }} font-medium group">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                            </svg>
                            <span>ISP Management</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('isp.customers') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('isp.customers') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span>Customers</span>
                        </a>
                        <a href="{{ route('isp.services') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('isp.services') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Services</span>
                        </a>
                        <a href="{{ route('isp.network') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('isp.network') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                            <span>Network Devices</span>
                        </a>
                        <a href="{{ route('isp.billing') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('isp.billing') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            <span>Billing & Invoices</span>
                        </a>
                    </div>
                </div>

                <!-- Hotspot Vouchers -->
                <a href="{{ route('vouchers.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('vouchers') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 21a2 2 0 002-2v-3a1 1 0 00-1-1H5a1 1 0 00-1 1v3a2 2 0 002 2h0zm0 0h14a2 2 0 002-2v-3a1 1 0 00-1-1h-1a1 1 0 00-1 1v3a2 2 0 01-2 2H5z"></path>
                    </svg>
                    <span>Hotspot Vouchers</span>
                </a>

                <!-- Products & Plans -->
                <a href="{{ route('products') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('products') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                    </svg>
                    <span>Products & Plans</span>
                </a>

                <!-- Reports -->
                                <!-- Reports -->
                <a href="{{ route('reports') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('reports') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Reports</span>
                </a>

                <!-- Settings -->
                <a href="{{ route('settings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>

                 <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 rounded-lg {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50' }} font-medium group">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>User Management</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('users.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <span>Users</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('roles.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <span>Roles</span>
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('permissions.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <span>Permissions</span>
                        </a>
                    </div>
                </div>
            </nav>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-4"></div>

                <!-- Subscriptions -->
                <a href="{{ route('subscriptions') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('subscriptions') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Subscriptions</span>
                </a>

                <a href="{{ route('radius-server') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('radius-server') ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Radius-Server</span>
                </a>
            </nav>

            <div class="mt-auto pt-6 space-y-4">
                <!-- Profile Completion Widget -->
                @auth
                    @if(auth()->user()->tenant && !auth()->user()->tenant->isProfileComplete() && auth()->user()->tenant->wasSetupSkipped())
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="flex items-start space-x-2">
                                <div class="flex-shrink-0">
                                    <svg class="h-4 w-4 text-orange-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-orange-800">Profile Incomplete</p>
                                    <p class="text-xs text-orange-600 mt-1">Complete your setup to unlock all features</p>
                                    <a href="{{ route('profile.setup') }}"
                                       class="inline-flex items-center text-xs text-orange-700 hover:text-orange-800 font-medium mt-2">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Complete Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center space-x-3 text-gray-500 hover:text-red-600 w-full text-left px-3 py-2 rounded-lg hover:bg-red-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>

                {{-- @livewire('radius-component') --}}
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-0">
            <!-- Profile Completion Alert Bar (Always visible when profile is incomplete) -->
            @auth
                @if(auth()->user()->tenant && !auth()->user()->tenant->isProfileComplete() && auth()->user()->tenant->wasSetupSkipped())
                    <div class="bg-orange-100 border-b border-orange-200" x-data="{ dismissed: false }">
                        <div class="px-4 py-3" x-show="!dismissed">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-orange-800">Complete Your Profile Setup</p>
                                        <p class="text-xs text-orange-600">Your company profile is incomplete. Complete your setup to unlock all features.</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('profile.setup') }}"
                                       class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-md transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Complete Setup
                                    </a>
                                    <button @click="dismissed = true"
                                            class="inline-flex items-center p-1.5 rounded-md text-orange-400 hover:text-orange-600 hover:bg-orange-200 transition-colors"
                                            title="Close (will reappear on page reload)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Mobile Menu Button -->
            <div class="lg:hidden p-4">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 rounded-md bg-white shadow-sm border border-gray-200 hover:bg-gray-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Page Content -->
            <div class="p-4 md:p-6 lg:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @vite('resources/js/app.js')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Auto-refresh components every 30 seconds
        setInterval(() => {
            Livewire.emit('refresh');
        }, 30000);
    </script>
</body>

</html>
