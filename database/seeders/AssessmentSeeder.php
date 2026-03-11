<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori (gunakan DB facade untuk menghindari error model)
        $categories = [
            [
                'name' => 'Teamwork',
                'description' => 'Kemampuan bekerja sama dalam tim',
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Komunikasi',
                'description' => 'Kemampuan berkomunikasi secara efektif',
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Inisiatif',
                'description' => 'Kemampuan mengambil tindakan proaktif',
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Kedisiplinan',
                'description' => 'Kepatuhan terhadap aturan dan ketepatan waktu',
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Profesionalisme',
                'description' => 'Sikap dan perilaku profesional di tempat kerja',
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert categories dan simpan ID-nya
        foreach ($categories as $categoryData) {
            $categoryId = DB::table('assessment_categories')->insertGetId($categoryData);

            // Tentukan pertanyaan berdasarkan kategori
            $questions = [];

            switch ($categoryData['name']) {
                case 'Teamwork':
                    $questions = [
                        'Bekerja sama dengan rekan tim secara efektif',
                        'Menghargai pendapat anggota tim lain',
                        'Membantu rekan tim yang mengalami kesulitan',
                        'Berkontribusi aktif dalam diskusi tim',
                        'Mampu menyelesaikan konflik dalam tim',
                    ];
                    break;

                case 'Komunikasi':
                    $questions = [
                        'Menyampaikan ide dengan jelas dan terstruktur',
                        'Mendengarkan dengan aktif saat orang lain berbicara',
                        'Menggunakan bahasa yang sopan dan profesional',
                        'Memberikan feedback yang membangun',
                        'Responsif dalam merespon pesan/email',
                    ];
                    break;

                case 'Inisiatif':
                    $questions = [
                        'Mengambil langkah tanpa menunggu perintah',
                        'Mencari solusi sebelum meminta bantuan',
                        'Mengajukan ide-ide baru untuk perbaikan',
                        'Antusias dalam menerima tugas baru',
                        'Belajar hal-hal baru secara mandiri',
                    ];
                    break;

                case 'Kedisiplinan':
                    $questions = [
                        'Datang tepat waktu sesuai jam kerja',
                        'Menyelesaikan tugas sebelum deadline',
                        'Mematuhi peraturan perusahaan',
                        'Mengelola waktu dengan baik',
                        'Bertanggung jawab atas kehadiran dan izin',
                    ];
                    break;

                case 'Profesionalisme':
                    $questions = [
                        'Menjaga etika dan sopan santun',
                        'Bersikap objektif dalam mengambil keputusan',
                        'Menjaga kerahasiaan perusahaan',
                        'Berpakaian rapi dan sesuai aturan',
                        'Bertanggung jawab atas pekerjaan',
                    ];
                    break;
            }

            // Buat 5 pertanyaan untuk setiap kategori
            foreach ($questions as $index => $question) {
                DB::table('assessment_questions')->insert([
                    'category_id' => $categoryId,
                    'question' => $question,
                    'description' => 'Penilaian untuk aspek ' . $categoryData['name'] . ' - pertanyaan ' . ($index + 1),
                    'order' => $index + 1,
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // 2. Buat sample penilaian (opsional)
        // Cek dulu apakah tabel users ada isinya
        $evaluator = DB::table('users')->where('role_id', 1)->first();
        $evaluatees = DB::table('users')->where('role_id', '!=', 1)->take(3)->get();

        if ($evaluator && $evaluatees->count() > 0) {
            // Ambil semua questions yang aktif
            $questions = DB::table('assessment_questions')->where('is_active', true)->get();

            foreach ($evaluatees as $evaluatee) {
                // Buat assessment untuk bulan ini
                $assessmentId = DB::table('assessments')->insertGetId([
                    'evaluator_id' => $evaluator->id,
                    'evaluatee_id' => $evaluatee->id,
                    'assessment_date' => Carbon::now(),
                    'period' => Carbon::now()->format('F Y'),
                    'period_type' => 'monthly',
                    'general_notes' => 'Penilaian bulan ' . Carbon::now()->format('F Y'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Buat nilai untuk setiap pertanyaan (random 3-5)
                foreach ($questions as $question) {
                    DB::table('assessment_details')->insert([
                        'assessment_id' => $assessmentId,
                        'question_id' => $question->id,
                        'score' => rand(3, 5), // Nilai random 3-5
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
