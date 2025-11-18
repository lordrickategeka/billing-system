<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestTenantRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tenant-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant registration functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Tenant Registration...');

        try {
            // Generate slug from company name
            $companyName = 'Test ISP Company';
            $slug = Str::slug($companyName);

            // Ensure unique slug
            $counter = 1;
            $originalSlug = $slug;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create tenant
            $tenant = Tenant::create([
                'name' => $companyName,
                'slug' => $slug,
                'type' => 'isp',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'country' => 'US',
                'currency' => 'USD',
                'timezone' => 'UTC',
                'address' => '123 Test Street, Test City',
                'settings' => [
                    'services' => ['bandwidth_management' => true],
                    'launch_date' => null,
                    'request_quote' => false,
                    'network_system' => 'mikrotik',
                    'radius_ip' => '192.168.1.1',
                    'gateway' => '192.168.1.1',
                    'billing_features' => ['recurring' => true]
                ],
                'status' => 'active'
            ]);

            $this->info('Tenant created successfully: ' . $tenant->name . ' (ID: ' . $tenant->id . ')');

            // Create admin user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'name' => 'Test Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'timezone' => 'UTC'
            ]);

            $this->info('Admin user created successfully: ' . $user->name . ' (ID: ' . $user->id . ')');

            $this->info('✅ Tenant registration test completed successfully!');
            $this->line('');
            $this->line('Created:');
            $this->line('- Tenant: ' . $tenant->name . ' (' . $tenant->slug . ')');
            $this->line('- Admin User: ' . $user->name . ' (' . $user->email . ')');

        } catch (\Exception $e) {
            $this->error('❌ Tenant registration test failed: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
