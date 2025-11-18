<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('layouts.setup')]
class ProfileSetupComponent extends Component
{
    public $currentStep = 1;
    public $totalSteps = 4;
    public $tenant;

    // Step 1: Business Details
    public $address = '';
    public $businessRegistrationNumber = '';
    public $taxNumber = '';
    public $timezone = 'UTC';
    public $businessHours = [
        'monday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
        'tuesday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
        'wednesday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
        'thursday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
        'friday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
        'saturday' => ['open' => '08:00', 'close' => '17:00', 'closed' => true],
        'sunday' => ['open' => '08:00', 'close' => '17:00', 'closed' => true],
    ];

    // Step 2: Network Configuration (ISP/Hotspot specific)
    public $networkSystem = '';
    public $radiusIp = '';
    public $radiusSecret = '';
    public $radiusPort = '1812';
    public $nasIp = '';
    public $nasSecret = '';
    public $gateway = '';
    public $dnsServers = ['8.8.8.8', '8.8.4.4'];

    // Hotspot specific
    public $hotspotType = '';
    public $accessPoints = '';
    public $dailyUsers = '';
    public $captivePortalUrl = '';
    public $socialAuth = [];
    public $smsProvider = '';

    // Step 3: Billing & Services
    public $currency = '';
    public $billingCycle = 'monthly';
    public $paymentMethods = [];
    public $services = [];
    public $defaultProducts = [];
    public $taxRate = 0;
    public $invoicePrefix = 'INV';
    public $autoSuspendDays = 7;

    // Step 4: Policies & Settings
    public $supportEmail = '';
    public $supportPhone = '';
    public $tosAccepted = false;
    public $privacyPolicyUrl = '';
    public $dataRetentionDays = 365;
    public $emailNotifications = true;
    public $smsNotifications = false;

    // UI States
    public $isSubmitting = false;

    public function mount()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $this->tenant = $user->tenant;

        if (!$this->tenant) {
            session()->flash('error', 'No tenant found. Please contact support.');
            return redirect()->route('register');
        }

        // If profile is already completed, redirect to dashboard
        if ($this->tenant->settings['profile_completed'] ?? false) {
            return redirect()->route('dashboard');
        }

