<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\Pengajuan;
use App\Models\PointLedger; // Tambahkan ini
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
        if (!now()->subDay()->isSunday()) { // Jangan potong poin kalau kemarin hari Minggu
            $hariNamaKemarin = $mappingHari[now()->subDay()->format('l')];

            // Cari orang yang harusnya kerja kemarin
            $jadwalKemarin = JadwalKerja::where('hari', $hariNamaKemarin)->where('status', 'aktif')->get();

            foreach ($jadwalKemarin as $jk) {
                // Cek apakah dia absen kemarin?
                $absenKemarin = Presensi::where('user_id', $jk->user_id)->where('tanggal', $kemarin)->exists();
                // Cek apakah dia izin kemarin?
                $izinKemarin = Pengajuan::where('user_id', $jk->user_id)->where('status_approval', 'disetujui')
                                ->whereDate('tanggal_mulai', '<=', $kemarin)->whereDate('tanggal_selesai', '>=', $kemarin)->exists();

                if (!$absenKemarin && !$izinKemarin) {
                    // Cek apakah sudah dikasih denda buat tanggal kemarin? (Biar gak dobel)
                    $sudahDihukum = PointLedger::where('user_id', $jk->user_id)
                                    ->where('description', 'like', '%Pinalti Alpa tanggal '.$kemarin.'%')
                                    ->exists();

                    if (!$sudahDihukum) {
                        $userTarget = User::find($jk->user_id);
                        $lastBalance = $userTarget->currentPoints();
                        PointLedger::create([
                            'user_id' => $userTarget->id,
                            'transaction_type' => 'PENALTY',
                            'amount' => -20, // Potong 20 poin
                            'current_balance' => $lastBalance - 20,
                            'description' => 'Pinalti Alpa tanggal ' . $kemarin
                        ]);
                    }
                }
            }
        }


        // 2. STATISTIK UTAMA (HARI INI)
        $totalKaryawan = Karyawan::count();

        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir')
            ->whereHas('user.karyawan')
            ->count();

        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

        // 3. HITUNG TIDAK HADIR (ALPHA) SECARA DINAMIS
        $semuaJadwal = JadwalKerja::with('shift')
            ->where('hari', $hariNama)
            ->where('status', 'aktif')
            ->get();

        $tidakHadir = 0;
        foreach ($semuaJadwal as $j) {
            $absenExist = Presensi::where('user_id', $j->user_id)->where('tanggal', $hariIni)->exists();
            $isIzin = Pengajuan::where('user_id', $j->user_id)->where('status_approval', 'disetujui')
                        ->whereDate('tanggal_mulai', '<=', $hariIni)->whereDate('tanggal_selesai', '>=', $hariIni)->exists();

            if (!$absenExist && !$isIzin) {
                $batasMasuk = Carbon::parse($hariIni . ' ' . $j->shift->jam_masuk)->addMinutes($j->shift->toleransi_telat);
                if ($waktuSekarang->greaterThan($batasMasuk)) {
                    $tidakHadir++;
                }
            }
        }

        // 4. REKAPITULASI BULANAN
        $rekapBulanan = [
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'hadir')->count(),
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'telat')->count(),
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)->whereYear('tanggal_mulai', $tahunIni)->where('status_approval', 'disetujui')->count(),
        ];

        // 5. LEADERBOARD POIN (UNTUK REVISI TO)
        // Ambil 5 besar poin tertinggi
        $topUsers = User::where('role', 'karyawan')->get()->sortByDesc(function($u) {
            return $u->currentPoints();
        })->take(5);

        // Ambil 5 besar poin terendah (Daftar Perhatian)
        $bottomUsers = User::where('role', 'karyawan')->get()->sortBy(function($u) {
            return $u->currentPoints();
        })->take(5);

        // 6. TABEL AKTIVITAS TERBARU
        $presensiTerbaru = Presensi::with(['user.karyawan.departemen', 'shift'])
            ->whereHas('user.karyawan')
            ->orderBy('created_at', 'desc')
            ->take(5)
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
