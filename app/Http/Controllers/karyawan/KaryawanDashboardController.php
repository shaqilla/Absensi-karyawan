<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Mapping Hari ke HURUF KECIL (agar cocok dengan database)
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

        // 2. Ambil Jadwal Kerja hari ini
        // Kita gunakan whereRaw agar pencarian hari tidak sensitif huruf besar/kecil
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        // 3. Ambil data absen hari ini
        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();

        // 4. LOGIKA OTOMATIS ALPHA
        $isAlpha = false;
        if (!$presensiHariIni && $jadwalHariIni) {
            // Cek Batas Masuk: Jam Masuk + Toleransi
            $jamMasukShift = Carbon::parse($jadwalHariIni->shift->jam_masuk);
            $batasMasuk = $jamMasukShift->addMinutes($jadwalHariIni->shift->toleransi_telat);

            if ($waktuSekarang->greaterThan($batasMasuk)) {
                $isAlpha = true;
            }
        }

        // 5. Riwayat 7 hari terakhir
        $riwayat = Presensi::where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->take(7)
            ->get();

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni', 'isAlpha'));
    }

    public function jadwal() 
    {
        // Ambil semua jadwal milik karyawan ini
        $jadwals = JadwalKerja::with('shift')
            ->where('user_id', Auth::id())
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            .get();

        return view('karyawan.jadwal', compact('jadwals'));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('karyawan.profil', compact('user'));
    }
}