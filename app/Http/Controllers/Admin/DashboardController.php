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
    public function index()
    {
        // Set timezone ke Jakarta agar sinkron dengan jam laptop/HP
        date_default_timezone_set('Asia/Jakarta');

        $hariIni = now()->toDateString();
        $waktuSekarang = now();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $hariInggris = now()->format('l');
        $mappingHari = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];
        $hariNama = $mappingHari[$hariInggris];

        // 1. STATISTIK HARI INI
        $totalKaryawan = Karyawan::count();

        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir')
            ->whereHas('user.karyawan')
            ->count();

        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

        // 2. LOGIKA "TIDAK HADIR" (ALPHA) HARI INI
        $semuaJadwal = JadwalKerja::with('shift')
            ->where('hari', $hariNama)
            ->where('status', 'aktif')
            ->get();

        $tidakHadir = 0;

        foreach ($semuaJadwal as $j) {
            $absenExist = Presensi::where('user_id', $j->user_id)
                ->where('tanggal', $hariIni)
                ->exists();

            $isIzin = Pengajuan::where('user_id', $j->user_id)
                ->where('status_approval', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $hariIni)
                ->whereDate('tanggal_selesai', '>=', $hariIni)
                ->exists();

            if (!$absenExist && !$isIzin) {
                $batasMasuk = Carbon::parse($hariIni . ' ' . $j->shift->jam_masuk)
                    ->addMinutes($j->shift->toleransi_telat);

                if ($waktuSekarang->greaterThan($batasMasuk)) {
                    $tidakHadir++;
                }
            }
        }

        // 3. REKAPITULASI BULAN INI (REVISI GURU)
        // Menghitung total data akumulasi selama sebulan ini
        $rekapBulanan = [
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)
                ->whereYear('tanggal', $tahunIni)
                ->where('status', 'hadir')
                ->count(),
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)
                ->whereYear('tanggal', $tahunIni)
                ->where('status', 'telat')
                ->count(),
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)
                ->whereYear('tanggal_mulai', $tahunIni)
                ->where('status_approval', 'disetujui')
                ->count(),
        ];

        // 4. Tabel Aktivitas Terbaru
        $presensiTerbaru = Presensi::whereHas('user.karyawan')
            ->with(['user.karyawan.departemen', 'shift'])
            ->where('tanggal', $hariIni)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'telatHariIni',
            'tidakHadir',
            'presensiTerbaru',
            'rekapBulanan' // Kirim data rekap ke view
        ));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(auth()->id());
        return view('admin.profil', compact('user'));
    }
}
