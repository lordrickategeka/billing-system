# Service Configuration Storage Structure

## Overview
Service Configuration details from the tenant registration form are stored in the **`tenants.settings`** JSON field in the database.

## Storage Location
- **Table**: `tenants`
- **Column**: `settings` (JSON type)
- **Access**: `$tenant->settings['key']` or `$tenant->settings['nested']['key']`

## ISP Configuration Structure

When a user registers as an ISP and completes Step 3 (Service Configuration), the following data is stored:

```json
{
    "network_system": "mikrotik|ubiquiti|cisco",
    "radius_ip": "192.168.1.1",
    "radius_secret": "secret123",
    "gateway": "192.168.1.1",
    "billing_features": {
        "recurring": true,
        "prepaid": false,
        "postpaid": true,
        "usage_tracking": true
    },
    "services": {
        "bandwidth_management": true,
        "user_management": true,
        "monitoring": false
    },
    "launch_date": "2025-01-15",
    "request_quote": false
}
```

## Hotspot Configuration Structure

For Hotspot operators, the structure includes:

```json
{
    "hotspot_type": "cafe|hotel|restaurant|retail|office",
    "access_points": "1-5|6-15|16-50|50+",
    "daily_users": "1-50|51-200|201-500|500+",
    "auth_methods": {
        "social_login": true,
        "sms_otp": false,
        "email_verification": true,
        "voucher_system": true
    },
    "features": {
        "bandwidth_control": true,
        "time_limits": true,
        "user_portal": true
    },
    "launch_date": "2025-02-01",
    "request_quote": true
}
```

## Code Access Examples

### Retrieving Configuration Data
```php
// Get the tenant
$tenant = Tenant::find(1);

// Access network system
$networkSystem = $tenant->settings['network_system']; // "mikrotik"

// Access RADIUS settings
$radiusIP = $tenant->settings['radius_ip']; // "192.168.1.1"
$radiusSecret = $tenant->settings['radius_secret']; // "secret123"

// Check billing features
$hasRecurring = $tenant->settings['billing_features']['recurring'] ?? false;
$hasUsageTracking = $tenant->settings['billing_features']['usage_tracking'] ?? false;

// Access services
$hasBandwidthMgmt = $tenant->settings['services']['bandwidth_management'] ?? false;
```

### Updating Configuration
```php
$tenant = Tenant::find(1);
$settings = $tenant->settings;
$settings['radius_secret'] = 'new_secret_456';
$tenant->settings = $settings;
$tenant->save();
```

## Key Benefits of JSON Storage

1. **Flexibility**: Each tenant type can have different configuration fields
2. **Scalability**: Easy to add new configuration options without database migrations
3. **Type Safety**: Laravel automatically handles JSON encoding/decoding
4. **Query Support**: MySQL/PostgreSQL support JSON queries for advanced filtering

## Current Implementation

The configuration is stored during registration in the `TenantRegistrationComponent::getSettings()` method:

```php
private function getSettings()
{
    if ($this->businessType === 'isp') {
        return [
            'network_system' => $this->networkSystem,
            'radius_ip' => $this->radiusIp,
            'radius_secret' => $this->radiusSecret,
            'gateway' => $this->gateway,
            'billing_features' => $this->billingFeatures,
            'services' => $this->services,
            'launch_date' => $this->launchDate,
            'request_quote' => $this->requestQuote,
        ];
    } else {
        return [
            'hotspot_type' => $this->hotspotType,
            'access_points' => $this->accessPoints,
            'daily_users' => $this->dailyUsers,
            'auth_methods' => $this->authMethods,
            'features' => $this->features,
            'launch_date' => $this->launchDate,
            'request_quote' => $this->requestQuote,
        ];
    }
}
```

This approach allows for efficient storage and retrieval of tenant-specific service configurations while maintaining data integrity and ease of access.
