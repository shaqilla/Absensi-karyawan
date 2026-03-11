<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Teamwork', 'description' => 'Kemampuan bekerja sama dalam tim'],
            ['name' => 'Komunikasi', 'description' => 'Kemampuan berkomunikasi secara efektif'],
            ['name' => 'Inisiatif', 'description' => 'Kemampuan mengambil tindakan proaktif'],
            ['name' => 'Kedisiplinan', 'description' => 'Kepatuhan terhadap aturan dan ketepatan waktu'],
            ['name' => 'Profesionalisme', 'description' => 'Sikap dan perilaku profesional di tempat kerja'],
        ];

        foreach ($categories as $cat) {
            DB::table('assessment_categories')->insert([
                'name' => $cat['name'],
                'description' => $cat['description'],
                'type' => 'Employee',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
