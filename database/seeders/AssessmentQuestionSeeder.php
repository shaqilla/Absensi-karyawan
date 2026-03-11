<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssessmentQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua kategori
        $categories = DB::table('assessment_categories')->get();

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

        foreach ($categories as $category) {
            if (isset($questionsByCategory[$category->name])) {
                foreach ($questionsByCategory[$category->name] as $index => $question) {
                    DB::table('assessment_questions')->insert([
                        'category_id' => $category->id,
                        'question' => $question,
                        'description' => 'Penilaian untuk aspek ' . $category->name,
                        'order' => $index + 1,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
