<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrice extends Model
{
    protected $fillable = ['artist_id', 'service_category_id', 'price'];

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}
