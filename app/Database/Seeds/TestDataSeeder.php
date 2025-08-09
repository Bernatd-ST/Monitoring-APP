<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        $this->call('FinishGoodTestDataSeeder');
        $this->call('SalesTestDataSeeder');
        $this->call('ActualSalesTestDataSeeder');
        $this->call('PlanningProductionTestDataSeeder');
        $this->call('ActualProductionTestDataSeeder');
        
        echo "All test data has been seeded successfully.\n";
    }
}
