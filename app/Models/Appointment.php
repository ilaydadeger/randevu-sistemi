<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'artist_id',
        'client_name',
        'appointment_date',
        'appointment_time',
        'image_path',
        'estimated_price',
        'status',
        'tracking_code',
    ];

    /**
     * Bu randevunun sahibi tırnakçıya (User) ait ilişki.
     */
    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
