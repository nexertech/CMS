<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'client_id',
        'city_id',
        'sector_id',
        'category',
        'priority',
        'description',
        'assigned_employee_id',
        'status',
        'closed_at',
        'availability_time',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * Get the client that owns the complaint.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id')->withTrashed();
    }

    /**
     * Get the employee assigned to the complaint.
     */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id', 'id')->withTrashed();
    }

    /**
     * Get the city that owns the complaint.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the sector that owns the complaint.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    /**
     * Get the attachments for the complaint.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id', 'id');
    }

    /**
     * Get the logs for the complaint.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class, 'complaint_id', 'id');
    }

    /**
     * Get the spare parts used for the complaint.
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(ComplaintSpare::class, 'complaint_id', 'id');
    }

    /**
     * Get the spare approval performas for the complaint.
     */
    public function spareApprovals(): HasMany
    {
        return $this->hasMany(SpareApprovalPerforma::class, 'complaint_id', 'id');
    }

    /**
     * Get the feedback for the complaint.
     */
    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ComplaintFeedback::class, 'complaint_id', 'id')->withTrashed();
    }

    /**
     * Get the stock logs for the complaint.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(SpareStockLog::class, 'reference_id', 'id');
    }

    /**
     * Get available complaint categories
     */
    public static function getCategories(): array
    {
        return [
            'technical' => 'Technical',
            'service' => 'Service',
            'billing' => 'Billing',
            'sanitary' => 'Sanitary',
            'electric' => 'Electric',
            'kitchen' => 'Kitchen',
            'plumbing' => 'Plumbing',
            'other' => 'Other',
        ];
    }

    /**
     * Get available complaint types (legacy method for SLA rules)
     */
    public static function getComplaintTypes(): array
    {
        return [
            'electric' => 'Electrical Issues',
            'sanitary' => 'Sanitary Issues',
            'kitchen' => 'Kitchen Appliances',
            'general' => 'General Maintenance',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'assigned' => 'Assigned',
            'in_progress' => 'In Process',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'barak_damages' => 'Barak Damages',
        ];
    }

    /**
     * Get available priorities
     */
    public static function getPriorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }

    /**
     * Get complaint category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        // Map 'new' status to 'assigned' for display purposes
        $status = $this->status === 'new' ? 'assigned' : $this->status;
        return self::getStatuses()[$status] ?? $status;
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayAttribute(): string
    {
        return self::getPriorities()[$this->priority] ?? $this->priority;
    }

    /**
     * Check if complaint is new
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Check if complaint is assigned
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if complaint is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if complaint is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if complaint is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if complaint is completed (resolved or closed)
     */
    public function isCompleted(): bool
    {
        return $this->status === 'resolved' || $this->status === 'closed';
    }

    /**
     * Get ticket number for the complaint
     */
    public function getTicketNumberAttribute(): string
    {
        $year = $this->created_at->format('Y');
        $month = $this->created_at->format('m');
        $id = str_pad($this->id, 5, '0', STR_PAD_LEFT);

        return "CMP-{$year}{$month}-{$id}";
    }

    /**
     * Get 4-digit complaint ID
     */
    public function getComplaintIdAttribute(): string
    {
        // Generate 4-digit complaint ID based on complaint id
        // Use modulo to ensure it's always 4 digits (0001-9999)
        $complaintNumber = ($this->id % 10000);
        return str_pad($complaintNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get hours elapsed since creation
     */
    public function getHoursElapsedAttribute(): int
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Check if complaint is overdue
     */
    public function isOverdue(int $days = 7): bool
    {
        return $this->created_at->addDays($days)->isPast() && !$this->isCompleted();
    }

    /**
     * Check if SLA response time is breached
     */
    public function isResponseTimeBreached(SlaRule $slaRule): bool
    {
        return $this->getHoursElapsedAttribute() > $slaRule->max_response_time;
    }

    /**
     * Check if SLA resolution time is breached
     */
    public function isResolutionTimeBreached(SlaRule $slaRule): bool
    {
        return $this->getHoursElapsedAttribute() > $slaRule->max_resolution_time;
    }

    /**
     * Check if SLA is breached
     */
    public function isSlaBreached(): bool
    {
        $slaRule = SlaRule::where('complaint_type', $this->category)
            ->where('status', 'active')
            ->first();

        if (!$slaRule) {
            return false;
        }

        return $this->getHoursElapsedAttribute() > $slaRule->max_resolution_time;
    }

    /**
     * Get hours overdue
     */
    public function getHoursOverdue(): int
    {
        $slaRule = SlaRule::where('complaint_type', $this->category)
            ->where('status', 'active')
            ->first();

        if (!$slaRule) {
            return 0;
        }

        $hoursElapsed = $this->getHoursElapsedAttribute();
        return max(0, $hoursElapsed - $slaRule->max_resolution_time);
    }

    /**
     * Get SLA rule for this complaint
     */
    public function slaRule()
    {
        return $this->belongsTo(SlaRule::class, 'category', 'complaint_type');
    }

    /**
     * Boot method to generate ticket number and auto-set addressed date
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            // Auto-generate ticket number will be handled by accessor
        });

        // Automatically create approval performa when complaint is created
        static::created(function ($complaint) {
            try {
                // Check if approval performa already exists (avoid duplicates)
                $existingApproval = \App\Models\SpareApprovalPerforma::where('complaint_id', $complaint->id)->first();

                if (!$existingApproval) {
                    // Get employee for requested_by
                    $requestedByEmployee = null;

                    if ($complaint->assigned_employee_id) {
                        $requestedByEmployee = \App\Models\Employee::find($complaint->assigned_employee_id);
                    }

                    // If no assigned employee, get first available employee
                    if (!$requestedByEmployee) {
                        $requestedByEmployee = \App\Models\Employee::first();
                    }

                    // Create approval performa if we have an employee
                    if ($requestedByEmployee) {
                        \App\Models\SpareApprovalPerforma::create([
                            'complaint_id' => $complaint->id,
                            'requested_by' => $requestedByEmployee->id,
                            'status' => 'pending',
                            'performa_type' => null, // No performa type selected initially
                            // waiting_for_authority removed
                            'remarks' => 'Auto-created for new complaint',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail complaint creation
                \Log::error('Failed to create approval performa for complaint: ' . $complaint->id, [
                    'error' => $e->getMessage()
                ]);
            }
        });

        // Auto-set closed_at when status becomes 'resolved' or 'closed'
        static::updating(function ($complaint) {
            if ($complaint->isDirty('status')) {
                $newStatus = $complaint->status;
                $oldStatus = $complaint->getOriginal('status');

                // Set closed_at when status becomes 'resolved' or 'closed', but only if not already set
                if (in_array($newStatus, ['resolved', 'closed']) && !$complaint->closed_at) {
                    // Get current time in Asia/Karachi and convert to UTC for storage
                    $nowKarachi = \Carbon\Carbon::now('Asia/Karachi');
                    $complaint->closed_at = $nowKarachi->copy()->utc();
                } elseif (!in_array($newStatus, ['resolved', 'closed']) && in_array($oldStatus, ['resolved', 'closed'])) {
                    // If status is changed from resolved/closed to something else, clear closed_at
                    $complaint->closed_at = null;
                }
            }
        });
    }

    /**
     * Check if complaint is pending (not completed)
     */
    public function isPending(): bool
    {
        return !$this->isCompleted();
    }

    /**
     * Get complaint age in days
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get resolution time in days
     */
    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->isCompleted()) {
            return null;
        }

        return $this->created_at->diffInDays($this->closed_at ?? now());
    }

    /**
     * Get total spare parts cost
     */
    public function getTotalSpareCostAttribute(): float
    {
        return $this->spareParts()
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->selectRaw('SUM(complaint_spares.quantity * spares.unit_price) as total')
            ->value('total') ?? 0;
    }


    /**
     * Get client name
     */
    public function getClientNameAttribute(): string
    {
        return $this->client ? $this->client->getDisplayNameAttribute() : 'Unknown Client';
    }

    /**
     * Get assigned employee name
     */
    public function getAssignedEmployeeNameAttribute(): string
    {
        return $this->assignedEmployee ? $this->assignedEmployee->getFullNameAttribute() : 'Unassigned';
    }

    /**
     * Scope for new complaints
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for assigned complaints
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for in progress complaints
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for resolved complaints
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for closed complaints
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for pending complaints
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['new', 'assigned', 'in_progress']);
    }

    /**
     * Scope for completed complaints
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    /**
     * Scope for complaints by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for complaints by type (legacy method)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('category', $type);
    }

    /**
     * Scope for complaints by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for complaints by assigned employee
     */
    public function scopeByAssignedEmployee($query, $employeeId)
    {
        return $query->where('assigned_employee_id', $employeeId);
    }

    /**
     * Scope for complaints by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for overdue complaints
     */
    public function scopeOverdue($query, $days = 7)
    {
        return $query->whereIn('complaints.status', ['new', 'assigned', 'in_progress'])
            ->leftJoin('sla_rules', function ($join) {
                $join->on('complaints.category', '=', 'sla_rules.complaint_type')
                    ->where('sla_rules.status', '=', 'active')
                    ->whereNull('sla_rules.deleted_at');
            })
            ->where(function ($q) use ($days) {
                // If SLA rule exists, check max_resolution_time (in hours)
                $q->where(function ($subQ) {
                    $subQ->whereNotNull('sla_rules.id')
                        ->whereRaw('complaints.created_at < DATE_SUB(NOW(), INTERVAL sla_rules.max_resolution_time HOUR)');
                })
                    // If no SLA rule exists, fallback to default days
                    ->orWhere(function ($subQ) use ($days) {
                    $subQ->whereNull('sla_rules.id')
                        ->where('complaints.created_at', '<', now()->subDays($days));
                });
            })
            ->select('complaints.*');
    }
}
