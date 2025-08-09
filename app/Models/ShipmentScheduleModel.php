<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentScheduleModel extends Model
{
    protected $table            = 'shipment_schedule';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'inv_no',
        'item_no',
        'class',
        'sch_qty',
        'etd_date',
        'eta_date',
        'eta_meina'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    /**
     * Mendapatkan data material berdasarkan ID
     *
     * @param int $id ID material
     * @return array|null Data material atau null jika tidak ditemukan
     */
    public function getMaterialById($id)
    {
        return $this->find($id);
    }
    
    /**
     * Mendapatkan semua data material dengan filter opsional
     *
     * @param array $filters Filter yang akan diterapkan
     * @return array Data material yang sudah difilter
     */
    public function getMaterials($filters = [])
    {
        $builder = $this->builder();
        
        // Terapkan filter jika ada
        if (!empty($filters['inv_no'])) {
            $builder->like('inv_no', $filters['inv_no']);
        }
        
        if (!empty($filters['item_no'])) {
            $builder->like('item_no', $filters['item_no']);
        }
        
        if (!empty($filters['class'])) {
            $builder->where('class', $filters['class']);
        }
        
        if (!empty($filters['sch_qty'])) {
            $builder->where('sch_qty', $filters['sch_qty']);
        }
        
        if (!empty($filters['etd_date'])) {
            $builder->where('etd_date', $filters['etd_date']);
        }
        
        if (!empty($filters['eta_date'])) {
            $builder->where('eta_date', $filters['eta_date']);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Format tanggal dari format Excel (dd/mm/yy) ke format MySQL (yyyy-mm-dd)
     *
     * @param string $excelDate Tanggal dalam format Excel (misalnya: 01/05/25)
     * @return string|null Tanggal dalam format MySQL atau null jika input tidak valid
     */
    public function formatExcelDate($excelDate)
    {
        if (empty($excelDate)) {
            return null;
        }
        
        // Coba parse tanggal dengan format dd/mm/yy
        $date = \DateTime::createFromFormat('d/m/y', $excelDate);
        
        // Jika gagal, coba format alternatif
        if (!$date) {
            // Coba format alternatif dd-mm-yy
            $date = \DateTime::createFromFormat('d-m-y', $excelDate);
        }
        
        // Jika masih gagal, coba format lain
        if (!$date) {
            // Coba format mm/dd/yy (untuk Excel yang menggunakan format US)
            $date = \DateTime::createFromFormat('m/d/y', $excelDate);
        }
        
        // Jika masih gagal, kembalikan null
        if (!$date) {
            return null;
        }
        
        // Kembalikan dalam format MySQL
        return $date->format('Y-m-d');
    }
    
    /**
     * Mendapatkan nilai unik dari kolom tertentu untuk dropdown filter
     *
     * @param string $column Nama kolom yang akan diambil nilai uniknya
     * @return array Array nilai unik dari kolom tersebut
     */
    public function getUniqueValues($column)
    {
        if (!in_array($column, $this->allowedFields)) {
            return [];
        }
        
        $builder = $this->builder();
        $builder->select($column);
        $builder->distinct();
        $builder->where($column . ' IS NOT NULL');
        $builder->where($column . ' != ""');
        $builder->orderBy($column, 'ASC');
        
        $query = $builder->get();
        $results = $query->getResultArray();
        
        $values = [];
        foreach ($results as $row) {
            if (!empty($row[$column])) {
                $values[] = $row[$column];
            }
        }
        
        return $values;
    }
}
