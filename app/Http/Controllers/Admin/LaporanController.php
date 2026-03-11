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
    private function getLaporanData(Request $request): array
    {
        date_default_timezone_set('Asia/Jakarta');
        $hariIni       = now()->toDateString();
        $waktuSekarang = now();

        $view_type      = $request->view_type  ?? 'daily';
        $start_date     = $request->start_date ?? $hariIni;
        $end_date       = $request->end_date   ?? $hariIni;
        $selected_year  = $request->year       ?? now()->year;
        $selected_month = $request->month      ?? now()->month; // ← FIX: tambah ini

        $karyawans   = Karyawan::with(['user', 'departemen'])->get();
        $laporanData = [];

        $totalHadir = $totalTelat = $totalIzin = $totalSakit = $totalCuti = $totalAlpha = 0;


        // MODE 1: LAPORAN TAHUNAN (annual)
        if ($view_type == 'annual') {

            foreach ($karyawans as $k) {

                $presensiYear = Presensi::where('user_id', $k->user_id)
                    ->whereYear('tanggal', $selected_year)->get();

                $pengajuanYear = Pengajuan::where('user_id', $k->user_id)
                    ->whereYear('tanggal_mulai', $selected_year)
                    ->where('status_approval', 'disetujui')->get();

                $countHadir = $presensiYear->where('status', 'hadir')->count();
                $countTelat = $presensiYear->where('status', 'telat')->count();
                $countIzin  = $pengajuanYear->where('jenis_pengajuan', 'izin')->count();
                $countSakit = $pengajuanYear->where('jenis_pengajuan', 'sakit')->count();
                $countCuti  = $pengajuanYear->where('jenis_pengajuan', 'cuti')->count();

                $totalHadir += $countHadir;
                $totalTelat += $countTelat;
                $totalIzin  += $countIzin;
                $totalSakit += $countSakit;
                $totalCuti  += $countCuti;

                $laporanData[] = (object)[
                    'nama'       => $k->user->nama,
                    'nip'        => $k->nip,
                    'departemen' => $k->departemen->nama_departemen ?? 'N/A',
                    'hadir'      => $countHadir,
                    'telat'      => $countTelat,
                    'izin'       => $countIzin,
                    'sakit'      => $countSakit,
                    'cuti'       => $countCuti,
                ];
            }


            // MODE 2: LAPORAN BULANAN (monthly) ← FIX: blok baru ini
        } elseif ($view_type == 'monthly') {

            foreach ($karyawans as $k) {

                $presensiMonth = Presensi::where('user_id', $k->user_id)
                    ->whereYear('tanggal', $selected_year)
                    ->whereMonth('tanggal', $selected_month)
                    ->get();

                $pengajuanMonth = Pengajuan::where('user_id', $k->user_id)
                    ->whereYear('tanggal_mulai', $selected_year)
                    ->whereMonth('tanggal_mulai', $selected_month)
                    ->where('status_approval', 'disetujui')
                    ->get();

                $countHadir = $presensiMonth->where('status', 'hadir')->count();
                $countTelat = $presensiMonth->where('status', 'telat')->count();
                $countIzin  = $pengajuanMonth->where('jenis_pengajuan', 'izin')->count();
                $countSakit = $pengajuanMonth->where('jenis_pengajuan', 'sakit')->count();
                $countCuti  = $pengajuanMonth->where('jenis_pengajuan', 'cuti')->count();

                $totalHadir += $countHadir;
                $totalTelat += $countTelat;
                $totalIzin  += $countIzin;
                $totalSakit += $countSakit;
                $totalCuti  += $countCuti;

                $laporanData[] = (object)[
                    'nama'       => $k->user->nama,
                    'nip'        => $k->nip,
                    'departemen' => $k->departemen->nama_departemen ?? 'N/A',
                    'hadir'      => $countHadir,
                    'telat'      => $countTelat,
                    'izin'       => $countIzin,
                    'sakit'      => $countSakit,
                    'cuti'       => $countCuti,
                ];
            }


            // MODE 3: LAPORAN HARIAN / RENTANG TANGGAL (daily)
        } else {

            $allPresensi  = Presensi::with('shift')
                ->whereBetween('tanggal', [$start_date, $end_date])->get();

            $allPengajuan = Pengajuan::where('status_approval', 'disetujui')
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('tanggal_mulai', [$start_date, $end_date])
                        ->orWhereBetween('tanggal_selesai', [$start_date, $end_date]);
                })->get();

            $mappingHari = [
                'Monday'    => 'senin',
                'Tuesday'   => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday'  => 'kamis',
                'Friday'    => 'jumat',
                'Saturday'  => 'sabtu',
                'Sunday'    => 'minggu'
            ];

            $begin     = new \DateTime($start_date);
            $end       = new \DateTime($end_date);
            $daterange = new \DatePeriod(
                $begin,
                new \DateInterval('P1D'),
                $end->modify('+1 day')
            );

            foreach ($daterange as $date) {
                $tgl      = $date->format("Y-m-d");
                $hariNama = $mappingHari[$date->format("l")];

                foreach ($karyawans as $k) {

                    $presensi = $allPresensi
                        ->where('tanggal', $tgl)
                        ->where('user_id', $k->user_id)->first();

                    $pengajuan = $allPengajuan
                        ->where('user_id', $k->user_id)
                        ->filter(fn($item) => $tgl >= $item->tanggal_mulai && $tgl <= $item->tanggal_selesai)
                        ->first();

                    $jadwal = JadwalKerja::with('shift')
                        ->where('user_id', $k->user_id)
                        ->where('hari', $hariNama)
                        ->where('status', 'aktif')->first();

                    $status     = '';
                    $shouldShow = false;

                    if ($presensi) {
                        $shouldShow = true;
                        $status     = $presensi->status;
                    } elseif ($pengajuan) {
                        $shouldShow = true;
                        $status     = $pengajuan->jenis_pengajuan;
                    } else {
                        if ($jadwal) {
                            $batasMasuk = Carbon::parse($tgl . ' ' . $jadwal->shift->jam_masuk)
                                ->addMinutes($jadwal->shift->toleransi_telat);

                            if ($tgl < $hariIni || ($tgl == $hariIni && $waktuSekarang->gt($batasMasuk))) {
                                $shouldShow = true;
                                $status     = 'alpha';
                            }
                        }
                    }

                    if ($shouldShow) {

                        $jam_masuk  = $presensi ? date('H:i', strtotime($presensi->jam_masuk)) : '--:--';
                        $jam_keluar = ($presensi && $presensi->jam_keluar)
                            ? date('H:i', strtotime($presensi->jam_keluar)) : '--:--';

                        $ket = $presensi
                            ? ($presensi->keterangan ?? 'Hadir')
                            : ($pengajuan ? $pengajuan->alasan : 'Alpha');

                        if ($status == 'hadir')      $totalHadir++;
                        elseif ($status == 'telat')  $totalTelat++;
                        elseif ($status == 'izin')   $totalIzin++;
                        elseif ($status == 'sakit')  $totalSakit++;
                        elseif ($status == 'cuti')   $totalCuti++;
                        else                         $totalAlpha++;

                        $laporanData[] = (object)[
                            'nama'       => $k->user->nama,
                            'departemen' => $k->departemen->nama_departemen ?? 'N/A',
                            'tanggal'    => $tgl,
                            'jam_masuk'  => $jam_masuk,
                            'jam_keluar' => $jam_keluar,
                            'status'     => $status,
                            'keterangan' => $ket,
                        ];
                    }
                }
            }
        }

        return [
            'laporans'       => $laporanData,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'view_type'      => $view_type,
            'selected_year'  => $selected_year,
            'selected_month' => $selected_month, // ← FIX: tambah ini
            'chartData'      => [
                'labels'   => ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'],
                'datasets' => [$totalHadir, $totalTelat, $totalIzin, $totalSakit, $totalCuti, $totalAlpha],
            ],
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getLaporanData($request);
        return view('admin.laporan.index', $data);
    }

    public function print(Request $request)
    {
        $data = $this->getLaporanData($request);
        return view('admin.laporan.print', $data);
    }
}
    