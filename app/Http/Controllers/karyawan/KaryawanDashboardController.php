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
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Mapping Hari
        $hariInggris = now()->format('l');
        $daftarHari = ['Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'];
        $namaHariIni = $daftarHari[$hariInggris];

        // 2. Ambil Jadwal Kerja Spesifik Karyawan ini
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();

        // 3. LOGIKA PENGUNCIAN SHIFT (Sesuai Request Anda)
        $isAlpha = false;
        $isWaiting = false;
        $isWrongShift = false; // Status jika mencoba absen di jam shift lain

        if (!$presensiHariIni && $jadwalHariIni) {
            $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwalHariIni->shift->jam_masuk);
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);
            $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwalHariIni->shift->jam_keluar);

            // Aturan: Tombol HANYA muncul pas jam masuk (Tidak boleh curi start / salah shift)
            if ($waktuSekarang->lt($jamMasukShift)) {
                $isWaiting = true; // Jam kerja belum mulai
            } elseif ($waktuSekarang->gt($batasMasuk)) {
                $isAlpha = true; // telat parah (Alpha)
            }
        }

        $riwayat = Presensi::where('user_id', $userId)->orderBy('tanggal', 'desc')->take(7)->get();

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
