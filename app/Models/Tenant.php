<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'country',
        'currency',
        'timezone',
        'branding',
        'tax_profile',
        'settings',
        'status'
    ];

    protected $casts = [
        'branding' => 'array',
        'tax_profile' => 'array',
        'settings' => 'array',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function networkDevices(): HasMany
    {
        return $this->hasMany(NetworkDevice::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }

    public function radiusIdentities(): HasMany
    {
        return $this->hasMany(RadiusIdentity::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function ratingRecords(): HasMany
    {
        return $this->hasMany(RatingRecord::class);
    }

    // Accessor methods
    public function getPrimaryColorAttribute(): ?string
    {
        return $this->branding['primary_color'] ?? null;
    }

    public function getSecondaryColorAttribute(): ?string
    {
        return $this->branding['secondary_color'] ?? null;
    }

    /**
     * Check if tenant profile is complete
     */
    public function isProfileComplete(): bool
    {
        return $this->settings['profile_completed'] ?? false;
    }

    /**
     * Check if tenant setup was skipped
     */
    public function wasSetupSkipped(): bool
    {
        return $this->settings['setup_skipped'] ?? false;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->branding['logo_url'] ?? null;
    }

    public function getTaxRateAttribute(): float
    {
        return $this->tax_profile['tax_rate'] ?? 0.0;
    }

    public function getTaxNumberAttribute(): ?string
    {
        return $this->tax_profile['tax_number'] ?? null;
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function getFormattedCurrencyAttribute(): string
    {
        $currencies = [
            'UGX' => 'Uganda Shilling',
            'KES' => 'Kenya Shilling',
            'TZS' => 'Tanzania Shilling',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound'
        ];

        return $currencies[$this->currency] ?? $this->currency;
    }

    public function getFormattedCountryAttribute(): string
    {
        $countries = [
            'UG' => 'Uganda',
            'KE' => 'Kenya',
            'TZ' => 'Tanzania',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France'
        ];

        return $countries[$this->country] ?? $this->country;
    }

    // Settings helpers
    public function allowsSelfRegistration(): bool
    {
        return $this->settings['allow_self_registration'] ?? false;
    }

    public function requiresKyc(): bool
    {
        return $this->settings['require_kyc'] ?? true;
    }

    public function getAutoSuspendDays(): int
    {
        return $this->settings['auto_suspend_days'] ?? 7;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    // Business logic methods
    public function getTotalRevenue()
    {
        return $this->subscriptions()
                   ->where('subscriptions.status', 'active')
                   ->join('products', 'subscriptions.product_id', '=', 'products.id')
                   ->sum('products.price');
    }

    public function getActiveCustomersCount(): int
    {
        return $this->customers()->where('status', 'active')->count();
    }

    public function getActiveServicesCount(): int
    {
        return $this->services()->where('status', 'active')->count();
    }

    public function formatMoney($amount): string
    {
        $symbols = [
            'UGX' => 'UGX ',
            'KES' => 'KSh ',
            'TZS' => 'TSh ',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';

        if (in_array($this->currency, ['UGX', 'KES', 'TZS'])) {
            // For East African currencies, no decimal places
            return $symbol . number_format($amount, 0);
        }

        return $symbol . number_format($amount, 2);
    }

    // Static methods
    public static function getAvailableCountries(): array
    {
        return [
            'UG' => 'Uganda',
            'KE' => 'Kenya',
            'TZ' => 'Tanzania',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France'
        ];
    }

    public static function getAvailableCurrencies(): array
    {
        return [
            'UGX' => 'Uganda Shilling (UGX)',
            'KES' => 'Kenya Shilling (KES)',
            'TZS' => 'Tanzania Shilling (TZS)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)'
        ];
    }

    public static function getAvailableTimezones(): array
    {
        return [
            'Africa/Kampala' => 'Kampala (UTC+3)',
            'Africa/Nairobi' => 'Nairobi (UTC+3)',
            'Africa/Dar_es_Salaam' => 'Dar es Salaam (UTC+3)',
            'America/New_York' => 'New York (UTC-5)',
            'Europe/London' => 'London (UTC+0)',
            'UTC' => 'UTC (UTC+0)'
        ];
    }
}
