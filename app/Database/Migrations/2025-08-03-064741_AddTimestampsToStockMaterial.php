<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimestampsToStockMaterial extends Migration
{
    public function up()
    {
        $this->forge->addColumn('stock_material', [
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('stock_material', ['created_at', 'updated_at']);
    }
}
