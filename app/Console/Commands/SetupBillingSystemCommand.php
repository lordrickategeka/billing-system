<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Policy;
use App\Models\NetworkDevice;
use Illuminate\Support\Str;

class SetupBillingSystemCommand extends Command
{
    protected $signature = 'billing:setup {--demo : Setup with demo data}';
    protected $description = 'Setup the billing system with initial data';

    public function handle()
    {
        $this->info('Setting up Billing System...');

        try {
            // Create default tenant
            $tenant = $this->createDefaultTenant();
            $this->info("✓ Created tenant: {$tenant->name}");

            // Create policies
            $policies = $this->createPolicies($tenant);
            $this->info("✓ Created {$policies->count()} policies");

            // Create products
            $products = $this->createProducts($tenant, $policies);
            $this->info("✓ Created {$products->count()} products");

            if ($this->option('demo')) {
                // Create demo network devices
                $devices = $this->createNetworkDevices($tenant);
                $this->info("✓ Created {$devices->count()} network devices");
            }

            $this->info("\n🎉 Billing system setup completed!");
            $this->info("\nNext steps:");
            $this->info("1. Test RADIUS connection: php artisan radius:sync");
            $this->info("2. Generate vouchers via API: POST /api/vouchers/generate");
            $this->info("3. Create broadband subscribers via API: POST /api/broadband/subscribers");

        } catch (\Exception $e) {
            $this->error("Setup failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function createDefaultTenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default ISP',
                'country' => 'US',
                'currency' => 'USD',
                'timezone' => 'UTC',
                'branding' => [
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#1E40AF',
                    'logo_url' => null
                ],
                'tax_profile' => [
                    'tax_rate' => 0.0,
                    'tax_number' => null
                ],
                'settings' => [
                    'allow_self_registration' => true,
                    'require_kyc' => false,
                    'auto_suspend_days' => 7
                ]
            ]
        );
    }

    private function createPolicies(Tenant $tenant)
    {
        $policies = collect([
            [
                'name' => 'Standard Internet',
                'description' => 'Standard internet access policy',
                'radius_attributes' => [
                    'MS-Primary-DNS-Server' => '8.8.8.8',
                    'MS-Secondary-DNS-Server' => '8.8.4.4'
                ],
                'address_pool' => 'main_pool',
                'dns_servers' => '8.8.8.8,8.8.4.4'
            ],
            [
                'name' => 'Premium Internet',
                'description' => 'Premium internet with QoS priority',
                'radius_attributes' => [
                    'MS-Primary-DNS-Server' => '1.1.1.1',
                    'MS-Secondary-DNS-Server' => '1.0.0.1',
                    'Class' => 'premium'
                ],
                'qos_profile' => 'premium',
                'address_pool' => 'premium_pool',
                'dns_servers' => '1.1.1.1,1.0.0.1'
            ],
            [
                'name' => 'Hotspot Basic',
                'description' => 'Basic hotspot access with walled garden',
                'radius_attributes' => [
                    'MS-Primary-DNS-Server' => '8.8.8.8',
                    'Idle-Timeout' => '1800'
                ],
                'walled_garden_urls' => 'portal.example.com,payment.example.com'
            ]
        ]);

        return $policies->map(function ($data) use ($tenant) {
            return Policy::create(array_merge($data, ['tenant_id' => $tenant->id]));
        });
    }

