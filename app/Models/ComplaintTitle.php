<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintTitle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'questions',
    ];

    public function category()
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    /**
     * Get all unique categories
     */
    public static function getCategories()
    {
        return ComplaintCategory::whereHas('titles')
            ->orderBy('name')
            ->pluck('name');
    }

    /**
     * Get titles by category
     */
    public static function getTitlesByCategory($category)
    {
        $query = self::query();
        
        if (is_numeric($category)) {
            $query->where('category_id', $category);
        } else {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        
        return $query->orderBy('title')->get();
    }
}
