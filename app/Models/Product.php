<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'type',        // hotspot or broadband
        'speed_up',
        'speed_down',
        'quota_mb',
        'session_minute',
        'price',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
