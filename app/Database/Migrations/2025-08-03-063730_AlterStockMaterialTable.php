<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterStockMaterialTable extends Migration
{
    public function up()
    {
        // Mengubah nama kolom dari sml menjadi ckd dan periode menjadi period
        $this->forge->modifyColumn('stock_material', [
            'sml' => [
                'name' => 'ckd',
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'periode' => [
                'name' => 'period',
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Mengembalikan nama kolom dari ckd menjadi sml dan period menjadi periode
        $this->forge->modifyColumn('stock_material', [
            'ckd' => [
                'name' => 'sml',
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'period' => [
                'name' => 'periode',
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}
