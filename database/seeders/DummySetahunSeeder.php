<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Pengajuan;
use App\Models\Shift;
use App\Models\Departemen;
use App\Models\KategoriAbsen;
use App\Models\QrSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DummySetahunSeeder extends Seeder
{
    public function run()
    {
        // 1. Bersihkan Database agar FRESH
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Presensi::truncate();
        Pengajuan::truncate();
        Karyawan::truncate();
        User::truncate();
        Shift::truncate();
        Departemen::truncate();
        KategoriAbsen::truncate();
        QrSession::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Data Master Dasar
        $it = Departemen::create(['nama_departemen' => 'IT Support', 'kode_departemen' => 'ITS']);
        $hrd = Departemen::create(['nama_departemen' => 'Human Resource', 'kode_departemen' => 'HRD']);
        $fin = Departemen::create(['nama_departemen' => 'Finance', 'kode_departemen' => 'FIN']);

        KategoriAbsen::create(['nama_kategori' => 'Hadir Kerja']);

        $shiftPagi = Shift::create([
            'nama_shift' => 'Shift Pagi',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'toleransi_telat' => 15
        ]);

        // 3. Bikin Admin Utama (Login Presentasi)
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Bikin Sesi QR (Induk absen)
        $qr = QrSession::create([
            'token' => 'token_final_ujian',
            'created_by' => $admin->id,
            'expired_at' => now()->addYears(5),
            'is_active' => true
        ]);

        // 4. Bikin 30 Karyawan (Agar Dashboard Penuh)
        $karyawanIds = [];
        $depts = [$it->id, $hrd->id, $fin->id];

        for ($i = 1; $i <= 30; $i++) {
            $user = User::create([
                'nama' => 'Pegawai ' . $i,
                'email' => 'karyawan' . $i . '@mail.com',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
            ]);

            Karyawan::create([
                'user_id' => $user->id,
                'nip' => '199000' . sprintf("%02d", $i),
                'jabatan' => 'Staff IT Level ' . $i,
                'departemen_id' => $depts[array_rand($depts)],
                'tanggal_masuk' => now()->subYear(),
                'alamat' => 'Alamat Pegawai No. ' . $i,
                'jenis_kelamin' => ($i % 2 == 0) ? 'laki-laki' : 'perempuan'
            ]);
            $karyawanIds[] = $user->id;
        }

        // 5. Generate Data Sejarah (1 Tahun Kebelakang)
        $this->command->info("Membuat data 1 tahun... Sabar ya bos.");
        $startDate = now()->subYear();
        $yesterday = now()->subDay();

        for ($date = $startDate->copy(); $date->lte($yesterday); $date->addDay()) {
            if ($date->isSunday()) continue;
            foreach ($karyawanIds as $uId) {
                $rand = rand(1, 100);
                if ($rand <= 80) { // Hadir
                    $status = (rand(1, 10) > 8) ? 'telat' : 'hadir';
                    $menit = ($status == 'hadir') ? rand(-20, 15) : rand(16, 60);
                    Presensi::create([
                        'user_id' => $uId,
                        'qr_session_id' => $qr->id,
                        'shift_id' => $shiftPagi->id,
                        'tanggal' => $date->toDateString(),
                        'jam_masuk' => Carbon::parse($date->toDateString() . ' 08:00:00')->addMinutes($menit),
                        'jam_keluar' => $date->copy()->setTime(17, rand(0, 30)),
                        'latitude' => -6.175,
                        'longitude' => 106.827,
                        'status' => $status,
                        'kategori_id' => 1
                    ]);
                }
            }
        }

        // 6. SETTING DASHBOARD HARI INI (BIAR GAK KOSONG)
        $this->command->info("Menyetel data khusus hari ini...");
        $today = now()->toDateString();

        // - 15 orang HADIR (Jam 7 pagi)
        for ($i = 0; $i < 15; $i++) {
            Presensi::create([
                'user_id' => $karyawanIds[$i],
                'qr_session_id' => $qr->id,
                'shift_id' => $shiftPagi->id,
                'tanggal' => $today,
                'jam_masuk' => now()->setTime(7, rand(30, 59)),
                'latitude' => -6.175,
                'longitude' => 106.827,
                'status' => 'hadir',
                'kategori_id' => 1
            ]);
        }
        // - 5 orang TELAT (Jam 8 lewat)
        for ($i = 15; $i < 20; $i++) {
            Presensi::create([
                'user_id' => $karyawanIds[$i],
                'qr_session_id' => $qr->id,
                'shift_id' => $shiftPagi->id,
                'tanggal' => $today,
                'jam_masuk' => now()->setTime(8, rand(20, 45)),
                'latitude' => -6.175,
                'longitude' => 106.827,
                'status' => 'telat',
                'kategori_id' => 1
            ]);
        }
        // - 5 orang IZIN
        for ($i = 20; $i < 25; $i++) {
            Pengajuan::create([
                'user_id' => $karyawanIds[$i],
                'jenis_pengajuan' => 'izin',
                'tanggal_mulai' => $today,
                'tanggal_selesai' => $today,
                'alasan' => 'Ada keperluan keluarga mendesak',
                'status_approval' => 'disetujui',
                'approved_by' => $admin->id
            ]);
        }
    }
}
