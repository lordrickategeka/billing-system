<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\RadiusSyncService;
use Illuminate\Support\Facades\DB;

class TestRadiusIntegrationCommand extends Command
{
    protected $signature = 'billing:test-radius {--generate-test-data}';
    protected $description = 'Test RADIUS integration and optionally generate test data';

    public function handle(RadiusSyncService $radiusSync)
    {
        $this->info('Testing RADIUS Integration...');

        try {
            // Test RADIUS connection
            $this->testRadiusConnection();

            if ($this->option('generate-test-data')) {
                $this->generateTestData($radiusSync);
            }

            $this->testRadiusSync($radiusSync);

        } catch (\Exception $e) {
            $this->error("Test failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function testRadiusConnection()
    {
        $this->info('Testing RADIUS database connection...');

        try {
            $connection = DB::connection('radius');
            $pdo = $connection->getPdo();

            // Test basic queries
            $nasCount = $connection->table('nas')->count();
            $checkCount = $connection->table('radcheck')->count();
            $replyCount = $connection->table('radreply')->count();

            $this->info("✓ RADIUS connection successful");
            $this->info("  - NAS devices: {$nasCount}");
            $this->info("  - Auth entries: {$checkCount}");
            $this->info("  - Reply entries: {$replyCount}");

        } catch (\Exception $e) {
            $this->error("✗ RADIUS connection failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function generateTestData(RadiusSyncService $radiusSync)
    {
        $this->info('Generating test data...');

        $tenant = Tenant::where('slug', 'default')->first();
        if (!$tenant) {
            $this->error('Default tenant not found. Run php artisan billing:setup first.');
            return;
        }

        // Generate test vouchers
        $product = Product::where('service_type', 'hotspot')->first();
        if ($product) {
            $vouchers = [];
            for ($i = 1; $i <= 5; $i++) {
                $vouchers[] = Voucher::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'code' => 'TEST' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'batch_id' => 'TEST_BATCH',
                    'value' => $product->price,
                    'max_uses' => 1,
                    'state' => 'unused'
                ]);
            }

            $this->info("✓ Generated " . count($vouchers) . " test vouchers");
            $this->info("  - Test codes: TEST0001, TEST0002, TEST0003, TEST0004, TEST0005");
        }
    }

    private function testRadiusSync(RadiusSyncService $radiusSync)
    {
        $this->info('Testing RADIUS sync...');

        // Sync network devices
        $deviceCount = $radiusSync->syncAllIdentities();
        $this->info("✓ Synced identities: {$deviceCount}");

        // Test accounting queries
        $recentSessions = DB::connection('radius')
            ->table('radacct')
            ->where('acctstarttime', '>=', now()->subHours(24))
            ->count();

        $this->info("✓ Recent sessions (24h): {$recentSessions}");

        $this->info("\n🎉 RADIUS integration test completed successfully!");
    }
}
