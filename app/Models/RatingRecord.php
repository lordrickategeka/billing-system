<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'service_id', 'subscription_id', 'billing_period',
        'period_start', 'period_end', 'total_input_octets', 'total_output_octets',
        'total_session_time', 'session_count', 'usage_charge', 'subscription_charge',
        'total_charge', 'is_invoiced', 'invoice_id'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'usage_charge' => 'decimal:2',
        'subscription_charge' => 'decimal:2',
        'total_charge' => 'decimal:2',
        'is_invoiced' => 'boolean',
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

    public function getTotalBytesAttribute(): int
    {
        return $this->total_input_octets + $this->total_output_octets;
    }

    public function getFormattedUsageAttribute(): string
    {
        $bytes = $this->getTotalBytesAttribute();
        return $this->formatBytes($bytes);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
