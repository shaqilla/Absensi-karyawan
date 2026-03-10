<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriAbsen extends Model
{
    use HasFactory;

    // Tulis manual nama tabel karena tidak mengikuti konvensi Laravel
    // KategoriAbsen → Laravel cari 'kategori_absens' (sudah sesuai, tapi lebih aman ditulis)
    protected $table = 'kategori_absens';

    // Kolom yang boleh diisi lewat create() atau update()
    protected $fillable = [
        'nama_kategori', // Contoh: Hadir di Kantor, Perjalanan Dinas, Lembur
        'keterangan'     // Penjelasan tambahan tentang kategori ini
    ];

    // RELASI: Satu kategori bisa dipakai oleh banyak data presensi
    // hasMany = one-to-many (1 kategori → banyak presensi)
    // Parameter kedua 'kategori_id' = nama foreign key di tabel presensi
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'kategori_id');
    }
}
