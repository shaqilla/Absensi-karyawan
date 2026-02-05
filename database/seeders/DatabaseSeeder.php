<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\KategoriAbsen; // Pastikan ini diimport
use App\Models\LokasiKantor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Daftar Departemen
        $list_departemen = [
            ['nama_departemen' => 'IT Support', 'kode_departemen' => 'ITS'],
            ['nama_departemen' => 'Human Resources', 'kode_departemen' => 'HRD'],
            ['nama_departemen' => 'Finance & Accounting', 'kode_departemen' => 'FIN'],
            ['nama_departemen' => 'Marketing', 'kode_departemen' => 'MKT'],
            ['nama_departemen' => 'General Affair', 'kode_departemen' => 'GA'],
        ];

        foreach ($list_departemen as $d) {
            Departemen::create($d);
        }

        // --- CARA MEMPERBAIKI ERROR: Ambil salah satu departemen dari DB ---
        $deptIt = Departemen::where('kode_departemen', 'ITS')->first();

        // 2. Buat Akun Admin
        User::create([
            'nama' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Buat Akun Karyawan
        $userKaryawan = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'karyawan@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        // 4. Buat Detail Data Karyawan (Tabel Karyawans)
        Karyawan::create([
            'user_id' => $userKaryawan->id,
            'nip' => '12345678',
            'jabatan' => 'Web Developer',
            'departemen_id' => $deptIt->id, // <--- SEKARANG MENGGUNAKAN $deptIt->id
            'tanggal_masuk' => now(),
            'alamat' => 'Jl. Merdeka No. 1',
            'jenis_kelamin' => 'laki-laki'
        ]);

        // 5. Buat Kategori Absen Dasar
        KategoriAbsen::create([
            'nama_kategori' => 'Hadir',
            'keterangan' => 'Hadir tepat waktu'
        ]);

        // 6. Lokasi Kantor
        LokasiKantor::create([
            'nama_kantor' => 'Kantor Pusat',
            'latitude' => -6.175392, // GANTI DENGAN KOORDINAT KAMU
            'longitude' => 106.827153, // GANTI DENGAN KOORDINAT KAMU
            'radius' => 50 // Kasih 50 meter biar akurat banget
        ]);
    }
}