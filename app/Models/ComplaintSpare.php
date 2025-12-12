<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintSpare extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'complaint_spares';

    protected $fillable = [
        'complaint_id',
        'spare_id',
        'quantity',
        'used_by',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * Get the complaint that owns the spare usage.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    /**
     * Get the spare that was used.
     */
    public function spare(): BelongsTo
    {
        return $this->belongsTo(Spare::class, 'spare_id', 'id');
    }

    /**
     * Get the employee who used the spare.
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'used_by', 'id');
    }

    /**
     * Get the user who used the spare through employee relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by', 'id')
            ->through('usedBy');
    }

    /**
     * Get total cost for this spare usage
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->spare->unit_price;
    }

    /**
     * Get formatted total cost
     */
    public function getFormattedTotalCostAttribute(): string
    {
        return '₹' . number_format($this->getTotalCostAttribute(), 2);
    }

    /**
     * Get spare name
     */
    public function getSpareNameAttribute(): string
    {
        return $this->spare ? $this->spare->item_name : 'Unknown Spare';
    }

    /**
     * Get used by employee name
     */
    public function getUsedByNameAttribute(): string
    {
        return $this->usedBy ? $this->usedBy->getFullNameAttribute() : 'Unknown Employee';
    }

    /**
     * Get formatted used date
     */
    public function getFormattedUsedDateAttribute(): string
    {
        return $this->used_at ? $this->used_at->format('M d, Y H:i') : 'N/A';
    }

    /**
     * Get used date ago
     */
    public function getUsedDateAgoAttribute(): string
    {
        return $this->used_at ? $this->used_at->diffForHumans() : 'N/A';
    }

    /**
     * Scope for specific complaint
     */
    public function scopeForComplaint($query, $complaintId)
    {
        return $query->where('complaint_id', $complaintId);
    }

    /**
     * Scope for specific spare
     */
    public function scopeForSpare($query, $spareId)
    {
        return $query->where('spare_id', $spareId);
    }

    /**
     * Scope for specific employee
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('used_by', $employeeId);
    }

    /**
     * Scope for recent usage
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('used_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for today's usage
     */
    public function scopeToday($query)
    {
        return $query->whereDate('used_at', today());
    }

    /**
     * Scope for this week's usage
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for this month's usage
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('used_at', now()->month)
            ->whereYear('used_at', now()->year);
    }
}
