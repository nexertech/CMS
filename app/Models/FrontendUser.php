<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class FrontendUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'frontend_users';

    protected $fillable = [
        'username',
        'name',
        'password',
        'email',
        'phone',
        'status',
        'cme_ids',
        'group_ids',
        'node_ids',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'cme_ids' => 'array',
            'group_ids' => 'array',
            'node_ids' => 'array',
        ];
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }



    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get user's display name (username)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username;
    }


}
