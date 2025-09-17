<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RadiusSyncService;
use App\Models\NetworkDevice;

class SyncRadiusCommand extends Command
{
    protected $signature = 'radius:sync {--type=all : Type to sync (all|identities|devices)}';
    protected $description = 'Sync Laravel data with FreeRADIUS database';

    public function handle(RadiusSyncService $radiusSync)
    {
        $type = $this->option('type');

        $this->info("Starting RADIUS sync for: {$type}");

        switch ($type) {
            case 'identities':
                $count = $radiusSync->syncAllIdentities();
                $this->info("Synced {$count} radius identities");
                break;

            case 'devices':
                $count = 0;
                NetworkDevice::where('is_active', true)->each(function ($device) use ($radiusSync, &$count) {
                    if ($radiusSync->syncNetworkDevice($device)) {
                        $count++;
                    }
                });
                $this->info("Synced {$count} network devices");
                break;

            case 'all':
            default:
                // Sync identities
                $identityCount = $radiusSync->syncAllIdentities();
                $this->info("Synced {$identityCount} radius identities");

                // Sync devices
                $deviceCount = 0;
                NetworkDevice::where('is_active', true)->each(function ($device) use ($radiusSync, &$deviceCount) {
                    if ($radiusSync->syncNetworkDevice($device)) {
                        $deviceCount++;
                    }
                });
                $this->info("Synced {$deviceCount} network devices");
                break;
        }

        $this->info('RADIUS sync completed!');
    }
}
