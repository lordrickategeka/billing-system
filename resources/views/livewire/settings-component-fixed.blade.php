<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                <p class="text-gray-600 mt-1">Manage your account and system configuration</p>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    {{ $tenant->type === 'both' ? 'ISP & Hotspot' : ucfirst($tenant->type) }} Account
                </span>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <p class="text-green-800 font-medium">Settings saved successfully!</p>
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="setActiveTab('general')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'general' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    General
                </button>

                @if($tenant->type === 'isp' || $tenant->type === 'both')
                <button wire:click="setActiveTab('technical')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'technical' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    Network & RADIUS
                </button>
                @endif

                @if($tenant->type === 'hotspot' || $tenant->type === 'both')
                <button wire:click="setActiveTab('hotspot')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'hotspot' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 717.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    Hotspot Config
                </button>
                @endif

                <button wire:click="setActiveTab('billing')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'billing' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Billing Features
                </button>

                <button wire:click="setActiveTab('notifications')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'notifications' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM5 7v10l5-5-5-5z"></path>
                    </svg>
                    Notifications
                </button>

                <button wire:click="setActiveTab('security')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'security' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Security
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Settings -->
            @if($activeTab === 'general')
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                            <input type="text" wire:model="companyName"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            @error('companyName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Email *</label>
                            <input type="email" wire:model="businessEmail"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            @error('businessEmail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" wire:model="phoneNumber"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <select wire:model="country"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Select country...</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="UK">United Kingdom</option>
                                <option value="AU">Australia</option>
                                <option value="DE">Germany</option>
                                <option value="FR">France</option>
                                <option value="IN">India</option>
                                <option value="BR">Brazil</option>
                            </select>
                            @error('country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Address *</label>
                            <textarea wire:model="businessAddress" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                      placeholder="Enter complete business address..."></textarea>
                            @error('businessAddress') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end">
                        <button wire:click="saveGeneralSettings"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save General Settings
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Technical Settings (ISP) -->
            @if($activeTab === 'technical')
                @if($tenant->type === 'isp' || $tenant->type === 'both')
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800 mb-1">Required for ISP Services</h4>
                                <p class="text-sm text-blue-700">RADIUS server configuration is mandatory for ISP billing and user management. These settings are used to authenticate users and track usage.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Network Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Network Management System *
                                </label>
                                <select wire:model="nmsSystem"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Select NMS...</option>
                                    <option value="mikrotik">MikroTik</option>
                                    <option value="ubiquiti">Ubiquiti</option>
                                    <option value="cisco">Cisco</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('nmsSystem') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SNMP Community</label>
                                <input type="text" wire:model="snmpCommunity"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="public">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">RADIUS Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">RADIUS Server IP *</label>
                                <input type="text" wire:model="radiusServerIp"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="192.168.1.100">
                                @error('radiusServerIp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">RADIUS Secret *</label>
                                <input type="password" wire:model="radiusSecret"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                @error('radiusSecret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Auth Port</label>
                                <input type="number" wire:model="radiusAuthPort"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="1812">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Accounting Port</label>
                                <input type="number" wire:model="radiusAcctPort"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="1813">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-end">
                            <button wire:click="saveTechnicalSettings"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Technical Settings
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            <!-- Hotspot Settings -->
            @if($activeTab === 'hotspot')
                @if($tenant->type === 'hotspot' || $tenant->type === 'both')
                <div class="space-y-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-green-800 mb-1">Hotspot Service Configuration</h4>
                                <p class="text-sm text-green-700">Configure your WiFi hotspot settings for guest access and voucher management.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">WiFi Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default SSID *</label>
                                <input type="text" wire:model="defaultSsid"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="HotSpot_Guest">
                                @error('defaultSsid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Captive Portal URL</label>
                                <input type="url" wire:model="captivePortalUrl"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="https://portal.yourdomain.com">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Voucher Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default Session Time (hours)</label>
                                <input type="number" wire:model="defaultSessionTime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="24">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Bandwidth (Mbps)</label>
                                <input type="number" wire:model="maxBandwidth"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="10">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-end">
                            <button wire:click="saveHotspotSettings"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Hotspot Settings
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5C2.962 18.333 3.924 20 5.464 20z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Hotspot Settings Not Available</h3>
                    <p class="text-gray-600">This section is only available for Hotspot and Combined accounts.</p>
                </div>
                @endif
            @endif

            <!-- Billing Settings -->
            @if($activeTab === 'billing')
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency *</label>
                            <select wire:model="defaultCurrency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Select currency...</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="CAD">CAD - Canadian Dollar</option>
                                <option value="AUD">AUD - Australian Dollar</option>
                            </select>
                            @error('defaultCurrency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                            <input type="number" wire:model="taxRate" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Prefix</label>
                            <input type="text" wire:model="invoicePrefix"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="INV">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Terms (days)</label>
                            <input type="number" wire:model="paymentTerms"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="30">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Features</h3>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="autoSuspend"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Auto-suspend overdue accounts</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="sendReminders"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Send payment reminders</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="allowPartialPayments"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Allow partial payments</span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end">
                        <button wire:click="saveBillingSettings"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Billing Settings
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notification Settings -->
            @if($activeTab === 'notifications')
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h3>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="emailNewCustomers"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">New customer registrations</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="emailPayments"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Payment confirmations</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="emailOverdue"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Overdue account notices</span>
                        </label>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SMS Notifications</h3>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="smsPaymentReminders"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Payment reminders</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="smsServiceAlerts"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Service interruption alerts</span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end">
                        <button wire:click="saveNotificationSettings"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Notification Settings
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Security Settings -->
            @if($activeTab === 'security')
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="twoFactorAuth"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Enable two-factor authentication</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="sessionTimeout"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Auto logout after inactivity</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" wire:model="auditLogging"
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Enable audit logging</span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end">
                        <button wire:click="saveSecuritySettings"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Security Settings
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
