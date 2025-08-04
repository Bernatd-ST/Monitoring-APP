<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialControlTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ckd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'period' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'part_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'class' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'beginning' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['ckd', 'part_no']);
        $this->forge->createTable('material_control');
    }

    public function down()
    {
        $this->forge->dropTable('material_control');
    }
}
