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
    // TAMPILKAN FORM INPUT ABSENSI MANUAL
    public function create()
    {
        // Ambil semua user yang rolenya karyawan untuk pilihan dropdown
        $karyawans = User::where('role', 'karyawan')->get();

        // Ambil semua shift yang tersedia untuk pilihan dropdown
        $shifts = Shift::all();

        return view('admin.presensi.manual', compact('karyawans', 'shifts'));
    }

    // SIMPAN ABSENSI MANUAL KE DATABASE
    public function store(Request $request)
    {
        // Validasi semua input dari form
        $request->validate([
            'user_id'    => 'required',
            'shift_id'   => 'required',
            'tanggal'    => 'required|date',                // Harus format tanggal valid
            'status'     => 'required|in:hadir,telat',     // Hanya boleh hadir atau telat
            'keterangan' => 'required|string',             // Wajib diisi alasannya
        ]);

        // Cek apakah karyawan ini sudah punya data absen di tanggal yang sama
        // Mencegah duplikasi data absensi
        $cek = Presensi::where('user_id', $request->user_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        // Kalau sudah ada → batalkan dan kembalikan pesan error
        if ($cek) {
            return back()->with('error', 'Karyawan ini sudah memiliki data absensi pada tanggal tersebut.');
        }

        // Simpan data absensi manual ke database
        Presensi::create([
            'user_id'       => $request->user_id,
            'shift_id'      => $request->shift_id,
            'qr_session_id' => null,        // Absen manual tidak melalui scan QR
            'tanggal'       => $request->tanggal,

            // Jam masuk = tanggal yang dipilih + jam sekarang saat admin input
            'jam_masuk'     => $request->tanggal . ' ' . now()->toTimeString(),

            'latitude'      => 0,           // Absen manual tidak pakai GPS
            'longitude'     => 0,           // Absen manual tidak pakai GPS
            'status'        => $request->status,
            'kategori_id'   => 1,

            // Keterangan ditandai [MANUAL BY ADMIN] supaya bisa dibedakan dari absen biasa
            'keterangan'    => '[MANUAL BY ADMIN] ' . $request->keterangan
        ]);

        // Redirect ke halaman laporan dengan pesan sukses
        return redirect()->route('admin.laporan.index')->with('success', 'Absensi manual berhasil dicatat.');
    }
}
