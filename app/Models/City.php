<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'cme_id',
        'name',
        'status',
    ];

    /**
     * Get the users for this city.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'city_id', 'id');
    }

    /**
     * Get the sectors for this city.
     */
    public function sectors(): HasMany
    {
        return $this->hasMany(Sector::class, 'city_id', 'id');
    }

    /**
     * Get the CMES this city belongs to.
     */
    public function cme()
    {
        return $this->belongsTo(Cme::class);
    }
}
