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
        $userId = Auth::id();
        $hariIni = now()->toDateString();

        $presensiHariIni = Presensi::where('user_id', $userId)
                                ->where('tanggal', $hariIni)
                                ->first();

        $riwayat = Presensi::where('user_id', $userId)
                        ->orderBy('tanggal', 'desc')
                        ->take(7)
                        ->get();

        // PERBAIKAN: Jangan pakai 'layouts.karyawan.dashboard'
        // Cukup 'karyawan.dashboard' sesuai folder resources/views/karyawan/dashboard.blade.php
        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat'));
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
}