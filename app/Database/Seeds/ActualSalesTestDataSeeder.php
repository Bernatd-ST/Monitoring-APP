<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActualSalesTestDataSeeder extends Seeder
{
    public function run()
    {
        // Data untuk tabel actual_sales
        $data = [
            [
                'model_no' => 'A001TG1591ZJ',
                'class' => '99',
                'sch_qty' => 10,
                'act_qty' => 8,
                'prd_cd' => 'A',
                'content' => 'Test',
                'shp_date' => '2025-08-01'
            ],
            [
                'model_no' => 'A001TG1991ZJ',
                'class' => '99',
                'sch_qty' => 15,
                'act_qty' => 12,
                'prd_cd' => 'A',
                'content' => 'Test',
                'shp_date' => '2025-08-02'
            ],
            [
                'model_no' => 'A001TG2191AJ',
                'class' => '99',
                'sch_qty' => 20,
                'act_qty' => 18,
                'prd_cd' => 'A',
                'content' => 'Test',
                'shp_date' => '2025-08-03'
            ]
        ];

        // Insert data ke tabel actual_sales
        $this->db->table('actual_sales')->insertBatch($data);
    }
}
