<?php

namespace App\Observers;

use App\Models\NetworkDevice;
use App\Services\RadiusSyncService;

class NetworkDeviceObserver
{
    protected $radiusSync;

    public function __construct(RadiusSyncService $radiusSync)
    {
        $this->radiusSync = $radiusSync;
    }

    public function created(NetworkDevice $device)
    {
        if ($device->is_active) {
            $this->radiusSync->syncNetworkDevice($device);
        }
    }

    public function updated(NetworkDevice $device)
    {
        $this->radiusSync->syncNetworkDevice($device);
    }
}
