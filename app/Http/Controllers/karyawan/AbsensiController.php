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
    /**
     * FUNGSI 1: Membuka Halaman Scan (Metode GET)
     */
    public function index()
    {
        $lokasi = LokasiKantor::first();
        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Lokasi belum diatur admin!');
        }
        return view('karyawan.scan', compact('lokasi'));
    }

    /**
     * FUNGSI 2: Proses Simpan Data (Metode POST)
     */
    public function store(Request $request)
    {
        $lokasi = LokasiKantor::first();
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Ambil Jadwal
        $hariInggris = now()->format('l');
        $daftarHari = ['Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'];
        $namaHariIni = $daftarHari[$hariInggris];

        $jadwal = JadwalKerja::with('shift')->where('user_id', $userId)->whereRaw('LOWER(hari) = ?', [$namaHariIni])->where('status', 'aktif')->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Gak ada jadwal hari ini!'], 422);
        }

        // 2. Validasi Jarak & QR (Tetap Harus Ada)
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Anda di luar radius!'], 403);
        }

        // 3. LOGIKA KETAT ABSEN MASUK
        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_masuk);
        $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);
        $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_keluar);

        if (!$presensi) {
            // CEK APAKAH TERLALU CEPAT? (Cuma boleh pas jamnya atau lebih)
            if ($waktuSekarang->lt($jamMasukShift)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Belum masuk jam kerja (' . $jadwal->shift->jam_masuk . ').'
                ], 422);
            }

            // CEK APAKAH TERLALU LAMBAT? (Alpha)
            if ($waktuSekarang->gt($batasMasuk)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Kamu udah melebihi batas waktu jam kerja (Kamu tidak masuk kerja).'
                ], 422);
            }

            // Simpan Masuk
            Presensi::create([
                'user_id' => $userId,
                'qr_session_id' => QrSession::where('token', $request->token)->first()->id ?? null,
                'tanggal' => $hariIniTanggal,
                'jam_masuk' => $waktuSekarang,
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'status' => 'hadir',
                'kategori_id' => 1
            ]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen masuk tepat waktu!']);
        } else {
            // --- LOGIKA PULANG ---
            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Sudah absen pulang!'], 422);
            }

            if ($waktuSekarang->lt($jamPulangShift)) {
                return response()->json(['success' => false, 'message' => 'Belum waktunya pulang!'], 422);
            }

            $presensi->update(['jam_keluar' => $waktuSekarang]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen pulang!']);
        }
    }
}
