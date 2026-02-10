<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end_date = $request->end_date ?? now()->toDateString();

        // Ambil data presensi beserta user dan karyawan (untuk departemen)
        $laporans = Presensi::with(['user.karyawan.departemen'])
            ->whereBetween('tanggal', [$start_date, $end_date])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->get();

        return view('admin.laporan.index', compact('laporans', 'start_date', 'end_date'));
    }
}
