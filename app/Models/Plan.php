<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'hp_plans';

    protected $fillable = [
        'name',
        'display_name',
        'amount',
        'duration',
        'speed_limit',
        'mikrotik_profile',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    // Methods
    public function getFormattedAmountAttribute()
    {
        return 'UGX ' . number_format($this->amount);
    }

    public function getDurationInSecondsAttribute()
    {
        // Convert duration string to seconds
        $duration = $this->duration;
        $seconds = 0;

        if (preg_match('/(\d+)h/', $duration, $matches)) {
            $seconds += $matches[1] * 3600;
        }
        if (preg_match('/(\d+)m/', $duration, $matches)) {
            $seconds += $matches[1] * 60;
        }
        if (preg_match('/(\d+)s/', $duration, $matches)) {
            $seconds += $matches[1];
        }

        return $seconds;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('amount');
    }
}
