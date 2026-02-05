<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;    
use App\Models\QrSession;   
use App\Models\Pengajuan;   
use App\Models\LokasiKantor; // TAMBAHKAN INI
use App\Helpers\GeoHelper;  
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function store(Request $request) 
    {
        $lokasi = \App\Models\LokasiKantor::first();
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now(); // Jam saat ini

        // 1. Mapping Hari untuk cari Jadwal
        $daftarHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHariIni = $daftarHari[now()->format('l')];

        // 2. Ambil Jadwal & Shift Karyawan hari ini
        $jadwal = \App\Models\JadwalKerja::with('shift')
                    ->where('user_id', $userId)
                    ->where('hari', $namaHariIni)
                    ->where('status', 'aktif')
                    ->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki jadwal kerja hari ini!'], 422);
        }

        // 3. Validasi Jarak & Token QR (Sama seperti sebelumnya)
        $jarak = \App\Helpers\GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Di luar radius kantor!'], 403);
        }

        $qr = \App\Models\QrSession::where('token', $request->token)->where('is_active', true)->where('expired_at', '>', now())->first();
        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code Kadaluwarsa!'], 403);
        }

        // 4. CEK APAKAH SUDAH ABSEN MASUK?
        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        if (!$presensi) {
            // --- LOGIKA PENENTUAN STATUS (HADIR ATAU TELAT) ---
            
            $jamMasukShift = $jadwal->shift->jam_masuk; // Contoh: 08:00:00
            $toleransi = $jadwal->shift->toleransi_telat; // Contoh: 15 (menit)

            // Buat objek waktu untuk membandingkan
            $jadwalMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jamMasukShift);
            $batasWaktu = $jadwalMasuk->copy()->addMinutes($toleransi);

            // Jika waktu sekarang melewati batas toleransi
            if ($waktuSekarang->greaterThan($batasWaktu)) {
                $status = 'telat';
                $pesan = 'Berhasil absen, tapi Anda TERLAMBAT!';
            } else {
                $status = 'hadir';
                $pesan = 'Berhasil absen MASUK tepat waktu. Selamat bekerja!';
            }

            Presensi::create([
                'user_id' => $userId,
                'qr_session_id' => $qr->id,
                'tanggal' => $hariIniTanggal,
                'jam_masuk' => $waktuSekarang,
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'status' => $status, // 'hadir' atau 'telat'
                'kategori_id' => 1
            ]);

            return response()->json(['success' => true, 'message' => $pesan]);

        } else {
            // --- LOGIKA PULANG (Sama seperti sebelumnya) ---
            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen pulang!'], 422);
            }

            $presensi->update(['jam_keluar' => $waktuSekarang]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen PULANG!']);
        }
    }
}