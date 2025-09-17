<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
     use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'status',       // active, suspended, expired
        'start_at',
        'end_at',
    ];

    protected $dates = ['start_at', 'end_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
