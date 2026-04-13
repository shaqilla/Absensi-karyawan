<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        // Set timezone secara eksplisit agar konsisten
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = Carbon::today($timezone);
        $userId = Auth::id();

        $mappingHari = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu'
        ];

        $hariIniNama = $mappingHari[$now->format('l')];

        // Ambil Jadwal Kerja
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->where('hari', $hariIniNama)
            ->where('status', 'aktif')
            ->first();

        // Ambil Data Presensi Hari Ini
        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $today->toDateString())
            ->first();

        $isAlpha = false;
        $isWaiting = false;
        $canScan = false;

        if ($jadwalHariIni && !$presensiHariIni) {
            // Parsing jam masuk dari shift
            $jamMasukShift = Carbon::createFromFormat('Y-m-d H:i:s', $today->toDateString() . ' ' . $jadwalHariIni->shift->jam_masuk, $timezone);

            // Batas awal boleh scan (misal: 60 menit sebelum jam masuk)
            $awalScan = $jamMasukShift->copy()->subMinutes(60);

            // Batas akhir (jam masuk + toleransi)
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);

            if ($now->lt($awalScan)) {
                // Belum waktunya scan (terlalu pagi)
                $isWaiting = true;
            } elseif ($now->between($awalScan, $batasMasuk)) {
                // Waktu yang tepat untuk scan
                $canScan = true;
            } elseif ($now->gt($batasMasuk)) {
                // Sudah lewat batas toleransi
                $isAlpha = true;
            }
        }

        // LOGIKA RIWAYAT 7 HARI
        $riwayatData = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today($timezone)->subDays($i);
            $dateString = $date->toDateString();
            $dayName = $mappingHari[$date->format('l')];

            $p = Presensi::where('user_id', $userId)->where('tanggal', $dateString)->first();
            $j = JadwalKerja::with('shift')->where('user_id', $userId)->where('hari', $dayName)->first();

            if ($p) {
                $riwayatData[] = (object)[
                    'tanggal' => $dateString,
                    'jam_masuk' => $p->jam_masuk ? Carbon::parse($p->jam_masuk)->format('H:i') : '--:--',
                    'jam_keluar' => $p->jam_keluar ? Carbon::parse($p->jam_keluar)->format('H:i') : '--:--',
                    'status' => $p->status
                ];
            } elseif ($j && $j->status == 'aktif') {
                $batas = Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $j->shift->jam_masuk, $timezone)
                               ->addMinutes($j->shift->toleransi_telat);

                // Jika hari sudah lewat ATAU hari ini tapi sudah lewat batas toleransi
                if ($date->lt($today) || ($date->equalTo($today) && $now->gt($batas))) {
                    $riwayatData[] = (object)[
                        'tanggal' => $dateString,
                        'jam_masuk' => '--:--',
                        'jam_keluar' => '--:--',
                        'status' => 'alpha'
                    ];
                }
            }
        }

        return view('karyawan.dashboard', [
            'presensiHariIni' => $presensiHariIni,
            'riwayat'         => $riwayatData,
            'jadwalHariIni'   => $jadwalHariIni,
            'isAlpha'         => $isAlpha,
            'isWaiting'       => $isWaiting,
            'canScan'         => $canScan, // Variabel baru untuk view
        ]);
    }

    public function jadwal()
    {
        $jadwals = JadwalKerja::with('shift')->where('user_id', Auth::id())->get();
        return view('karyawan.jadwal', compact('jadwals'));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('karyawan.profil', compact('user'));
    }
}
