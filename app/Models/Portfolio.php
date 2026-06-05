<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'image_path',
    ];

    /**
     * Get the artist (User) that owns this portfolio item.
     */
    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
