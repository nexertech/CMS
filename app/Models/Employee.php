<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'designation_id',
        'phone',
        'date_of_hire',
        'address',
        'city_id',
        'sector_id',
        'sector_ids',
        'status',
    ];

    /**
     * Get the category of the employee.
     */
    public function category()
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    /**
     * Get the designation of the employee.
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    protected $casts = [
        'date_of_hire' => 'date',
        'status' => 'integer',
        'sector_ids' => 'array',
    ];

    // Derived name accessor retained for backwards compatibility
    public function getFullNameAttribute(): string
    {
        return $this->name ?? 'Unknown Employee';
    }

    /**
     * Get the complaints assigned to this employee.
     */
    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_employee_id', 'id');
    }

    /**
     * Get the spare parts used by this employee.
     */
    public function usedSpares(): HasMany
    {
        return $this->hasMany(ComplaintSpare::class, 'used_by', 'id');
    }

    /**
     * Get the spare approval performas requested by this employee.
     */
    public function requestedApprovals(): HasMany
    {
        return $this->hasMany(SpareApprovalPerforma::class, 'requested_by', 'id');
    }

    /**
     * Get the spare approval performas approved by this employee.
     */
    public function approvedApprovals(): HasMany
    {
        return $this->hasMany(SpareApprovalPerforma::class, 'approved_by', 'id');
    }

    /**
     * Get the complaint logs for this employee.
     */
    public function complaintLogs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class, 'action_by', 'id');
    }

    /**
     * Get the city that owns the employee.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the sector that owns the employee.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    /**
     * Get comma-separated names of assigned sectors (GE Nodes).
     */
    public function getAssignedSectorsTextAttribute(): string
    {
        $ids = $this->all_sector_ids;
        if (!empty($ids)) {
            $sectorNames = Sector::whereIn('id', $ids)->pluck('name')->toArray();
            if (!empty($sectorNames)) {
                return implode(', ', $sectorNames);
            }
        }
        return $this->sector ? $this->sector->name : 'N/A';
    }

    /**
     * Get array of all sector IDs assigned to employee (sector_ids JSON column with fallback to primary sector_id).
     */
    public function getAllSectorIdsAttribute(): array
    {
        $ids = [];
        $jsonIds = $this->sector_ids;
        if (is_string($jsonIds)) {
            $decoded = json_decode($jsonIds, true);
            $jsonIds = is_array($decoded) ? $decoded : [];
        }
        if (is_array($jsonIds) && !empty($jsonIds)) {
            foreach ($jsonIds as $v) {
                $ids[] = (int)$v;
            }
        }
        if ($this->sector_id) {
            $ids[] = (int) $this->sector_id;
        }
        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Get comma-separated string of all assigned sector IDs.
     */
    public function getAllSectorIdsCsvAttribute(): string
    {
        return implode(',', $this->all_sector_ids);
    }


    // Removed username/email/status/user-dependent accessors

    /**
     * Get employee performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $totalComplaints = $this->assignedComplaints()->count();
        $resolvedComplaints = $this->assignedComplaints()
            ->where('status', \App\Models\Complaint::STATUS_RESOLVED)
            ->count();
        $closedComplaints = $this->assignedComplaints()
            ->where('status', \App\Models\Complaint::STATUS_RESOLVED)
            ->count();

        return [
            'total_complaints' => $totalComplaints,
            'resolved_complaints' => $resolvedComplaints,
            'closed_complaints' => $closedComplaints,
            'resolution_rate' => $totalComplaints > 0 ? round(($resolvedComplaints + $closedComplaints) / $totalComplaints * 100, 2) : 0,
        ];
    }

    /**
     * Get available designations
     */
    public static function getAvailableDesignations(): array
    {
        return [
            'technician' => 'Technician',
            'senior_technician' => 'Senior Technician',
            'supervisor' => 'Supervisor',
            'manager' => 'Manager',
            'admin' => 'Administrator',
        ];
    }
}
