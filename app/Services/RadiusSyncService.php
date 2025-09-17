<?php

// app/Services/RadiusSyncService.php
namespace App\Services;

use App\Models\RadiusIdentity;
use App\Models\Subscription;
use App\Models\NetworkDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusSyncService
{
    protected $radiusConnection = 'radius';

    /**
     * Sync a radius identity to FreeRADIUS tables
     */
    public function syncIdentity(RadiusIdentity $identity): bool
    {
        try {
            DB::connection($this->radiusConnection)->transaction(function () use ($identity) {
                // Clear existing entries
                $this->clearRadiusUser($identity->username);

                if ($identity->status === 'active') {
                    $this->createRadiusUser($identity);
                }
            });

            Log::info("Synced RADIUS identity", ['username' => $identity->username]);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to sync RADIUS identity", [
                'username' => $identity->username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create RADIUS user entries
     */
    protected function createRadiusUser(RadiusIdentity $identity): void
    {
        $subscription = $identity->subscription;
        $product = $subscription->product ?? null;
        $policy = $subscription->policy ?? null;

        // Insert into radcheck (authentication)
        $this->insertRadcheck($identity);

        // Insert into radreply (authorization attributes)
        $this->insertRadreply($identity, $product, $policy);

        // Handle group-based attributes if needed
        if ($policy && $product->class) {
            $this->ensureRadiusGroup($product->class, $policy);
            $this->assignUserToGroup($identity->username, $product->class);
        }
    }

    /**
     * Insert authentication attributes into radcheck
     */
    protected function insertRadcheck(RadiusIdentity $identity): void
    {
        $checks = [];

        // Password authentication
        if ($identity->password) {
            $checks[] = [
                'username' => $identity->username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $identity->password
            ];
        }

        // MAC address binding for hotspot
        if ($identity->mac_binding && $identity->mac_address) {
            $checks[] = [
                'username' => $identity->username,
                'attribute' => 'Calling-Station-Id',
                'op' => '==',
                'value' => strtoupper(str_replace([':', '-'], '', $identity->mac_address))
            ];
        }

        // Simultaneous use limit
        $checks[] = [
            'username' => $identity->username,
            'attribute' => 'Simultaneous-Use',
            'op' => ':=',
            'value' => '1'
        ];

        foreach ($checks as $check) {
            DB::connection($this->radiusConnection)->table('radcheck')->insert($check);
        }
    }

    /**
     * Insert authorization attributes into radreply
     */
    protected function insertRadreply(RadiusIdentity $identity, $product = null, $policy = null): void
    {
        $replies = [];

        if ($product) {
            // Speed limits
            if ($product->speed_down_kbps || $product->speed_up_kbps) {
                $speedLimit = ($product->speed_up_kbps ?: 0) . 'k/' . ($product->speed_down_kbps ?: 0) . 'k';
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => ':=',
                    'value' => $speedLimit
                ];

                // For Cisco/generic
                if ($product->speed_down_kbps) {
                    $replies[] = [
                        'username' => $identity->username,
                        'attribute' => 'Filter-Id',
                        'op' => ':=',
                        'value' => 'download_' . $product->speed_down_kbps . 'k'
                    ];
                }
            }

            // Session timeout
            if ($product->session_timeout) {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Session-Timeout',
                    'op' => ':=',
                    'value' => (string)$product->session_timeout
                ];
            }

            // Idle timeout
            if ($product->idle_timeout) {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Idle-Timeout',
                    'op' => ':=',
                    'value' => (string)$product->idle_timeout
                ];
            }

            // Data quota (for hotspot)
            if ($product->quota_mb && $product->service_type === 'hotspot') {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'ChilliSpot-Max-Total-Octets',
                    'op' => ':=',
                    'value' => (string)($product->quota_mb * 1024 * 1024)
                ];
            }
        }

        if ($policy) {
            // VLAN assignment
            if ($policy->vlan_id) {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Tunnel-Type',
                    'op' => ':=',
                    'value' => '13'
                ];
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Tunnel-Medium-Type',
                    'op' => ':=',
                    'value' => '6'
                ];
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Tunnel-Private-Group-Id',
                    'op' => ':=',
                    'value' => (string)$policy->vlan_id
                ];
            }

            // IP address assignment
            if ($identity->static_ip) {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => $identity->static_ip
                ];
            } elseif ($policy->address_pool || $identity->address_pool) {
                $pool = $identity->address_pool ?: $policy->address_pool;
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => 'Framed-Pool',
                    'op' => ':=',
                    'value' => $pool
                ];
            }

            // DNS servers
            if ($policy->dns_servers) {
                $dnsServers = explode(',', $policy->dns_servers);
                foreach ($dnsServers as $i => $dns) {
                    if ($i == 0) {
                        $replies[] = [
                            'username' => $identity->username,
                            'attribute' => 'MS-Primary-DNS-Server',
                            'op' => ':=',
                            'value' => trim($dns)
                        ];
                    } elseif ($i == 1) {
                        $replies[] = [
                            'username' => $identity->username,
                            'attribute' => 'MS-Secondary-DNS-Server',
                            'op' => ':=',
                            'value' => trim($dns)
                        ];
                    }
                }
            }

            // Custom RADIUS attributes from policy
            if ($policy->radius_attributes) {
                foreach ($policy->radius_attributes as $attr => $value) {
                    $replies[] = [
                        'username' => $identity->username,
                        'attribute' => $attr,
                        'op' => ':=',
                        'value' => (string)$value
                    ];
                }
            }
        }

        // Insert cached attributes from identity
        if ($identity->radius_attributes) {
            foreach ($identity->radius_attributes as $attr => $value) {
                $replies[] = [
                    'username' => $identity->username,
                    'attribute' => $attr,
                    'op' => ':=',
                    'value' => (string)$value
                ];
            }
        }

        foreach ($replies as $reply) {
            DB::connection($this->radiusConnection)->table('radreply')->insert($reply);
        }
    }

    /**
     * Clear existing RADIUS user data
     */
    protected function clearRadiusUser(string $username): void
    {
        DB::connection($this->radiusConnection)->table('radcheck')
            ->where('username', $username)->delete();

        DB::connection($this->radiusConnection)->table('radreply')
            ->where('username', $username)->delete();

        DB::connection($this->radiusConnection)->table('radusergroup')
            ->where('username', $username)->delete();
    }

    /**
     * Ensure RADIUS group exists with policy attributes
     */
    protected function ensureRadiusGroup(string $groupName, $policy): void
    {
        // Check if group already exists
        $exists = DB::connection($this->radiusConnection)->table('radgroupreply')
            ->where('groupname', $groupName)->exists();

        if (!$exists && $policy) {
            $groupAttrs = [];

            // Add policy attributes to group
            if ($policy->radius_attributes) {
                foreach ($policy->radius_attributes as $attr => $value) {
                    $groupAttrs[] = [
                        'groupname' => $groupName,
                        'attribute' => $attr,
                        'op' => ':=',
                        'value' => (string)$value
                    ];
                }
            }

            if ($groupAttrs) {
                DB::connection($this->radiusConnection)->table('radgroupreply')
                    ->insert($groupAttrs);
            }
        }
    }

    /**
     * Assign user to RADIUS group
     */
    protected function assignUserToGroup(string $username, string $groupName): void
    {
        DB::connection($this->radiusConnection)->table('radusergroup')->insert([
            'username' => $username,
            'groupname' => $groupName,
            'priority' => 1
        ]);
    }

    /**
     * Sync network device to RADIUS NAS table
     */
    public function syncNetworkDevice(NetworkDevice $device): bool
    {
        try {
            DB::connection($this->radiusConnection)->table('nas')->updateOrInsert(
                ['nasname' => $device->nas_ip_address],
                [
                    'shortname' => $device->name,
                    'type' => $device->type,
                    'secret' => $device->secret,
                    'server' => $device->nas_ip_address,
                    'community' => 'public',
                    'description' => $device->site_name ?: $device->name
                ]
            );

            Log::info("Synced network device to RADIUS", ['device' => $device->name]);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to sync network device", [
                'device' => $device->name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send CoA (Change of Authorization) command
     */
    public function sendCoA(string $username, string $nasIp, array $attributes = []): bool
    {
        try {
            // This would integrate with a RADIUS client library
            // For now, we'll log the action
            Log::info("CoA command sent", [
                'username' => $username,
                'nas_ip' => $nasIp,
                'attributes' => $attributes
            ]);

            // TODO: Implement actual CoA sending using a RADIUS client
            // Example: radclient -x $nasIp:3799 coa $secret <<< "User-Name=$username"

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send CoA", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Disconnect user session
     */
    public function disconnectUser(string $username, string $nasIp): bool
    {
        try {
            // Send disconnect message
            Log::info("Disconnect command sent", [
                'username' => $username,
                'nas_ip' => $nasIp
            ]);

            // TODO: Implement actual disconnect using RADIUS client
            // Example: echo "User-Name=$username" | radclient -x $nasIp:3799 disconnect $secret

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to disconnect user", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Bulk sync all active identities
     */
    public function syncAllIdentities(): int
    {
        $synced = 0;

        RadiusIdentity::with(['subscription.product', 'subscription.policy'])
            ->where('status', 'active')
            ->chunk(100, function ($identities) use (&$synced) {
                foreach ($identities as $identity) {
                    if ($this->syncIdentity($identity)) {
                        $synced++;
                    }
                }
            });

        Log::info("Bulk sync completed", ['synced_count' => $synced]);
        return $synced;
    }

    /**
     * Get accounting data for a user
     */
    public function getAccountingData(string $username, int $days = 30): array
    {
        return DB::connection($this->radiusConnection)
            ->table('radacct')
            ->where('username', $username)
            ->where('acctstarttime', '>=', now()->subDays($days))
            ->orderBy('acctstarttime', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get current active sessions for a user
     */
    public function getActiveSessions(string $username): array
    {
        return DB::connection($this->radiusConnection)
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->get()
            ->toArray();
    }
}
