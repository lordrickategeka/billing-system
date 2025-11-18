<div>
    <!-- Simplified Registration Form -->
    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
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
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex max-w-6xl mx-auto px-4 py-8">
            <!-- Left Sidebar -->
            <div class="hidden lg:block w-1/3 pr-8">
                <div class="sticky top-8">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Get Started</h2>
                        <h3 class="text-3xl font-bold text-orange-500 mb-4">In Minutes</h3>
                        <p class="text-gray-600 text-base leading-relaxed">
                            Create your account and we'll help you set up your ISP or Hotspot management system step by step.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Quick registration</p>
                                <p class="text-xs text-gray-500">Get started in under 2 minutes</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Secure & encrypted</p>
                                <p class="text-xs text-gray-500">Your data is protected and secure</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Progressive setup</p>
                                <p class="text-xs text-gray-500">Complete configuration after registration</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <p class="text-sm text-gray-600 mb-3">Follow us for updates & tips</p>
                        <div class="flex space-x-3">
                            <a href="#" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 lg:max-w-2xl">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Create Your Account</h3>
                        <p class="text-gray-600">Join thousands of businesses managing their networks with confidence</p>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-800">{{ session('message') }}</p>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="space-y-6">
                            <!-- Business Information -->
                            <div class="space-y-5">
                                <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Business Information</h4>

                                <div>
                                    <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        Company Name *
                                    </label>
                                    <input type="text" wire:model="companyName"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('companyName') border-red-300 @enderror"
                                        placeholder="Enter your company name">
                                    @error('companyName')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-3 block flex items-center">
                                        Business Type *
                                        <div wire:loading.delay wire:target="updateTenantType" class="ml-2">
                                            <svg class="animate-spin h-4 w-4 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </label>
                                    <p class="text-xs text-gray-500 mb-4">Select ISP, Hotspot, or both if you offer both services</p>
                                    <div class="space-y-3" wire:loading.class="opacity-60" wire:target="updateTenantType">
                                        <label class="relative block cursor-pointer" wire:loading.class="cursor-not-allowed" wire:target="updateTenantType">
                                            <input type="checkbox" 
                                                   wire:model.live="isIspSelected" 
                                                   wire:change="updateTenantType"
                                                   wire:loading.attr="disabled"
                                                   wire:target="updateTenantType"
                                                   class="sr-only peer">
                                            <div class="border-2 rounded-lg p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-gray-300 border-gray-200">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900">Internet Service Provider (ISP)</h4>
                                                        <p class="text-sm text-gray-600">Manage subscribers, bandwidth, and billing</p>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="w-5 h-5 border-2 rounded {{ $isIspSelected ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }} flex items-center justify-center">
                                                            @if($isIspSelected)
                                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="relative block cursor-pointer" wire:loading.class="cursor-not-allowed" wire:target="updateTenantType">
                                            <input type="checkbox" 
                                                   wire:model.live="isHotspotSelected" 
                                                   wire:change="updateTenantType"
                                                   wire:loading.attr="disabled"
                                                   wire:target="updateTenantType"
                                                   class="sr-only peer">
                                            <div class="border-2 rounded-lg p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-gray-300 border-gray-200">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900">Hotspot Provider</h4>
                                                        <p class="text-sm text-gray-600">Manage WiFi hotspots and guest access</p>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="w-5 h-5 border-2 rounded {{ $isHotspotSelected ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }} flex items-center justify-center">
                                                            @if($isHotspotSelected)
                                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- Selected type display -->
                                        @if($tenantType)
                                            <div class="mt-3 p-3 bg-gray-50 rounded-lg border">
                                                <p class="text-sm font-medium text-gray-700">Selected Business Type:</p>
                                                <p class="text-sm text-orange-600 font-semibold mt-1">
                                                    @if($tenantType === 'both')
                                                        Both ISP & Hotspot Provider
                                                    @elseif($tenantType === 'isp')
                                                        Internet Service Provider (ISP)
                                                    @elseif($tenantType === 'hotspot')
                                                        Hotspot Provider
                                                    @endif
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    @error('tenantType')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            Phone Number *
                                        </label>
                                        <input type="tel" wire:model="phoneNumber"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('phoneNumber') border-red-300 @enderror"
                                            placeholder="+256 700 000 000">
                                        @error('phoneNumber')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Country *
                                        </label>
                                        <select wire:model="country"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('country') border-red-300 @enderror">
                                            <option value="">Select your country</option>
                                            <option value="UG">Uganda</option>
                                            <option value="KE">Kenya</option>
                                            <option value="TZ">Tanzania</option>
                                            <option value="RW">Rwanda</option>
                                            <option value="NG">Nigeria</option>
                                            <option value="GH">Ghana</option>
                                            <option value="ZA">South Africa</option>
                                            <option value="US">United States</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="CA">Canada</option>
                                            <option value="AU">Australia</option>
                                        </select>
                                        @error('country')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Administrator Account -->
                            <div class="space-y-5">
                                <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Administrator Account</h4>

                                <div class="grid md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 mb-2 block">First Name *</label>
                                        <input type="text" wire:model="firstName"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('firstName') border-red-300 @enderror"
                                            placeholder="John">
                                        @error('firstName')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-700 mb-2 block">Last Name *</label>
                                        <input type="text" wire:model="lastName"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('lastName') border-red-300 @enderror"
                                            placeholder="Doe">
                                        @error('lastName')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                        Email Address *
                                    </label>
                                    <input type="email" wire:model="adminEmail"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('adminEmail') border-red-300 @enderror"
                                        placeholder="admin@yourcompany.com">
                                    @error('adminEmail')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 mb-2 block">Password *</label>
                                        <div class="relative">
                                            <input type="{{ $showPassword ? 'text' : 'password' }}" wire:model="password"
                                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('password') border-red-300 @enderror"
                                                placeholder="Enter password">
                                            <button type="button" wire:click="togglePasswordVisibility"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($showPassword)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414l-2.829 2.829m14.138-2.829l-2.829 2.829M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043.932M3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-.932m0 0L21 21"></path>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    @endif
                                                </svg>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror

                                        @if($password)
                                            <!-- Password Strength Indicator -->
                                            <div class="mt-2">
                                                <div class="flex items-center justify-between text-xs mb-1">
                                                    <span class="text-gray-600">Password strength</span>
                                                    <span class="@if($passwordStrength < 50) text-red-600 @elseif($passwordStrength < 75) text-yellow-600 @else text-green-600 @endif">
                                                        @if($passwordStrength < 50) Weak @elseif($passwordStrength < 75) Good @else Strong @endif
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full transition-all duration-300 @if($passwordStrength < 50) bg-red-500 @elseif($passwordStrength < 75) bg-yellow-500 @else bg-green-500 @endif" style="width: {{ $passwordStrength }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-700 mb-2 block">Confirm Password *</label>
                                        <div class="relative">
                                            <input type="{{ $showPasswordConfirm ? 'text' : 'password' }}" wire:model="passwordConfirmation"
                                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all @error('password') border-red-300 @enderror"
                                                placeholder="Confirm password">
                                            <button type="button" wire:click="togglePasswordConfirmVisibility"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($showPasswordConfirm)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414l-2.829 2.829m14.138-2.829l-2.829 2.829M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043.932M3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-.932m0 0L21 21"></path>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    @endif
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="acceptTerms"
                                        class="w-5 h-5 mt-0.5 text-orange-500 border-orange-300 rounded focus:ring-orange-500">
                                    <div class="ml-3">
                                        <span class="text-gray-900 font-medium">I agree to the Terms of Service and Privacy Policy *</span>
                                        <p class="text-gray-600 text-sm mt-1">
                                            By creating an account, you agree to our
                                            <a href="#" class="text-orange-600 hover:underline">terms of service</a> and
                                            <a href="#" class="text-orange-600 hover:underline">privacy policy</a>.
                                        </p>
                                    </div>
                                </label>
                                @error('acceptTerms')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                {{ !$acceptTerms ? 'disabled' : '' }}
                                class="w-full flex items-center justify-center px-8 py-4 {{ $acceptTerms ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-400 cursor-not-allowed' }} text-white font-semibold rounded-lg focus:ring-4 focus:ring-orange-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <!-- Loading Spinner -->
                                <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>

                                <span wire:loading.remove wire:target="submit">Create Account & Get Started</span>
                                <span wire:loading wire:target="submit">Creating Your Account...</span>
                            </button>

                            <p class="text-center text-sm text-gray-600 mt-4">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-orange-600 font-medium hover:underline">Sign in here</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-100 py-8 mt-16">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-gray-600 mb-4">Need help? We're here to assist you every step of the way.</p>
                <div class="flex justify-center space-x-6 text-sm">
                    <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">Contact Support</a>
                    <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">Documentation</a>
                    <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">Video Tutorials</a>
                </div>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} BASH Digitalizing Tech. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</div>
