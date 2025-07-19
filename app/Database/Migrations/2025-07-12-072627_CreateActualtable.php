<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActualTable extends Migration
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
            'update_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'prd_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'model_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'class' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'day_1' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_2' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_3' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_4' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_5' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_6' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_7' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_8' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_9' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_10' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_11' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_12' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_13' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_14' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_15' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_16' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_17' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_18' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_19' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_20' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_21' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_22' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_23' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_24' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_25' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_26' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_27' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_28' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_29' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_30' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'day_31' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('actual_production');
    }

    public function down()
    {
        $this->forge->dropTable('actual_production');
    }
}