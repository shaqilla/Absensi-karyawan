<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'jabatan',
        'departemen_id',
        'tanggal_masuk',
        'alamat',
        'jenis_kelamin',
    ];

    /**
     * Relasi Balik ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Departemen
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }
}