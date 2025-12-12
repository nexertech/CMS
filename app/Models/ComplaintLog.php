<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_id',
        'action_by',
        'action',
        'remarks',
    ];

    /**
     * Get the complaint that owns the log.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    /**
     * Get the user who performed the action.
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'action_by', 'id');
    }

    /**
     * Get available actions
     */
    public static function getAvailableActions(): array
    {
        return [
            'created' => 'Complaint Created',
            'assigned' => 'Complaint Assigned',
            'status_changed' => 'Status Changed',
            'priority_changed' => 'Priority Changed',
            'employee_changed' => 'Employee Changed',
            'comment_added' => 'Comment Added',
            'attachment_added' => 'Attachment Added',
            'spare_used' => 'Spare Part Used',
            'resolved' => 'Complaint Resolved',
            'closed' => 'Complaint Closed',
            'reopened' => 'Complaint Reopened',
        ];
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        return self::getAvailableActions()[$this->action] ?? $this->action;
    }

    /**
     * Get action by user name
     */
    public function getActionByNameAttribute(): string
    {
        return $this->actionBy ? $this->actionBy->getDisplayNameAttribute() : 'Unknown User';
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('M d, Y H:i:s');
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if action is status change
     */
    public function isStatusChange(): bool
    {
        return $this->action === 'status_changed';
    }

    /**
     * Check if action is assignment
     */
    public function isAssignment(): bool
    {
        return $this->action === 'assigned';
    }

    /**
     * Check if action is resolution
     */
    public function isResolution(): bool
    {
        return $this->action === 'resolved';
    }

    /**
     * Check if action is closure
     */
    public function isClosure(): bool
    {
        return $this->action === 'closed';
    }

    /**
     * Check if action is reopening
     */
    public function isReopening(): bool
    {
        return $this->action === 'reopened';
    }

    /**
     * Get action icon class
     */
    public function getActionIconAttribute(): string
    {
        $icons = [
            'created' => 'fas fa-plus-circle text-success',
            'assigned' => 'fas fa-user-check text-primary',
            'status_changed' => 'fas fa-exchange-alt text-info',
            'priority_changed' => 'fas fa-exclamation-triangle text-warning',
            'employee_changed' => 'fas fa-user-edit text-secondary',
            'comment_added' => 'fas fa-comment text-info',
            'attachment_added' => 'fas fa-paperclip text-secondary',
            'spare_used' => 'fas fa-tools text-warning',
            'resolved' => 'fas fa-check-circle text-success',
            'closed' => 'fas fa-times-circle text-danger',
            'reopened' => 'fas fa-redo text-warning',
        ];

        return $icons[$this->action] ?? 'fas fa-circle text-muted';
    }

    /**
     * Get action color class
     */
    public function getActionColorAttribute(): string
    {
        $colors = [
            'created' => 'success',
            'assigned' => 'primary',
            'status_changed' => 'info',
            'priority_changed' => 'warning',
            'employee_changed' => 'secondary',
            'comment_added' => 'info',
            'attachment_added' => 'secondary',
            'spare_used' => 'warning',
            'resolved' => 'success',
            'closed' => 'danger',
            'reopened' => 'warning',
        ];

        return $colors[$this->action] ?? 'muted';
    }

    /**
     * Scope for specific action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('action_by', $userId);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for status changes
     */
    public function scopeStatusChanges($query)
    {
        return $query->where('action', 'status_changed');
    }

    /**
     * Scope for assignments
     */
    public function scopeAssignments($query)
    {
        return $query->where('action', 'assigned');
    }

    /**
     * Scope for resolutions
     */
    public function scopeResolutions($query)
    {
        return $query->whereIn('action', ['resolved', 'closed']);
    }
}
