<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\QrSession;
use App\Models\Pengajuan;
use App\Models\LokasiKantor; 
use App\Models\JadwalKerja; // Pastikan ini diimport
use App\Helpers\GeoHelper;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman scanner dan mengirim data lokasi kantor
     */
    public function scan()
    {
        // Mengambil data lokasi kantor untuk deteksi radius di sisi browser (JS)
        $lokasi = LokasiKantor::first();

        if (!$lokasi) {
            return redirect()->back()->with('error', 'Data lokasi kantor belum diatur oleh admin.');
        }

        return view('karyawan.scan', compact('lokasi'));
    }

    /**
     * Memproses data absensi dari hasil scan
     */
    public function store(Request $request)
    {
        $lokasi = LokasiKantor::first();
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now(); 

        // 1. Mapping Hari untuk cari Jadwal
        $daftarHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $namaHariIni = $daftarHari[now()->format('l')];

        // 2. Ambil Jadwal & Shift Karyawan hari ini
        $jadwal = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->where('hari', $namaHariIni)
            ->where('status', 'aktif')
            ->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki jadwal kerja hari ini!'], 422);
        }

        // 3. Validasi Jarak & Token QR
        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Di luar radius kantor!'], 403);
        }

        $qr = QrSession::where('token', $request->token)
            ->where('is_active', true)
            ->where('expired_at', '>', now())
            ->first();

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code Kadaluwarsa!'], 403);
        }

        // 4. CEK APAKAH SUDAH ABSEN MASUK?
        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        if (!$presensi) {
            // --- LOGIKA PENENTUAN STATUS (HADIR ATAU TELAT) ---
            $jamMasukShift = $jadwal->shift->jam_masuk; 
            $toleransi = $jadwal->shift->toleransi_telat; 

            $jadwalMasuk = Carbon::createFromFormat('H:i:s', $jamMasukShift);
            $batasWaktu = $jadwalMasuk->copy()->addMinutes($toleransi);

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
                'status' => $status, 
                'kategori_id' => 1
            ]);

            return response()->json(['success' => true, 'message' => $pesan]);
        } else {
            // --- LOGIKA PULANG ---
            if ($presensi->jam_keluar != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen pulang!'], 422);
            }

            $presensi->update(['jam_keluar' => $waktuSekarang]);
            return response()->json(['success' => true, 'message' => 'Berhasil absen PULANG!']);
        }
    }
}