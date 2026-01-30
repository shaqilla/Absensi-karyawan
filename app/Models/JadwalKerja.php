<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kerjas';

    protected $fillable = [
        'user_id',
        'shift_id',
        'hari',
        'status' // 'aktif' atau 'libur'
    ];

    // Relasi: Jadwal ini milik siapa (User/Karyawan)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Jadwal ini menggunakan Shift yang mana
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
