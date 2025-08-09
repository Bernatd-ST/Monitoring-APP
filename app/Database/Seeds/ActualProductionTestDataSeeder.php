<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActualProductionTestDataSeeder extends Seeder
{
    public function run()
    {
        // Data untuk tabel actual_production
        $data = [
            [
                'model_no' => 'A001TG1591ZJ',
                'class' => '99',
                'day_1' => 12,
                'day_2' => 18,
                'day_3' => 22,
                'day_4' => 28,
                'day_5' => 32
            ],
            [
                'model_no' => 'A001TG1991ZJ',
                'class' => '99',
                'day_1' => 18,
                'day_2' => 22,
                'day_3' => 28,
                'day_4' => 32,
                'day_5' => 38
            ],
            [
                'model_no' => 'A001TG2191AJ',
                'class' => '99',
                'day_1' => 22,
                'day_2' => 28,
                'day_3' => 32,
                'day_4' => 38,
                'day_5' => 42
            ]
        ];

        // Insert data ke tabel actual_production
        $this->db->table('actual_production')->insertBatch($data);
    }
}
