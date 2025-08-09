<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FinishGoodTestDataSeeder extends Seeder
{
    public function run()
    {
        // Data untuk tabel finish_good
        $data = [
            [
                'criteria' => 'FGD',
                'period' => '2025-08-01',
                'description' => 'TEST PART',
                'part_no' => 'A001TG1591ZJ',
                'class' => '99',
                'end_bal' => '100',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'criteria' => 'FGD',
                'period' => '2025-08-01',
                'description' => 'TEST PART',
                'part_no' => 'A001TG1991ZJ',
                'class' => '99',
                'end_bal' => '150',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'criteria' => 'FGD',
                'period' => '2025-08-01',
                'description' => 'TEST PART',
                'part_no' => 'A001TG2191AJ',
                'class' => '99',
                'end_bal' => '200',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data ke tabel finish_good
        $this->db->table('finish_good')->insertBatch($data);
    }
}
