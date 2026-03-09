<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiManualController extends Controller
{
    public function create()
    {
        $karyawans = User::where('role', 'karyawan')->get();
        $shifts = Shift::all();
        return view('admin.presensi.manual', compact('karyawans', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'shift_id' => 'required',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,telat',
            'keterangan' => 'required|string',
        ]);

        // Cek apakah sudah ada absen di tanggal tersebut
        $cek = Presensi::where('user_id', $request->user_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($cek) {
            return back()->with('error', 'Karyawan ini sudah memiliki data absensi pada tanggal tersebut.');
        }

        Presensi::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'qr_session_id' => null, // Manual tidak pakai QR
            'tanggal' => $request->tanggal,
            'jam_masuk' => $request->tanggal . ' ' . now()->toTimeString(),
            'latitude' => 0, // Manual tidak pakai koordinat
            'longitude' => 0,
            'status' => $request->status,
            'kategori_id' => 1,
            'keterangan' => '[MANUAL BY ADMIN] ' . $request->keterangan
        ]);

        return redirect()->route('admin.laporan.index')->with('success', 'Absensi manual berhasil dicatat.');
    }
}
