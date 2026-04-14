<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, JadwalKerja, User, PointRule, PointLedger, Pengajuan};
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $todayStr = $now->toDateString();
        $userId = Auth::id();

        $mappingHari = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'
        ];

        // 1. LOGIKA AUTO-ALPHA (CEK HARI INI SAMPAI 3 HARI KEBELAKANG)
        for ($i = 0; $i <= 3; $i++) {
            $checkDate = Carbon::today($timezone)->subDays($i);
            $checkDateStr = $checkDate->toDateString();
            $dayName = $mappingHari[$checkDate->format('l')];

            // Ambil jadwal kerja karyawan
            $jadwal = JadwalKerja::with('shift')->where('user_id', $userId)
                ->where('hari', $dayName)
                ->where('status', 'aktif')
                ->first();

            if ($jadwal) {
                $jamKeluarShift = Carbon::parse($checkDateStr . ' ' . $jadwal->shift->jam_keluar, $timezone);

                // JALANKAN ALPHA JIKA:
                // - Ini hari kemarin ($i > 0)
                // - ATAU Ini hari ini ($i == 0) tapi jam sekarang sudah LEWAT jam pulang shift
                if ($i > 0 || ($i == 0 && $now->gt($jamKeluarShift))) {

                    $exists = Presensi::where('user_id', $userId)->where('tanggal', $checkDateStr)->exists();
                    $izin = Pengajuan::where('user_id', $userId)->where('status', 'disetujui')
                            ->where('tanggal_mulai', '<=', $checkDateStr)
                            ->where('tanggal_selesai', '>=', $checkDateStr)
                            ->exists();

                    if (!$exists && !$izin) {
                        DB::beginTransaction();
                        try {
                            // Simpan ke tabel Presensi sebagai Alpha
                            Presensi::create([
                                'user_id' => $userId,
                                'shift_id' => $jadwal->shift_id,
                                'tanggal' => $checkDateStr,
                                'status' => 'alpha',
                                'keterangan' => 'Sistem: Alpha Otomatis (Tidak Masuk)',
                                'kategori_id' => 1
                            ]);

                            // AMBIL ATURAN POINT UNTUK ALPHA
                            $ruleA = PointRule::whereRaw('LOWER(rule_name) = ?', ['alpha'])->first();
                            if ($ruleA) {
                                $lastL = PointLedger::where('user_id', $userId)->latest()->first();
                                $currentB = $lastL ? $lastL->current_balance : 0;

                                // Catat di Buku Besar (Ledger) potong poin
                                PointLedger::create([
                                    'user_id' => $userId,
                                    'transaction_type' => 'PENALTY',
                                    'amount' => $ruleA->point_modifier, // Minus (misal -5)
                                    'current_balance' => $currentB + $ruleA->point_modifier,
                                    'description' => 'Penalti Alpha: ' . $checkDateStr
                                ]);
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                        }
                    }
                }
            }
        }

        // 2. LOGIKA AKSI DASHBOARD HARI INI (TAMPILAN TOMBOL)
        $jadwalHariIni = JadwalKerja::with('shift')->where('user_id', $userId)
            ->where('hari', $mappingHari[$now->format('l')])
            ->where('status', 'aktif')
            ->first();

        $presensiHariIni = Presensi::where('user_id', $userId)->where('tanggal', $todayStr)->first();

        $canScan = false; $isWaiting = false; $isAlpha = false;

        if ($jadwalHariIni && !$presensiHariIni) {
            $jamMasuk = Carbon::parse($todayStr . ' ' . $jadwalHariIni->shift->jam_masuk, $timezone);
            $jamKeluar = Carbon::parse($todayStr . ' ' . $jadwalHariIni->shift->jam_keluar, $timezone);
            $awalScan = $jamMasuk->copy()->subMinutes(60);

            if ($now->lt($awalScan)) {
                $isWaiting = true; // Belum waktunya
            } elseif ($now->between($awalScan, $jamKeluar)) {
                $canScan = true; // Bisa scan (termasuk telat)
            } else {
                $isAlpha = true; // Sudah lewat jam kerja
            }
        }

        // Ambil riwayat absen
        $riwayat = Presensi::where('user_id', $userId)->orderBy('tanggal', 'desc')->take(7)->get();

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni', 'isAlpha', 'isWaiting', 'canScan'));
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
