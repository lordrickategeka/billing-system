<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Default ISP Tenant (Main/Production)
        $defaultTenant = Tenant::updateOrCreate([
            'slug' => 'default-isp',
        ], [
            'name' => 'Default ISP',
            'slug' => 'default-isp',
            'country' => 'UG',
            'currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'status' => 'active',
            'branding' => [
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E40AF',
                'logo_url' => null
            ],
            'tax_profile' => [
                'tax_rate' => 18.0,
                'tax_number' => 'UG-VAT-1000123456'
            ],
            'settings' => [
                'business_type' => 'both',
                'setup_completed' => true,
                'allow_self_registration' => true,
                'require_kyc' => true,
                'auto_suspend_days' => 7,
                'grace_period_days' => 3,
                'primary_service_type' => 'mixed',
                'business_hours' => [
                    'start' => '08:00',
                    'end' => '18:00',
                    'days' => [1, 2, 3, 4, 5] // Monday to Friday
                ],
                'contact_information' => [
                    'primary_contact_name' => 'Admin User',
                    'primary_contact_email' => 'admin@defaultisp.ug',
                    'primary_contact_phone' => '+256700000000',
                    'technical_contact_email' => 'tech@defaultisp.ug',
                    'billing_contact_email' => 'billing@defaultisp.ug',
                    'support_contact_email' => 'support@defaultisp.ug'
                ],
                'network_settings' => [
                    'default_dns_primary' => '8.8.8.8',
                    'default_dns_secondary' => '8.8.4.4',
                    'radius_nas_secret' => 'testing123',
                    'ip_pool_ranges' => ['192.168.100.0/24', '10.10.0.0/16']
                ],
                'billing_settings' => [
                    'billing_cycle_default' => 'monthly',
                    'payment_terms_days' => 30,
                    'late_fee_percentage' => 5.0,
                    'payment_methods_accepted' => ['mobile_money', 'bank_transfer', 'cash']
                ]
            ]
        ]);

        // Create admin user for default tenant
        User::updateOrCreate([
            'email' => 'admin@defaultisp.ug',
        ], [
            'tenant_id' => $defaultTenant->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'name' => 'Admin User',
            'email' => 'admin@defaultisp.ug',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create Sample Hotspot Business Tenant
        $hotspotTenant = Tenant::updateOrCreate([
            'slug' => 'coffee-house-wifi',
        ], [
            'name' => 'Coffee House WiFi',
            'slug' => 'coffee-house-wifi',
            'country' => 'UG',
            'currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'status' => 'active',
            'branding' => [
                'primary_color' => '#8B4513',
                'secondary_color' => '#D2691E',
                'logo_url' => null
            ],
            'tax_profile' => [
                'tax_rate' => 18.0,
                'tax_number' => 'UG-VAT-2000456789'
            ],
            'settings' => [
                'business_type' => 'hotspot',
                'setup_completed' => true,
                'allow_self_registration' => false,
                'require_kyc' => false,
                'auto_suspend_days' => 1,
                'grace_period_days' => 0,
                'primary_service_type' => 'hotspot',
                'business_hours' => [
                    'start' => '06:00',
                    'end' => '22:00',
                    'days' => [1, 2, 3, 4, 5, 6, 7] // All days
                ],
                'contact_information' => [
                    'primary_contact_name' => 'Coffee Manager',
                    'primary_contact_email' => 'manager@coffeehouse.ug',
                    'primary_contact_phone' => '+256701111111',
                    'technical_contact_email' => 'tech@coffeehouse.ug',
                    'billing_contact_email' => 'billing@coffeehouse.ug',
                    'support_contact_email' => 'support@coffeehouse.ug'
                ],
                'network_settings' => [
                    'default_dns_primary' => '1.1.1.1',
                    'default_dns_secondary' => '1.0.0.1',
                    'radius_nas_secret' => 'coffeehouse123',
                    'ip_pool_ranges' => ['192.168.50.0/24']
                ],
                'billing_settings' => [
                    'billing_cycle_default' => 'one_time',
                    'payment_terms_days' => 0,
                    'payment_methods_accepted' => ['mobile_money', 'cash']
                ],
                'hotspot_settings' => [
                    'enable_captive_portal' => true,
                    'enable_social_login' => true,
                    'max_voucher_validity_days' => 7,
                    'default_session_timeout' => 3600
                ]
            ]
        ]);

        // Create admin for hotspot tenant
        User::updateOrCreate([
            'email' => 'admin@coffeehouse.ug',
        ], [
            'tenant_id' => $hotspotTenant->id,
            'first_name' => 'Hotspot',
            'last_name' => 'Admin',
            'name' => 'Hotspot Admin',
            'email' => 'admin@coffeehouse.ug',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create Sample ISP Tenant
        $ispTenant = Tenant::updateOrCreate([
            'slug' => 'fastnet-broadband',
        ], [
            'name' => 'FastNet Broadband',
            'slug' => 'fastnet-broadband',
            'country' => 'UG',
            'currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'status' => 'active',
            'branding' => [
                'primary_color' => '#10B981',
                'secondary_color' => '#059669',
                'logo_url' => null
            ],
            'tax_profile' => [
                'tax_rate' => 18.0,
                'tax_number' => 'UG-VAT-3000789012'
            ],
            'settings' => [
                'business_type' => 'isp',
                'setup_completed' => true,
                'allow_self_registration' => false,
                'require_kyc' => true,
                'auto_suspend_days' => 7,
                'grace_period_days' => 5,
                'primary_service_type' => 'pppoe',
                'business_hours' => [
                    'start' => '08:00',
                    'end' => '17:00',
                    'days' => [1, 2, 3, 4, 5]
                ],
                'contact_information' => [
                    'primary_contact_name' => 'ISP Manager',
                    'primary_contact_email' => 'manager@fastnet.ug',
                    'primary_contact_phone' => '+256702222222',
                    'technical_contact_email' => 'noc@fastnet.ug',
                    'billing_contact_email' => 'billing@fastnet.ug',
                    'support_contact_email' => 'support@fastnet.ug'
                ],
                'network_settings' => [
                    'default_dns_primary' => '8.8.8.8',
                    'default_dns_secondary' => '8.8.4.4',
                    'radius_nas_secret' => 'fastnet_secret123',
                    'ip_pool_ranges' => ['10.0.0.0/16', '172.16.0.0/16']
                ],
                'billing_settings' => [
                    'billing_cycle_default' => 'monthly',
                    'payment_terms_days' => 30,
                    'late_fee_percentage' => 10.0,
                    'payment_methods_accepted' => ['mobile_money', 'bank_transfer', 'direct_debit']
                ],
                'isp_settings' => [
                    'enable_pppoe' => true,
                    'enable_ipoe' => true,
                    'enable_static_ip' => true,
                    'minimum_contract_months' => 3,
                    'installation_fee' => 100000
                ]
            ]
        ]);

        // Create admin for ISP tenant
        User::updateOrCreate([
            'email' => 'admin@fastnet.ug',
        ], [
            'tenant_id' => $ispTenant->id,
            'first_name' => 'ISP',
            'last_name' => 'Admin',
            'name' => 'ISP Admin',
            'email' => 'admin@fastnet.ug',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create a Suspended Tenant (for testing)
        Tenant::updateOrCreate([
            'slug' => 'old-network',
        ], [
            'name' => 'Old Network Services',
            'slug' => 'old-network',
            'country' => 'UG',
            'currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'status' => 'suspended',
            'branding' => [
                'primary_color' => '#EF4444',
                'secondary_color' => '#DC2626',
                'logo_url' => null
            ],
            'tax_profile' => [
                'tax_rate' => 18.0,
                'tax_number' => 'UG-VAT-9000999999'
            ],
            'settings' => [
                'business_type' => 'isp',
                'setup_completed' => true,
                'suspension_reason' => 'Non-payment of platform fees',
                'suspended_at' => now()->subDays(30)
            ]
        ]);

        // Create Test Tenant for Development
        if (app()->environment(['local', 'development'])) {
            $testTenant = Tenant::updateOrCreate([
                'slug' => 'test-isp',
            ], [
                'name' => 'Test ISP Development',
                'slug' => 'test-isp',
                'country' => 'UG',
                'currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'status' => 'active',
                'branding' => [
                    'primary_color' => '#F59E0B',
                    'secondary_color' => '#D97706',
                    'logo_url' => null
                ],
                'tax_profile' => [
                    'tax_rate' => 18.0,
                    'tax_number' => 'TEST-VAT-000000'
                ],
                'settings' => [
                    'business_type' => 'both',
                    'setup_completed' => true,
                    'is_test_tenant' => true,
                    'allow_self_registration' => true,
                    'require_kyc' => false,
                    'auto_suspend_days' => 30
                ]
            ]);

            // Create test user
            User::updateOrCreate([
                'email' => 'test@test.com',
            ], [
                'tenant_id' => $testTenant->id,
                'first_name' => 'Test',
                'last_name' => 'User',
                'name' => 'Test User',
                'email' => 'test@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
        }

        $this->command->info('✅ Tenants seeded successfully!');
        $this->command->info('📧 Login Credentials:');
        $this->command->table(
            ['Tenant', 'Email', 'Password'],
            [
                ['Default ISP', 'admin@defaultisp.ug', 'password'],
                ['Coffee House WiFi', 'admin@coffeehouse.ug', 'password'],
                ['FastNet Broadband', 'admin@fastnet.ug', 'password'],
                app()->environment(['local', 'development']) ? ['Test ISP', 'test@test.com', 'password'] : null
            ]
        );
    }
}
