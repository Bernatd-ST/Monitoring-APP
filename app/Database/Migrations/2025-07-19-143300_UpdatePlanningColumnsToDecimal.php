<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePlanningColumnsToDecimal extends Migration
{
    public function up()
    {
        // Ubah tipe data kolom day_1 sampai day_31 dan total menjadi decimal(10,1)
        for ($i = 1; $i <= 31; $i++) {
            $this->forge->modifyColumn('planning_production', [
                "day_{$i}" => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,1',
                    'default'    => 0,
                ],
            ]);
        }
        
        // Ubah juga kolom total
        $this->forge->modifyColumn('planning_production', [
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,1',
                'default'    => 0,
            ],
        ]);
    }

    public function down()
    {
        // Kembalikan ke integer jika perlu rollback
        for ($i = 1; $i <= 31; $i++) {
            $this->forge->modifyColumn('planning_production', [
                "day_{$i}" => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
            ]);
        }
        
        // Kembalikan juga kolom total
        $this->forge->modifyColumn('planning_production', [
            'total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ]);
    }
}
