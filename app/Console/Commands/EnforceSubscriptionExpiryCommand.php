<?php

namespace App\Console\Commands;

use App\Models\NetworkDevice;
use App\Models\RadiusIdentity;
use App\Models\Subscription;
use App\Services\MikrotikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnforceSubscriptionExpiryCommand extends Command
{
    protected $signature = 'subscriptions:enforce-expiry';

    protected $description = 'Disable and disconnect expired hotspot users from MikroTik';

    public function handle(): int
    {
        $now = now();
        $processed = 0;

        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereNotNull('expires_at')
                        ->where('expires_at', '<=', $now);
                })->orWhere(function ($q) use ($now) {
                    $q->whereNull('expires_at')
                        ->whereNotNull('end_at')
                        ->where('end_at', '<=', $now);
                });
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            $device = NetworkDevice::query()
                ->where('tenant_id', $subscription->tenant_id)
                ->where('is_active', true)
                ->first();

            if (!$device) {
                Log::warning('Expiry enforcement skipped: no active network device for tenant', [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                ]);

                $subscription->update(['status' => 'expired']);
                $processed++;
                continue;
            }

            $mikrotik = new MikrotikService($device);

            RadiusIdentity::query()
                ->where('subscription_id', $subscription->id)
                ->where('status', 'active')
                ->get()
                ->each(function (RadiusIdentity $identity) use ($mikrotik, $subscription, $device): void {
                    try {
                        $mikrotik->disableUser($identity->username);
                        $mikrotik->disconnectActiveSession($identity->username);

                        $identity->update(['status' => 'expired']);

                        Log::info('Expired user disabled and disconnected', [
                            'subscription_id' => $subscription->id,
                            'identity_id' => $identity->id,
                            'username' => $identity->username,
                            'device_id' => $device->id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to disconnect expired user', [
                            'subscription_id' => $subscription->id,
                            'identity_id' => $identity->id,
                            'username' => $identity->username,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });

            $subscription->update(['status' => 'expired']);
            $processed++;
        }

        $this->info("Processed {$processed} expired subscription(s).");

        return self::SUCCESS;
    }
}
