<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\Pengajuan;
use App\Models\PointLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. PENGATURAN WAKTU & TIMEZONE
        date_default_timezone_set('Asia/Jakarta');
        $hariIni = now()->toDateString();
        $kemarin = now()->subDay()->toDateString();
        $waktuSekarang = now();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $hariInggris = now()->format('l');
        $mappingHari = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu'
        ];
        $hariNama = $mappingHari[$hariInggris];

        // --- LOGIKA PENALTI ALPA OTOMATIS (DIPROSES SAAT ADMIN LOGIN) ---
        // (KODE INI TETAP ADA, TIDAK GUE HAPUS)
        if (!now()->subDay()->isSunday()) {
            $hariNamaKemarin = $mappingHari[now()->subDay()->format('l')];
            $jadwalKemarin = JadwalKerja::where('hari', $hariNamaKemarin)->where('status', 'aktif')->get();

            foreach ($jadwalKemarin as $jk) {
                $absenKemarin = Presensi::where('user_id', $jk->user_id)->where('tanggal', $kemarin)->exists();
                $izinKemarin = Pengajuan::where('user_id', $jk->user_id)->where('status_approval', 'disetujui')
                    ->whereDate('tanggal_mulai', '<=', $kemarin)->whereDate('tanggal_selesai', '>=', $kemarin)->exists();

                if (!$absenKemarin && !$izinKemarin) {
                    $sudahDihukum = PointLedger::where('user_id', $jk->user_id)
                        ->where('description', 'like', '%Pinalti Alpa tanggal ' . $kemarin . '%')
                        ->exists();

                    if (!$sudahDihukum) {
                        $userTarget = User::find($jk->user_id);
                        if ($userTarget) {
                            $lastBalance = $userTarget->currentPoints();
                            PointLedger::create([
                                'user_id' => $userTarget->id,
                                'transaction_type' => 'PENALTY',
                                'amount' => -20,
                                'current_balance' => $lastBalance - 20,
                                'description' => 'Pinalti Alpa tanggal ' . $kemarin
                            ]);
                        }
                    }
                }
            }
        }

        // 2. STATISTIK UTAMA
        $totalKaryawan = Karyawan::count();

        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir')
            ->count();

        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->count();

        // Mengambil jumlah Alpha hari ini (yang ditembak otomatis oleh sistem karyawan)
        $tidakHadir = Presensi::where('tanggal', $hariIni)
            ->where('status', 'alpha')
            ->count();

        // 4. REKAPITULASI BULANAN (KODE INI TETAP ADA)
        $rekapBulanan = [
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'hadir')->count(),
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'telat')->count(),
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)->whereYear('tanggal_mulai', $tahunIni)->where('status_approval', 'disetujui')->count(),
        ];

        // 5. LEADERBOARD POIN (KODE INI TETAP ADA)
        $topUsers = User::where('role', 'karyawan')->get()->sortByDesc(function ($u) {
            return (int)$u->currentPoints();
        })->take(5);

        $bottomUsers = User::where('role', 'karyawan')->get()->sortBy(function ($u) {
            return (int)$u->currentPoints();
        })->take(5);

        // 6. TABEL AKTIVITAS TERBARU (DI SINI PERBAIKANNYA)
        // Tambahin filter whereDate hari ini biar riwayat data kemarin ILANG.
        $presensiTerbaru = Presensi::with(['user.karyawan.departemen', 'shift'])
            ->whereDate('tanggal', $hariIni)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'telatHariIni',
            'tidakHadir',
            'presensiTerbaru',
            'rekapBulanan',
            'topUsers',
            'bottomUsers'
        ));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('admin.profil', compact('user'));
    }
}
