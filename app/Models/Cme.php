<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Get the GE groups (cities) that belong to this CMES.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}

