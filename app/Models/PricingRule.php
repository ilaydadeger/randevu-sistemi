<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = [
        'artist_id',
        'service_name',
        'base_price',
    ];

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