        // Pre-populate some fields
        $this->currency = $this->tenant->currency ?? 'USD';
        $this->supportEmail = $this->tenant->email;
        $this->supportPhone = $this->tenant->phone;
        $this->timezone = $this->tenant->timezone ?? 'UTC';
    }

    public function updated($propertyName)
    {
        if (strpos($propertyName, 'businessHours') !== false) {
            // Validate business hours format
        }
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateCurrentStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'address' => 'required|min:10',
                'timezone' => 'required',
            ], [
                'address.required' => 'Business address is required',
                'address.min' => 'Please provide a complete address',
                'timezone.required' => 'Please select your timezone',
            ]);
        } elseif ($this->currentStep === 2) {
            if ($this->tenant->type === 'isp' || $this->tenant->type === 'both') {
                $this->validate([
                    'networkSystem' => 'required',
                    'radiusIp' => 'required|ip',
                    'radiusSecret' => 'required|min:6',
                ], [
                    'networkSystem.required' => 'Please select your network management system',
                    'radiusIp.required' => 'RADIUS server IP is required',
                    'radiusIp.ip' => 'Please enter a valid IP address',
                    'radiusSecret.required' => 'RADIUS secret is required',
                    'radiusSecret.min' => 'RADIUS secret must be at least 6 characters',
                ]);
            }

            if ($this->tenant->type === 'hotspot' || $this->tenant->type === 'both') {
                $this->validate([
                    'hotspotType' => 'required',
                    'accessPoints' => 'required|numeric|min:1',
                ], [
                    'hotspotType.required' => 'Please select your hotspot type',
                    'accessPoints.required' => 'Number of access points is required',
                    'accessPoints.numeric' => 'Please enter a valid number',
                    'accessPoints.min' => 'At least 1 access point is required',
                ]);
            }
        } elseif ($this->currentStep === 3) {
            $this->validate([
                'currency' => 'required',
                'billingCycle' => 'required',
                'services' => 'required|array|min:1',
            ], [
                'currency.required' => 'Please select your currency',
                'billingCycle.required' => 'Please select billing cycle',
                'services.required' => 'Please select at least one service',
                'services.min' => 'Please select at least one service',
            ]);
        } elseif ($this->currentStep === 4) {
            $this->validate([
                'supportEmail' => 'required|email',
                'supportPhone' => 'required',
                'tosAccepted' => 'accepted',
            ], [
                'supportEmail.required' => 'Support email is required',
                'supportEmail.email' => 'Please enter a valid email address',
                'supportPhone.required' => 'Support phone is required',
                'tosAccepted.accepted' => 'You must accept the terms of service',
            ]);
        }
    }

    public function completeSetup()
    {
        $this->isSubmitting = true;

        try {
            $this->validateCurrentStep();

            DB::transaction(function () {
                // Prepare settings array
                $settings = array_merge($this->tenant->settings ?? [], [
                    'profile_completed' => true,
                    'profile_completed_at' => now(),
                    'setup_required' => false,

                    // Business details
                    'business_hours' => $this->businessHours,
                    'business_registration_number' => $this->businessRegistrationNumber,
                    'tax_number' => $this->taxNumber,
                    'data_retention_days' => $this->dataRetentionDays,

                    // Network configuration
                    'network_system' => $this->networkSystem,
                    'radius_config' => [
                        'ip' => $this->radiusIp,
                        'port' => $this->radiusPort,
                        'secret' => $this->radiusSecret,
                    ],
                    'nas_config' => [
                        'ip' => $this->nasIp,
                        'secret' => $this->nasSecret,
                    ],
                    'gateway' => $this->gateway,
                    'dns_servers' => $this->dnsServers,

                    // Hotspot specific
                    'hotspot_type' => $this->hotspotType,
                    'access_points' => $this->accessPoints,
                    'daily_users' => $this->dailyUsers,
                    'captive_portal_url' => $this->captivePortalUrl,
                    'social_auth' => $this->socialAuth,
                    'sms_provider' => $this->smsProvider,

                    // Billing configuration
                    'billing_cycle' => $this->billingCycle,
                    'payment_methods' => $this->paymentMethods,
                    'services' => $this->services,
                    'default_products' => $this->defaultProducts,
                    'tax_rate' => $this->taxRate,
                    'invoice_prefix' => $this->invoicePrefix,
                    'auto_suspend_days' => $this->autoSuspendDays,

                    // Support & notifications
                    'support_email' => $this->supportEmail,
                    'support_phone' => $this->supportPhone,
                    'privacy_policy_url' => $this->privacyPolicyUrl,
                    'email_notifications' => $this->emailNotifications,
                    'sms_notifications' => $this->smsNotifications,
                ]);

                // Update tenant
                $this->tenant->update([
                    'address' => $this->address,
                    'timezone' => $this->timezone,
                    'currency' => $this->currency,
                    'settings' => $settings,
                    'status' => 'active',
                ]);

                session()->flash('message', 'Profile setup completed successfully! Welcome to your dashboard.');
            });

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('Profile setup failed: ' . $e->getMessage(), [
                'tenant_id' => $this->tenant->id,
                'user_id' => Auth::id(),
                'exception' => $e
            ]);
            
            session()->flash('error', 'Setup failed: ' . $e->getMessage());
            $this->isSubmitting = false;
        }
    }

    public function skipSetup()
    {
        // Minimal setup to allow access to dashboard
        $settings = array_merge($this->tenant->settings ?? [], [
            'profile_completed' => true,
            'profile_completed_at' => now(),
            'setup_required' => false,
            'setup_skipped' => true,
        ]);

        $this->tenant->update([
            'settings' => $settings,
            'status' => 'active',
        ]);

        session()->flash('message', 'Setup skipped. You can complete your profile anytime from settings.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.profile-setup-component');
    }
}
