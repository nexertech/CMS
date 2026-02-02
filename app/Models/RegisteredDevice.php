<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisteredDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'is_active',
        'assigned_to_house_no',
        'city_id',
        'sector_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
