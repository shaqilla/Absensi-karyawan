<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    // DASHBOARD UTAMA KARYAWAN
    public function index()
    {
        $userId         = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang  = now();

        // Mapping nama hari Inggris → Indonesia (sama seperti controller lain)
        $hariInggris = now()->format('l');
        $daftarHari  = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];
        $namaHariIni = $daftarHari[$hariInggris];

        // Ambil jadwal kerja karyawan ini untuk hari ini
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni]) // Case-insensitive
            ->where('status', 'aktif')
            ->first();

        // Ambil data presensi karyawan ini untuk hari ini (kalau ada)
        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();


        // LOGIKA STATUS TAMPILAN DASHBOARD

        $isAlpha   = false; // Penanda karyawan dinyatakan alpha
        $isWaiting = false; // Penanda belum waktunya scan

        // Hanya dijalankan kalau belum absen DAN punya jadwal hari ini
        if (!$presensiHariIni && $jadwalHariIni) {

            // Patokan waktu dari data shift
            $jamMasukShift  = Carbon::parse($hariIniTanggal . ' ' . $jadwalHariIni->shift->jam_masuk);

            // Batas toleransi = jam masuk + toleransi menit
            $batasMasuk     = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);

            // Boleh mulai scan = 15 menit SEBELUM jam masuk
            // subMinutes(15) = kurangi 15 menit dari jam masuk
            $mulaiBolehScan = $jamMasukShift->copy()->subMinutes(15);

            if ($waktuSekarang->lt($mulaiBolehScan)) {
                // Waktu sekarang masih terlalu awal (lebih dari 15 menit sebelum masuk)
                $isWaiting = true;
            } elseif ($waktuSekarang->gt($batasMasuk)) {
                // Waktu sekarang sudah lewat batas toleransi → alpha
                $isAlpha = true;
            }
            // Kalau tidak keduanya → berarti dalam rentang boleh scan (normal)
        }

        // Ambil 7 riwayat presensi terakhir untuk ditampilkan di dashboard
        $riwayat = Presensi::where('user_id', $userId)
            ->orderBy('tanggal', 'desc') // Terbaru dulu
            ->take(7)                    // Ambil 7 data saja
            ->get();

        return view('karyawan.dashboard', compact(
            'presensiHariIni',  // Status absen hari ini
            'riwayat',          // 7 riwayat presensi terakhir
            'jadwalHariIni',    // Info jadwal & shift hari ini
            'isAlpha',          // True kalau karyawan alpha
            'isWaiting'         // True kalau belum waktunya scan
        ));
    }

    // HALAMAN JADWAL KERJA KARYAWAN
    public function jadwal()
    {
        $jadwals = JadwalKerja::with('shift')
            ->where('user_id', Auth::id())
            // Urutkan hari sesuai urutan yang logis (senin-minggu)
            // FIELD() = fungsi MySQL untuk urutan custom
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->get();

        return view('karyawan.jadwal', compact('jadwals'));
    }

    // HALAMAN PROFIL KARYAWAN
    public function profil()
    {
        // Ambil data user yang login beserta detail karyawan dan departemennya
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('karyawan.profil', compact('user'));
    }

    // HALAMAN LAPORAN PRESENSI PRIBADI
    public function laporan(Request $request)
    {
        $userId = Auth::id();

        // Ambil filter bulan & tahun dari URL, default ke bulan & tahun sekarang
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // Ambil semua presensi karyawan ini di bulan & tahun yang dipilih
        $laporans = Presensi::with('shift')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc') // Urutkan dari tanggal terlama
            ->get();

        // Hitung statistik dari data yang sudah diambil
        $stats = [
            // Hitung dari collection presensi (tidak query ulang ke DB)
            'hadir' => $laporans->where('status', 'hadir')->count(),
            'telat' => $laporans->where('status', 'telat')->count(),

            // Hitung izin & sakit dari tabel pengajuan (query terpisah)
            'izin'  => Pengajuan::where('user_id', $userId)
                ->where('status_approval', 'disetujui')
                ->where('jenis_pengajuan', 'izin')
                ->whereMonth('tanggal_mulai', $bulan)
                ->count(),
            'sakit' => Pengajuan::where('user_id', $userId)
                ->where('status_approval', 'disetujui')
                ->where('jenis_pengajuan', 'sakit')
                ->whereMonth('tanggal_mulai', $bulan)
                ->count(),
        ];

        return view('karyawan.laporan', compact('laporans', 'stats', 'bulan', 'tahun'));
    }

    public function raporSaya()
    {
        $userId = Auth::id();

        // 1. Ambil penilaian terbaru untuk saya
        $penilaian = \App\Models\Assessment::with('details.category')
            ->where('evaluatee_id', $userId)
            ->latest()
            ->first();

        // 2. Jika belum ada nilai, kirim data kosong
        if (!$penilaian) {
            return view('karyawan.rapor', ['labels' => [], 'scores' => [], 'notes' => 'Belum ada penilaian.']);
        }

        // 3. Susun data untuk Grafik Radar
        $labels = [];
        $scores = [];
        foreach ($penilaian->details as $detail) {
            $labels[] = $detail->category->name;
            $scores[] = $detail->score;
        }

        $notes = $penilaian->general_notes;

        return view('karyawan.rapor', compact('labels', 'scores', 'notes'));
    }
}
