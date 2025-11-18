<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class SettingsComponent extends Component
{
    public $tenant;
    public $activeTab = 'general';

    // General Settings
    public $companyName;
    public $businessEmail;
    public $phoneNumber;
    public $country;
    public $address;
    public $website;
    public $timezone;

    // Technical Settings (for ISP)
    public $networkSystem;
    public $radiusIp;
    public $radiusSecret;
    public $gateway;

    // Hotspot Settings
    public $hotspotType;
    public $accessPoints;
    public $dailyUsers;

    // Billing Features
    public $billingFeatures = [];
    public $services = [];
    public $authMethods = [];
    public $features = [];

    // Notification Settings
    public $emailNotifications = true;
    public $smsNotifications = false;
    public $dashboardAlerts = true;

    // Security Settings
    public $twoFactorEnabled = false;
    public $sessionTimeout = 120; // minutes

    public $showSuccessMessage = false;
    public $isLoading = false;

    public function mount()
    {
        $this->tenant = Auth::user()->tenant;
        if (!$this->tenant) {
            abort(403, 'No tenant associated with this user.');
        }

        $this->loadSettings();
    }

    public function loadSettings()
    {
        $settings = $this->tenant->settings ?? [];

        // Load general settings
        $this->companyName = $this->tenant->name;
        $this->businessEmail = $this->tenant->email ?? '';
        $this->phoneNumber = $settings['phone_number'] ?? '';
        $this->country = $settings['country'] ?? '';
        $this->address = $settings['address'] ?? '';
        $this->website = $settings['website'] ?? '';
        $this->timezone = $settings['timezone'] ?? 'UTC';

        // Load technical settings
        $this->networkSystem = $settings['network_system'] ?? 'mikrotik';
        $this->radiusIp = $settings['radius_ip'] ?? '';
        $this->radiusSecret = $settings['radius_secret'] ?? '';
        $this->gateway = $settings['gateway'] ?? '';

        // Load hotspot settings
        $this->hotspotType = $settings['hotspot_type'] ?? '';
        $this->accessPoints = $settings['access_points'] ?? '';
        $this->dailyUsers = $settings['daily_users'] ?? '';

        // Load feature arrays
        $this->billingFeatures = $settings['billing_features'] ?? [];
        $this->services = $settings['services'] ?? [];
        $this->authMethods = $settings['auth_methods'] ?? [];
        $this->features = $settings['features'] ?? [];

        // Load notification settings
        $this->emailNotifications = $settings['notifications']['email'] ?? true;
        $this->smsNotifications = $settings['notifications']['sms'] ?? false;
        $this->dashboardAlerts = $settings['notifications']['dashboard'] ?? true;

        // Load security settings
        $this->twoFactorEnabled = $settings['security']['two_factor'] ?? false;
        $this->sessionTimeout = $settings['security']['session_timeout'] ?? 120;
    }

    public function saveGeneralSettings()
    {
        $this->isLoading = true;

        $this->validate([
            'companyName' => 'required|min:2|max:255',
            'businessEmail' => 'required|email',
            'phoneNumber' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url',
            'timezone' => 'required|string'
        ]);

        $settings = $this->tenant->settings ?? [];
        $settings['phone_number'] = $this->phoneNumber;
        $settings['country'] = $this->country;
        $settings['address'] = $this->address;
        $settings['website'] = $this->website;
        $settings['timezone'] = $this->timezone;

        $this->tenant->update([
            'name' => $this->companyName,
            'email' => $this->businessEmail,
            'settings' => $settings
        ]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        // Hide success message after 3 seconds
        $this->dispatch('hide-success');
    }

    public function saveTechnicalSettings()
    {
        $this->isLoading = true;

        $rules = [];
        $messages = [];

        // For ISP or both types, RADIUS settings are required
        if ($this->tenant->type === 'isp' || $this->tenant->type === 'both') {
            $rules = [
                'networkSystem' => 'required|in:mikrotik,ubiquiti,cisco,other',
                'radiusIp' => 'required|ip',
                'radiusSecret' => 'required|min:6|max:100',
                'gateway' => 'nullable|ip'
            ];

            $messages = [
                'networkSystem.required' => 'Please select your network management system',
                'radiusIp.required' => 'RADIUS server IP is required for ISP services',
                'radiusIp.ip' => 'Please enter a valid IP address',
                'radiusSecret.required' => 'RADIUS secret is required for ISP services',
                'radiusSecret.min' => 'RADIUS secret must be at least 6 characters',
                'gateway.ip' => 'Please enter a valid gateway IP address'
            ];
        } else {
            $rules = [
                'networkSystem' => 'nullable|in:mikrotik,ubiquiti,cisco,other',
                'radiusIp' => 'nullable|ip',
                'radiusSecret' => 'nullable|string|max:100',
                'gateway' => 'nullable|ip'
            ];
        }

        $this->validate($rules, $messages);

        $settings = $this->tenant->settings ?? [];
        $settings['network_system'] = $this->networkSystem;
        $settings['radius_ip'] = $this->radiusIp;
        $settings['radius_secret'] = $this->radiusSecret;
        $settings['gateway'] = $this->gateway;

        $this->tenant->update(['settings' => $settings]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        $this->dispatch('hide-success');
    }

    public function saveHotspotSettings()
    {
        $this->isLoading = true;

        $rules = [];
        $messages = [];

        // For Hotspot or both types, certain fields are required
        if ($this->tenant->type === 'hotspot' || $this->tenant->type === 'both') {
            $rules = [
                'hotspotType' => 'required|in:cafe,hotel,restaurant,retail,office,other',
                'accessPoints' => 'required|in:1-5,6-15,16-50,50+',
                'dailyUsers' => 'required|in:1-50,51-200,201-500,500+'
            ];

            $messages = [
                'hotspotType.required' => 'Please select your hotspot type',
                'accessPoints.required' => 'Please specify the number of access points',
                'dailyUsers.required' => 'Please estimate your expected daily users'
            ];
        } else {
            $rules = [
                'hotspotType' => 'nullable|string',
                'accessPoints' => 'nullable|string',
                'dailyUsers' => 'nullable|string'
            ];
        }

        $this->validate($rules, $messages);

        $settings = $this->tenant->settings ?? [];
        $settings['hotspot_type'] = $this->hotspotType;
        $settings['access_points'] = $this->accessPoints;
        $settings['daily_users'] = $this->dailyUsers;

        $this->tenant->update(['settings' => $settings]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        $this->dispatch('hide-success');
    }

    public function saveBillingSettings()
    {
        $this->isLoading = true;

        // Validate that at least one billing feature and service is selected
        $this->validate([
            'billingFeatures' => 'required|array|min:1',
            'services' => 'required|array|min:1'
        ], [
            'billingFeatures.required' => 'Please select at least one billing feature',
            'billingFeatures.min' => 'Please select at least one billing feature',
            'services.required' => 'Please select at least one service',
            'services.min' => 'Please select at least one service'
        ]);

        $settings = $this->tenant->settings ?? [];
        $settings['billing_features'] = $this->billingFeatures;
        $settings['services'] = $this->services;

        $this->tenant->update(['settings' => $settings]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        $this->dispatch('hide-success');
    }

    public function saveNotificationSettings()
    {
        $this->isLoading = true;

        $settings = $this->tenant->settings ?? [];
        $settings['notifications'] = [
            'email' => $this->emailNotifications,
            'sms' => $this->smsNotifications,
            'dashboard' => $this->dashboardAlerts
        ];

        $this->tenant->update(['settings' => $settings]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        $this->dispatch('hide-success');
    }

    public function saveSecuritySettings()
    {
        $this->isLoading = true;

        $this->validate([
            'sessionTimeout' => 'required|integer|min:5|max:480'
        ]);

        $settings = $this->tenant->settings ?? [];
        $settings['security'] = [
            'two_factor' => $this->twoFactorEnabled,
            'session_timeout' => $this->sessionTimeout
        ];

        $this->tenant->update(['settings' => $settings]);

        $this->showSuccessMessage = true;
        $this->isLoading = false;

        $this->dispatch('hide-success');
    }

    public function setActiveTab($tab)
    {
        $validTabs = ['general', 'technical', 'hotspot', 'billing', 'notifications', 'security'];

        if (in_array($tab, $validTabs)) {
            $this->activeTab = $tab;
            // Reset any success messages when switching tabs
            $this->showSuccessMessage = false;
        }
    }

    public function render()
    {
        return view('livewire.settings-component');
    }
}
