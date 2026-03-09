<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;    // WAJIB ADA
use App\Models\JadwalKerja; // WAJIB ADA
use App\Models\Pengajuan;   // WAJIB ADA (Ini penyebab error tadi)
use App\Models\User;        // WAJIB ADA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    /**
     * TAMPILAN DASHBOARD UTAMA
     */
    public function index()
    {
        $userId = Auth::id();
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang = now();

        // 1. Mapping Hari
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
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        // 3. Ambil data absen hari ini
        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $hariIniTanggal)
            ->first();

        // 4. Logika Alpha / Menunggu
        $isAlpha = false;
        $isWaiting = false;

        if (!$presensiHariIni && $jadwalHariIni) {
            $jamMasukShift = Carbon::parse($hariIniTanggal . ' ' . $jadwalHariIni->shift->jam_masuk);
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);
            $mulaiBolehScan = $jamMasukShift->copy()->subMinutes(15);

            if ($waktuSekarang->lt($mulaiBolehScan)) {
                $isWaiting = true;
            } elseif ($waktuSekarang->gt($batasMasuk)) {
                $isAlpha = true;
            }
        }

        $riwayat = Presensi::where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->take(7)
            ->get();

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni', 'isAlpha', 'isWaiting'));
    }

    /**
     * TAMPILAN JADWAL KERJA
     */
    public function jadwal()
    {
        $jadwals = JadwalKerja::with('shift')
            ->where('user_id', Auth::id())
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->get();

        return view('karyawan.jadwal', compact('jadwals'));
    }

    /**
     * TAMPILAN PROFIL
     */
    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('karyawan.profil', compact('user'));
    }

    /**
     * TAMPILAN LAPORAN SAYA (Fitur Baru)
     */
    public function laporan(Request $request)
    {
        $userId = Auth::id();
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // Ambil data presensi
        $laporans = Presensi::with('shift')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung Statistik
        $stats = [
            'hadir' => $laporans->where('status', 'hadir')->count(),
            'telat' => $laporans->where('status', 'telat')->count(),
            'izin'  => Pengajuan::where('user_id', $userId)->where('status_approval', 'disetujui')
                ->where('jenis_pengajuan', 'izin')->whereMonth('tanggal_mulai', $bulan)->count(),
            'sakit' => Pengajuan::where('user_id', $userId)->where('status_approval', 'disetujui')
                ->where('jenis_pengajuan', 'sakit')->whereMonth('tanggal_mulai', $bulan)->count(),
        ];

        return view('karyawan.laporan', compact('laporans', 'stats', 'bulan', 'tahun'));
    }
}
