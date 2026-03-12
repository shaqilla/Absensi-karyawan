<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\QrSession;
use App\Models\Pengajuan;
use App\Models\LokasiKantor;
use App\Models\JadwalKerja;
use App\Helpers\GeoHelper;       // Helper khusus untuk hitung jarak GPS
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Class adalah sebuah cetakan/template untuk membuat objek.
// extends termasuk ke inheritance karena konsep pewarisan dari satu class ke class lain.
class AbsensiController extends Controller
{
    // menampilkan halaman scan QR ke karyawan.
    // Function adalah sekumpulan kode yang diberi nama
    public function index()
    {
        // Ambil data lokasi kantor (untuk validasi radius GPS)
        $lokasi = LokasiKantor::first();

        // Kalau admin belum setting lokasi kantor → tidak bisa absen
        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Lokasi belum diatur admin!');
        }

        // Kirim data lokasi ke view (untuk keperluan GPS di frontend)
        return view('karyawan.scan', compact('lokasi'));
    }

    // PROSES ABSENSI SAAT KARYAWAN SCAN QR
    // memproses semua logika absensi saat karyawan scan QR
    public function store(Request $request)
    {
        $lokasi         = LokasiKantor::first();
        $userId         = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang  = now();

        // LANGKAH 1: Mapping nama hari Inggris → Indonesia
        // Karena database menyimpan hari dalam bahasa Indonesia
        $hariInggris = now()->format('l');
        $daftarHari  = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat',
            'Saturday' => 'sabtu', 'Sunday' => 'minggu'
        ];
        $namaHariIni = $daftarHari[$hariInggris];

        // LANGKAH 2: Cek apakah karyawan punya jadwal kerja aktif hari ini
        $jadwal = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni]) // LOWER() supaya huruf besar/kecil tidak masalah
            ->where('status', 'aktif')
            ->first();

        // Kalau tidak ada jadwal → tolak absensi
        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Gak ada jadwal buat kamu hari ini!'
            ], 422);
        }

        // LANGKAH 3: Validasi jarak GPS
        // GeoHelper::calculateDistance() menghitung jarak antara 2 koordinat GPS dalam meter
        // function kumpulan perintah untuk menjalankan tugas tertentu
        // memanggil function calculateDistance() dari file GeoHelper untuk menghitung jarak GPS.
        $jarak = GeoHelper::calculateDistance(
            $request->lat, $request->lng,           // Koordinat karyawan sekarang
            $lokasi->latitude, $lokasi->longitude   // Koordinat kantor
        );

        // Kalau jarak melebihi radius yang diset admin → tolak
        if ($jarak > $lokasi->radius) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Di luar radius kantor.'
            ], 403);
        }

        // LANGKAH 4: Validasi token QR
        // Token harus aktif DAN belum expired
        $qr = QrSession::where('token', $request->token)
            ->where('is_active', true)
            ->where('expired_at', '>', now())   // Cek token belum kadaluwarsa
            ->first();

        // Kalau token tidak valid / sudah expired → tolak
        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code Kadaluwarsa!'
            ], 403);
        }

        // LANGKAH 5: Siapkan patokan waktu dari data shift
        // Jam masuk shift (contoh: 08:00)
        $jamMasukShift  = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_masuk);

        // Batas toleransi = jam masuk + toleransi (contoh: 08:00 + 5 menit = 08:05)
        // copy() supaya $jamMasukShift tidak ikut berubah
        $batasToleransi = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);

        // Jam pulang shift (contoh: 17:00)
        $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_keluar);

        // Cek apakah karyawan sudah punya presensi hari ini
        $presensi = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();


        // BELUM ADA PRESENSI → PROSES ABSEN MASUK

        if (!$presensi) {

            // A. Belum waktunya masuk → tolak
            if ($waktuSekarang->lt($jamMasukShift)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Belum masuk jam kerja. Jadwal Anda: ' . $jadwal->shift->jam_masuk
                ], 422);
            }

            // B. Sudah lewat batas toleransi → dianggap tidak masuk (alpha)
            if ($waktuSekarang->gt($batasToleransi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gak bisa absen! Kamu udah melebihi batas waktu jam kerja.'
                ], 422);
            }

            // C. Tentukan status: HADIR atau TELAT
            // Tepat jam masuk atau sebelumnya → hadir
            // Setelah jam masuk tapi masih dalam toleransi → telat
            if ($waktuSekarang->lte($jamMasukShift)) {
                $status = 'hadir';
                $msg    = 'Berhasil absen masuk tepat waktu! Selamat bekerja.';
            } else {
                $status = 'telat';
                $msg    = 'Berhasil absen, tapi Anda tercatat TERLAMBAT!';
            }

            // Simpan data presensi masuk
            Presensi::create([
                'user_id'       => $userId,
                'qr_session_id' => $qr->id,         // ID sesi QR yang dipakai
                'shift_id'      => $jadwal->shift_id,
                'tanggal'       => $hariIniTanggal,
                'jam_masuk'     => $waktuSekarang,
                'latitude'      => $request->lat,   // Koordinat GPS saat absen
                'longitude'     => $request->lng,
                'status'        => $status,          // hadir / telat
                'kategori_id'   => 1
            ]);

            return response()->json(['success' => true, 'message' => $msg]);


        // SUDAH ADA PRESENSI → PROSES ABSEN PULANG

        } else {

            // Kalau jam_keluar sudah ada → sudah absen pulang sebelumnya
            if ($presensi->jam_keluar != null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Udah absen pulang!'
                ], 422);
            }

            // Kalau belum jam pulang → tolak, kasih tahu sisa waktu
            if ($waktuSekarang->lt($jamPulangShift)) {
                $menit = $waktuSekarang->diffInMinutes($jamPulangShift); // Hitung sisa menit
                return response()->json([
                    'success' => false,
                    'message' => 'Belum jam pulang! Tunggu ' . $menit . ' menit lagi.'
                ], 422);
            }

            // Semua validasi lolos → update jam keluar
            $presensi->update(['jam_keluar' => $waktuSekarang]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil absen pulang! Hati-hati di jalan.'
            ]);
        }
    }
}