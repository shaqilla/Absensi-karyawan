<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- INI KUNCINYA
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
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

        $totalKaryawan = Karyawan::count();

        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir')
            ->whereHas('user.karyawan')
            ->count();

        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

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

        $rekapBulanan = [
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'hadir')->count(),
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'telat')->count(),
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)->whereYear('tanggal_mulai', $tahunIni)->where('status_approval', 'disetujui')->count(),
        ];

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
            'rekapBulanan'
        ));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('admin.profil', compact('user'));
    }
}
