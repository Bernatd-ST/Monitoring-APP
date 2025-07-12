<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'model_no',
        'class',
        'schedule_1',
        'schedule_2',
        'schedule_3',
        'schedule_4',
        'schedule_5',
        'schedule_6',
        'schedule_7',
        'schedule_8',
        'schedule_9',
        'schedule_10',
        'schedule_11',
        'schedule_12',
        'schedule_13',
        'schedule_14',
        'schedule_15',
        'schedule_16',
        'schedule_17',
        'schedule_18',
        'schedule_19',
        'schedule_20',
        'schedule_21',
        'schedule_22',
        'schedule_23',
        'schedule_24',
        'schedule_25',
        'schedule_26',
        'schedule_27',
        'schedule_28',
        'schedule_29',
        'schedule_30',
        'schedule_31',
        'total',
    ];


    // Dates
    protected $useTimestamps = false;

}
