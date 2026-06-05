<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleBlock extends Model
{
    protected $fillable = ['user_id', 'blocked_date', 'blocked_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
