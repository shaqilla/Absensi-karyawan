<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Set timezone ke Jakarta agar sinkron dengan jam laptop/HP
        date_default_timezone_set('Asia/Jakarta');

        $hariIni = now()->toDateString();
        $waktuSekarang = now(); // Objek Carbon waktu saat ini

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

        // 1. Total Karyawan Aktif
        $totalKaryawan = \App\Models\Karyawan::count();

        // 2. Hitung yang sudah hadir (Status 'hadir' atau 'telat')
        $hadirHariIni = \App\Models\Presensi::where('tanggal', $hariIni)
            ->whereHas('user.karyawan')
            ->count();

        // 3. Hitung yang Telat
        $telatHariIni = \App\Models\Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat')
            ->whereHas('user.karyawan')
            ->count();

        // 4. LOGIKA FINAL "TIDAK HADIR" (ALPHA) - SUPER KETAT
        // Ambil semua jadwal yang harusnya masuk hari ini
        $semuaJadwal = \App\Models\JadwalKerja::with('shift')
            ->where('hari', $hariNama)
            ->where('status', 'aktif')
            ->get();

        $tidakHadir = 0;

        foreach ($semuaJadwal as $j) {
            // Cek apakah orang ini sudah absen (masuk ke tabel presensi)
            $absenExist = \App\Models\Presensi::where('user_id', $j->user_id)
                ->where('tanggal', $hariIni)
                ->exists();

            // JIKA BELUM ABSEN
            if (!$absenExist) {
                // Gabungkan Tanggal + Jam Masuk Shift + Toleransi
                // Contoh: 2024-05-20 + 08:00:00 + 15 menit = 2024-05-20 08:15:00
                $batasMasuk = \Carbon\Carbon::parse($hariIni . ' ' . $j->shift->jam_masuk)
                    ->addMinutes($j->shift->toleransi_telat);

                // LOGIKA: Dia hanya dihitung TIDAK HADIR jika jam sekarang SUDAH MELEWATI batas masuknya.
                // Jika sekarang jam 05:00 dan jadwal dia jam 06:00, maka dia TIDAK dihitung bolos.
                if ($waktuSekarang->greaterThan($batasMasuk)) {
                    $tidakHadir++;
                }
            }
        }

        // 5. Tabel Aktivitas Terbaru
        $presensiTerbaru = \App\Models\Presensi::whereHas('user.karyawan')
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
            'presensiTerbaru'
        ));
    }

    public function profil()
    {
        // Admin biasanya tetap ada di tabel User & Karyawan
        $user = \App\Models\User::with(['karyawan.departemen'])->findOrFail(auth()->id());
        return view('admin.profil', compact('user'));
    }
}
