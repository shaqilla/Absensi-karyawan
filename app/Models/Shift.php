<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_keluar',
        'toleransi_telat'
    ];

    // Relasi balik ke Presensi
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'shift_id');
    }
}
