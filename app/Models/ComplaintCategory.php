<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'app_name',
        'description',
        'status',
    ];

    /**
     * Get the SLA rule for the category.
     */
    public function slaRule()
    {
        return $this->hasOne(SlaRule::class, 'category_id', 'id');
    }

    /**
     * Get the complaints for the category.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_id', 'id');
    }

    /**
     * Get the complaint titles for the category.
     */
    public function titles()
    {
        return $this->hasMany(ComplaintTitle::class, 'category_id', 'id');
    }
}
