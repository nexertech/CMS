<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class House extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, \Illuminate\Notifications\Notifiable;

    protected $fillable = [
        'username',
        'house_no',
        'password',
        'name',
        'phone',
        'city_id',
        'sector_id',
        'address',
        'status',
        'fcm_token',
        'password_updated_at',
    ];

    protected $hidden = [
        'password',
    ];

    // Prevent eager loading of relationships during authentication
    protected $with = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password_updated_at' => 'datetime',
    ];

    /**
     * Automatically hash password when setting
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Get the city (GE Group) that owns the house.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the sector (GE Node) that owns the house.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    /**
     * Get the complaints for the house.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'house_id');
    }
    
    /**
     * Route notifications for the FCM channel.
     *
     * @return string|null
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->fcm_token;
    }

    /**
     * Override toArray to prevent loading all relationships
     * This prevents memory exhaustion during authentication
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Remove relationships that shouldn't be loaded during auth
        unset($array['complaints']);
        unset($array['notifications']);
        unset($array['tokens']);
        
        return $array;
    }
}
