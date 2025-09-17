<?php

namespace App\Observers;

use App\Models\RadiusIdentity;
use App\Services\RadiusSyncService;

class RadiusIdentityObserver
{
    protected $radiusSync;

    public function __construct(RadiusSyncService $radiusSync)
    {
        $this->radiusSync = $radiusSync;
    }

    public function created(RadiusIdentity $identity)
    {
        $this->radiusSync->syncIdentity($identity);
    }

    public function updated(RadiusIdentity $identity)
    {
        $this->radiusSync->syncIdentity($identity);
    }

    public function deleted(RadiusIdentity $identity)
    {
        // Clear from RADIUS when deleted
        $this->radiusSync->syncIdentity($identity->fill(['status' => 'deleted']));
    }
}
