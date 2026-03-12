<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // HasFactory = memungkinkan model ini dibuat data dummy pakai Factory
    // Berguna saat testing atau seeding database
    use HasFactory;

    // Kolom yang boleh diisi lewat create() atau update()
    // termasuk property karena termasuk ke variabel yang ada di dalem class
    protected $fillable = [
        'user_id',       // Foreign key ke tabel users
        'nip',           // Nomor Induk Pegawai (unik)
        'jabatan',       // Contoh: Staff, Manager, dll
        'departemen_id', // Foreign key ke tabel departemens
        'tanggal_masuk', // Tanggal pertama masuk kerja
        'alamat',
        'jenis_kelamin',
        'supervisor_id',
    ];

    // RELASI: Karyawan ini milik satu User (akun login)
    // belongsTo = satu karyawan → satu user
    // Dipakai untuk akses data login seperti nama, email, role
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELASI: Karyawan ini berada di satu Departemen
    // belongsTo = satu karyawan → satu departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }

    // menambahkan relasi untuk menghubungkan ke atasan (supervisor)
    public function supervisor()
    {
        //Supervisor di ambil dari tabel users pake supervisor_id
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
