<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\RadiusIdentity;
use App\Models\NetworkDevice;
use App\Observers\RadiusIdentityObserver;
use App\Observers\NetworkDeviceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        RadiusIdentity::observe(RadiusIdentityObserver::class);
        NetworkDevice::observe(NetworkDeviceObserver::class);
    }
}
