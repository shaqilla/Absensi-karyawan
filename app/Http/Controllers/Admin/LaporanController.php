<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil range tanggal dari filter (Default hari ini)
        $start_date = $request->start_date ?? now()->toDateString();
        $end_date = $request->end_date ?? now()->toDateString();

        // 2. Ambil semua karyawan aktif (Tanpa Admin jika ingin laporan staf saja)
        $karyawans = Karyawan::with(['user', 'departemen'])->get();

        // 3. Ambil data absen dan pengajuan (izin/sakit/cuti) yang di-acc dalam range tsb
        $allPresensi = Presensi::whereBetween('tanggal', [$start_date, $end_date])->get();
        $allPengajuan = Pengajuan::where('status_approval', 'disetujui')
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('tanggal_mulai', [$start_date, $end_date])
                    ->orWhereBetween('tanggal_selesai', [$start_date, $end_date])
                    // Antisipasi izin jangka panjang yang melewati range filter
                    ->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->where('tanggal_mulai', '<=', $start_date)
                            ->where('tanggal_selesai', '>=', $end_date);
                    });
            })->get();

        $laporanData = [];
        // Inisialisasi variabel untuk Chart
        $totalHadir = 0;
        $totalTelat = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalCuti = 0;
        $totalAlpha = 0;

        // 4. Generate Data Laporan per Hari per Karyawan
        $begin = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval, $end);

        foreach ($daterange as $date) {
            $tgl = $date->format("Y-m-d");

            foreach ($karyawans as $k) {
                // Cari data presensi karyawan k di tanggal tgl
                $presensi = $allPresensi->where('tanggal', $tgl)->where('user_id', $k->user_id)->first();

                // Cari data pengajuan di tanggal tgl
                $pengajuan = $allPengajuan->where('user_id', $k->user_id)
                    ->filter(function ($item) use ($tgl) {
                        return $tgl >= $item->tanggal_mulai && $tgl <= $item->tanggal_selesai;
                    })->first();

                // Penentuan Status dan Perhitungan Statistik
                if ($presensi) {
                    $status = $presensi->status; // 'hadir' atau 'telat'
                    $jam_masuk = $presensi->jam_masuk ? date('H:i', strtotime($presensi->jam_masuk)) : '--:--';
                    $jam_keluar = $presensi->jam_keluar ? date('H:i', strtotime($presensi->jam_keluar)) : '--:--';
                    $keterangan = $presensi->keterangan ?? 'Hadir Kerja';

                    if ($status == 'hadir') $totalHadir++;
                    else $totalTelat++;
                } elseif ($pengajuan) {
                    $status = $pengajuan->jenis_pengajuan; // 'sakit', 'izin', 'cuti'
                    $jam_masuk = '--:--';
                    $jam_keluar = '--:--';
                    $keterangan = $pengajuan->alasan;

                    if ($status == 'izin') $totalIzin++;
                    elseif ($status == 'sakit') $totalSakit++;
                    else $totalCuti++;
                } else {
                    $status = 'alpha';
                    $jam_masuk = '--:--';
                    $jam_keluar = '--:--';
                    $keterangan = 'Tanpa Keterangan';
                    $totalAlpha++;
                }

                $laporanData[] = (object)[
                    'nama' => $k->user->nama,
                    'departemen' => $k->departemen->nama_departemen ?? 'General',
                    'tanggal' => $tgl,
                    'jam_masuk' => $jam_masuk,
                    'jam_keluar' => $jam_keluar,
                    'status' => $status,
                    'keterangan' => $keterangan
                ];
            }
        }

        // Susun data final untuk dikirim ke view
        $chartData = [
            'labels' => ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'],
            'datasets' => [$totalHadir, $totalTelat, $totalIzin, $totalSakit, $totalCuti, $totalAlpha]
        ];

        return view('admin.laporan.index', [
            'laporans' => $laporanData,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'chartData' => $chartData
        ]);
    }
}
