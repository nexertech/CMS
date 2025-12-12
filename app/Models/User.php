<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'name',
        'password',
        'email',
        'phone',
        'role_id',
        'city_id',
        'sector_id',
        'status',
        'theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Get the city that owns the user.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the sector that owns the user.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    /**
     * Get the employee record for the user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    /**
     * Get the complaints logged by this user.
     */
    public function loggedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'logged_by', 'id');
    }

    /**
     * Get the SLA rules where this user is notified.
     */
    public function slaRules(): HasMany
    {
        return $this->hasMany(SlaRule::class, 'notify_to', 'id');
    }

    /**
     * Get the complaint logs for this user through employee.
     */
    public function complaintLogs()
    {
        return $this->hasManyThrough(
            ComplaintLog::class,
            Employee::class,
            'user_id', // Foreign key on employees table
            'action_by', // Foreign key on complaint_logs table
            'id', // Local key on users table
            'id' // Local key on employees table
        );
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->role_name === $roleName;
    }

    /**
     * Check if user has permission for a module and action
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    /**
     * Get user's display name (username)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Check if user has any admin permissions
     */
    public function hasAnyAdminPermission(): bool
    {
        if (!$this->role) {
            return false;
        }

        // Admin role (role_id 1 or role_name 'admin') always has admin permissions
        if ($this->role->id === 1 || $this->role->role_name === 'admin') {
            return true;
        }

        // Check if user has any permissions in any module
        return $this->role->rolePermissions()->exists();
    }
}
