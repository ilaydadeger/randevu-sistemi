<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'slug', 'password', 'role', 'bio', 'profile_photo_path', 'portfolio_image_1', 'portfolio_image_2', 'portfolio_image_3', 'salon_name', 'show_portfolio', 'work_hours', 'exclude_length_pricing', 'exclude_shape_pricing'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'work_hours' => 'array',
            'exclude_length_pricing' => 'boolean',
            'exclude_shape_pricing' => 'boolean',
        ];
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class, 'artist_id');
    }
    
    public function userPrices()
    {
        return $this->hasMany(UserPrice::class, 'artist_id');
    }
    
    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'artist_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'artist_id');
    }
}
