<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Pengajuan;
use App\Models\JadwalKerja;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $hariIni = now()->toDateString();
        $waktuSekarang = now();

        $start_date = $request->start_date ?? $hariIni;
        $end_date = $request->end_date ?? $hariIni;

        // 1. Ambil Data Dasar
        $karyawans = Karyawan::with(['user', 'departemen'])->get();
        $allPresensi = Presensi::with('shift')->whereBetween('tanggal', [$start_date, $end_date])->get();
        $allPengajuan = Pengajuan::where('status_approval', 'disetujui')
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('tanggal_mulai', [$start_date, $end_date])
                    ->orWhereBetween('tanggal_selesai', [$start_date, $end_date]);
            })->get();

        $mappingHari = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];

        $laporanData = [];
        $totalHadir = 0;
        $totalTelat = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalCuti = 0;
        $totalAlpha = 0;

        $begin = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $end->modify('+1 day');
        $daterange = new \DatePeriod($begin, new \DateInterval('P1D'), $end);

        foreach ($daterange as $date) {
            $tgl = $date->format("Y-m-d");
            $hariNama = $mappingHari[$date->format("l")];

            foreach ($karyawans as $k) {
                $presensi = $allPresensi->where('tanggal', $tgl)->where('user_id', $k->user_id)->first();
                $pengajuan = $allPengajuan->where('user_id', $k->user_id)
                    ->filter(function ($item) use ($tgl) {
                        return $tgl >= $item->tanggal_mulai && $tgl <= $item->tanggal_selesai;
                    })->first();

                // Cari jadwal kerja untuk hari tersebut
                $jadwal = JadwalKerja::with('shift')
                    ->where('user_id', $k->user_id)
                    ->where('hari', $hariNama)
                    ->where('status', 'aktif')
                    ->first();

                $status = '';
                $shouldShow = false;

                if ($presensi) {
                    $shouldShow = true;
                    $status = $presensi->status;
                } elseif ($pengajuan) {
                    $shouldShow = true;
                    $status = $pengajuan->jenis_pengajuan;
                } else {
                    // LOGIKA PERBAIKAN: Hanya tampilkan Alpha jika waktu sudah melewati jam masuk
                    if ($jadwal) {
                        // Gabungkan tanggal laporan dengan jam masuk shift + toleransi
                        $batasMasuk = Carbon::parse($tgl . ' ' . $jadwal->shift->jam_masuk)
                            ->addMinutes($jadwal->shift->toleransi_telat);

                        if ($tgl < $hariIni) {
                            // Jika tanggal sudah lewat, otomatis Alpha
                            $shouldShow = true;
                            $status = 'alpha';
                        } elseif ($tgl == $hariIni && $waktuSekarang->greaterThan($batasMasuk)) {
                            // Jika tanggal hari ini, hanya tampilkan Alpha jika SUDAH MELEWATI jam masuk
                            $shouldShow = true;
                            $status = 'alpha';
                        }
                    }
                }

                if ($shouldShow) {
                    $jam_masuk = $presensi ? date('H:i', strtotime($presensi->jam_masuk)) : '--:--';
                    $jam_keluar = ($presensi && $presensi->jam_keluar) ? date('H:i', strtotime($presensi->jam_keluar)) : '--:--';
                    $ket = $presensi ? ($presensi->keterangan ?? 'Hadir') : ($pengajuan ? $pengajuan->alasan : 'Alpha');

                    if ($status == 'hadir') $totalHadir++;
                    elseif ($status == 'telat') $totalTelat++;
                    elseif ($status == 'izin') $totalIzin++;
                    elseif ($status == 'sakit') $totalSakit++;
                    elseif ($status == 'cuti') $totalCuti++;
                    else $totalAlpha++;

                    $laporanData[] = (object)[
                        'nama' => $k->user->nama,
                        'departemen' => $k->departemen->nama_departemen ?? 'N/A',
                        'tanggal' => $tgl,
                        'jam_masuk' => $jam_masuk,
                        'jam_keluar' => $jam_keluar,
                        'status' => $status,
                        'keterangan' => $ket
                    ];
                }
            }
        }

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
