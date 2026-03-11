<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\KategoriAbsen;
use App\Models\LokasiKantor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Daftar Departemen
        $list_departemen = [
            ['nama_departemen' => 'IT Support', 'kode_departemen' => 'ITS'],
            ['nama_departemen' => 'Human Resources', 'kode_departemen' => 'HRD'],
            ['nama_departemen' => 'Finance & Accounting', 'kode_departemen' => 'FIN'],
            ['nama_departemen' => 'Marketing', 'kode_departemen' => 'MKT'],
            ['nama_departemen' => 'General Affair', 'kode_departemen' => 'GA'],
        ];

        foreach ($list_departemen as $d) {
            Departemen::create($d);
        }

        // Ambil departemen untuk referensi
        $deptIt = Departemen::where('kode_departemen', 'ITS')->first();
        $deptHrd = Departemen::where('kode_departemen', 'HRD')->first();
        $deptFin = Departemen::where('kode_departemen', 'FIN')->first();
        $deptMkt = Departemen::where('kode_departemen', 'MKT')->first();

        // 2. Buat Akun Admin (role: admin)
        $admin = User::create([
            'nama' => 'Admin Utama',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Buat Akun Karyawan (semua role: karyawan)
        // Karyawan dengan jabatan Manager (sebagai penilai)
        $manager = User::create([
            'nama' => 'Dewi Kusuma',
            'email' => 'manager@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        // Karyawan dengan jabatan Supervisor (sebagai penilai juga)
        $supervisor = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'supervisor@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        // Karyawan biasa (yang akan dinilai)
        $karyawan1 = User::create([
            'nama' => 'Ani Wijaya',
            'email' => 'ani@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $karyawan2 = User::create([
            'nama' => 'Citra Dewi',
            'email' => 'citra@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $karyawan3 = User::create([
            'nama' => 'Eko Prasetyo',
            'email' => 'eko@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $karyawan4 = User::create([
            'nama' => 'Rina Susanti',
            'email' => 'rina@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $karyawan5 = User::create([
            'nama' => 'Deni Saputra',
            'email' => 'deni@mail.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        // 4. Buat Detail Data Karyawan (Tabel Karyawans)
        // Manager dan Supervisor (sebagai penilai)
        Karyawan::create([
            'user_id' => $manager->id,
            'nip' => 'MGR001',
            'jabatan' => 'Manager HRD',
            'departemen_id' => $deptHrd->id,
            'tanggal_masuk' => now()->subYears(3),
            'alamat' => 'Jl. Sudirman No. 45',
            'jenis_kelamin' => 'perempuan'
        ]);

        Karyawan::create([
            'user_id' => $supervisor->id,
            'nip' => 'SPV001',
            'jabatan' => 'Supervisor IT',
            'departemen_id' => $deptIt->id,
            'tanggal_masuk' => now()->subYears(2),
            'alamat' => 'Jl. Gatot Subroto No. 78',
            'jenis_kelamin' => 'laki-laki'
        ]);

        // Karyawan biasa (yang akan dinilai)
        Karyawan::create([
            'user_id' => $karyawan1->id,
            'nip' => 'KRY001',
            'jabatan' => 'HR Staff',
            'departemen_id' => $deptHrd->id,
            'tanggal_masuk' => now()->subMonths(6),
            'alamat' => 'Jl. Merdeka No. 1',
            'jenis_kelamin' => 'perempuan'
        ]);

        Karyawan::create([
            'user_id' => $karyawan2->id,
            'nip' => 'KRY002',
            'jabatan' => 'Finance Staff',
            'departemen_id' => $deptFin->id,
            'tanggal_masuk' => now()->subYear(),
            'alamat' => 'Jl. Asia Afrika No. 23',
            'jenis_kelamin' => 'perempuan'
        ]);

        Karyawan::create([
            'user_id' => $karyawan3->id,
            'nip' => 'KRY003',
            'jabatan' => 'IT Staff',
            'departemen_id' => $deptIt->id,
            'tanggal_masuk' => now()->subMonths(3),
            'alamat' => 'Jl. Diponegoro No. 56',
            'jenis_kelamin' => 'laki-laki'
        ]);

        Karyawan::create([
            'user_id' => $karyawan4->id,
            'nip' => 'KRY004',
            'jabatan' => 'Marketing Staff',
            'departemen_id' => $deptMkt->id,
            'tanggal_masuk' => now()->subMonths(8),
            'alamat' => 'Jl. Thamrin No. 12',
            'jenis_kelamin' => 'perempuan'
        ]);

        Karyawan::create([
            'user_id' => $karyawan5->id,
            'nip' => 'KRY005',
            'jabatan' => 'IT Staff',
            'departemen_id' => $deptIt->id,
            'tanggal_masuk' => now()->subMonths(4),
            'alamat' => 'Jl. Ahmad Yani No. 34',
            'jenis_kelamin' => 'laki-laki'
        ]);

        // 5. Buat Kategori Absen Dasar
        KategoriAbsen::create([
            'nama_kategori' => 'Hadir',
            'keterangan' => 'Hadir tepat waktu'
        ]);

        KategoriAbsen::create([
            'nama_kategori' => 'Izin',
            'keterangan' => 'Izin tidak masuk'
        ]);

        KategoriAbsen::create([
            'nama_kategori' => 'Sakit',
            'keterangan' => 'Sakit dengan surat dokter'
        ]);

        KategoriAbsen::create([
            'nama_kategori' => 'Cuti',
            'keterangan' => 'Cuti tahunan'
        ]);

        // 6. Lokasi Kantor
        LokasiKantor::create([
            'nama_kantor' => 'Kantor Pusat',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius' => 50
        ]);

        // ============= TAMBAHAN: SEEDER UNTUK ASSESSMENT =============
        $this->seedAssessmentData(
            $admin->id,                    // Admin ID
            [$manager->id, $supervisor->id], // Penilai (karyawan dengan jabatan manager/supervisor)
            [$karyawan1->id, $karyawan2->id, $karyawan3->id, $karyawan4->id, $karyawan5->id] // Yang dinilai
        );
    }

    /**
     * Fungsi tambahan untuk seeding data assessment
     */
    private function seedAssessmentData($adminId, array $penilaiIds, array $karyawanIds)
    {
        $this->command->info('🚀 Mulai seeding data assessment...');

        // 1. Buat Kategori Assessment
        $categories = [
            [
                'name' => 'Teamwork',
                'description' => 'Kemampuan bekerja sama dalam tim',
                'type' => 'Employee',
                'is_active' => true,
            ],
            [
                'name' => 'Komunikasi',
                'description' => 'Kemampuan berkomunikasi secara efektif',
                'type' => 'Employee',
                'is_active' => true,
            ],
            [
                'name' => 'Inisiatif',
                'description' => 'Kemampuan mengambil tindakan proaktif',
                'type' => 'Employee',
                'is_active' => true,
            ],
            [
                'name' => 'Kedisiplinan',
                'description' => 'Kepatuhan terhadap aturan dan ketepatan waktu',
                'type' => 'Employee',
                'is_active' => true,
            ],
            [
                'name' => 'Profesionalisme',
                'description' => 'Sikap dan perilaku profesional di tempat kerja',
                'type' => 'Employee',
                'is_active' => true,
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $catData) {
            $categoryId = DB::table('assessment_categories')->insertGetId([
                'name' => $catData['name'],
                'description' => $catData['description'],
                'type' => $catData['type'],
                'is_active' => $catData['is_active'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $categoryIds[$catData['name']] = $categoryId;
            $this->command->info("  ✅ Kategori: {$catData['name']}");
        }

        // 2. Buat Pertanyaan untuk setiap kategori (5 pertanyaan per kategori)
        $questionsByCategory = [
            'Teamwork' => [
                'Bekerja sama dengan rekan tim secara efektif',
                'Menghargai pendapat anggota tim lain',
                'Membantu rekan tim yang mengalami kesulitan',
                'Berkontribusi aktif dalam diskusi tim',
                'Mampu menyelesaikan konflik dalam tim',
            ],
            'Komunikasi' => [
                'Menyampaikan ide dengan jelas dan terstruktur',
                'Mendengarkan dengan aktif saat orang lain berbicara',
                'Menggunakan bahasa yang sopan dan profesional',
                'Memberikan feedback yang membangun',
                'Responsif dalam merespon pesan/email',
            ],
            'Inisiatif' => [
                'Mengambil langkah tanpa menunggu perintah',
                'Mencari solusi sebelum meminta bantuan',
                'Mengajukan ide-ide baru untuk perbaikan',
                'Antusias dalam menerima tugas baru',
                'Belajar hal-hal baru secara mandiri',
            ],
            'Kedisiplinan' => [
                'Datang tepat waktu sesuai jam kerja',
                'Menyelesaikan tugas sebelum deadline',
                'Mematuhi peraturan perusahaan',
                'Mengelola waktu dengan baik',
                'Bertanggung jawab atas kehadiran dan izin',
            ],
            'Profesionalisme' => [
                'Menjaga etika dan sopan santun',
                'Bersikap objektif dalam mengambil keputusan',
                'Menjaga kerahasiaan perusahaan',
                'Berpakaian rapi dan sesuai aturan',
                'Bertanggung jawab atas pekerjaan',
            ],
        ];

        $allQuestionIds = [];
        foreach ($questionsByCategory as $categoryName => $questions) {
            $categoryId = $categoryIds[$categoryName];
            
            foreach ($questions as $index => $question) {
                $questionId = DB::table('assessment_questions')->insertGetId([
                    'category_id' => $categoryId,
                    'question' => $question,
                    'description' => 'Penilaian untuk aspek ' . $categoryName,
                    'order' => $index + 1,
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $allQuestionIds[] = $questionId;
            }
        }
        $this->command->info("  ✅ Total Pertanyaan: " . count($allQuestionIds));

        // 3. Buat Sample Penilaian untuk 3 bulan terakhir
        $periods = [
            Carbon::now()->subMonths(2)->format('F Y'),
            Carbon::now()->subMonth()->format('F Y'),
            Carbon::now()->format('F Y'),
        ];

        $totalAssessments = 0;
        foreach ($periods as $index => $period) {
            $assessmentDate = Carbon::now()->subMonths(2 - $index);
            
            foreach ($karyawanIds as $karyawanId) {
                // Setiap karyawan dinilai oleh setiap penilai
                foreach ($penilaiIds as $penilaiId) {
                    // Buat assessment dari penilai
                    $assessmentId = DB::table('assessments')->insertGetId([
                        'evaluator_id' => $penilaiId,
                        'evaluatee_id' => $karyawanId,
                        'assessment_date' => $assessmentDate,
                        'period' => $period,
                        'period_type' => 'monthly',
                        'general_notes' => 'Penilaian bulan ' . $period,
                        'created_at' => $assessmentDate,
                        'updated_at' => $assessmentDate,
                    ]);

                    // Buat nilai untuk setiap pertanyaan
                    foreach ($allQuestionIds as $questionId) {
                        // Nilai cenderung meningkat setiap bulan
                        $baseScore = 3 + $index; // Bulan 1: 3, Bulan 2: 4, Bulan 3: 5
                        $randomFactor = rand(-1, 1);
                        $score = min(5, max(1, $baseScore + $randomFactor));
                        
                        DB::table('assessment_details')->insert([
                            'assessment_id' => $assessmentId,
                            'question_id' => $questionId,
                            'score' => $score,
                            'created_at' => $assessmentDate,
                            'updated_at' => $assessmentDate,
                        ]);
                    }
                    $totalAssessments++;
                }

                // Buat assessment dari admin (sebagai evaluasi tambahan)
                $assessmentId2 = DB::table('assessments')->insertGetId([
                    'evaluator_id' => $adminId,
                    'evaluatee_id' => $karyawanId,
                    'assessment_date' => $assessmentDate,
                    'period' => $period,
                    'period_type' => 'monthly',
                    'general_notes' => 'Evaluasi admin bulan ' . $period,
                    'created_at' => $assessmentDate,
                    'updated_at' => $assessmentDate,
                ]);

                // Nilai dari admin
                foreach ($allQuestionIds as $questionId) {
                    $score = rand(3, 5);
                    
                    DB::table('assessment_details')->insert([
                        'assessment_id' => $assessmentId2,
                        'question_id' => $questionId,
                        'score' => $score,
                        'created_at' => $assessmentDate,
                        'updated_at' => $assessmentDate,
                    ]);
                }
                $totalAssessments++;
            }
        }
    }
}