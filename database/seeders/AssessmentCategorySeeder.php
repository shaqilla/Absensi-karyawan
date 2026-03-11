<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssessmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $items = [
            ['name' => 'Teamwork', 'description' => 'Apakah individu ini dapat diandalkan dan kooperatif dalam tim?'],
            ['name' => 'Communication', 'description' => 'Apakah dia terbuka dan jelas dalam berkomunikasi?'],
            ['name' => 'Reliability', 'description' => 'Tingkat kepercayaan dalam menyelesaikan tugas tepat waktu.'],
            ['name' => 'Initiative', 'description' => 'Kemampuan untuk mengambil langkah tanpa harus menunggu perintah.'],
            ['name' => 'Professionalism', 'description' => 'Etika kerja, penampilan, dan sikap di lingkungan kantor.'],
        ];

        foreach ($items as $item) {
            \App\Models\AssessmentCategory::create($item);
        }
    }
}
