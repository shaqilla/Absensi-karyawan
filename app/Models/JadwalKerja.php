<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    // Karena nama tabel tidak mengikuti konvensi Laravel (harusnya 'jadwal_kerja' -> 'jadwal_kerjas')
    // maka harus ditulis manual supaya Laravel tahu tabel yang dipakai
    protected $table = 'jadwal_kerjas';

    // Kolom yang boleh diisi lewat create() atau update()
    protected $fillable = ['user_id', 'shift_id', 'hari', 'status'];

    // RELASI: Jadwal ini milik satu User (karyawan)
    // belongsTo = many-to-one (banyak jadwal → 1 user)
    // Parameter kedua 'user_id' = nama foreign key di tabel ini
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELASI: Jadwal ini menggunakan satu Shift
    // belongsTo = many-to-one (banyak jadwal → 1 shift)
    // Parameter kedua 'shift_id' = nama foreign key di tabel ini
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
