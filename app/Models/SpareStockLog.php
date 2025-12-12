<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpareStockLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'spare_id',
        'brand_name',
        'change_type',
        'quantity',
        'reference_id',
        'remarks',
    ];

    /**
     * Get the spare that owns the stock log.
     */
    public function spare(): BelongsTo
    {
        return $this->belongsTo(Spare::class, 'spare_id', 'id');
    }

    /**
     * Get available change types
     */
    public static function getChangeTypes(): array
    {
        return [
            'in' => 'Stock In',
            'out' => 'Stock Out',
        ];
    }

    /**
     * Get change type display name
     */
    public function getChangeTypeDisplayAttribute(): string
    {
        return self::getChangeTypes()[$this->change_type] ?? $this->change_type;
    }

    /**
     * Get change type color
     */
    public function getChangeTypeColorAttribute(): string
    {
        return $this->change_type === 'in' ? 'success' : 'danger';
    }

    /**
     * Get change type icon
     */
    public function getChangeTypeIconAttribute(): string
    {
        return $this->change_type === 'in' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
    }

    /**
     * Get formatted quantity with sign
     */
    public function getFormattedQuantityAttribute(): string
    {
        $sign = $this->change_type === 'in' ? '+' : '-';
        return $sign . $this->quantity;
    }

    /**
     * Get reference type (complaint or purchase)
     */
    public function getReferenceTypeAttribute(): string
    {
        if (!$this->reference_id) {
            return 'manual';
        }

        // Check if it's a complaint (assuming complaints have IDs in a certain range)
        // This is a simplified approach - you might want to add a reference_type field
        return 'complaint';
    }

    /**
     * Get reference object
     */
    public function getReferenceAttribute()
    {
        if (!$this->reference_id) {
            return null;
        }

        // This is a simplified approach - you might want to add a reference_type field
        return Complaint::find($this->reference_id);
    }

    /**
     * Scope for stock in
     */
    public function scopeStockIn($query)
    {
        return $query->where('change_type', 'in');
    }

    /**
     * Scope for stock out
     */
    public function scopeStockOut($query)
    {
        return $query->where('change_type', 'out');
    }

    /**
     * Scope for specific spare
     */
    public function scopeForSpare($query, $spareId)
    {
        return $query->where('spare_id', $spareId);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for logs with reference
     */
    public function scopeWithReference($query)
    {
        return $query->whereNotNull('reference_id');
    }

    /**
     * Scope for manual logs
     */
    public function scopeManual($query)
    {
        return $query->whereNull('reference_id');
    }
}
