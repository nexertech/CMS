<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAddData extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'spare_id',
        'add_date',
        'category',
        'product_name',
        'quantity_added',
        'available_stock_after',
        'remarks',
        'added_by',
        'reference_id',
    ];

    protected $casts = [
        'add_date' => 'date',
        'quantity_added' => 'integer',
        'available_stock_after' => 'integer',
    ];

    /**
     * Get the spare that owns the stock add data.
     */
    public function spare(): BelongsTo
    {
        return $this->belongsTo(Spare::class, 'spare_id', 'id');
    }

    /**
     * Get the employee who added the stock.
     */
    public function addedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'added_by', 'id');
    }
}

