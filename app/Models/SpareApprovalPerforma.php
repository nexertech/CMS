<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpareApprovalPerforma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'spare_approval_performa';

    protected $fillable = [
        'complaint_id',
        'requested_by',
        'approved_by',
        'status',
        'performa_type',
        'waiting_for_authority',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'waiting_for_authority' => 'boolean',
    ];

    /**
     * Get the complaint that owns the approval performa.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    /**
     * Get the employee who requested the approval.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by', 'id')->withTrashed();
    }

    /**
     * Get the employee who approved the request.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'id')->withTrashed();
    }

    /**
     * Get the approval items for the performa.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SpareApprovalItem::class, 'performa_id', 'id');
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'In Progress',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'barak_damages' => 'Barak Damages',
        ];
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'barak_damages' => 'olive',
        ];

        return $colors[$this->status] ?? 'muted';
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        $icons = [
            'pending' => 'fas fa-clock',
            'approved' => 'fas fa-check-circle',
            'rejected' => 'fas fa-times-circle',
            'barak_damages' => 'fas fa-home',
        ];

        return $icons[$this->status] ?? 'fas fa-circle';
    }

    /**
     * Check if performa is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if performa is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if performa is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get requested by employee name
     */
    public function getRequestedByNameAttribute(): string
    {
        return $this->requestedBy ? $this->requestedBy->getFullNameAttribute() : 'Unknown Employee';
    }

    /**
     * Get approved by employee name
     */
    public function getApprovedByNameAttribute(): string
    {
        return $this->approvedBy ? $this->approvedBy->getFullNameAttribute() : 'Not Approved';
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get total quantity requested
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items()->sum('quantity_requested');
    }

    /**
     * Get total estimated cost
     */
    public function getTotalEstimatedCostAttribute(): float
    {
        $total = $this->items()
            ->join('spares', 'spare_approval_items.spare_id', '=', 'spares.id')
            ->selectRaw('SUM(spare_approval_items.quantity_requested * spares.unit_price) as total')
            ->value('total');
        
        return $total ? (float) $total : 0.0;
    }

    /**
     * Get total value requested (alias for total estimated cost)
     */
    public function getTotalValueRequestedAttribute(): float
    {
        return $this->getTotalEstimatedCostAttribute();
    }

    /**
     * Get formatted total estimated cost
     */
    public function getFormattedTotalEstimatedCostAttribute(): string
    {
        return 'PKR ' . number_format($this->getTotalEstimatedCostAttribute(), 2);
    }

    /**
     * Get formatted approved date
     */
    public function getFormattedApprovedDateAttribute(): string
    {
        return $this->approved_at ? $this->approved_at->format('M d, Y H:i') : 'N/A';
    }

    /**
     * Get approval time
     */
    public function getApprovalTimeAttribute(): ?int
    {
        if (!$this->approved_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->approved_at);
    }

    /**
     * Get formatted approval time
     */
    public function getFormattedApprovalTimeAttribute(): string
    {
        $time = $this->getApprovalTimeAttribute();
        if (!$time) {
            return 'N/A';
        }

        if ($time < 24) {
            return $time . ' hours';
        } else {
            return round($time / 24, 1) . ' days';
        }
    }

    /**
     * Get complaint ticket number
     */
    public function getComplaintTicketAttribute(): string
    {
        return $this->complaint ? $this->complaint->getTicketNumberAttribute() : 'N/A';
    }

    /**
     * Scope for pending performas
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved performas
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected performas
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for specific employee requests
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('requested_by', $employeeId);
    }

    /**
     * Scope for specific approver
     */
    public function scopeByApprover($query, $employeeId)
    {
        return $query->where('approved_by', $employeeId);
    }

    /**
     * Scope for recent performas
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for overdue performas (pending for more than specified hours)
     */
    public function scopeOverdue($query, $hours = 24)
    {
        return $query->where('status', 'pending')
            ->where('created_at', '<', now()->subHours($hours));
    }

    /**
     * Check if performa is Barak Damages
     */
    public function isBarakDamages(): bool
    {
        return $this->status === 'barak_damages';
    }

    /**
     * Scope for Barak Damages performas
     */
    public function scopeBarakDamages($query)
    {
        return $query->where('status', 'barak_damages');
    }
}
