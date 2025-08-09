<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SalesTestDataSeeder extends Seeder
{
    public function run()
    {
        // Update data di tabel sales untuk model yang ada di finish_good
        $this->db->query("UPDATE sales SET 
            schedule_1 = 10, 
            schedule_2 = 15, 
            schedule_3 = 20, 
            schedule_4 = 25, 
            schedule_5 = 30,
            total = 100
            WHERE model_no IN ('A001TG1591ZJ', 'A001TG1991ZJ', 'A001TG2191AJ')");
    }
}
