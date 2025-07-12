<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalColumnToSales extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sales', [
            'total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 0,
                'after'      => 'schedule_31'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sales', 'total');
    }
}
