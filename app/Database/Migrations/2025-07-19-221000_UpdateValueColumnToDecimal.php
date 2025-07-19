<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateValueColumnToDecimal extends Migration
{
    public function up()
    {
        // Mengubah kolom update_value dari INTEGER ke DECIMAL(10,2)
        // Gunakan 2 digit desimal untuk remark/update_value karena lebih umum untuk nilai uang/persentase
        $this->forge->modifyColumn('planning_production', [
            'update_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Mengembalikan kolom update_value ke INTEGER
        $this->forge->modifyColumn('planning_production', [
            'update_value' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
    }
}
