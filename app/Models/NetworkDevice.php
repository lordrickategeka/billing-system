<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'nas_ip_address', 'secret', 'vendor', 'type',
        'site_name', 'location', 'management', 'capabilities',
        'is_active', 'last_seen_at'
    ];

    protected $casts = [
        'location' => 'array',
        'management' => 'array',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'last_seen_at' => 'timestamp',
    ];

    protected $hidden = [
        'secret'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supportsCoA(): bool
    {
        return $this->capabilities['coa'] ?? false;
    }

    public function supportsDM(): bool
    {
        return $this->capabilities['dm'] ?? false;
    }
}
