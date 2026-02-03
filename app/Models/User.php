<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama', 
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELASI NYA DI SINI ---

    /**
     * Menghubungkan User ke data detail Karyawan
     */
    public function karyawan()
    {
        return $this->hasOne(Karyawan::class, 'user_id');
    }

    /**
     * Menghubungkan User ke data Presensi (Absensi)
     */
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'user_id');
    }

    /**
     * Menghubungkan User ke data Pengajuan (Izin/Sakit/Lembur)
     */
    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'user_id');
    }
}