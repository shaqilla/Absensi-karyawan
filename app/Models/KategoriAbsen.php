<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriAbsen extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'kategori_absens';

    protected $fillable = [
        'nama_kategori', // Contoh: Hadir di Kantor, Perjalanan Dinas, Lembur
        'keterangan'
    ];

    // Relasi: Satu kategori bisa dimiliki oleh banyak data presensi
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'kategori_id');
    }
}