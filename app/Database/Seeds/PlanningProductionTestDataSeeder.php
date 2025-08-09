<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanningProductionTestDataSeeder extends Seeder
{
    public function run()
    {
        // Data untuk tabel planning_production
        $data = [
            [
                'model_no' => 'A001TG1591ZJ',
                'class' => '99',
                'day_1' => 15,
                'day_2' => 20,
                'day_3' => 25,
                'day_4' => 30,
                'day_5' => 35
            ],
            [
                'model_no' => 'A001TG1991ZJ',
                'class' => '99',
                'day_1' => 20,
                'day_2' => 25,
                'day_3' => 30,
                'day_4' => 35,
                'day_5' => 40
            ],
            [
                'model_no' => 'A001TG2191AJ',
                'class' => '99',
                'day_1' => 25,
                'day_2' => 30,
                'day_3' => 35,
                'day_4' => 40,
                'day_5' => 45
            ]
        ];

        // Insert data ke tabel planning_production
        $this->db->table('planning_production')->insertBatch($data);
    }
}
