<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $hariIni = now()->toDateString();

        $presensiHariIni = \App\Models\Presensi::where('user_id', $userId)
                                ->where('tanggal', $hariIni)
                                ->first();

        $riwayat = \App\Models\Presensi::where('user_id', $userId)
                        ->orderBy('tanggal', 'desc')
                        ->take(7)
                        ->get();

        // Pastikan file ini ada di resources/views/karyawan/dashboard.blade.php
        return view('layouts.karyawan.dashboard', compact('presensiHariIni', 'riwayat'));
    }
}