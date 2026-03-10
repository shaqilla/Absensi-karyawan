<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    // fillable = kolom yang BOLEH diisi lewat create() atau update()
    // Kolom yang tidak ada di sini tidak bisa diisi massal (proteksi keamanan)
    protected $fillable = ['nama_departemen', 'kode_departemen'];

    // RELASI: Satu departemen punya BANYAK karyawan
    // hasMany = one-to-many (1 departemen → banyak karyawan)
    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }
}
