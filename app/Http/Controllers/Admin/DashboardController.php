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

        // Statistik
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $hadirHariIni = Presensi::where('tanggal', $hariIni)->where('status', 'hadir')->count();
        $telatHariIni = Presensi::where('tanggal', $hariIni)->where('status', 'telat')->count();
        $pendingIzin = Pengajuan::where('status_approval', 'pending')->count();

        // Data Presensi Terbaru
        $presensiTerbaru = Presensi::with('user')
                            ->where('tanggal', $hariIni)
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan', 
            'hadirHariIni', 
            'telatHariIni', 
            'pendingIzin',
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