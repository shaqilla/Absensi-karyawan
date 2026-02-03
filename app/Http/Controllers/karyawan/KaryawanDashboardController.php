<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JadwalKerja; // Tambahkan ini agar tidak error
use Illuminate\Support\Facades\Auth;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $hariIniTanggal = now()->toDateString();
        
        // 1. Ambil nama hari ini (Pastikan mapping ini sama dengan yang ada di database)
        $hariInggris = now()->format('l'); 
        $daftarHari = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu'
        ];
        $hariIni = $daftarHari[$hariInggris];

        // 2. Ambil Jadwal Kerja hari ini
        $jadwalHariIni = \App\Models\JadwalKerja::with('shift')
                            ->where('user_id', $userId)
                            ->where('hari', $hariIni)
                            ->where('status', 'aktif')
                            ->first();

        // 3. Ambil data absen yang sudah dilakukan hari ini
        $presensiHariIni = \App\Models\Presensi::where('user_id', $userId)
                                    ->where('tanggal', $hariIniTanggal)
                                    ->first();

        // 4. Ambil riwayat 7 hari terakhir
        $riwayat = \App\Models\Presensi::where('user_id', $userId)
                            ->orderBy('tanggal', 'desc')
                            ->take(7)
                            ->get();

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni'));
    }

    public function jadwal() 
    {
        // auth()->id() mengambil ID user yang sedang login
        $userId = Auth::id();
            
        // Ambil jadwal milik user tersebut, lengkap dengan data shift-nya
        $jadwals = JadwalKerja::with('shift')
                    ->where('user_id', $userId)
                    ->get();

        // Mengarah ke resources/views/karyawan/jadwal.blade.php
        return view('karyawan.jadwal', compact('jadwals'));
    }

    public function profil()
    {
        // Mengambil data user yang sedang login beserta detail karyawan dan departemennya
        $user = \App\Models\User::with(['karyawan.departemen'])->findOrFail(auth()->id());
        return view('karyawan.profil', compact('user'));
    }
}