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
        'category_id',
        'priority',
        'max_response_time',
        'max_resolution_time',
        'notify_to',
        'status',
    ];

    /**
     * Get the complaint category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

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
        return $this->hasMany(Complaint::class, 'category_id', 'category_id');
    }

    /**
     * Get available complaint types (Deprecated or Update to use categories)
     */
    public static function getComplaintTypes(): array
    {
        // This static list might be obsolete if we strictly use the database categories now
        return ComplaintCategory::pluck('name', 'id')->toArray();
    }

    /**
     * Get complaint type display name
     */
    public function getComplaintTypeDisplayAttribute(): string
    {
        return $this->category ? ucfirst($this->category->name) : 'Unknown Category';
    }

    // ... (keep unchecked methods)

    /**
     * Check if this rule applies to complaint type (by category ID now)
     */
    public function appliesToCategory(int $categoryId): bool
    {
        return $this->category_id === $categoryId;
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
     * Scope for specific complaint type (by category ID)
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
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
