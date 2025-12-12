<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintTitle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'title',
        'description',
    ];

    /**
     * Get all unique categories
     */
    public static function getCategories()
    {
        return self::distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * Get titles by category
     */
    public static function getTitlesByCategory($category)
    {
        return self::where('category', $category)
            ->orderBy('title')
            ->get();
    }
}