    private function createProducts(Tenant $tenant, $policies)
    {
        $standardPolicy = $policies->where('name', 'Standard Internet')->first();
        $premiumPolicy = $policies->where('name', 'Premium Internet')->first();
        $hotspotPolicy = $policies->where('name', 'Hotspot Basic')->first();

        $products = collect([
            // Hotspot Products
            [
                'name' => '1 Hour WiFi',
                'slug' => '1-hour-wifi',
                'service_type' => 'hotspot',
                'access_type' => 'voucher',
                'class' => 'basic',
                'speed_up_kbps' => 1024,
                'speed_down_kbps' => 2048,
                'session_timeout' => 3600,
                'idle_timeout' => 1800,
                'price' => 2.00,
                'billing_cycle' => 'one_time',
                'term_days' => 1
            ],
            [
                'name' => '1 Day WiFi',
                'slug' => '1-day-wifi',
                'service_type' => 'hotspot',
                'access_type' => 'voucher',
                'class' => 'standard',
                'speed_up_kbps' => 2048,
                'speed_down_kbps' => 5120,
                'quota_mb' => 1024,
                'session_timeout' => 86400,
                'idle_timeout' => 3600,
                'price' => 5.00,
                'billing_cycle' => 'one_time',
                'term_days' => 1
            ],
            [
                'name' => '1 Week WiFi Premium',
                'slug' => '1-week-wifi-premium',
                'service_type' => 'hotspot',
                'access_type' => 'voucher',
                'class' => 'premium',
                'speed_up_kbps' => 5120,
                'speed_down_kbps' => 10240,
                'quota_mb' => 10240,
                'session_timeout' => 28800,
                'idle_timeout' => 7200,
                'price' => 25.00,
                'billing_cycle' => 'one_time',
                'term_days' => 7
            ],

            // Broadband Products - PPPoE
            [
                'name' => '5 Mbps Home',
                'slug' => '5mbps-home',
                'service_type' => 'broadband',
                'access_type' => 'pppoe',
                'class' => 'bronze',
                'speed_up_kbps' => 1024,
                'speed_down_kbps' => 5120,
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'fup_rules' => [
                    'monthly_limit_gb' => 100,
                    'fup_speed_down_kbps' => 1024,
                    'fup_speed_up_kbps' => 512
                ]
            ],
            [
                'name' => '10 Mbps Home',
                'slug' => '10mbps-home',
                'service_type' => 'broadband',
                'access_type' => 'pppoe',
                'class' => 'silver',
                'speed_up_kbps' => 2048,
                'speed_down_kbps' => 10240,
                'price' => 49.99,
                'billing_cycle' => 'monthly',
                'fup_rules' => [
                    'monthly_limit_gb' => 200,
                    'fup_speed_down_kbps' => 2048,
                    'fup_speed_up_kbps' => 1024
                ]
            ],
            [
                'name' => '25 Mbps Business',
                'slug' => '25mbps-business',
                'service_type' => 'broadband',
                'access_type' => 'pppoe',
                'class' => 'gold',
                'speed_up_kbps' => 5120,
                'speed_down_kbps' => 25600,
                'price' => 99.99,
                'billing_cycle' => 'monthly',
                'burst_config' => [
                    'burst_limit_down_kbps' => 51200,
                    'burst_limit_up_kbps' => 10240,
                    'burst_threshold_down_kbps' => 20480,
                    'burst_threshold_up_kbps' => 4096,
                    'burst_time_seconds' => 8
                ]
            ],
            [
                'name' => '100 Mbps Fiber',
                'slug' => '100mbps-fiber',
                'service_type' => 'broadband',
                'access_type' => 'pppoe',
                'class' => 'platinum',
                'speed_up_kbps' => 25600,
                'speed_down_kbps' => 102400,
                'price' => 199.99,
                'billing_cycle' => 'monthly'
            ],

            // IPoE Products
            [
                'name' => '50 Mbps IPoE',
                'slug' => '50mbps-ipoe',
                'service_type' => 'broadband',
                'access_type' => 'ipoe',
                'class' => 'gold',
                'speed_up_kbps' => 10240,
                'speed_down_kbps' => 51200,
                'price' => 149.99,
                'billing_cycle' => 'monthly'
            ]
        ]);

        return $products->map(function ($data) use ($tenant) {
            return Product::create(array_merge($data, ['tenant_id' => $tenant->id]));
        });
    }

    private function createNetworkDevices(Tenant $tenant)
    {
        $devices = collect([
            [
                'name' => 'Main Gateway',
                'nas_ip_address' => '192.168.1.1',
                'secret' => 'testing123',
                'vendor' => 'mikrotik',
                'type' => 'router',
                'site_name' => 'Main Office',
                'location' => [
                    'address' => '123 Main St, City',
                    'lat' => 40.7128,
                    'lng' => -74.0060
                ],
                'capabilities' => [
                    'coa' => true,
                    'dm' => true,
                    'accounting' => true
                ]
            ],
            [
                'name' => 'Hotspot AP 1',
                'nas_ip_address' => '192.168.1.10',
                'secret' => 'hotspot123',
                'vendor' => 'ubiquiti',
                'type' => 'access_point',
                'site_name' => 'Coffee Shop A',
                'location' => [
                    'address' => '456 Coffee Ave, City',
                    'lat' => 40.7589,
                    'lng' => -73.9851
                ],
                'capabilities' => [
                    'coa' => false,
                    'dm' => true,
                    'accounting' => true
                ]
            ],
            [
                'name' => 'BNG Router',
                'nas_ip_address' => '10.0.1.1',
                'secret' => 'bng_secret123',
                'vendor' => 'cisco',
                'type' => 'bng',
                'site_name' => 'Data Center',
                'location' => [
                    'address' => '789 Data Center Blvd, City',
                    'lat' => 40.6892,
                    'lng' => -74.0445
                ],
                'capabilities' => [
                    'coa' => true,
                    'dm' => true,
                    'accounting' => true,
                    'ipoe' => true,
                    'pppoe' => true
                ]
            ]
        ]);

        return $devices->map(function ($data) use ($tenant) {
            return NetworkDevice::create(array_merge($data, ['tenant_id' => $tenant->id]));
        });
    }
}
