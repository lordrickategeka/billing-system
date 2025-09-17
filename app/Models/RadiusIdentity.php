<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiusIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'service_id', 'subscription_id', 'voucher_id',
        'username', 'password', 'token', 'mac_address', 'mac_binding',
        'circuit_id', 'ont_serial', 'static_ip', 'address_pool',
        'auth_type', 'status', 'last_auth_at', 'radius_attributes'
    ];

    protected $casts = [
        'mac_binding' => 'boolean',
        'last_auth_at' => 'timestamp',
        'radius_attributes' => 'array',
    ];

    protected $hidden = [
        'password', 'token'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
