<?php

namespace App\Http\Controllers\Karyawan; // Sesuaikan namespace jika dalam folder

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;    // WAJIB ADA AGAR TIDAK ERROR
use App\Models\QrSession;   // WAJIB ADA AGAR TIDAK ERROR
use App\Helpers\GeoHelper;  // Memanggil Helper Jarak
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function store(Request $request) 
    {
        // Parameter Kantor (Bisa diubah sesuai koordinat kantormu)
        $latKantor = -6.123456; 
        $lonKantor = 106.123456;
        $radiusMaks = 100; // dalam meter

        $userId = Auth::id();
        $hariIni = now()->toDateString();

        // --- VALIDASI 1: CEK APAKAH SUDAH ABSEN HARI INI? (Request Kamu) ---
        $sudahAbsen = Presensi::where('user_id', $userId)
                              ->where('tanggal', $hariIni)
                              ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda sudah melakukan absensi hari ini.'
            ], 422);
        }

        // --- VALIDASI 2: CEK JARAK GPS ---
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $latKantor, $lonKantor);
        if ($jarak > $radiusMaks) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda berada di luar radius kantor (' . round($jarak) . ' meter)'
            ], 403);
        }

        // --- VALIDASI 3: CEK TOKEN QR ---
        $qr = QrSession::where('token', $request->token)
                        ->where('is_active', true)
                        ->where('expired_at', '>', now())
                        ->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluwarsa!'
            ], 403);
        }

        // --- PROSES SIMPAN JIKA SEMUA VALID ---
        Presensi::create([
            'user_id' => $userId,
            'qr_session_id' => $qr->id,
            'tanggal' => $hariIni,
            'jam_masuk' => now(),
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'status' => 'hadir', 
            'kategori_id' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Absen! Selamat bekerja.'
        ]);
    }
}