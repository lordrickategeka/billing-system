<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $connection = 'radius';   // use radius DB
    protected $table = 'radcheck';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];
}
