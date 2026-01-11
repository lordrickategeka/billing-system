<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => env('MIKROTIK_HOST', '192.168.88.1'),
            'user' => env('MIKROTIK_USER', 'admin'),
            'pass' => env('MIKROTIK_PASS',  ''),
            'port' => env('MIKROTIK_PORT', 8728),
            'timeout' => 3,
        ]);
    }

    public function createHotspotUser($username, $password, $profile, $limitUptime = null)
    {
        $query = (new Query('/ip/hotspot/user/add'))
           ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $profile);

        if ($limitUptime) {
            $query->equal('limit-uptime', $limitUptime);
        }

        return $this->client->query($query)->read();
    }

    public function hotspotUserExists($username)
    {
        $query = (new Query('/ip/hotspot/user/print'))
            ->where('name', $username);

        $response = $this->client->query($query)->read();

        return count($response) > 0;
    }

    public function removeHotspotUser($username)
    {
        // Get ID of user
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
        $result = $this->client->query($query)->read();

        if (count($result)) {
            $id = $result[0]['.id'];

            $removeQuery = (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $id);

            return $this->client->query($removeQuery)->read();
        }

        return false;
    }
}
