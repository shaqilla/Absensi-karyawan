<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'qr_session_id',
        'shift_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'latitude',
        'longitude',
        'status',
        'kategori_id',
        'keterangan'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELASI YANG TADI HILANG (INI YANG BIKIN ERROR)
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
