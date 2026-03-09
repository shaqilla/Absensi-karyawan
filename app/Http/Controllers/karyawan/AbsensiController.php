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
    public function index()
    {
        $lokasi = LokasiKantor::first();
        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Lokasi belum diatur admin!');
        }
        return view('karyawan.scan', compact('lokasi'));
    }

    public function store(Request $request)
    {
        $lokasi = LokasiKantor::first();
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Mapping Hari
        $hariInggris = now()->format('l');
        $daftarHari = ['Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'];
        $namaHariIni = $daftarHari[$hariInggris];

        // 2. Ambil Jadwal Kerja Aktif
        $jadwal = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Gak ada jadwal buat kamu hari ini!'], 422);
        }

        // 3. Validasi Jarak GPS
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Gagal! Di luar radius kantor.'], 403);
        }

        // 4. Cek Token QR
        $qr = QrSession::where('token', $request->token)->where('is_active', true)->where('expired_at', '>', now())->first();
        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code Kadaluwarsa!'], 403);
        }

        // 5. KUNCI JAM SHIFT
        $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_masuk);
        $batasToleransi = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);
        $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_keluar);

        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        if (!$presensi) {
            // ============================
            // --- LOGIKA ABSEN MASUK ---
            // ============================

            // A. JIKA BELUM WAKTUNYA MASUK
            if ($waktuSekarang->lt($jamMasukShift)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Belum masuk jam kerja. Jadwal Anda: ' . $jadwal->shift->jam_masuk
                ], 422);
            }

            // B. JIKA SUDAH MELEWATI BATAS TOLERANSI (Misal lewat dari 08:05) -> JADI ALPHA
            if ($waktuSekarang->gt($batasToleransi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Kamu udah melebihi batas waktu jam kerja (Kamu tidak masuk kerja).'
                ], 422);
            }

            // C. PENENTUAN STATUS (HADIR vs TELAT)
            // Jika jam 08:00:00 atau sebelumnya -> hadir
            // Jika jam 08:00:01 s/d 08:05:00 -> telat
            if ($waktuSekarang->lte($jamMasukShift)) {
                $status = 'hadir';
                $msg = 'Berhasil absen masuk tepat waktu! Selamat bekerja.';
            } else {
                $status = 'telat';
                $msg = 'Berhasil absen, tapi Anda tercatat TERLAMBAT!';
            }

            Presensi::create([
                'user_id' => $userId,
                'qr_session_id' => $qr->id,
                'shift_id' => $jadwal->shift_id,
                'tanggal' => $hariIniTanggal,
                'jam_masuk' => $waktuSekarang,
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'status' => $status,
                'kategori_id' => 1
            ]);

            return response()->json(['success' => true, 'message' => $msg]);
        } else {
            // ============================
            // --- LOGIKA ABSEN PULANG ---
            // ============================
            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Udah absen pulang!'], 422);
            }

            if ($waktuSekarang->lt($jamPulangShift)) {
                $menit = $waktuSekarang->diffInMinutes($jamPulangShift);
                return response()->json([
                    'success' => false,
                    'message' => 'Belum jam pulang! Tunggu ' . $menit . ' menit lagi.'
                ], 422);
            }

            $presensi->update(['jam_keluar' => $waktuSekarang]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen pulang! Hati-hati di jalan.']);
        }
    }
}
