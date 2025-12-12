<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintFeedback extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'complaint_feedbacks';

    protected $fillable = [
        'complaint_id',
        'client_id',
        'entered_by',
        'submitted_by',
        'overall_rating',
        'rating_score',
        'service_quality',
        'response_time',
        'resolution_quality',
        'staff_behavior',
        'comments',
        'remarks',
        'feedback_date',
        'entered_at',
    ];

    protected $casts = [
        'feedback_date' => 'datetime',
        'entered_at' => 'datetime',
        'rating_score' => 'integer',
    ];

    /**
     * Get the complaint that this feedback belongs to.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    /**
     * Get the client that provided this feedback.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Get the user who entered this feedback.
     */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by', 'id');
    }

    /**
     * Get rating display text
     */
    public function getOverallRatingDisplayAttribute(): string
    {
        return ucfirst($this->overall_rating ?? 'N/A');
    }

    /**
     * Get rating color for display
     */
    public function getRatingColorAttribute(): string
    {
        return match ($this->overall_rating) {
            'excellent' => '#22c55e', // Green
            'good' => '#3b82f6',      // Blue
            'average' => '#f59e0b',    // Orange
            'poor' => '#ef4444',      // Red
            default => '#64748b'      // Gray
        };
    }

    /**
     * Get rating badge color
     */
    public function getRatingBadgeColorAttribute(): string
    {
        return match ($this->overall_rating) {
            'excellent' => 'success',
            'good' => 'primary',
            'average' => 'warning',
            'poor' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Check if feedback exists for complaint
     */
    public static function hasFeedback($complaintId): bool
    {
        return self::where('complaint_id', $complaintId)->exists();
    }
}

