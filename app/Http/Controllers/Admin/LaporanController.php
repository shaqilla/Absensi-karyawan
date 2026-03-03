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
        // 1. Ambil range tanggal dari filter
        $start_date = $request->start_date ?? now()->toDateString();
        $end_date = $request->end_date ?? now()->toDateString();

        // 2. Ambil semua karyawan aktif
        $karyawans = \App\Models\Karyawan::with(['user', 'departemen'])->get();

        // 3. Ambil data absen dan pengajuan (izin/sakit/cuti) yang di-acc dalam range tsb
        $allPresensi = \App\Models\Presensi::whereBetween('tanggal', [$start_date, $end_date])->get();
        $allPengajuan = \App\Models\Pengajuan::where('status_approval', 'disetujui')
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('tanggal_mulai', [$start_date, $end_date])
                    ->orWhereBetween('tanggal_selesai', [$start_date, $end_date]);
            })->get();

        // 4. Generate Data Laporan per Hari per Karyawan
        $laporanData = [];
        $begin = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval, $end);

        foreach ($daterange as $date) {
            $tgl = $date->format("Y-m-d");

            foreach ($karyawans as $k) {
                // Cek apakah ada absen masuk
                $presensi = $allPresensi->where('tanggal', $tgl)->where('user_id', $k->user_id)->first();

                // Cek apakah ada izin/sakit/cuti di tanggal ini
                $pengajuan = $allPengajuan->where('user_id', $k->user_id)
                    ->filter(function ($item) use ($tgl) {
                        return $tgl >= $item->tanggal_mulai && $tgl <= $item->tanggal_selesai;
                    })->first();

                // Penentuan Status
                if ($presensi) {
                    $status = $presensi->status; // 'hadir' atau 'telat'
                    $jam_masuk = date('H:i', strtotime($presensi->jam_masuk));
                    $jam_keluar = $presensi->jam_keluar ? date('H:i', strtotime($presensi->jam_keluar)) : '--:--';
                    $keterangan = $presensi->keterangan ?? '-';
                } elseif ($pengajuan) {
                    $status = $pengajuan->jenis_pengajuan; // 'sakit', 'izin', 'cuti'
                    $jam_masuk = '--:--';
                    $jam_keluar = '--:--';
                    $keterangan = $pengajuan->alasan;
                } else {
                    $status = 'alpha';
                    $jam_masuk = '--:--';
                    $jam_keluar = '--:--';
                    $keterangan = 'Tanpa Keterangan';
                }

                $laporanData[] = (object)[
                    'nama' => $k->user->nama,
                    'departemen' => $k->departemen->nama_departemen ?? 'N/A',
                    'tanggal' => $tgl,
                    'jam_masuk' => $jam_masuk,
                    'jam_keluar' => $jam_keluar,
                    'status' => $status,
                    'keterangan' => $keterangan
                ];
            }
        }

        return view('admin.laporan.index', [
            'laporans' => $laporanData,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
}
