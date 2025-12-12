<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrontendUserLocation extends Model
{
    use HasFactory;

    protected $table = 'frontend_user_locations';

    protected $fillable = [
        'frontend_user_id',
        'city_id',
        'sector_id',
    ];

    /**
     * Get the frontend user that owns this location
     */
    public function frontendUser(): BelongsTo
    {
        return $this->belongsTo(FrontendUser::class, 'frontend_user_id');
    }

    /**
     * Get the city
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the sector
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }
}

