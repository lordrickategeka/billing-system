<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantRegistrationComponent extends Component
{
    // Essential registration fields only
    public $companyName = '';
    public $tenantType = '';
    public $isIspSelected = false;
    public $isHotspotSelected = false;
    public $firstName = '';
    public $lastName = '';
    public $adminEmail = '';
    public $password = '';
    public $passwordConfirmation = '';
    public $phoneNumber = '';
    public $country = '';
    public $acceptTerms = false;

    // UI states
    public $showPassword = false;
    public $showPasswordConfirm = false;
    public $passwordStrength = 0;
    public $isSubmitting = false;
    public $requestQuote = false;
    public $businessTypeLoading = false;

    // Wizard properties (for compatibility with view)
    public $currentStep = 1;
    public $totalSteps = 5;

    protected $rules = [
        'companyName' => 'required|min:3|max:255',
        'tenantType' => 'required|in:isp,hotspot,both',
        'isIspSelected' => 'boolean',
        'isHotspotSelected' => 'boolean',
        'firstName' => 'required|min:2|max:50',
        'lastName' => 'required|min:2|max:50',
        'adminEmail' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'passwordConfirmation' => 'required|same:password',
        'phoneNumber' => 'required|min:10',
        'country' => 'required',
        'acceptTerms' => 'accepted'
    ];

    protected $messages = [
        'companyName.required' => 'Company name is required',
        'companyName.min' => 'Company name must be at least 3 characters',
        'tenantType.required' => 'Please select at least one business type (ISP and/or Hotspot)',
        'firstName.required' => 'First name is required',
        'lastName.required' => 'Last name is required',
        'adminEmail.required' => 'Email is required',
        'adminEmail.email' => 'Please enter a valid email address',
        'adminEmail.unique' => 'This email is already registered',
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 8 characters',
        'password.confirmed' => 'Password confirmation does not match',
        'phoneNumber.required' => 'Phone number is required',
        'country.required' => 'Please select your country',
        'acceptTerms.accepted' => 'You must accept the terms and conditions'
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'password') {
            $this->calculatePasswordStrength();
        }

        // Handle business type selection changes
        if ($propertyName === 'isIspSelected' || $propertyName === 'isHotspotSelected') {
            $this->updateTenantType();
        }

        $this->validateOnly($propertyName);
    }

    public function updateTenantType()
    {
        // Show loading state
        $this->businessTypeLoading = true;
        
        // Simulate processing time
        usleep(500000); // 0.5 second delay
        
        if ($this->isIspSelected && $this->isHotspotSelected) {
            $this->tenantType = 'both';
        } elseif ($this->isIspSelected) {
            $this->tenantType = 'isp';
        } elseif ($this->isHotspotSelected) {
            $this->tenantType = 'hotspot';
        } else {
            $this->tenantType = '';
        }
        
        // Hide loading state
        $this->businessTypeLoading = false;
    }

    public function calculatePasswordStrength()
    {
        $strength = 0;

        if (strlen($this->password) >= 8) $strength += 25;
        if (preg_match('/[A-Z]/', $this->password)) $strength += 25;
        if (preg_match('/[0-9]/', $this->password)) $strength += 25;
        if (preg_match('/[^A-Za-z0-9]/', $this->password)) $strength += 25;

        $this->passwordStrength = $strength;
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirmVisibility()
    {
        $this->showPasswordConfirm = !$this->showPasswordConfirm;
    }

    public function submit()
    {
        $this->isSubmitting = true;

        try {
            $this->validate();

            DB::transaction(function () {
                // Generate slug from company name
                $slug = Str::slug($this->companyName);

                // Ensure unique slug
                $counter = 1;
                $originalSlug = $slug;
                while (Tenant::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // Create tenant with minimal required fields
                $tenant = Tenant::create([
                    'name' => $this->companyName,
                    'slug' => $slug,
                    'type' => $this->tenantType,
                    'email' => $this->adminEmail, // Use admin email as primary business email
                    'phone' => $this->phoneNumber,
                    'country' => $this->country,
                    'currency' => $this->getDefaultCurrency($this->country),
                    'timezone' => 'UTC', // Default timezone, will be set during profile setup
                    'settings' => [
                        'profile_completed' => false,
                        'registration_completed_at' => now(),
                        'setup_required' => true
                    ],
                    'status' => 'pending_setup' // New status for incomplete profiles
                ]);

                // Create admin user
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'name' => $this->firstName . ' ' . $this->lastName,
                    'email' => $this->adminEmail,
                    'password' => Hash::make($this->password),
                    'role' => 'admin',
                    'timezone' => 'UTC'
                ]);

                // Log the user in
                Auth::login($user);

                session()->flash('message', 'Registration successful! Let\'s complete your business profile.');
            });

            // Redirect to profile setup instead of dashboard
            return redirect()->route('profile.setup');

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage(), [
                'email' => $this->adminEmail,
                'company' => $this->companyName,
                'exception' => $e
            ]);
            
            session()->flash('error', 'Registration failed: ' . $e->getMessage());
            $this->isSubmitting = false;
        }
    }

    private function getDefaultCurrency($countryCode)
    {
        $currencies = [
            'UG' => 'UGX',
            'KE' => 'KES',
            'TZ' => 'TZS',
            'RW' => 'RWF',
            'NG' => 'NGN',
            'GH' => 'GHS',
            'ZA' => 'ZAR',
            'US' => 'USD',
            'GB' => 'GBP',
            'CA' => 'CAD',
            'AU' => 'AUD',
        ];

        return $currencies[$countryCode] ?? 'USD';
    }

    public function render()
    {
        return view('livewire.tenant-registration-component');
    }
}
