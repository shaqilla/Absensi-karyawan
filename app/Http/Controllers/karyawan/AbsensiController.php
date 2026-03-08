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

        // 1. Ambil Nama Hari
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

        // 4. KUNCI JAM SHIFT
        $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_masuk);
        $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);
        $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_keluar);

        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        if (!$presensi) {

            // ---ABSEN MASUK ---


            // JIKA SHIFT BELUM DIMULAI
            if ($waktuSekarang->lt($jamMasukShift)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Belum masuk jam kerja. Jadwal Anda: ' . $jadwal->shift->jam_masuk
                ], 422);
            }

            // JIKA SUDAH LEWAT TOLERANSI (ALPHA)
            if ($waktuSekarang->gt($batasMasuk)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Kamu udah melewati batas waktu (Alpha).'
                ], 422);
            }

            // SIMPAN ABSEN DENGAN MENGUNCI ID SHIFT (REVISI GURU)
            Presensi::create([
                'user_id' => $userId,
                'qr_session_id' => QrSession::where('token', $request->token)->first()->id ?? null,
                'shift_id' => $jadwal->shift_id, // <--- INI KUNCI SNAPSHOTNYA
                'tanggal' => $hariIniTanggal,
                'jam_masuk' => $waktuSekarang,
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'status' => 'hadir',
                'kategori_id' => 1
            ]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen masuk!']);
        } else {
            // ============================
            // --- LOGIKA ABSEN PULANG ---

            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Udah absen pulang!'], 422);
            }

            // CEK APAKAH SUDAH WAKTUNYA PULANG
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
