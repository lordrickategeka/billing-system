<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class ShowTenantSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:tenant-settings {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show how Service Configuration data is stored in tenant settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant') ?? 1;

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant with ID {$tenantId} not found.");
            return;
        }

        $this->info("🏢 Tenant: {$tenant->name} ({$tenant->type})");
        $this->line("📍 Storage Location: tenants.settings (JSON field)");
        $this->line('');

        $this->info('📋 Service Configuration Storage Structure:');
        $this->line('===============================================');

        $settings = $tenant->settings ?? [];

        if ($tenant->type === 'isp') {
            $this->info('🌐 ISP Configuration:');
            $this->line('  Network System: ' . ($settings['network_system'] ?? 'Not set'));
            $this->line('  RADIUS Server IP: ' . ($settings['radius_ip'] ?? 'Not set'));
            $this->line('  RADIUS Secret: ' . ($settings['radius_secret'] ?? 'Not set'));
            $this->line('  Gateway IP: ' . ($settings['gateway'] ?? 'Not set'));

            $this->line('');
            $this->info('💰 Billing Features:');
            $billingFeatures = $settings['billing_features'] ?? [];
            if (is_array($billingFeatures)) {
                foreach ($billingFeatures as $feature => $enabled) {
                    $status = $enabled ? '✅' : '❌';
                    $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $feature)));
                }
            } else {
                $this->line('  No billing features configured');
            }

        } elseif ($tenant->type === 'hotspot') {
            $this->info('📶 Hotspot Configuration:');
            $this->line('  Hotspot Type: ' . ($settings['hotspot_type'] ?? 'Not set'));
            $this->line('  Access Points: ' . ($settings['access_points'] ?? 'Not set'));
            $this->line('  Daily Users: ' . ($settings['daily_users'] ?? 'Not set'));

            $this->line('');
            $this->info('🔐 Authentication Methods:');
            $authMethods = $settings['auth_methods'] ?? [];
            if (is_array($authMethods)) {
                foreach ($authMethods as $method => $enabled) {
                    $status = $enabled ? '✅' : '❌';
                    $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $method)));
                }
            }
        }

        $this->line('');
        $this->info('📊 Raw JSON Data:');
        $this->line('================');
        $this->line(json_encode($settings, JSON_PRETTY_PRINT));

        $this->line('');
        $this->info('💡 Access Methods in Code:');
        $this->line("// Get network system: \$tenant->settings['network_system']");
        $this->line("// Get RADIUS IP: \$tenant->settings['radius_ip']");
        $this->line("// Get billing features: \$tenant->settings['billing_features']");
        $this->line("// Check if recurring billing enabled: \$tenant->settings['billing_features']['recurring']");
    }
}
