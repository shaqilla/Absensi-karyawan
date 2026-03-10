<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Fungsi utama yang dipanggil saat admin buka halaman dashboard
    public function index()
    {
        // Set timezone ke Jakarta supaya jam yang dipakai sesuai waktu Indonesia
        date_default_timezone_set('Asia/Jakarta');

        // Ambil tanggal hari ini dalam format Y-m-d (contoh: 2025-03-10)
        $hariIni = now()->toDateString();

        // Ambil waktu sekarang (dengan jam:menit:detik) untuk perbandingan toleransi
        $waktuSekarang = now();

        // Ambil bulan dan tahun sekarang untuk filter rekap bulanan
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Ambil nama hari dalam bahasa Inggris (contoh: 'Monday')
        $hariInggris = now()->format('l');

        // Mapping nama hari dari Inggris ke Indonesia
        // Karena database menyimpan nama hari dalam bahasa Indonesia
        $mappingHari = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu'
        ];

        // Ambil nama hari Indonesia berdasarkan hari Inggris hari ini
        $hariNama = $mappingHari[$hariInggris];

    
        // 1. STATISTIK HARI INI
        // Hitung total seluruh karyawan yang ada di database
        $totalKaryawan = Karyawan::count();

        // Hitung karyawan yang statusnya 'hadir' hari ini
        // whereHas('user.karyawan') = pastikan yang dihitung hanya user yang punya data karyawan
        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir')
            ->whereHas('user.karyawan')
            ->count();

        // Hitung karyawan yang statusnya 'telat' hari ini
        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

    
        // 2. LOGIKA "TIDAK HADIR" / ALPHA HARI INI
        // Ambil semua jadwal kerja yang aktif untuk hari ini
        // with('shift') = sekalian ambil data shift-nya (jam masuk, toleransi, dll)
        $semuaJadwal = JadwalKerja::with('shift')
            ->where('hari', $hariNama)
            ->where('status', 'aktif')
            ->get();

        // Mulai hitung dari 0
        $tidakHadir = 0;

        // Loop satu per satu jadwal yang ada hari ini
        foreach ($semuaJadwal as $j) {

            // Cek apakah karyawan ini sudah melakukan presensi hari ini (apapun statusnya)
            $absenExist = Presensi::where('user_id', $j->user_id)
                ->where('tanggal', $hariIni)
                ->exists();

            // Cek apakah karyawan ini punya izin/cuti yang sudah disetujui dan mencakup hari ini
            $isIzin = Pengajuan::where('user_id', $j->user_id)
                ->where('status_approval', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $hariIni)  // izin sudah mulai
                ->whereDate('tanggal_selesai', '>=', $hariIni) // izin belum selesai
                ->exists();

            // Kalau belum absen DAN tidak sedang izin, baru dicek lebih lanjut
            if (!$absenExist && !$isIzin) {

                // Hitung batas waktu masuk = jam masuk + toleransi telat
                // Contoh: jam masuk 08:00, toleransi 15 menit → batas = 08:15
                $batasMasuk = Carbon::parse($hariIni . ' ' . $j->shift->jam_masuk)
                    ->addMinutes($j->shift->toleransi_telat);

                // Kalau waktu sekarang sudah melewati batas masuk → dihitung alpha
                if ($waktuSekarang->greaterThan($batasMasuk)) {
                    $tidakHadir++;
                }
            }
        }

        // 3. REKAPITULASI BULAN INI
        // Hitung total hadir, telat, dan izin selama bulan ini
        $rekapBulanan = [
            // Total presensi dengan status 'hadir' di bulan & tahun ini
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)
                ->whereYear('tanggal', $tahunIni)
                ->where('status', 'hadir')
                ->count(),

            // Total presensi dengan status 'telat' di bulan & tahun ini
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)
                ->whereYear('tanggal', $tahunIni)
                ->where('status', 'telat')
                ->count(),

            // Total pengajuan izin/cuti yang sudah disetujui di bulan ini
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)
                ->whereYear('tanggal_mulai', $tahunIni)
                ->where('status_approval', 'disetujui')
                ->count(),
        ];

        // 4. TABEL AKTIVITAS TERBARU
        // Ambil 5 data presensi terbaru hari ini, diurutkan dari yang paling baru
        // with([...]) = eager loading, sekalian ambil relasi user → karyawan → departemen & shift
        $presensiTerbaru = Presensi::whereHas('user.karyawan')
            ->with(['user.karyawan.departemen', 'shift'])
            ->where('tanggal', $hariIni)
            ->orderBy('created_at', 'desc') // urutkan terbaru dulu
            ->take(5)                        // ambil maksimal 5 data
            ->get();

        // KIRIM SEMUA DATA KE VIEW DASHBOARD
        // compact() mengubah variabel menjadi array associative untuk dikirim ke blade view
        return view('admin.dashboard', compact(
            'totalKaryawan',    // Total semua karyawan
            'hadirHariIni',     // Jumlah hadir hari ini
            'telatHariIni',     // Jumlah telat hari ini
            'tidakHadir',       // Jumlah alpha hari ini
            'presensiTerbaru',  // 5 aktivitas presensi terbaru
            'rekapBulanan'      // Rekap total bulan ini
        ));
    }

    // FUNGSI PROFIL ADMIN
    // Menampilkan halaman profil admin yang sedang login
    public function profil()
    {
        // Ambil data user yang sedang login beserta relasi karyawan dan departemennya
        // findOrFail = kalau tidak ditemukan, otomatis lempar error 404
        $user = User::with(['karyawan.departemen'])->findOrFail(auth()->id());

        // Kirim data user ke halaman profil admin
        return view('admin.profil', compact('user'));
    }
}
