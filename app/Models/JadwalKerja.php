<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'jadwal_kerjas';
    protected $fillable = ['user_id', 'shift_id', 'hari', 'status'];

    // Relasi ke User (Karyawan)
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Shift
    public function shift() {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}