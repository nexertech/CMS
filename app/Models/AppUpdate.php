<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUpdate extends Model
{
    protected $fillable = [
        'platform',
        'version_name',
        'version_code',
        'is_force_update',
        'update_url',
        'message',
        'status'
    ];
}
