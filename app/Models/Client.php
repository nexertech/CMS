<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'sector',
        'state',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the complaints for the client.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'client_id', 'id');
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Check if client is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if client is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get client's display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->client_name ?: 'Unknown Client';
    }

    /**
     * Get contact person or client name as fallback
     */
    public function getContactNameAttribute(): string
    {
        return $this->contact_person ?: $this->client_name;
    }

    /**
     * Get total complaints count
     */
    public function getTotalComplaintsAttribute(): int
    {
        return $this->complaints()->count();
    }

    /**
     * Get resolved complaints count
     */
    public function getResolvedComplaintsAttribute(): int
    {
        return $this->complaints()
            ->whereIn('status', ['resolved', 'closed'])
            ->count();
    }

    /**
     * Get pending complaints count
     */
    public function getPendingComplaintsAttribute(): int
    {
        return $this->complaints()
            ->whereIn('status', ['new', 'assigned', 'in_progress'])
            ->count();
    }

    /**
     * Get client's resolution rate
     */
    public function getResolutionRateAttribute(): float
    {
        $total = $this->getTotalComplaintsAttribute();
        if ($total === 0) {
            return 0;
        }

        return round(($this->getResolvedComplaintsAttribute() / $total) * 100, 2);
    }

    /**
     * Get recent complaints (last 30 days)
     */
    public function getRecentComplaintsAttribute()
    {
        return $this->complaints()
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get complaints by type
     */
    public function getComplaintsByType(): array
    {
        return $this->complaints()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Check if client has repeated complaints
     */
    public function hasRepeatedComplaints(): bool
    {
        return $this->complaints()
            ->where('created_at', '>=', now()->subDays(30))
            ->count() > 3;
    }

    /**
     * Get client's full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive clients
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for clients with recent complaints
     */
    public function scopeWithRecentComplaints($query, $days = 30)
    {
        return $query->whereHas('complaints', function ($q) use ($days) {
            $q->where('created_at', '>=', now()->subDays($days));
        });
    }

    /**
     * Scope for clients by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }
}
