<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpareApprovalItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'performa_id',
        'spare_id',
        'quantity_requested',
        'quantity_approved',
        'reason',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved' => 'integer',
    ];

    /**
     * Get the performa that owns the item.
     */
    public function performa(): BelongsTo
    {
        return $this->belongsTo(SpareApprovalPerforma::class, 'performa_id', 'id');
    }

    /**
     * Get the spare that is requested.
     */
    public function spare(): BelongsTo
    {
        return $this->belongsTo(Spare::class, 'spare_id', 'id');
    }

    /**
     * Get spare name
     */
    public function getSpareNameAttribute(): string
    {
        return $this->spare ? $this->spare->item_name : 'Unknown Spare';
    }

    /**
     * Get spare category
     */
    public function getSpareCategoryAttribute(): string
    {
        return $this->spare ? $this->spare->getCategoryDisplayAttribute() : 'Unknown';
    }

    /**
     * Get spare unit
     */
    public function getSpareUnitAttribute(): string
    {
        return $this->spare ? $this->spare->getUnitDisplayAttribute() : 'Unknown';
    }

    /**
     * Get spare unit price
     */
    public function getSpareUnitPriceAttribute(): float
    {
        return $this->spare ? $this->spare->unit_price : 0;
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'PKR ' . number_format($this->getSpareUnitPriceAttribute(), 2);
    }

    /**
     * Get total estimated cost for this item
     */
    public function getTotalEstimatedCostAttribute(): float
    {
        return $this->quantity_requested * $this->getSpareUnitPriceAttribute();
    }

    /**
     * Get formatted total estimated cost
     */
    public function getFormattedTotalEstimatedCostAttribute(): string
    {
        return 'PKR ' . number_format($this->getTotalEstimatedCostAttribute(), 2);
    }

    /**
     * Check if spare is available in sufficient quantity
     */
    public function isSpareAvailable(): bool
    {
        if (!$this->spare) {
            return false;
        }

        return $this->spare->current_stock >= $this->quantity_requested;
    }

    /**
     * Get availability status
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if (!$this->spare) {
            return 'Spare not found';
        }

        if ($this->isSpareAvailable()) {
            return 'Available';
        }

        return 'Insufficient stock';
    }

    /**
     * Get stock shortfall
     */
    public function getStockShortfallAttribute(): int
    {
        if (!$this->spare) {
            return $this->quantity_requested;
        }

        return max(0, $this->quantity_requested - $this->spare->current_stock);
    }
}
