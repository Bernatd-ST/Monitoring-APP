<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialShortageModelFixed extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getMaterialShortageData($startDate, $endDate, $partNo = null, $hClass = null, $class = null, $minusOnly = false)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Getting data with params: startDate={$startDate}, endDate={$endDate}, partNo={$partNo}, hClass={$hClass}, class={$class}, minusOnly=" . ($minusOnly ? 'true' : 'false'));

        // Konversi tanggal ke format database
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Converted dates: startDate={$startDate}, endDate={$endDate}");

        // Step 1: Ambil semua model_no dari planning_production
        $modelQuery = $this->db->table('planning_production')
            ->select('model_no, class')
            ->distinct();

        $planningData = $modelQuery->get()->getResultArray();
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($planningData) . " unique model_no combinations");

        // Hasil akhir
        $result = [];

        // Step 2: Untuk setiap model_no dan class, ambil BOM items yang MATCH
        foreach ($planningData as $planning) {
            $currentModelNo = $planning['model_no'];
            $modelClass = $planning['class'];

            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing model_no: {$currentModelNo}, class: {$modelClass}");

            // PENTING: Relasi yang benar adalah model_no dan h_class (bukan class)
            // planning_production.class -> master_bom.h_class (class model)
            // master_bom.class adalah class part_no, bukan class model
            $bomQuery = $this->db->table('master_bom')
                ->select('master_bom.part_no, master_bom.description, master_bom.h_class, master_bom.class, master_bom.qty_assy')
                ->where('master_bom.model_no', $currentModelNo)
                ->where('master_bom.h_class', $modelClass); // Gunakan h_class untuk join dengan planning_production.class

            if ($hClass) {
                $bomQuery->where('master_bom.h_class', $hClass);
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Added h_class filter: {$hClass}");
            }

            if ($class) {
                $bomQuery->where('master_bom.class', $class);
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Added class filter: {$class}");
            }

            $bomItems = $bomQuery->get()->getResultArray();
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($bomItems) . " BOM items for model_no: {$currentModelNo}");

            // Step 3: Untuk setiap part_no, hitung data harian
            foreach ($bomItems as $bomItem) {
                $currentPartNo = $bomItem['part_no'];
                $partHClass = $bomItem['h_class'];
                $partClass = $bomItem['class'];
                $qtyAssy = (float)$bomItem['qty_assy'];
                
                // Filter berdasarkan part_no jika parameter diberikan
                if ($partNo && $currentPartNo != $partNo) {
                    continue; // Skip jika tidak sesuai dengan filter part_no
                }

                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing part_no: {$currentPartNo}, h_class: {$partHClass}, class: {$partClass}, qty_assy: {$qtyAssy}");

                // Step 3.1: Ambil begin_stock dari stock_material
                $beginStock = $this->getBeginStock($currentPartNo, $partClass);
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Begin stock for part_no {$currentPartNo}: {$beginStock}");

                // Step 3.2: Hitung data harian dengan rumus yang benar (menggunakan logika hardcode test)
                $dailyData = $this->calculateDailyDataFixed($currentModelNo, $modelClass, $currentPartNo, $partClass, $qtyAssy, $startDate, $endDate);
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Daily data calculated for {$currentPartNo}: " . json_encode($dailyData));

                // Step 3.3: Hitung stock plan dan stock act secara kumulatif
                $stockPlan = $beginStock;
                $stockAct = $beginStock;

                $currentDate = strtotime($startDate);
                $endDateTimestamp = strtotime($endDate);

                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    
                    if (isset($dailyData[$dateKey])) {
                        // Hitung stock plan: begin_stock - use_plan + eta (kumulatif)
                        $stockPlan = $stockPlan - $dailyData[$dateKey]['use_plan'] + $dailyData[$dateKey]['eta'];
                        $dailyData[$dateKey]['stock_plan'] = $stockPlan;
                        
                        // Hitung stock act: begin_stock - use_act + eta (kumulatif)
                        $stockAct = $stockAct - $dailyData[$dateKey]['use_act'] + $dailyData[$dateKey]['eta'];
                        $dailyData[$dateKey]['stock_act'] = $stockAct;
                        
                        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Stock for {$dateKey}: Plan={$stockPlan}, Act={$stockAct}");
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                }

                // Step 3.4: Buat item result
                $item = [
                    'model_no' => $currentModelNo,
                    'h_class' => $partHClass,
                    'part_no' => $currentPartNo,
                    'description' => $bomItem['description'],
                    'class' => $partClass,
                    'begin_stock' => $beginStock,
                    'daily_data' => $dailyData
                ];

                // Step 3.5: Filter minus only jika diperlukan
                if ($minusOnly) {
                    $hasNegative = false;
                    foreach ($dailyData as $data) {
                        if ($data['stock_plan'] < 0 || $data['stock_act'] < 0) {
                            $hasNegative = true;
                            break;
                        }
                    }
                    if (!$hasNegative) {
                        continue; // Skip item ini jika tidak ada nilai negatif
                    }
                }

                $result[] = $item;
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Added item to result: {$currentModelNo} - {$partNo}");
            }
        }

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Final result count: " . count($result));
        return $result;
    }

    private function getBeginStock($partNo, $class)
    {
        $stock = $this->db->table('stock_material')
            ->select('beginning')
            ->where('part_no', $partNo)
            ->where('class', $class)
            ->get()
            ->getRowArray();

        return $stock ? (int)$stock['beginning'] : 0;
    }

    private function calculateDailyDataFixed($modelNo, $modelClass, $partNo, $partClass, $qtyAssy, $startDate, $endDate)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - calculateDailyDataFixed called for model_no: {$modelNo}, part_no: {$partNo}, qty_assy: {$qtyAssy}");
        
        $dailyData = [];

        // FIXED: Menggunakan logika yang sama dengan hardcode test yang sudah terbukti benar
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Ambil data planning_production untuk model ini
        $planningData = $this->db->table('planning_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();
            
        // Ambil data actual_production untuk model ini
        $actualData = $this->db->table('actual_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found planning data: " . ($planningData ? 'YES' : 'NO'));
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found actual data: " . ($actualData ? 'YES' : 'NO'));

        // Inisialisasi dan hitung data harian
        while ($currentDate <= $endTimestamp) {
            $dateKey = date('Y-m-d', $currentDate);
            $day = date('j', $currentDate); // Ambil hari (1-31)
            $dayColumn = 'day_' . $day;
            
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing date: {$dateKey} (day {$day})");

            // Hitung use_plan: planning_production × qty_assy
            $planValue = 0;
            if ($planningData && isset($planningData[$dayColumn])) {
                $planValue = (float)$planningData[$dayColumn];
            }
            $usePlan = $planValue * (float)$qtyAssy;
            
            // Hitung use_act: actual_production × qty_assy
            $actualValue = 0;
            if ($actualData && isset($actualData[$dayColumn])) {
                $actualValue = (float)$actualData[$dayColumn];
            }
            $useAct = $actualValue * (float)$qtyAssy;
            
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Day {$day}: Plan={$planValue} × {$qtyAssy} = {$usePlan}, Act={$actualValue} × {$qtyAssy} = {$useAct}");

            // Hitung ETA dan INV_NO dari shipment_schedule
            $eta = 0;
            $invNo = '';
            
            try {
                $shipmentData = $this->db->table('shipment_schedule')
                    ->where('item_no', $partNo)
                    ->where('class', $partClass)
                    ->where('eta_meina', $dateKey)
                    ->get()
                    ->getRowArray();
                
                if ($shipmentData) {
                    $eta = (float)$shipmentData['sch_qty'];
                    $invNo = $shipmentData['inv_no'];
                    log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found shipment for {$dateKey}: ETA={$eta}, INV={$invNo}");
                }
            } catch (\Exception $e) {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Shipment query error for {$dateKey}: " . $e->getMessage());
            }

            $dailyData[$dateKey] = [
                'use_plan' => $usePlan,
                'use_act' => $useAct,
                'eta' => $eta,
                'inv_no' => $invNo,
                'stock_plan' => 0, // Will be calculated later
                'stock_act' => 0   // Will be calculated later
            ];

            $currentDate = strtotime('+1 day', $currentDate);
        }

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Calculated daily data for " . count($dailyData) . " days using hardcode test logic");
        
        return $dailyData;
    }

    public function getAvailableParts($search = null, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        // Base query untuk mendapatkan part_no yang unik
        $query = $this->db->table('master_bom')
            ->select('part_no')
            ->distinct();
        
        // Tambahkan kondisi pencarian jika ada
        if ($search) {
            $query->like('part_no', $search);
        }
        
        // Hitung total record untuk pagination
        $countQuery = clone $query;
        $totalCount = $countQuery->countAllResults();
        
        // Ambil data dengan limit dan offset
        $parts = $query->orderBy('part_no', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
        
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found {$totalCount} parts matching search '{$search}', returning page {$page} with {$limit} items");
        
        return [
            'parts' => $parts,
            'total_count' => $totalCount
        ];
    }
    
    public function getAvailableModels()
    {
        $models = $this->db->table('planning_production')
            ->select('model_no')
            ->distinct()
            ->orderBy('model_no', 'ASC')
            ->get()
            ->getResultArray();

        return $models;
    }

    public function getAvailableHClasses()
    {
        $hClasses = $this->db->table('master_bom')
            ->select('h_class')
            ->distinct()
            ->orderBy('h_class', 'ASC')
            ->get()
            ->getResultArray();

        return $hClasses;
    }

    public function getAvailableClasses()
    {
        $classes = $this->db->table('master_bom')
            ->select('class')
            ->distinct()
            ->orderBy('class', 'ASC')
            ->get()
            ->getResultArray();

        return $classes;
    }
}
