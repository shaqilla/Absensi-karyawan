<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrSession extends Model
{
    protected $table = 'qr_sessions';
    protected $fillable = ['token', 'created_by', 'expired_at', 'is_active'];
    protected $casts = [
        'expired_at' => 'datetime',
        'is_active' => 'boolean'
    ];
}