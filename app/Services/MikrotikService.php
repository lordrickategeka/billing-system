<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use App\Models\NetworkDevice;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected $client;
    protected NetworkDevice $device;

    public function __construct(NetworkDevice $device)
    {
        $this->device = $device;

        $this->client = new Client([
            'host' => $device->nas_ip_address,
            'user' => $device->api_username,
            'pass' => $device->api_password,
            'port' => $device->api_port ?? 8728,
            'ssl'  => $device->api_ssl ?? false,
            'timeout' => 5,
        ]);
    }

    protected function execute(Query $query)
    {
        try {
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            Log::error('Mikrotik API Error: ' . $e->getMessage(), [
                'device' => $this->device->id
            ]);

            return false;
        }
    }


    public function createHotspotUser(
        $username,
        $password,
        $profile,
        $limitUptime = null,
        $limitBytes = null
    ) {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $profile);

        if ($limitUptime) {
            $query->equal('limit-uptime', $limitUptime);
        }

        if ($limitBytes) {
            $query->equal('limit-bytes-total', $limitBytes);
        }

        return $this->execute($query);
    }

    public function resetUserCounters($username)
    {
        $query = (new Query('/ip/hotspot/user/reset-counters'))
            ->equal('numbers', $username);

        return $this->execute($query);
    }

    public function hotspotUserExists($username)
    {
        $query = (new Query('/ip/hotspot/user/print'))
            ->where('name', $username);

        $response = $this->execute($query);

        return $response && count($response) > 0;
    }

    public function removeHotspotUser($username)
    {
        $query = (new Query('/ip/hotspot/user/print'))
            ->where('name', $username);

        $result = $this->execute($query);

        if ($result && count($result)) {

            $id = $result[0]['.id'];

            $removeQuery = (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $id);

            return $this->execute($removeQuery);
        }

        return false;
    }

    public function disconnectActiveSession($username)
    {
        $query = (new Query('/ip/hotspot/active/print'))
            ->where('user', $username);

        $active = $this->execute($query);

        if ($active && count($active)) {

            $id = $active[0]['.id'];

            $removeQuery = (new Query('/ip/hotspot/active/remove'))
                ->equal('.id', $id);

            return $this->execute($removeQuery);
        }

        return false;
    }

    public function disableUser($username)
    {
        $query = (new Query('/ip/hotspot/user/set'))
            ->equal('numbers', $username)
            ->equal('disabled', 'yes');

        return $this->execute($query);
    }

    public function enableUser($username)
    {
        $query = (new Query('/ip/hotspot/user/set'))
            ->equal('numbers', $username)
            ->equal('disabled', 'no');

        return $this->execute($query);
    }
}
