<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, JadwalKerja, User, PointRule, PointLedger, Pengajuan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log};
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
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];

        // Nyari aturan Alpha buat ngambil nilai minusnya
        $ruleAlpha = PointRule::where('rule_name', 'LIKE', '%alpha%')->first();

        // 1. LOGIKA AUTO-ALPHA (CEK HARI INI & 2 HARI KEBELAKANG)
        for ($i = 0; $i <= 2; $i++) {
            $checkDate = Carbon::today($timezone)->subDays($i);
            $checkDateStr = $checkDate->toDateString();
            $dayName = $mappingHari[$checkDate->format('l')];

            $jadwal = JadwalKerja::with('shift')->where('user_id', $userId)
                ->where('hari', $dayName)->where('status', 'aktif')->first();

            if ($jadwal) {
                $jamPulangShift = Carbon::parse($checkDateStr . ' ' . $jadwal->shift->jam_keluar, $timezone);

                // TRIGGER: Kalau sudah lewat jam pulang tapi belum ada data
                if ($now->gt($jamPulangShift)) {
                    $exists = Presensi::where('user_id', $userId)->where('tanggal', $checkDateStr)->exists();

                    if (!$exists) {
                        try {
                            DB::beginTransaction();
                            // INSERT ALPHA KE TABEL PRESENSI
                            DB::table('presensis')->insert([
                                'user_id'       => $userId,
                                'shift_id'      => $jadwal->shift_id,
                                'tanggal'       => $checkDateStr,
                                'status'        => 'alpha',
                                'jam_masuk'     => null, // PAKSA NULL BIAR GAK MUNCUL JAM 07:00
                                'jam_keluar'    => null, // PAKSA NULL
                                'keterangan'    => 'Sistem: Alpha (Otomatis Lewat Jam Pulang)',
                                'kategori_id'   => 1,
                                'latitude'      => '0',
                                'longitude'     => '0',
                                'created_at'    => now(),
                                'updated_at'    => now()
                            ]);

                            // INSERT POINT PENALTY
                            if ($ruleAlpha) {
                                $lastL = PointLedger::where('user_id', $userId)->orderBy('id', 'desc')->first();
                                $currentB = $lastL ? (int)$lastL->current_balance : 0;

                                DB::table('point_ledgers')->insert([
                                    'user_id'          => $userId,
                                    'transaction_type' => 'PENALTY',
                                    'amount'           => (int)$ruleAlpha->point_modifier,
                                    'current_balance'  => $currentB + (int)$ruleAlpha->point_modifier,
                                    'description'      => 'Denda Alpha: ' . $checkDateStr,
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ]);
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                            Log::error("Alpha Error: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        $ticketsOpen = \App\Models\Ticket::where('status', 'open')->count();
        $ticketsInProgress = \App\Models\Ticket::where('status', 'in-progress')->count();
        $totalTickets = \App\Models\Ticket::count();

        // 2. DATA UNTUK BLADE (LOGIKA TAMPILAN)
        $jadwalHariIni = JadwalKerja::with('shift')->where('user_id', $userId)
            ->where('hari', $mappingHari[$now->format('l')])->where('status', 'aktif')->first();

        $presensiHariIni = Presensi::where('user_id', $userId)->where('tanggal', $todayStr)->first();

        $canScan = false;
        $isWaiting = false;
        $isAlpha = false;

        // Cek status Alpha dari database dulu buat matiin tombol scan
        if ($presensiHariIni && $presensiHariIni->status == 'alpha') {
            $isAlpha = true;
        } elseif ($jadwalHariIni && !$presensiHariIni) {
            $jamMasuk = Carbon::parse($todayStr . ' ' . $jadwalHariIni->shift->jam_masuk, $timezone);
            $jamPulang = Carbon::parse($todayStr . ' ' . $jadwalHariIni->shift->jam_keluar, $timezone);
            $awalScan = $jamMasuk->copy()->subMinutes(60);

            if ($now->lt($awalScan)) {
                $isWaiting = true;
            } elseif ($now->lte($jamPulang)) {
                $canScan = true;
            } else {
                $isAlpha = true;
            }
        }

        $riwayat = Presensi::where('user_id', $userId)->orderBy('tanggal', 'desc')->take(7)->get();

        return view('karyawan.dashboard', compact('presensiHariIni', 'riwayat', 'jadwalHariIni', 'isAlpha', 'isWaiting', 'canScan', 'ticketsOpen', 'ticketsInProgress', 'totalTickets'));
    }

    public function jadwal()
    {
        $jadwals = JadwalKerja::with('shift')->where('user_id', Auth::id())->get();
        return view('karyawan.jadwal', compact('jadwals'));
    }

    // --- FUNGSI LAPORAN (UNTUK FIX ERROR METHOD & UNDEFINED VARIABLE) ---
    public function laporan(Request $request)
    {
        $userId = Auth::id();

        // 1. Ambil filter bulan dan tahun, default ke hari ini
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // 2. Ambil data presensi (absen) karyawan di bulan tersebut
        $laporans = Presensi::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        // 3. HITUNG STATISTIK (Ini yang bikin error tadi)
        // Kita hitung dari data yang sudah ditarik di atas
        $stats = [
            'hadir' => $laporans->where('status', 'hadir')->count(),
            'telat' => $laporans->where('status', 'telat')->count(),
            'alpha' => $laporans->where('status', 'alpha')->count(),
            // Untuk izin & sakit, kita ambil dari tabel Pengajuan yang sudah disetujui
            'izin'  => Pengajuan::where('user_id', $userId)
                ->where('jenis_pengajuan', 'izin')
                ->where('status', 'disetujui')
                ->whereMonth('tanggal_mulai', $bulan)
                ->count(),
            'sakit' => Pengajuan::where('user_id', $userId)
                ->where('jenis_pengajuan', 'sakit')
                ->where('status', 'disetujui')
                ->whereMonth('tanggal_mulai', $bulan)
                ->count(),
        ];

        // 4. Kirim variabel yang dibutuhkan Blade (laporans, stats, bulan, tahun)
        return view('karyawan.laporan', compact('laporans', 'stats', 'bulan', 'tahun'));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('karyawan.profil', compact('user'));
    }
}
