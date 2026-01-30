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

        $laporans = Presensi::with('user')
            ->whereBetween('tanggal', [$start_date, $end_date])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.laporan.index', compact('laporans', 'start_date', 'end_date'));
    }
}