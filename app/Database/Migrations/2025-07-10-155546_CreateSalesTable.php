<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesTable extends Migration
{
    public function up() // Dijalankan saat kita membuat tabel.
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'model_no' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'class' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],


        ]);

        for ($i = 1; $i <= 31; $i++) {
            $this->forge->addField([
                "schedule_{$i}" => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'default' => 0,
                ]
            ]);
        }

        $this->forge->addKey('id', true);
        $this->forge->createTable('sales');
    }

    public function down() // Dijalankan saat kita ingin menghapus tabel (misalnya untuk rollback).
    {
        $this->forge->dropTable('sales');
    }
}
