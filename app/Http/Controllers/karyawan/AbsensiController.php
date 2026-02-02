<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;    
use App\Models\QrSession;   
use App\Models\Pengajuan;   // TAMBAHKAN INI UNTUK CEK LEMBUR
use App\Helpers\GeoHelper;  
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function store(Request $request) 
    {
        // 1. Parameter Kantor
        $latKantor = -6.123456; 
        $lonKantor = 106.123456;
        $radiusMaks = 100; 

        $userId = Auth::id();
        $hariIni = now()->toDateString();

        // 2. CEK STATUS ABSEN HARI INI
        $presensi = Presensi::where('user_id', $userId)
                            ->where('tanggal', $hariIni)
                            ->first();

        // Jika sudah absen masuk DAN sudah absen pulang
        if ($presensi && $presensi->jam_keluar !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda sudah menyelesaikan absensi (Masuk & Pulang) untuk hari ini.'
            ], 422);
        }

        // 3. VALIDASI JARAK GPS (Berlaku untuk Masuk maupun Pulang)
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $latKantor, $lonKantor);
        if ($jarak > $radiusMaks) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda berada di luar radius kantor (' . round($jarak) . ' meter)'
            ], 403);
        }

        // 4. VALIDASI TOKEN QR (Berlaku untuk Masuk maupun Pulang)
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

        // 5. EKSEKUSI ABSEN (LOGIC MASUK VS PULANG)
        if (!$presensi) {
            // --- LOGIKA ABSEN MASUK ---
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
                'message' => 'Berhasil Absen MASUK! Selamat bekerja.'
            ]);

        } else {
            // --- LOGIKA ABSEN PULANG ---
            
            // Cek apakah ada pengajuan LEMBUR yang sudah di-ACC Admin untuk hari ini
            $lembur = Pengajuan::where('user_id', $userId)
                                ->where('jenis_pengajuan', 'lembur')
                                ->where('status_approval', 'disetujui')
                                ->whereDate('tanggal_mulai', $hariIni)
                                ->first();

            $presensi->update([
                'jam_keluar' => now(),
                'keterangan' => $lembur ? 'Pulang (Lembur Disetujui)' : 'Pulang Standar'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Absen PULANG! ' . ($lembur ? 'Lembur Anda telah tercatat.' : 'Hati-hati di jalan.')
            ]);
        }
    }
}