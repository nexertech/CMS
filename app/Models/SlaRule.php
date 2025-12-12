<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_type',
        'priority',
        'max_response_time',
        'max_resolution_time',
        'notify_to',
        'status',
    ];

    /**
     * Get the user who should be notified.
     */
    public function notifyTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_to', 'id')->withTrashed();
    }

    /**
     * Get the complaints for this SLA rule.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'category', 'complaint_type');
    }

    /**
     * Get available complaint types
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
     * Get complaint type display name
     */
    public function getComplaintTypeDisplayAttribute(): string
    {
        return self::getComplaintTypes()[$this->complaint_type] ?? $this->complaint_type;
    }


    /**
     * Get max response time display
     */
    public function getMaxResponseTimeDisplayAttribute(): string
    {
        if ($this->max_response_time < 24) {
            return $this->max_response_time . ' hours';
        } else {
            $days = round($this->max_response_time / 24, 1);
            return $days . ' day' . ($days > 1 ? 's' : '');
        }
    }

    /**
     * Get notify to user name
     */
    public function getNotifyToNameAttribute(): string
    {
        return $this->notifyTo ? $this->notifyTo->getDisplayNameAttribute() : 'Unknown User';
    }

    /**
     * Get notify to user email
     */
    public function getNotifyToEmailAttribute(): ?string
    {
        return $this->notifyTo ? $this->notifyTo->email : null;
    }

    /**
     * Check if complaint is overdue based on this rule
     */
    public function isComplaintOverdue($complaintCreatedAt): bool
    {
        $hoursSinceCreation = $complaintCreatedAt->diffInHours(now());
        return $hoursSinceCreation > $this->max_response_time;
    }

    /**
     * Get time remaining for complaint based on this rule
     */
    public function getTimeRemainingForComplaint($complaintCreatedAt): int
    {
        $hoursSinceCreation = $complaintCreatedAt->diffInHours(now());
        return max(0, $this->max_response_time - $hoursSinceCreation);
    }

    /**
     * Get formatted time remaining for complaint
     */
    public function getFormattedTimeRemainingForComplaint($complaintCreatedAt): string
    {
        $hoursRemaining = $this->getTimeRemainingForComplaint($complaintCreatedAt);
        
        if ($hoursRemaining <= 0) {
            return 'Overdue';
        }

        if ($hoursRemaining < 24) {
            return $hoursRemaining . ' hours remaining';
        } else {
            $days = round($hoursRemaining / 24, 1);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' remaining';
        }
    }

    /**
     * Get urgency level for complaint based on this rule
     */
    public function getUrgencyLevelForComplaint($complaintCreatedAt): string
    {
        $hoursRemaining = $this->getTimeRemainingForComplaint($complaintCreatedAt);
        
        if ($hoursRemaining <= 0) {
            return 'critical';
        } elseif ($hoursRemaining <= $this->max_response_time * 0.25) {
            return 'high';
        } elseif ($hoursRemaining <= $this->max_response_time * 0.5) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get urgency color for complaint based on this rule
     */
    public function getUrgencyColorForComplaint($complaintCreatedAt): string
    {
        $urgency = $this->getUrgencyLevelForComplaint($complaintCreatedAt);
        
        $colors = [
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
        ];

        return $colors[$urgency] ?? 'muted';
    }

    /**
     * Check if this rule applies to complaint type
     */
    public function appliesToComplaintType(string $complaintType): bool
    {
        return $this->complaint_type === $complaintType;
    }

    /**
     * Get rule summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'complaint_type' => $this->getComplaintTypeDisplayAttribute(),
            'priority' => ucfirst($this->priority ?? 'medium'),
            'max_response_time' => $this->getMaxResponseTimeDisplayAttribute(),
            'notify_to' => $this->getNotifyToNameAttribute(),
        ];
    }

    /**
     * Scope for specific complaint type
     */
    public function scopeForComplaintType($query, $type)
    {
        return $query->where('complaint_type', $type);
    }


    /**
     * Scope for specific notify user
     */
    public function scopeForNotifyUser($query, $userId)
    {
        return $query->where('notify_to', $userId);
    }

    /**
     * Scope for rules with response time less than specified hours
     */
    public function scopeWithResponseTimeLessThan($query, $hours)
    {
        return $query->where('max_response_time', '<', $hours);
    }

    /**
     * Scope for rules with response time greater than specified hours
     */
    public function scopeWithResponseTimeGreaterThan($query, $hours)
    {
        return $query->where('max_response_time', '>', $hours);
    }

    /**
     * Get rule for specific complaint type
     */
    public static function forComplaintType(string $complaintType): ?self
    {
        return static::where('complaint_type', $complaintType)->first();
    }

    /**
     * Get all rules ordered by response time (fastest first)
     */
    public static function orderedByResponseTime()
    {
        return static::orderBy('max_response_time', 'asc');
    }
}
