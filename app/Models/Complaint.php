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
        'complaint_title_id',
        'title',
        'house_id',
        'city_id',
        'sector_id',
        'category_id',
        'priority',
        'description',
        'assigned_employee_id',
        'status',
        'closed_at',
        'availability_time',
        'spare_id',
        'spare_quantity',
        'spare_used_by',
        'spare_used_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * Get the complaint category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id', 'id');
    }

    /**
     * Get the complaint title (type).
     */
    public function complaintTitle(): BelongsTo
    {
        return $this->belongsTo(ComplaintTitle::class, 'complaint_title_id', 'id');
    }

    /**
     * Get the house associated with the complaint.
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class, 'house_id', 'id')->withTrashed();
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
     * Get the logs for the complaint.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class, 'complaint_id', 'id');
    }

    /**
     * Get the attachments for the complaint.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id', 'id');
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

    // Status Integer Constants
    const STATUS_IN_PROGRESS           = 0;
    const STATUS_RESOLVED              = 1;
    const STATUS_UNASSIGNED            = 2;
    const STATUS_ASSIGNED              = 3;
    const STATUS_WORK_PERFORMA         = 4;
    const STATUS_MAINT_PERFORMA        = 5;
    const STATUS_WORK_PRICED_PERFORMA  = 6;
    const STATUS_MAINT_PRICED_PERFORMA = 7;
    const STATUS_PRODUCT_NA            = 8;
    const STATUS_UN_AUTHORIZED         = 9;
    const STATUS_BARRACK_DAMAGES       = 10;
    const STATUS_DOOR_LOCK             = 11;

    public static function getStatusIdMap(): array
    {
        return [
            self::STATUS_IN_PROGRESS           => 'in_progress',
            self::STATUS_RESOLVED              => 'resolved',
            self::STATUS_UNASSIGNED            => 'unassigned',
            self::STATUS_ASSIGNED              => 'assigned',
            self::STATUS_WORK_PERFORMA         => 'work_performa',
            self::STATUS_MAINT_PERFORMA        => 'maint_performa',
            self::STATUS_WORK_PRICED_PERFORMA  => 'work_priced_performa',
            self::STATUS_MAINT_PRICED_PERFORMA => 'maint_priced_performa',
            self::STATUS_PRODUCT_NA            => 'product_na',
            self::STATUS_UN_AUTHORIZED         => 'un_authorized',
            self::STATUS_BARRACK_DAMAGES       => 'barrack_damages',
            self::STATUS_DOOR_LOCK             => 'door_lock',
        ];
    }

    public static function getStatusKeyToIdMap(): array
    {
        return array_flip(self::getStatusIdMap());
    }

    /**
     * Accessor: Convert raw DB integer status to string key
     */
    public function getStatusAttribute($value): string
    {
        if (is_numeric($value)) {
            $map = self::getStatusIdMap();
            return $map[(int)$value] ?? 'unassigned';
        }
        return $value ?: 'unassigned';
    }

    /**
     * Mutator: Convert string status key or integer to DB integer value
     */
    public function setStatusAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['status'] = (int)$value;
        } else {
            $keyMap = self::getStatusKeyToIdMap();
            $this->attributes['status'] = $keyMap[$value] ?? self::STATUS_UNASSIGNED;
        }
    }

    /**
     * Accessor: Get numeric status ID directly
     */
    public function getStatusIdAttribute(): int
    {
        $raw = $this->attributes['status'] ?? self::STATUS_UNASSIGNED;
        if (is_numeric($raw)) {
            return (int)$raw;
        }
        $keyMap = self::getStatusKeyToIdMap();
        return $keyMap[$raw] ?? self::STATUS_UNASSIGNED;
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'unassigned' => 'Unassigned',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'resolved' => 'Addressed',
            'work_performa' => 'Work Performa',
            'maint_performa' => 'Maintenance Performa',
            'work_priced_performa' => 'Work Performa Priced',
            'maint_priced_performa' => 'Maintenance Performa Priced',
            'product_na' => 'Product N/A',
            'un_authorized' => 'Un-Authorized',
            'barrack_damages' => 'Barrack Damages',
            'door_lock' => 'Door Lock',
        ];
    }

    /**
     * Get available priorities
     */
    public static function getPriorities(): array
    {
        return [
            'normal' => 'Normal',
            'emergency' => 'Emergency',
        ];
    }

    /**
     * Get complaint category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return $this->category ? $this->category->name : ($this->category_id ? 'Unknown Category' : 'Uncategorized');
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $status = $this->status;

        // Performa types and 'in_progress' should display as "In Progress"
        if (in_array($status, ['in_progress', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
            return 'In Progress';
        }

        // 'resolved' maps to 'Addressed'
        if ($status === 'resolved') {
            return 'Addressed';
        }

        return self::getStatuses()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get the mapped status code for mobile app compatibility
     * Groups all performa types into 'in_progress'
     */
    public function getMappedStatusAttribute(): string
    {
        $status = $this->status;

        if (in_array($status, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
            return 'in_progress';
        }

        return $status;
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayAttribute(): string
    {
        $raw = strtolower($this->priority ?? 'normal');
        if (in_array($raw, ['emergency', 'high', 'urgent'])) {
            return 'Emergency';
        }
        return 'Normal';
    }

    /**
     * Check if complaint is new/unassigned
     */
    public function isNew(): bool
    {
        return (int)$this->status === self::STATUS_UNASSIGNED;
    }

    /**
     * Check if complaint is assigned
     */
    public function isAssigned(): bool
    {
        return (int)$this->status === self::STATUS_ASSIGNED;
    }

    /**
     * Check if complaint is in progress
     */
    public function isInProgress(): bool
    {
        return (int)$this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if complaint is resolved
     */
    public function isResolved(): bool
    {
        return (int)$this->status === self::STATUS_RESOLVED;
    }

    /**
     * Check if complaint is completed
     */
    public function isCompleted(): bool
    {
        return (int)$this->status === self::STATUS_RESOLVED;
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
     * Get complaint ID (padded to at least 4 digits)
     */
    public function getComplaintIdAttribute(): string
    {
        // Return the full complaint ID, padded with zeros to at least 4 digits
        return str_pad((string)$this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get hours elapsed since creation
     */
    public function getHoursElapsedAttribute(): int
    {
        return $this->created_at->diffInHours(now());
    }

    public function isOverdue(): bool
    {
        if (!in_array($this->status, ['new', 'assigned', 'in_progress'])) {
            return false;
        }

        $slaRule = $this->slaRule;

        if ($slaRule && $slaRule->status === 1) {
            return $this->created_at->addHours($slaRule->max_resolution_time)->isPast();
        }

        return false;
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
        $slaRule = $this->slaRule;

        if (!$slaRule || $slaRule->status !== 1) {
            return false;
        }

        return $this->getHoursElapsedAttribute() > $slaRule->max_resolution_time;
    }

    /**
     * Get hours overdue
     */
    public function getHoursOverdue(): int
    {
        $slaRule = $this->slaRule;

        if (!$slaRule || $slaRule->status !== 1) {
            return 0;
        }

        $hoursElapsed = $this->getHoursElapsedAttribute();
        return max(0, $hoursElapsed - $slaRule->max_resolution_time);
    }

    /**
     * Get SLA rule for this complaint
     * @deprecated Use category->slaRule instead
     */
    /**
     * Get SLA rule for this complaint via category
     */
    public function slaRule()
    {
        return $this->hasOne(SlaRule::class, 'category_id', 'category_id')
                    ->whereColumn('priority', 'complaints.priority');
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
        return $query->where('complaints.status', self::STATUS_UNASSIGNED);
    }

    /**
     * Scope for assigned complaints
     */
    public function scopeAssigned($query)
    {
        return $query->where('complaints.status', self::STATUS_ASSIGNED);
    }

    /**
     * Scope for in progress complaints
     */
    public function scopeInProgress($query)
    {
        return $query->where('complaints.status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for resolved complaints
     */
    public function scopeResolved($query)
    {
        return $query->where('complaints.status', self::STATUS_RESOLVED);
    }

    /**
     * Scope for closed complaints
     */
    public function scopeClosed($query)
    {
        return $query->where('complaints.status', self::STATUS_RESOLVED);
    }

    /**
     * Scope for pending complaints
     */
    public function scopePending($query)
    {
        return $query->whereIn('complaints.status', [self::STATUS_UNASSIGNED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope for completed complaints
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('complaints.status', [self::STATUS_RESOLVED]);
    }

    /**
     * Scope for complaints by category
     */
    /**
     * Scope for complaints by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        // If passed name, try to find ID or use join (safer to assume ID if int, if string need logic)
        // Controller passed ID now.
        return $query->where('complaints.category_id', $categoryId);
    }

    /**
     * Scope for complaints by type (legacy method)
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('complaints.complaint_title_id', $typeId);
    }

    /**
     * Scope for complaints by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('complaints.priority', $priority);
    }

    /**
     * Scope for complaints by assigned employee
     */
    public function scopeByAssignedEmployee($query, $employeeId)
    {
        return $query->where('complaints.assigned_employee_id', $employeeId);
    }

    /**
     * Scope for overdue complaints
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('complaints.status', ['new', 'assigned', 'in_progress'])
            ->join('sla_rules', function ($join) {
                $join->on('complaints.category_id', '=', 'sla_rules.category_id')
                     ->on('complaints.priority', '=', 'sla_rules.priority')
                    ->where('sla_rules.status', '=', 1)
                    ->whereNull('sla_rules.deleted_at');
            })
            ->whereRaw('complaints.created_at < DATE_SUB(NOW(), INTERVAL sla_rules.max_resolution_time HOUR)')
            ->select('complaints.*');
    }
}
