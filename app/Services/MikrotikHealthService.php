<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MikrotikHealthService
{
    /**
     * Check whether Mikrotik device is reachable (powered on)
     */
    public function isRouterOnline(string $ip, int $port = 8728, int $timeout = 2): bool
    {
        try {
            $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);

            if ($connection) {
                fclose($connection);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Mikrotik connectivity check failed", [
                'router_ip' => $ip,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check whether Mikrotik RADIUS service is reachable
     * Port 1812 (authentication) and 1813 (accounting)
     */
    public function isRadiusOnline(string $ip, int $timeout = 2): bool
    {
        $auth = @fsockopen($ip, 1812, $errno1, $err1, $timeout);
        $acct = @fsockopen($ip, 1813, $errno2, $err2, $timeout);

        if ($auth) fclose($auth);
        if ($acct) fclose($acct);

        return $auth && $acct;
    }

    /**
     * High-level status report
     */
    public function getStatus(string $ip): array
    {
        return [
            'mikrotik_online' => $this->isRouterOnline($ip),
            'radius_online'   => $this->isRadiusOnline($ip),
            'checked_at'      => now()->toDateTimeString(),
        ];
    }
}
