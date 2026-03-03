<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;    
use App\Models\QrSession;   
use App\Models\Pengajuan;   
use App\Models\LokasiKantor; 
use App\Models\JadwalKerja;
use App\Helpers\GeoHelper;  
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // TAMPILAN: Membuka Halaman Scan Kamera
    public function index()
    {
        $lokasi = LokasiKantor::first();

        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Lokasi kantor belum diatur oleh admin!');
        }

        return view('karyawan.scan', compact('lokasi'));
    }

    // LOGIKA: Proses Simpan Absen (Masuk/Pulang)
    public function store(Request $request) 
    {
        $lokasi = LokasiKantor::first(); 
        if (!$lokasi) {
            return response()->json(['success' => false, 'message' => 'Lokasi belum diset admin.'], 500);
        }

        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Ambil Nama Hari
        $hariInggris = now()->format('l');
        $daftarHari = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'
        ];
        $namaHariIni = $daftarHari[$hariInggris];

        // 2. Cari Jadwal Kerja
        $jadwal = JadwalKerja::with('shift')
                    ->where('user_id', $userId)
                    ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
                    ->where('status', 'aktif')
                    ->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki jadwal kerja aktif hari ini!'], 422);
        }

        // 3. Validasi Jarak
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Gagal! Anda berada di luar radius kantor.'], 403);
        }

        // 4. Validasi Token QR
        $qr = QrSession::where('token', $request->token)->where('is_active', true)->where('expired_at', '>', now())->first();
        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid/kadaluwarsa!'], 403);
        }

        // 5. Cek Data Absen Hari Ini
        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        if (!$presensi) {
            // ============================
            // --- LOGIKA ABSEN MASUK ---
            // ============================
            $jamMasukShift = Carbon::parse($jadwal->shift->jam_masuk);
            $batasWaktu = $jamMasukShift->addMinutes($jadwal->shift->toleransi_telat);
            
            $status = ($waktuSekarang->greaterThan($batasWaktu)) ? 'telat' : 'hadir';

            Presensi::create([
                'user_id' => $userId,
                'qr_session_id' => $qr->id,
                'tanggal' => $hariIniTanggal,
                'jam_masuk' => $waktuSekarang,
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'status' => $status,
                'kategori_id' => 1
            ]);
            
            $msg = ($status == 'telat') ? 'Berhasil! Anda tercatat TERLAMBAT.' : 'Berhasil absen masuk tepat waktu.';
            return response()->json(['success' => true, 'message' => $msg]);

        } else {
            // ============================
            // --- LOGIKA ABSEN PULANG ---
            // ============================
            
            // A. Cek jika sudah pernah pulang
            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absen pulang hari ini.'], 422);
            }

            // B. PROTEKSI PULANG AWAL (REVISI ANDA)
            $jamPulangJadwal = Carbon::parse($jadwal->shift->jam_keluar);
            
            // Jika jam sekarang masih kurang dari jam pulang di jadwal
            if ($waktuSekarang->lessThan($jamPulangJadwal)) {
                $menitKurang = $waktuSekarang->diffInMinutes($jamPulangJadwal);
                return response()->json([
                    'success' => false, 
                    'message' => "Belum waktunya pulang! Tunggu " . $menitKurang . " menit lagi sesuai jadwal (" . date('H:i', strtotime($jadwal->shift->jam_keluar)) . ")."
                ], 422);
            }

            // C. Cek apakah ada pengajuan LEMBUR yang disetujui untuk hari ini
            $lembur = Pengajuan::where('user_id', $userId)
                        ->where('jenis_pengajuan', 'lembur')
                        ->where('status_approval', 'disetujui')
                        ->whereDate('tanggal_mulai', $hariIniTanggal)
                        ->first();

            // D. Proses Update Pulang
            $presensi->update([
                'jam_keluar' => $waktuSekarang,
                'keterangan' => $lembur ? 'Pulang (Lembur Disetujui)' : 'Pulang Standar'
            ]);

            $pesanSukses = $lembur ? 'Berhasil absen pulang! Lembur Anda telah tercatat.' : 'Berhasil absen pulang! Hati-hati di jalan.';
            return response()->json(['success' => true, 'message' => $pesanSukses]);
        }
    }
}