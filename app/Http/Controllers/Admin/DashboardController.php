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
        $totalKaryawan = Karyawan::count();

        // 2. HITUNG HADIR (Hanya yang TEPAT WAKTU)
        $hadirHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'hadir') // Filter status hadir saja
            ->whereHas('user.karyawan')
            ->count();

        // 3. HITUNG TERLAMBAT (Hanya yang TELAT)
        $telatHariIni = Presensi::where('tanggal', $hariIni)
            ->where('status', 'telat') // Filter status telat saja
            ->whereHas('user.karyawan')
            ->count();

        // 4. LOGIKA FINAL "TIDAK HADIR" (ALPHA) - DINAMIS & KETAT
        $semuaJadwal = JadwalKerja::with('shift')
            ->where('hari', $hariNama)
            ->where('status', 'aktif')
            ->get();

        $tidakHadir = 0;

        foreach ($semuaJadwal as $j) {
            // Cek apakah karyawan ini sudah ada data absennya (baik hadir maupun telat)
            $absenExist = Presensi::where('user_id', $j->user_id)
                ->where('tanggal', $hariIni)
                ->exists();

            // Cek apakah dia punya izin/sakit yang sudah disetujui (biar tidak dihitung alpha)
            $isIzin = Pengajuan::where('user_id', $j->user_id)
                ->where('status_approval', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $hariIni)
                ->whereDate('tanggal_selesai', '>=', $hariIni)
                ->exists();

            // JIKA BELUM ABSEN & TIDAK IZIN
            if (!$absenExist && !$isIzin) {
                // Batas Masuk = Jam Masuk Shift + Toleransi
                $batasMasuk = Carbon::parse($hariIni . ' ' . $j->shift->jam_masuk)
                    ->addMinutes($j->shift->toleransi_telat);

                // Baru dihitung TIDAK HADIR jika jam sekarang sudah MELEWATI batas masuknya
                if ($waktuSekarang->greaterThan($batasMasuk)) {
                    $tidakHadir++;
                }
            }
        }

        // 5. Tabel Aktivitas Terbaru
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
            'presensiTerbaru'
        ));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(auth()->id());
        return view('admin.profil', compact('user'));
    }
}
