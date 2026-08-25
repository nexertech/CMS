<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sub_categories';

    protected $fillable = [
        'category_id',
        'name',
        'app_name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the main complaint category.
     */
    public function category()
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id', 'id');
    }

    /**
     * Get complaints associated with this subcategory.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'sub_category_id', 'id');
    }
}
