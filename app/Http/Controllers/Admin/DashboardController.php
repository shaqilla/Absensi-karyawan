<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $hariIni = now()->toDateString();

        // 1. Total semua karyawan yang terdaftar
        $totalKaryawan = \App\Models\Karyawan::count();

        // 2. Total yang hadir (status 'hadir' atau 'telat')
        $hadirHariIni = \App\Models\Presensi::where('tanggal', $hariIni)
            ->whereHas('user.karyawan')
            ->count();

        // 3. Menghitung yang Tidak Hadir (Total - Hadir)
        $tidakHadir = $totalKaryawan - $hadirHariIni;

        // 4. Tetap ambil jumlah Telat untuk kartu nomor 3
        $telatHariIni = \App\Models\Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

        // 5. Data Tabel Presensi Terbaru
        $presensiTerbaru = \App\Models\Presensi::whereHas('user.karyawan')
            ->with(['user.karyawan.departemen'])
            ->where('tanggal', $hariIni)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'telatHariIni',
            'tidakHadir', // Variabel baru
            'presensiTerbaru'
        ));
    }

    public function profil()
    {
        // Admin biasanya tetap ada di tabel User & Karyawan
        $user = \App\Models\User::with(['karyawan.departemen'])->findOrFail(auth()->id());
        return view('admin.profil', compact('user'));
    }
}
