<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterBomModel extends Model
{
    protected $table            = 'master_bom';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'model_no', 
        'h_class', 
        'qty_assy', 
        'part_no', 
        'description', 
        'prd_code', 
        'class', 
        'upd_date'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'qty_assy' => 'float'
        // Hapus cast untuk upd_date karena menyebabkan error
    ];
    
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'model_no' => 'permit_empty|max_length[100]',
        'part_no'  => 'permit_empty|max_length[100]',
        'class'    => 'permit_empty|max_length[50]'
    ];
    
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['formatDate'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['formatDate'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    /**
     * Format tanggal upd_date sebelum disimpan ke database
     * 
     * @param array $data
     * @return array
     */
    protected function formatDate(array $data)
    {
        if (isset($data['data']['upd_date']) && !empty($data['data']['upd_date'])) {
            $upd_date = $data['data']['upd_date'];
            $formatted_date = null;
            
            // Coba berbagai format tanggal yang mungkin
            $formats = [
                'd-M-y',     // 22-Jul-24
                'Y-m-d',     // 2024-07-22
                'd/m/Y',     // 22/07/2024
                'm/d/Y',     // 07/22/2024
                'd-m-Y',     // 22-07-2024
                'Y/m/d'      // 2024/07/22
            ];
            
            // Jika sudah dalam format Y-m-d, tidak perlu diubah
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $upd_date)) {
                return $data;
            }
            
            // Coba parse dengan DateTime jika berupa string
            if (is_string($upd_date)) {
                // Coba format-format yang umum
                foreach ($formats as $format) {
                    $date = \DateTime::createFromFormat($format, $upd_date);
                    if ($date !== false) {
                        $formatted_date = $date->format('Y-m-d');
                        break;
                    }
                }
                
                // Jika belum berhasil, coba parse secara umum
                if ($formatted_date === null) {
                    try {
                        $date = new \DateTime($upd_date);
                        $formatted_date = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to parse date: ' . $upd_date . ': ' . $e->getMessage());
                    }
                }
            } else if ($upd_date instanceof \DateTime) {
                // Jika sudah berupa objek DateTime
                $formatted_date = $upd_date->format('Y-m-d');
            }
            
            // Jika berhasil diformat, update data
            if ($formatted_date !== null) {
                $data['data']['upd_date'] = $formatted_date;
            } else {
                // Jika gagal parsing, gunakan tanggal hari ini sebagai fallback
                log_message('warning', 'Using today as fallback for unparseable date: ' . $upd_date);
                $data['data']['upd_date'] = date('Y-m-d');
            }
        } else if (isset($data['data']['upd_date']) && empty($data['data']['upd_date'])) {
            // Jika tanggal kosong, gunakan tanggal hari ini
            $data['data']['upd_date'] = date('Y-m-d');
        }
        
        return $data;
    }
}
