<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use App\Models\User;
use App\Models\PointRule;
use App\Models\PointLedger;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        // 1. Inisialisasi Waktu & User
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = Carbon::today($timezone);
        $userId = Auth::id();
        $user = Auth::user();

        $mappingHari = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu'
        ];

        // 2. LOGIKA AUTO-SYNC ALPHA (Mencatat Alpha ke Database Otomatis)
        // Kita cek 3 hari ke belakang untuk melihat apakah ada absen yang bolong
        for ($i = 1; $i <= 3; $i++) {
            $checkDate = $today->copy()->subDays($i);
            $checkDateString = $checkDate->toDateString();
            $dayName = $mappingHari[$checkDate->format('l')];

            // Cek apakah ada jadwal aktif di hari tersebut
            $jadwal = JadwalKerja::where('user_id', $userId)
                ->where('hari', $dayName)
                ->where('status', 'aktif')
                ->first();

            if ($jadwal) {
                // Cek apakah sudah ada data presensi atau pengajuan izin
                $sudahAbsen = Presensi::where('user_id', $userId)->where('tanggal', $checkDateString)->exists();
                $sudahIzin = Pengajuan::where('user_id', $userId)
                    ->where('status', 'disetujui')
                    ->where('tanggal_mulai', '<=', $checkDateString)
                    ->where('tanggal_selesai', '>=', $checkDateString)
                    ->exists();

                // Jika HARUSNYA KERJA tapi TIDAK ABSEN & TIDAK IZIN
                if (!$sudahAbsen && !$sudahIzin) {
                    DB::beginTransaction();
                    try {
                        // Buat Record Alpha di Tabel Presensi
                        Presensi::create([
                            'user_id'     => $userId,
                            'shift_id'    => $jadwal->shift_id,
                            'tanggal'     => $checkDateString,
                            'status'      => 'alpha',
                            'keterangan'  => 'Sistem: Alpha Otomatis (Tidak ada keterangan)',
                            'kategori_id' => 1
                        ]);

                        // Potong Poin di Dompet Integritas
                        $ruleAlpha = PointRule::where('rule_name', 'ALPHA')->first();
                        if ($ruleAlpha) {
                            $lastLedger = PointLedger::where('user_id', $userId)->latest()->first();
                            $currentBalance = $lastLedger ? $lastLedger->current_balance : 0;

                            PointLedger::create([
                                'user_id'          => $userId,
                                'transaction_type' => 'PENALTY',
                                'amount'           => $ruleAlpha->point_modifier,
                                'current_balance'  => $currentBalance + $ruleAlpha->point_modifier,
                                'description'      => 'Potongan Poin: Alpha ' . $checkDateString
                            ]);
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                    }
                }
            }
        }

        // 3. LOGIKA TOMBOL ABSEN HARI INI
        $hariIniNama = $mappingHari[$now->format('l')];
        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->where('hari', $hariIniNama)
            ->where('status', 'aktif')
            ->first();

        $presensiHariIni = Presensi::where('user_id', $userId)
            ->where('tanggal', $today->toDateString())
            ->first();

        $isAlpha = false;
        $isWaiting = false;
        $canScan = false;

        if ($jadwalHariIni && !$presensiHariIni) {
            $jamMasukShift = Carbon::createFromFormat('Y-m-d H:i:s', $today->toDateString() . ' ' . $jadwalHariIni->shift->jam_masuk, $timezone);
            $awalScan = $jamMasukShift->copy()->subMinutes(60);
            $batasMasuk = $jamMasukShift->copy()->addMinutes($jadwalHariIni->shift->toleransi_telat);

            if ($now->lt($awalScan)) {
                $isWaiting = true;
            } elseif ($now->between($awalScan, $batasMasuk)) {
                $canScan = true;
            } elseif ($now->gt($batasMasuk)) {
                $isAlpha = true;
            }
        }

        // 4. AMBIL DATA RIWAYAT (Sudah termasuk Alpha asli dari database)
        $riwayat = Presensi::where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->take(7)
            ->get()
            ->map(function($p) {
                return (object)[
                    'tanggal'    => $p->tanggal,
                    'jam_masuk'  => $p->jam_masuk ? Carbon::parse($p->jam_masuk)->format('H:i') : '--:--',
                    'jam_keluar' => $p->jam_keluar ? Carbon::parse($p->jam_keluar)->format('H:i') : '--:--',
                    'status'     => $p->status
                ];
            });

        return view('karyawan.dashboard', [
            'presensiHariIni' => $presensiHariIni,
            'riwayat'         => $riwayat,
            'jadwalHariIni'   => $jadwalHariIni,
            'isAlpha'         => $isAlpha,
            'isWaiting'       => $isWaiting,
            'canScan'         => $canScan,
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
