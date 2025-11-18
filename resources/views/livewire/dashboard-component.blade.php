@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<!-- Dashboard Content -->
<div class="space-y-6">
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('message') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" 
                                class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Profile Incomplete Alert -->
    @if($showProfileIncompleteAlert && $currentTenant)
    <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-orange-800">
                    Complete Your Profile Setup
                </p>
                <div class="mt-2 text-sm text-orange-700">
                    <p>Your company profile is incomplete. Complete your setup to unlock all features and get the most out of your billing system.</p>
                </div>
                <div class="mt-4">
                    <div class="flex space-x-3">
                        <button wire:click="completeProfile" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Complete Profile Setup
                        </button>
                        <button wire:click="dismissAlert" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-orange-700 bg-transparent hover:bg-orange-100 rounded-lg transition-colors duration-200">
                            Don't show again
                        </button>
                    </div>
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button wire:click="dismissAlert" 
                            class="inline-flex rounded-md p-1.5 text-orange-500 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-orange-50 focus:ring-orange-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard</h1>
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <select wire:model="selectedTenant"
                class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
            
            <!-- User Menu -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}" 
                         class="w-10 h-10 rounded-full border" alt="User Avatar" />
                    <span class="hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                        <div class="font-medium">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="text-gray-500">{{ auth()->user()->email ?? '' }}</div>
                    </div>
                    <button wire:click="logout" 
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- Top Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all border-l-4 border-orange-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Services</p>
                        <p class="text-xl md:text-2xl lg:text-3xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['total_services'] ?? 0 }}</p>
                        <span class="text-orange-500 text-xs md:text-sm font-medium">↑ 12% from last month</span>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all border-l-4 border-green-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active Services</p>
                        <p class="text-xl md:text-2xl lg:text-3xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['active_services'] ?? 0 }}</p>
                        <span class="text-green-500 text-xs md:text-sm font-medium">↑ 5% from last month</span>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all border-l-4 border-blue-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Hotspot Services</p>
                        <p class="text-xl md:text-2xl lg:text-3xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['hotspot_services'] ?? 0 }}</p>
                        <span class="text-red-500 text-xs md:text-sm font-medium">↓ 2% from last month</span>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all border-l-4 border-purple-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Broadband Services</p>
                        <p class="text-xl md:text-2xl lg:text-3xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['broadband_services'] ?? 0 }}</p>
                        <span class="text-orange-500 text-xs md:text-sm font-medium">↑ 8% from last month</span>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
            <!-- Chart -->
            <div class="bg-white p-4 md:p-6 rounded-xl shadow xl:col-span-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                    <h2 class="text-lg font-semibold text-gray-700">Revenue Trend</h2>
                    <div wire:loading.delay class="flex items-center text-orange-500 text-sm">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </div>
                </div>
                <div class="relative h-64 md:h-80">
                    <canvas id="revenueChart" wire:ignore></canvas>
                    <div wire:loading.delay class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 mt-2">Loading chart data...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="space-y-4 md:space-y-6">
                <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Vouchers</p>
                            <p class="text-xl md:text-2xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['total_vouchers'] ?? 0 }}</p>
                        </div>
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Unused Vouchers</p>
                            <p class="text-xl md:text-2xl font-bold mt-1 md:mt-2 text-gray-800">{{ $stats['unused_vouchers'] ?? 0 }}</p>
                        </div>
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-xl shadow hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Monthly Revenue</p>
                            <p class="text-xl md:text-2xl font-bold mt-1 md:mt-2 text-gray-800">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
                            <span class="text-orange-500 text-xs md:text-sm font-medium">↑ This month</span>
                        </div>
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue ($)',
                        data: [500, 700, 800, 650, 900, 1200],
                        borderColor: '#E6801E',
                        backgroundColor: 'rgba(230, 128, 30, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#E6801E',
                        pointBorderColor: '#E6801E',
                        pointHoverBackgroundColor: '#D97016',
                        pointHoverBorderColor: '#D97016'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: window.innerWidth < 768 ? 12 : 14
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: window.innerWidth < 768 ? 3 : 4,
                            hoverRadius: window.innerWidth < 768 ? 5 : 6
                        }
                    }
                }
            });
        }
    });
</script>
