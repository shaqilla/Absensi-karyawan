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

        // 1. Ambil Nama Hari (sesuai DB)
        $hariInggris = now()->format('l');
        $daftarHari = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];
        $namaHariIni = $daftarHari[$hariInggris];

        // 2. Ambil Jadwal
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();

        // 3. LOGIKA KETAT: TOMBOL HANYA MUNCUL PAS JAMNYA
        $isAlpha = false;
        $isWaiting = false;

        if (!$presensiHariIni && $jadwalHariIni) {
            $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwalHariIni->shift->jam_masuk);
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);

            if ($waktuSekarang->lt($jamMasukShift)) {
                // BELUM JAMNYA: Tombol Gak Bakal Muncul
                $isWaiting = true;
            } elseif ($waktuSekarang->gt($batasMasuk)) {
                // LEWAT TOLERANSI: Tombol Ilang, Status Jadi Alpha
                $isAlpha = true;
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
