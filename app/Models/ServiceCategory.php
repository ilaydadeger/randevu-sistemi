<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['group_name', 'name'];

    public function userPrices()
    {
        return $this->hasMany(UserPrice::class);
    }
}
