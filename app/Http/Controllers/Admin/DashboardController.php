<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, User, Karyawan, JadwalKerja, Pengajuan, PointLedger, Ticket, SatisfactionRating}; // SatisfactionRating Ditambahin
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. PENGATURAN WAKTU & TIMEZONE
        date_default_timezone_set('Asia/Jakarta');
        $hariIni = now()->toDateString();
        $kemarin = now()->subDay()->toDateString();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $mappingHari =[
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu'
        ];
        $hariNama = $mappingHari[now()->format('l')];

        // --- LOGIKA PENALTI ALPA OTOMATIS ---
        if (!now()->subDay()->isSunday()) {
            $hariNamaKemarin = $mappingHari[now()->subDay()->format('l')];
            $jadwalKemarin = JadwalKerja::where('hari', $hariNamaKemarin)->where('status', 'aktif')->get();

            foreach ($jadwalKemarin as $jk) {
                $absenKemarin = Presensi::where('user_id', $jk->user_id)->where('tanggal', $kemarin)->exists();
                $izinKemarin = Pengajuan::where('user_id', $jk->user_id)->where('status', 'disetujui')
                    ->whereDate('tanggal_mulai', '<=', $kemarin)
                    ->whereDate('tanggal_selesai', '>=', $kemarin)->exists();

                if (!$absenKemarin && !$izinKemarin) {
                    $sudahDihukum = PointLedger::where('user_id', $jk->user_id)
                        ->where('description', 'like', '%Pinalti Alpa tanggal ' . $kemarin . '%')
                        ->exists();

                    if (!$sudahDihukum) {
                        $userTarget = User::find($jk->user_id);
                        if ($userTarget) {
                            $lastBalance = $userTarget->currentPoints();
                            PointLedger::create([
                                'user_id' => $userTarget->id,
                                'transaction_type' => 'PENALTY',
                                'amount' => -20,
                                'current_balance' => $lastBalance - 20,
                                'description' => 'Pinalti Alpa tanggal ' . $kemarin
                            ]);
                        }
                    }
                }
            }
        }

        // 2. STATISTIK UTAMA (ABSENSI)
        $totalKaryawan = Karyawan::count();
        $hadirHariIni = Presensi::where('tanggal', $hariIni)->where('status', 'hadir')->count();
        $telatHariIni = Presensi::where('tanggal', $hariIni)->where('status', 'telat')->count();
        $tidakHadir = Presensi::where('tanggal', $hariIni)->where('status', 'alpha')->count();

        // 3. REKAPITULASI BULANAN
        $rekapBulanan =[
            'hadir' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'hadir')->count(),
            'telat' => Presensi::whereMonth('tanggal', $bulanIni)->whereYear('tanggal', $tahunIni)->where('status', 'telat')->count(),
            'izin'  => Pengajuan::whereMonth('tanggal_mulai', $bulanIni)->whereYear('tanggal_mulai', $tahunIni)->where('status', 'disetujui')->count(),
        ];

        // 4. LEADERBOARD POIN
        $topUsers = User::where('role', 'karyawan')->get()->sortByDesc(function ($u) {
            return (int)$u->currentPoints();
        })->values()->take(5);

        $bottomUsers = User::where('role', 'karyawan')->get()->sortBy(function ($u) {
            return (int)$u->currentPoints();
        })->values()->take(5);

        // 5. TABEL AKTIVITAS TERBARU (HARI INI SAJA)
        $presensiTerbaru = Presensi::with(['user.karyawan.departemen', 'shift'])
            ->whereDate('tanggal', $hariIni)
            ->orderBy('created_at', 'desc')
            ->get();

        // 6. STATISTIK HELPDESK & RATING (UDAH GUA MASUKIN KE SINI)
        $ticketsOpen = Ticket::where('status', 'open')->count();
        $ticketsInProgress = Ticket::where('status', 'in-progress')->count();
        $totalTickets = Ticket::count();

        // Hitung Rata-rata Semua Kepuasan
        $avgRating = DB::table('satisfaction_ratings')->avg('score') ?? 0;

        // Hitung Performa Tiap Operator
        $operatorStats = User::whereIn('role', ['operator', 'admin'])
            ->get()
            ->map(function ($operator) {
                // Tiket yang udah diselesaikan operator ini
                $operator->tickets_count = Ticket::where('operator_id', $operator->id)
                    ->where('status', 'closed')
                    ->count();

                // Rata-rata rating yang didapet operator ini
                $operator->avg_rating = DB::table('satisfaction_ratings')
                    ->join('tickets', 'satisfaction_ratings.ticket_id', '=', 'tickets.id')
                    ->where('tickets.operator_id', $operator->id)
                    ->avg('satisfaction_ratings.score') ?? 0;

                // Rata-rata response time (SLA) dalam menit
                $avgResponse = Ticket::where('operator_id', $operator->id)
                    ->whereNotNull('first_response_at') 
                    ->get()
                    ->avg(function ($ticket) {
                        return $ticket->created_at->diffInMinutes($ticket->first_response_at);
                    });

                $operator->avg_response = $avgResponse ?? 0;

                return $operator;
            })
            ->filter(function ($operator) {
                // Jangan tampilin user yang belum pernah nanganin tiket
                return Ticket::where('operator_id', $operator->id)->count() > 0;
            })
            ->values();

        // RETURN VIEW HANYA BOLEH 1 KALI (DI PALING BAWAH SINI)
        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'telatHariIni',
            'tidakHadir',
            'ticketsOpen',
            'ticketsInProgress',
            'totalTickets',
            'presensiTerbaru',
            'rekapBulanan',
            'topUsers',
            'bottomUsers',
            'avgRating',        // <-- VARIABEL RATING DIKIRIM KE VIEW
            'operatorStats'     // <-- VARIABEL OPERATOR DIKIRIM KE VIEW
        ));
    }

    public function profil()
    {
        $user = User::with(['karyawan.departemen'])->findOrFail(Auth::id());
        return view('admin.profil', compact('user'));
    }
}