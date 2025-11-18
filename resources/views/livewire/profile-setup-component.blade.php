<div>
    <!-- Profile Setup Wizard -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50">
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
                                    <h1 class="text-xl font-bold text-gray-900">{{ $tenant->name }}</h1>
                                    <p class="text-xs text-orange-500 font-medium">Profile Setup</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button wire:click="skipSetup" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                            Skip for now
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Progress Steps -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-5xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute left-0 top-1/2 w-full h-0.5 bg-gray-200 -translate-y-1/2"></div>
                    <div class="absolute left-0 top-1/2 h-0.5 bg-blue-500 -translate-y-1/2 transition-all duration-500"
                        style="width: {{ (($currentStep - 1) / ($totalSteps - 1)) * 100 }}%"></div>

                    <!-- Steps -->
                    <div class="relative flex justify-between w-full">
                        @for ($i = 1; $i <= $totalSteps; $i++)
                            <div class="flex flex-col items-center">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $i <= $currentStep ? 'bg-blue-500' : 'bg-gray-200' }} text-white relative z-10 transition-all duration-300">
                                    @if ($i < $currentStep)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <span class="text-sm font-semibold">{{ $i }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-xs font-medium {{ $i <= $currentStep ? 'text-gray-900' : 'text-gray-400' }}">
                                        @if ($i == 1)
                                            Business Details
                                        @elseif($i == 2)
                                            Network Config
                                        @elseif($i == 3)
                                            Billing Setup
                                        @else
                                            Policies & Support
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

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

                <!-- Step 1: Business Details -->
                @if ($currentStep === 1)
                    <div wire:key="step-1">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Business Details</h3>
                            <p class="text-gray-600">Complete your business information</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Business Address *
                                </label>
                                <textarea wire:model="address" rows="3"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('address') border-red-300 @enderror"
                                    placeholder="Enter your complete business address"></textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Business Registration Number (Optional)</label>
                                    <input type="text" wire:model="businessRegistrationNumber"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="Registration/License number">
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Tax/VAT Number (Optional)</label>
                                    <input type="text" wire:model="taxNumber"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="Tax identification number">
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Timezone *</label>
                                <select wire:model="timezone"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('timezone') border-red-300 @enderror">
                                    <option value="UTC">UTC (GMT+0)</option>
                                    <option value="Africa/Kampala">East Africa Time (GMT+3)</option>
                                    <option value="Africa/Nairobi">East Africa Time - Nairobi (GMT+3)</option>
                                    <option value="Africa/Lagos">West Africa Time (GMT+1)</option>
                                    <option value="Africa/Cairo">Egypt Time (GMT+2)</option>
                                    <option value="America/New_York">Eastern Time (GMT-5/-4)</option>
                                    <option value="America/Los_Angeles">Pacific Time (GMT-8/-7)</option>
                                    <option value="Europe/London">Greenwich Mean Time (GMT+0/+1)</option>
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Business Hours -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-3 block">Business Hours (Optional)</label>
                                <div class="space-y-3">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                                            <div class="w-20">
                                                <span class="text-sm font-medium text-gray-700 capitalize">{{ $day }}</span>
                                            </div>
                                            <label class="flex items-center">
                                                <input type="checkbox" wire:model="businessHours.{{ $day }}.closed"
                                                    class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-600">Closed</span>
                                            </label>
                                            @if(!$businessHours[$day]['closed'])
                                                <div class="flex items-center space-x-2">
                                                    <input type="time" wire:model="businessHours.{{ $day }}.open"
                                                        class="px-3 py-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <span class="text-gray-400">to</span>
                                                    <input type="time" wire:model="businessHours.{{ $day }}.close"
                                                        class="px-3 py-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 2: Network Configuration -->
                @if ($currentStep === 2)
                    <div wire:key="step-2">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Network Configuration</h3>
                            <p class="text-gray-600">Configure your network settings for {{ ucfirst($tenant->type) }} services</p>
                        </div>

                        <div class="space-y-6">
                            @if($tenant->type === 'isp' || $tenant->type === 'both')
                                <!-- ISP Configuration -->
                                <div class="border border-blue-200 bg-blue-50 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-blue-900 mb-4">ISP Configuration</h4>

                                    <div class="space-y-5">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-3 block">Network Management System *</label>
                                            <div class="grid md:grid-cols-2 gap-3">
                                                @foreach(['mikrotik' => 'MikroTik RouterOS', 'ubiquiti' => 'Ubiquiti UniFi', 'cisco' => 'Cisco', 'other' => 'Other/Custom'] as $value => $label)
                                                    <label class="relative block">
                                                        <input type="radio" wire:model="networkSystem" value="{{ $value }}" class="sr-only peer">
                                                        <div class="border-2 rounded-lg p-3 cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-100 hover:border-gray-300 border-gray-200">
                                                            <span class="font-medium text-gray-900">{{ $label }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('networkSystem')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">RADIUS Server IP *</label>
                                                <input type="text" wire:model="radiusIp"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('radiusIp') border-red-300 @enderror"
                                                    placeholder="192.168.1.1">
                                                @error('radiusIp')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">RADIUS Port</label>
                                                <input type="text" wire:model="radiusPort"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                    placeholder="1812">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">RADIUS Secret *</label>
                                            <input type="password" wire:model="radiusSecret"
                                                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('radiusSecret') border-red-300 @enderror"
                                                placeholder="Enter RADIUS secret">
                                            @error('radiusSecret')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">Gateway IP (Optional)</label>
                                                <input type="text" wire:model="gateway"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                    placeholder="192.168.1.1">
                                            </div>

                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">NAS IP (Optional)</label>
                                                <input type="text" wire:model="nasIp"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                    placeholder="192.168.1.1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($tenant->type === 'hotspot' || $tenant->type === 'both')
                                <!-- Hotspot Configuration -->
                                <div class="border border-green-200 bg-green-50 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-green-900 mb-4">Hotspot Configuration</h4>

                                    <div class="space-y-5">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-3 block">Hotspot Type *</label>
                                            <div class="grid md:grid-cols-3 gap-3">
                                                @foreach(['hotel' => 'Hotel/Resort', 'cafe' => 'Cafe/Restaurant', 'public' => 'Public WiFi', 'office' => 'Office Building', 'retail' => 'Retail Store'] as $value => $label)
                                                    <label class="relative block">
                                                        <input type="radio" wire:model="hotspotType" value="{{ $value }}" class="sr-only peer">
                                                        <div class="border-2 rounded-lg p-3 cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-100 hover:border-gray-300 border-gray-200">
                                                            <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('hotspotType')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">Number of Access Points *</label>
                                                <input type="number" wire:model="accessPoints" min="1"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all @error('accessPoints') border-red-300 @enderror"
                                                    placeholder="Enter number">
                                                @error('accessPoints')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">Expected Daily Users</label>
                                                <input type="number" wire:model="dailyUsers" min="1"
                                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                                                    placeholder="Estimated users per day">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Captive Portal URL (Optional)</label>
                                            <input type="url" wire:model="captivePortalUrl"
                                                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                                                placeholder="https://portal.yoursite.com">
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-3 block">Authentication Methods</label>
                                            <div class="grid md:grid-cols-2 gap-3">
                                                @foreach(['voucher' => 'Voucher System', 'sms' => 'SMS OTP', 'social' => 'Social Media Login', 'email' => 'Email Verification'] as $key => $label)
                                                    <label class="flex items-center p-3 bg-white border border-gray-200 rounded-lg">
                                                        <input type="checkbox" wire:model="socialAuth" value="{{ $key }}"
                                                            class="w-4 h-4 text-green-500 border-gray-300 rounded focus:ring-green-500">
                                                        <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Step 3: Billing & Services -->
                @if ($currentStep === 3)
                    <div wire:key="step-3">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Billing & Services</h3>
                            <p class="text-gray-600">Configure your billing settings and services</p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Currency *</label>
                                    <select wire:model="currency"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('currency') border-red-300 @enderror">
                                        <option value="">Select currency</option>
                                        <option value="USD">USD - US Dollar</option>
                                        <option value="UGX">UGX - Ugandan Shilling</option>
                                        <option value="KES">KES - Kenyan Shilling</option>
                                        <option value="TZS">TZS - Tanzanian Shilling</option>
                                        <option value="RWF">RWF - Rwandan Franc</option>
                                        <option value="NGN">NGN - Nigerian Naira</option>
                                        <option value="GHS">GHS - Ghanaian Cedi</option>
                                        <option value="ZAR">ZAR - South African Rand</option>
                                        <option value="EUR">EUR - Euro</option>
                                        <option value="GBP">GBP - British Pound</option>
                                    </select>
                                    @error('currency')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Default Billing Cycle *</label>
                                    <select wire:model="billingCycle"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('billingCycle') border-red-300 @enderror">
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="semi-annually">Semi-Annually</option>
                                        <option value="annually">Annually</option>
                                    </select>
                                    @error('billingCycle')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-3 block">Services You'll Offer *</label>
                                <div class="grid md:grid-cols-2 gap-3">
                                    @if($tenant->type === 'isp' || $tenant->type === 'both')
                                        @foreach(['broadband' => 'Broadband Internet', 'fiber' => 'Fiber Optic', 'wireless' => 'Wireless Internet', 'dedicated' => 'Dedicated Lines'] as $key => $label)
                                            <label class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <input type="checkbox" wire:model="services" value="{{ $key }}"
                                                    class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    @endif

                                    @if($tenant->type === 'hotspot' || $tenant->type === 'both')
                                        @foreach(['guest_wifi' => 'Guest WiFi', 'premium_wifi' => 'Premium WiFi', 'event_wifi' => 'Event WiFi', 'corporate_wifi' => 'Corporate WiFi'] as $key => $label)
                                            <label class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                                                <input type="checkbox" wire:model="services" value="{{ $key }}"
                                                    class="w-4 h-4 text-green-500 border-gray-300 rounded focus:ring-green-500">
                                                <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                @error('services')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid md:grid-cols-3 gap-5">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Tax Rate (%)</label>
                                    <input type="number" wire:model="taxRate" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="18.00">
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Invoice Prefix</label>
                                    <input type="text" wire:model="invoicePrefix"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="INV">
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Auto-Suspend After (Days)</label>
                                    <input type="number" wire:model="autoSuspendDays" min="1"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="7">
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-3 block">Payment Methods You'll Accept</label>
                                <div class="grid md:grid-cols-2 gap-3">
                                    @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'card' => 'Credit/Debit Card', 'paypal' => 'PayPal', 'stripe' => 'Stripe'] as $key => $label)
                                        <label class="flex items-center p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                            <input type="checkbox" wire:model="paymentMethods" value="{{ $key }}"
                                                class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                                            <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 4: Policies & Support -->
                @if ($currentStep === 4)
                    <div wire:key="step-4">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Policies & Support</h3>
                            <p class="text-gray-600">Set up your support channels and policies</p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                        Support Email *
                                    </label>
                                    <input type="email" wire:model="supportEmail"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('supportEmail') border-red-300 @enderror"
                                        placeholder="support@yourcompany.com">
                                    @error('supportEmail')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Support Phone *
                                    </label>
                                    <input type="tel" wire:model="supportPhone"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('supportPhone') border-red-300 @enderror"
                                        placeholder="+256 700 000 000">
                                    @error('supportPhone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Privacy Policy URL (Optional)</label>
                                <input type="url" wire:model="privacyPolicyUrl"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="https://yourcompany.com/privacy">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Data Retention Period (Days)</label>
                                <input type="number" wire:model="dataRetentionDays" min="30" max="3650"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="365">
                                <p class="mt-1 text-xs text-gray-500">How long to keep customer data after account closure (minimum 30 days)</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-3 block">Notification Preferences</label>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="emailNotifications"
                                            class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3 text-sm text-gray-700">Enable email notifications to customers</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="smsNotifications"
                                            class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3 text-sm text-gray-700">Enable SMS notifications to customers</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="tosAccepted"
                                        class="w-5 h-5 mt-0.5 text-blue-500 border-blue-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="text-gray-900 font-medium">I accept the Terms of Service *</span>
                                        <p class="text-gray-600 text-sm mt-1">
                                            By completing this setup, you agree to our platform's terms of service and confirm that you have the authority to set up this business account.
                                        </p>
                                    </div>
                                </label>
                                @error('tosAccepted')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center mt-8">
                @if ($currentStep > 1)
                    <button wire:click="previousStep" wire:loading.attr="disabled" wire:target="previousStep"
                        class="flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous
                    </button>
                @else
                    <div></div>
                @endif

                @if ($currentStep < $totalSteps)
                    <button wire:click="nextStep" wire:loading.attr="disabled" wire:target="nextStep"
                        class="flex items-center px-8 py-3 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 focus:ring-4 focus:ring-blue-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Next Step
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                @else
                    <button wire:click="completeSetup" wire:loading.attr="disabled" wire:target="completeSetup"
                        {{ !$tosAccepted ? 'disabled' : '' }}
                        class="flex items-center px-8 py-3 {{ $tosAccepted ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed' }} text-white font-medium rounded-lg focus:ring-4 focus:ring-green-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="completeSetup" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="completeSetup" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span wire:loading.remove wire:target="completeSetup">Complete Setup</span>
                        <span wire:loading wire:target="completeSetup">Saving...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
