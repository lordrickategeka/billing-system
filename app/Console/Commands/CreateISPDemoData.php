<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\RadiusIdentity;
use App\Models\NetworkDevice;
use Illuminate\Support\Str;

class CreateISPDemoData extends Command
{
    protected $signature = 'isp:demo {--customers=10} {--services=20}';
    protected $description = 'Create demo ISP data for testing';

    public function handle()
    {
        $this->info('Creating ISP demo data...');

        $tenant = Tenant::first();
        if (!$tenant) {
            $this->error('No tenant found. Run php artisan billing:setup first.');
            return 1;
        }

        // Create demo customers
        $customerCount = $this->option('customers');
        $this->info("Creating {$customerCount} demo customers...");

        $customers = collect();
        for ($i = 1; $i <= $customerCount; $i++) {
            $customers->push(Customer::create([
                'tenant_id' => $tenant->id,
                'customer_number' => 'CUST-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'address' => [
                    'street' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'US'
                ],
                'kyc_data' => [
                    'id_type' => 'national_id',
                    'id_number' => fake()->numerify('##########'),
                    'verified' => fake()->boolean(80),
                    'verified_at' => fake()->boolean(80) ? now() : null
                ],
                'status' => fake()->randomElement(['active', 'active', 'active', 'suspended']),
                'kyc_verified_at' => fake()->boolean(80) ? now() : null
            ]));
        }

        // Create demo broadband services
        $serviceCount = $this->option('services');
        $this->info("Creating {$serviceCount} demo services...");

        $broadbandProducts = Product::where('tenant_id', $tenant->id)
            ->where('service_type', 'broadband')
            ->get();

        if ($broadbandProducts->isEmpty()) {
            $this->error('No broadband products found. Run php artisan billing:setup --demo first.');
            return 1;
        }

        for ($i = 1; $i <= $serviceCount; $i++) {
            $customer = $customers->random();
            $product = $broadbandProducts->random();

            $service = Service::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'service_number' => 'SV-' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'service_type' => 'broadband',
                'installation_address' => [
                    'street' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'coordinates' => [
                        'lat' => fake()->latitude(),
                        'lng' => fake()->longitude()
                    ]
                ],
                'circuit_id' => fake()->randomElement(['eth1/0/', 'eth2/0/', 'gig0/0/']) . fake()->numberBetween(1, 48) . ':' . fake()->numberBetween(100, 4000),
                'ont_serial' => 'ZTEG' . fake()->numerify('########'),
                'static_ip' => fake()->boolean(20) ? fake()->ipv4() : null,
                'status' => fake()->randomElement(['pending', 'active', 'active', 'active', 'suspended']),
                'activated_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-6 months', 'now') : null
            ]);

            // Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'service_id' => $service->id,
                'product_id' => $product->id,
                'start_at' => $service->activated_at ?: now(),
                'end_at' => now()->addMonth(),
                'auto_renew' => true,
                'status' => $service->status === 'active' ? 'active' : 'pending',
                'activated_at' => $service->activated_at
            ]);

            // Create RADIUS identity
            $authType = fake()->randomElement(['pppoe', 'pppoe', 'pppoe', 'ipoe']); // 75% PPPoE
            $username = strtolower($authType . '_' . $service->service_number);

            RadiusIdentity::create([
                'tenant_id' => $tenant->id,
                'service_id' => $service->id,
                'subscription_id' => $subscription->id,
                'username' => $username,
                'password' => Str::random(12),
                'circuit_id' => $service->circuit_id,
                'ont_serial' => $service->ont_serial,
                'static_ip' => $service->static_ip,
                'auth_type' => $authType,
                'status' => $service->status === 'active' ? 'active' : 'suspended'
            ]);
        }

        // Create some demo network devices if none exist
        if (NetworkDevice::where('tenant_id', $tenant->id)->count() === 0) {
            $this->info('Creating demo network devices...');

            $devices = [
                [
                    'name' => 'Main BNG Router',
                    'nas_ip_address' => '10.0.1.1',
                    'vendor' => 'cisco',
                    'type' => 'bng',
                    'site_name' => 'Main Data Center'
                ],
                [
                    'name' => 'Backup BNG Router',
                    'nas_ip_address' => '10.0.1.2',
                    'vendor' => 'cisco',
                    'type' => 'bng',
                    'site_name' => 'Main Data Center'
                ],
                [
                    'name' => 'OLT Fiber Node 1',
                    'nas_ip_address' => '10.0.2.10',
                    'vendor' => 'huawei',
                    'type' => 'olt',
                    'site_name' => 'North Tower'
                ],
                [
                    'name' => 'OLT Fiber Node 2',
                    'nas_ip_address' => '10.0.2.11',
                    'vendor' => 'zte',
                    'type' => 'olt',
                    'site_name' => 'South Tower'
                ]
            ];

            foreach ($devices as $deviceData) {
                NetworkDevice::create(array_merge($deviceData, [
                    'tenant_id' => $tenant->id,
                    'secret' => 'testing123',
                    'location' => [
                        'address' => fake()->address(),
                        'lat' => fake()->latitude(),
                        'lng' => fake()->longitude()
                    ],
                    'capabilities' => [
                        'coa' => true,
                        'dm' => true,
                        'accounting' => true,
                        'pppoe' => $deviceData['type'] === 'bng',
                        'ipoe' => $deviceData['type'] === 'bng'
                    ],
                    'is_active' => true
                ]));
            }
        }

        $this->info('✅ ISP demo data created successfully!');
        $this->info("   • {$customerCount} customers");
        $this->info("   • {$serviceCount} broadband services");
        $this->info("   • Network devices configured");
        $this->info("\nYou can now:");
        $this->info("1. Visit /isp/customers to see customer management");
        $this->info("2. Visit /isp/services to see service management");
        $this->info("3. Visit /isp/network to see network devices");
        $this->info("4. Run 'php artisan radius:sync' to sync to RADIUS");

        return 0;
    }
}
