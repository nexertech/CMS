<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'user_type',
        'username',
        'ip_address',
        'user_agent',
        'source',
    ];

    /**
     * Get the owning user model.
     */
    public function user()
    {
        return $this->morphTo();
    }
}
