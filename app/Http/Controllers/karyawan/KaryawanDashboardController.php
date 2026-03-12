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
        date_default_timezone_set('Asia/Jakarta');
        $userId = Auth::id();
        $today = Carbon::today();

        // Array adalah struktur data yang menyimpan banyak nilai dalam satu variabel dengan urutan tertentu
        // 1. Mapping Hari Indonesia (Harus Huruf Kecil sesuai Database)
        $mappingHari = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];

        // 2. Ambil Jadwal & Absen Hari Ini
        $hariIniNama = $mappingHari[$today->format('l')];
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->where('hari', $hariIniNama)
            ->where('status', 'aktif')
            ->first();

        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $today->toDateString())
            ->first();

        // 3. Logika Alpha/Waiting untuk Tombol di Dashboard
        $isAlpha = false;
        $isWaiting = false;
        if (!$presensiHariIni && $jadwalHariIni) {
            $jamMasukShift = Carbon::parse($today->toDateString() . ' ' . $jadwalHariIni->shift->jam_masuk);
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);

            if (now()->lt($jamMasukShift)) {
                $isWaiting = true;
            } elseif (now()->gt($batasMasuk)) {
                $isAlpha = true;
            }
        }

        // 4. LOGIKA RIWAYAT 7 HARI (MENGGABUNGKAN DATA ASLI + ALPHA)
        $riwayatData = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->toDateString();
            $dayName = $mappingHari[$date->format('l')];

            // Cari di tabel presensi
            $p = Presensi::where('user_id', $userId)->where('tanggal', $dateString)->first();

            // Cari di tabel jadwal
            $j = JadwalKerja::with('shift')->where('user_id', $userId)->where('hari', $dayName)->first();

            if ($p) {
                // JIKA ADA DATA ABSEN
                $riwayatData[] = (object)[
                    'tanggal' => $dateString,
                    'jam_masuk' => date('H:i', strtotime($p->jam_masuk)),
                    'jam_keluar' => $p->jam_keluar ? date('H:i', strtotime($p->jam_keluar)) : '--:--',
                    'status' => $p->status
                ];
            } elseif ($j && $j->status == 'aktif') {
                // JIKA GAK ADA ABSEN TAPI HARUSNYA KERJA (ALPHA)
                $batas = Carbon::parse($dateString . ' ' . $j->shift->jam_masuk)->addMinutes($j->shift->toleransi_telat);

                // Hanya tampilkan jika hari sudah lewat, atau hari ini sudah lewat batas masuk
                if ($date->lt($today) || ($date->equalTo($today) && now()->gt($batas))) {
                    $riwayatData[] = (object)[
                        'tanggal' => $dateString,
                        'jam_masuk' => '--:--',
                        'jam_keluar' => '--:--',
                        'status' => 'alpha'
                    ];
                }
            }
        }

        // Kita beri nama variabel 'riwayat' agar cocok dengan Blade
        $riwayat = $riwayatData;

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni', 'isAlpha', 'isWaiting'));
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
