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
    // =========================================
    // FUNGSI PRIVATE: AMBIL & PROSES DATA LAPORAN
    // Private = hanya bisa dipanggil dari dalam controller ini saja
    // Dipakai bersama oleh index() dan print() supaya tidak nulis kode dua kali
    // =========================================
    private function getLaporanData(Request $request): array
    {
        date_default_timezone_set('Asia/Jakarta');
        $hariIni       = now()->toDateString();
        $waktuSekarang = now();

        // Ambil parameter filter dari URL, kalau tidak ada pakai nilai default
        // ?? = operator "null coalescing" → kalau kiri null, pakai nilai kanan
        $view_type     = $request->view_type  ?? 'daily';      // Default: harian
        $start_date    = $request->start_date ?? $hariIni;     // Default: hari ini
        $end_date      = $request->end_date   ?? $hariIni;     // Default: hari ini
        $selected_year = $request->year       ?? now()->year;  // Default: tahun ini

        // Ambil semua karyawan beserta relasi user dan departemen
        $karyawans   = Karyawan::with(['user', 'departemen'])->get();
        $laporanData = [];

        // Inisialisasi counter untuk total masing-masing status
        $totalHadir = $totalTelat = $totalIzin = $totalSakit = $totalCuti = $totalAlpha = 0;

        // =========================================
        // MODE 1: LAPORAN TAHUNAN (annual)
        // Menampilkan rekap total per karyawan selama 1 tahun
        // =========================================
        if ($view_type == 'annual') {

            foreach ($karyawans as $k) {

                // Ambil semua presensi karyawan ini di tahun yang dipilih
                $presensiYear = Presensi::where('user_id', $k->user_id)
                    ->whereYear('tanggal', $selected_year)->get();

                // Ambil semua pengajuan izin/sakit/cuti yang disetujui di tahun ini
                $pengajuanYear = Pengajuan::where('user_id', $k->user_id)
                    ->whereYear('tanggal_mulai', $selected_year)
                    ->where('status_approval', 'disetujui')->get();

                // Hitung masing-masing status dari koleksi yang sudah diambil
                // Menggunakan ->where() pada collection (bukan query database)
                $countHadir = $presensiYear->where('status', 'hadir')->count();
                $countTelat = $presensiYear->where('status', 'telat')->count();
                $countIzin  = $pengajuanYear->where('jenis_pengajuan', 'izin')->count();
                $countSakit = $pengajuanYear->where('jenis_pengajuan', 'sakit')->count();
                $countCuti  = $pengajuanYear->where('jenis_pengajuan', 'cuti')->count();

                // Akumulasi ke total keseluruhan
                $totalHadir += $countHadir;
                $totalTelat += $countTelat;
                $totalIzin  += $countIzin;
                $totalSakit += $countSakit;
                $totalCuti  += $countCuti;

                // Masukkan data karyawan ini ke array laporan
                // (object) = konversi array ke object supaya bisa diakses pakai ->
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

            // =========================================
            // MODE 2: LAPORAN HARIAN / RENTANG TANGGAL
            // Menampilkan detail per hari per karyawan
            // =========================================
        } else {

            // Ambil semua presensi dalam rentang tanggal yang dipilih
            $allPresensi  = Presensi::with('shift')
                ->whereBetween('tanggal', [$start_date, $end_date])->get();

            // Ambil semua pengajuan yang disetujui dan tanggalnya masuk rentang
            $allPengajuan = Pengajuan::where('status_approval', 'disetujui')
                ->where(function ($q) use ($start_date, $end_date) {
                    // Cek kalau tanggal mulai ATAU tanggal selesai ada di rentang
                    $q->whereBetween('tanggal_mulai', [$start_date, $end_date])
                        ->orWhereBetween('tanggal_selesai', [$start_date, $end_date]);
                })->get();

            // Mapping nama hari Inggris → Indonesia
            $mappingHari = [
                'Monday' => 'senin',
                'Tuesday' => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday' => 'kamis',
                'Friday' => 'jumat',
                'Saturday' => 'sabtu',
                'Sunday' => 'minggu'
            ];

            // Buat rentang tanggal dari start_date sampai end_date
            // DatePeriod = generator semua tanggal dalam rentang tersebut
            $begin     = new \DateTime($start_date);
            $end       = new \DateTime($end_date);
            $daterange = new \DatePeriod(
                $begin,
                new \DateInterval('P1D'), // P1D = interval 1 hari
                $end->modify('+1 day')    // +1 hari supaya end_date ikut masuk
            );

            // Loop setiap tanggal dalam rentang
            foreach ($daterange as $date) {
                $tgl      = $date->format("Y-m-d");
                $hariNama = $mappingHari[$date->format("l")]; // Nama hari Indonesia

                // Loop setiap karyawan untuk tanggal ini
                foreach ($karyawans as $k) {

                    // Cari presensi karyawan ini di tanggal ini (dari data yang sudah diambil)
                    $presensi = $allPresensi
                        ->where('tanggal', $tgl)
                        ->where('user_id', $k->user_id)->first();

                    // Cari pengajuan izin yang mencakup tanggal ini
                    $pengajuan = $allPengajuan
                        ->where('user_id', $k->user_id)
                        ->filter(fn($item) => $tgl >= $item->tanggal_mulai && $tgl <= $item->tanggal_selesai)
                        ->first();

                    // Cari jadwal kerja karyawan ini di hari ini
                    $jadwal = JadwalKerja::with('shift')
                        ->where('user_id', $k->user_id)
                        ->where('hari', $hariNama)
                        ->where('status', 'aktif')->first();

                    $status    = '';
                    $shouldShow = false; // Flag: apakah data ini perlu ditampilkan?

                    if ($presensi) {
                        // Karyawan sudah presensi → tampilkan dengan status presensinya
                        $shouldShow = true;
                        $status     = $presensi->status; // hadir / telat

                    } elseif ($pengajuan) {
                        // Karyawan tidak presensi tapi ada izin disetujui → tampilkan
                        $shouldShow = true;
                        $status     = $pengajuan->jenis_pengajuan; // izin / sakit / cuti

                    } else {
                        // Tidak presensi, tidak izin → cek apakah alpha
                        if ($jadwal) {
                            $batasMasuk = Carbon::parse($tgl . ' ' . $jadwal->shift->jam_masuk)
                                ->addMinutes($jadwal->shift->toleransi_telat);

                            // Dihitung alpha kalau:
                            // - Tanggalnya sudah lewat (hari lalu), ATAU
                            // - Hari ini dan waktu sekarang sudah lewat batas masuk
                            if ($tgl < $hariIni || ($tgl == $hariIni && $waktuSekarang->gt($batasMasuk))) {
                                $shouldShow = true;
                                $status     = 'alpha';
                            }
                        }
                    }

                    // Hanya masukkan ke laporan kalau shouldShow = true
                    if ($shouldShow) {

                        // Format jam masuk & keluar, tampilkan '--:--' kalau tidak ada
                        $jam_masuk  = $presensi ? date('H:i', strtotime($presensi->jam_masuk)) : '--:--';
                        $jam_keluar = ($presensi && $presensi->jam_keluar)
                            ? date('H:i', strtotime($presensi->jam_keluar)) : '--:--';

                        // Keterangan: ambil dari presensi, atau alasan pengajuan, atau 'Alpha'
                        $ket = $presensi
                            ? ($presensi->keterangan ?? 'Hadir')
                            : ($pengajuan ? $pengajuan->alasan : 'Alpha');

                        // Tambah ke counter total sesuai status
                        if ($status == 'hadir')      $totalHadir++;
                        elseif ($status == 'telat')  $totalTelat++;
                        elseif ($status == 'izin')   $totalIzin++;
                        elseif ($status == 'sakit')  $totalSakit++;
                        elseif ($status == 'cuti')   $totalCuti++;
                        else                         $totalAlpha++;

                        // Masukkan baris data ke laporan
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

        // Kembalikan semua data sebagai array
        // Termasuk data chart untuk grafik di halaman laporan
        return [
            'laporans'      => $laporanData,
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'view_type'     => $view_type,
            'selected_year' => $selected_year,
            'chartData'     => [
                'labels'   => ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'],
                // Data angka untuk ditampilkan di grafik
                'datasets' => [$totalHadir, $totalTelat, $totalIzin, $totalSakit, $totalCuti, $totalAlpha],
            ],
        ];
    }

    // =========================================
    // TAMPILKAN LAPORAN DI HALAMAN WEB
    // =========================================
    public function index(Request $request)
    {
        // Panggil fungsi private untuk ambil data, lalu kirim ke view
        $data = $this->getLaporanData($request);
        return view('admin.laporan.index', $data);
    }

    // =========================================
    // TAMPILKAN LAPORAN VERSI CETAK
    // =========================================
    public function print(Request $request)
    {
        // Data sama persis dengan index(), hanya view-nya berbeda (layout cetak)
        $data = $this->getLaporanData($request);
        return view('admin.laporan.print', $data);
    }
}
