<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

use Laravel\Sanctum\HasApiTokens;

class House extends Model
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
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        // Return the user's FCM token from the database
        // You will need to add an 'fcm_token' column to your houses table
        return $this->fcm_token;
    }
}
