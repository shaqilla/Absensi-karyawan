<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    // WAJIB tambahkan status dan used_at_attendance_id di sini
    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'used_at_attendance_id'
    ];

    public function item()
    {
        return $this->belongsTo(FlexibilityItem::class, 'item_id');
    }
}
