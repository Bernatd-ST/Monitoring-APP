<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialShortageModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getMaterialShortageData($startDate, $endDate, $modelNo = null, $hClass = null, $class = null, $minusOnly = false)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Getting data with params: startDate={$startDate}, endDate={$endDate}, modelNo={$modelNo}, hClass={$hClass}, class={$class}, minusOnly=" . ($minusOnly ? 'true' : 'false'));

        // Konversi tanggal ke format database
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Converted dates: startDate={$startDate}, endDate={$endDate}");

        // Step 1: Ambil semua model_no dari planning_production (atau filter berdasarkan modelNo jika ada)
        $modelQuery = $this->db->table('planning_production')
            ->select('model_no, class')
            ->distinct();

        if ($modelNo) {
            $modelQuery->where('model_no', $modelNo);
        }

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
                $partNo = $bomItem['part_no'];
                $partHClass = $bomItem['h_class'];
                $partClass = $bomItem['class'];
                $qtyAssy = (float)$bomItem['qty_assy'];

                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing part_no: {$partNo}, h_class: {$partHClass}, class: {$partClass}, qty_assy: {$qtyAssy}");

                // Step 3.1: Ambil begin_stock dari stock_material
                $beginStock = $this->getBeginStock($partNo, $partClass);
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Begin stock for part_no {$partNo}: {$beginStock}");

                // Step 3.2: Hitung data harian dengan rumus yang benar (menggunakan logika hardcode test)
                $dailyData = $this->calculateDailyDataFixed($currentModelNo, $modelClass, $partNo, $partClass, $qtyAssy, $startDate, $endDate);
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Daily data calculated for {$partNo}: " . json_encode($dailyData));

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
                    'part_no' => $partNo,
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
        return $result;ndDate);

                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);

                    // Pastikan data untuk tanggal ini ada
                    if (!isset($dailyData[$dateKey])) {
                        $dailyData[$dateKey] = [
                            'use_plan' => 0,
                            'use_act' => 0,
                            'eta' => 0,
                            'inv_no' => '',
                            'stock_plan' => 0,
                            'stock_act' => 0
                        ];
                    }

                    // Hitung stock plan: begin_stock - use_plan + eta (kumulatif)
                    $stockPlan = $stockPlan - $dailyData[$dateKey]['use_plan'] + $dailyData[$dateKey]['eta'];
                    $dailyData[$dateKey]['stock_plan'] = $stockPlan;

                    // Hitung stock act: begin_stock - use_act + eta (kumulatif)
                    $stockAct = $stockAct - $dailyData[$dateKey]['use_act'] + $dailyData[$dateKey]['eta'];
                    $dailyData[$dateKey]['stock_act'] = $stockAct;

                    log_message('debug', "MATERIAL_SHORTAGE_MODEL - Stock calculation for {$dateKey}: stock_plan={$stockPlan}, stock_act={$stockAct}");

                    $currentDate = strtotime('+1 day', $currentDate);
                }

                // Step 3.4: Filter jika minusOnly dan tidak ada nilai negatif
                $hasNegativeStock = false;
                if ($minusOnly) {
                    foreach ($dailyData as $data) {
                        if ($data['stock_plan'] < 0 || $data['stock_act'] < 0) {
                            $hasNegativeStock = true;
                            break;
                        }
                    }
                    
                    if (!$hasNegativeStock) {
                        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Skipping {$partNo} because no negative stock and minusOnly=true");
                        continue; // Skip jika tidak ada nilai negatif dan minusOnly = true
                    }
                }

                // Step 3.5: Tambahkan ke hasil
                $result[] = [
                    'model_no' => $currentModelNo,
                    'h_class' => $partHClass,
                    'part_no' => $partNo,
                    'description' => $bomItem['description'],
                    'class' => $partClass,
                    'begin_stock' => $beginStock,
                    'daily_data' => $dailyData
                ];
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Added result for {$partNo} with " . count($dailyData) . " daily entries");
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

    private function calculateDailyData($modelNo, $modelClass, $partNo, $partClass, $qtyAssy, $startDate, $endDate)
    {
        $dailyData = [];

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Inisialisasi array untuk setiap tanggal
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $dateKey = date('Y-m-d', $currentDate);
            $dailyData[$dateKey] = [
                'use_plan' => 0,
                'use_act' => 0,
                'eta' => 0,
                'inv_no' => '',
                'stock_plan' => 0,
                'stock_act' => 0
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }

        // Step 1: Ambil data planning_production untuk use_plan
        $this->calculateUsePlan($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, $dailyData);

        // Step 2: Ambil data actual_production untuk use_act
        $this->calculateUseAct($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, $dailyData);

        // Step 3: Ambil data shipment_schedule untuk eta dan inv_no
        $this->calculateEtaAndInvNo($partNo, $partClass, $startDate, $endDate, $dailyData);

        return $dailyData;
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
    
    // DEPRECATED: Keep old methods for compatibility but use new logic above
    private function calculateDailyDataFixedOLD($modelNo, $modelClass, $partNo, $partClass, $qtyAssy, $startDate, $endDate)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - calculateDailyDataFixedOLD called for model_no: {$modelNo}, part_no: {$partNo}, qty_assy: {$qtyAssy}");
        
        $dailyData = [];

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Inisialisasi array untuk setiap tanggal
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $dateKey = date('Y-m-d', $currentDate);
            $dailyData[$dateKey] = [
                'use_plan' => 0,
                'use_act' => 0,
                'eta' => 0,
                'inv_no' => '',
                'stock_plan' => 0,
                'stock_act' => 0
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Initialized daily data for " . count($dailyData) . " days");

        // Step 1: Hitung use_plan berdasarkan rumus: planning_production × qty_assy
        $this->calculateUsePlanFixed($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, $dailyData);

        // Step 2: Hitung use_act berdasarkan rumus: actual_production × qty_assy
        $this->calculateUseActFixed($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, $dailyData);

        // Step 3: Hitung eta_meina dan inv_no dari shipment_schedule
        $this->calculateEtaAndInvNoFixed($partNo, $partClass, $startDate, $endDate, $dailyData);

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Final daily data for {$partNo}: " . json_encode($dailyData));
        
        return $dailyData;
    }

    private function calculateUsePlan($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, &$dailyData)
    {
        // Ambil data planning_production
        $planningData = $this->db->table('planning_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        if (!$planningData) {
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - No planning data found for model_no: {$modelNo}, class: {$modelClass}");
            return;
        }

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Hitung use_plan untuk setiap tanggal
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $day = date('j', $currentDate); // Ambil tanggal (1-31)
            $dayColumn = 'day_' . $day;

            if (isset($planningData[$dayColumn]) && is_numeric($planningData[$dayColumn])) {
                $planValue = (float)$planningData[$dayColumn];
                $usePlan = $planValue * $qtyAssy; // Sesuai rumus: nilai dari planning_production × qty_assy dari master_bom

                $dateKey = date('Y-m-d', $currentDate);
                $dailyData[$dateKey]['use_plan'] = $usePlan;
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Calculated use_plan for {$dateKey}: {$planValue} × {$qtyAssy} = {$usePlan}");
            } else {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - No planning data for day {$day} or value not numeric");
            }

            $currentDate = strtotime('+1 day', $currentDate);
        }
    }

    private function calculateUseAct($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, &$dailyData)
    {
        // Ambil data actual_production
        $actualData = $this->db->table('actual_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        if (!$actualData) {
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - No actual data found for model_no: {$modelNo}, class: {$modelClass}");
            return;
        }

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Hitung use_act untuk setiap tanggal
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $day = date('j', $currentDate); // Ambil tanggal (1-31)
            $dayColumn = 'day_' . $day;

            if (isset($actualData[$dayColumn]) && is_numeric($actualData[$dayColumn])) {
                $actValue = (float)$actualData[$dayColumn];
                $useAct = $actValue * $qtyAssy; // Sesuai rumus: nilai dari actual_production × qty_assy dari master_bom

                $dateKey = date('Y-m-d', $currentDate);
                $dailyData[$dateKey]['use_act'] = $useAct;
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Calculated use_act for {$dateKey}: {$actValue} × {$qtyAssy} = {$useAct}");
            } else {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - No actual data for day {$day} or value not numeric");
            }

            $currentDate = strtotime('+1 day', $currentDate);
        }
    }

    private function calculateEtaAndInvNo($partNo, $partClass, $startDate, $endDate, &$dailyData)
    {
        // Ambil data shipment_schedule
        $shipmentData = $this->db->table('shipment_schedule')
            ->select('inv_no, sch_qty, eta_meina')
            ->where('item_no', $partNo)
            ->where('class', $partClass)
            ->where('eta_meina >=', $startDate)
            ->where('eta_meina <=', $endDate)
            ->get()
            ->getResultArray();
            
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($shipmentData) . " shipment records for part_no: {$partNo}, class: {$partClass}");

        foreach ($shipmentData as $shipment) {
            $etaDate = $shipment['eta_meina'];
            $invNo = $shipment['inv_no'];
            $schQty = (float)$shipment['sch_qty']; // Nilai eta_meina diambil dari sch_qty pada tabel shipment_schedule

            // Extract day from eta_meina (ignore month/year)
            $etaDay = (int)date('d', strtotime($etaDate));

            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing shipment: eta_date={$etaDate}, eta_day={$etaDay}, inv_no={$invNo}, sch_qty={$schQty}");

            // Match berdasarkan hari saja, abaikan bulan/tahun
            foreach ($dailyData as $dateKey => &$dayData) {
                $currentDay = (int)date('d', strtotime($dateKey));
                
                if ($currentDay === $etaDay) {
                    $dayData['eta'] += $schQty;
                    
                    // Jika sudah ada inv_no, tambahkan dengan koma
                    if (!empty($dayData['inv_no'])) {
                        $dayData['inv_no'] .= ', ' . $invNo;
                    } else {
                        $dayData['inv_no'] = $invNo;
                    }
                    
                    log_message('debug', "MATERIAL_SHORTAGE_MODEL - Updated eta for day {$currentDay} (date {$dateKey}): {$schQty}, total now: {$dayData['eta']}, inv_no: {$dayData['inv_no']}");
                }
            }
        }
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

    // FIXED CALCULATION FUNCTIONS - Implementasi rumus yang benar sesuai instruksi user
    
    private function calculateUsePlanFixed($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, &$dailyData)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - calculateUsePlanFixed for model_no: {$modelNo}, class: {$modelClass}");
        
        // Ambil data planning_production
        $planningData = $this->db->table('planning_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        if (!$planningData) {
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - No planning data found for model_no: {$modelNo}, class: {$modelClass}");
            return;
        }

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found planning data: " . json_encode($planningData));

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Hitung use_plan untuk setiap tanggal berdasarkan rumus: planning_production × qty_assy
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $day = date('j', $currentDate); // Ambil tanggal (1-31)
            $dayColumn = 'day_' . $day;
            $dateKey = date('Y-m-d', $currentDate);

            if (isset($planningData[$dayColumn]) && is_numeric($planningData[$dayColumn])) {
                $planValue = (float)$planningData[$dayColumn];
                $usePlan = $planValue * $qtyAssy; // Rumus: nilai dari planning_production × qty_assy dari master_bom

                $dailyData[$dateKey]['use_plan'] = $usePlan;
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Calculated use_plan for {$dateKey} (day {$day}): {$planValue} × {$qtyAssy} = {$usePlan}");
            } else {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - No planning data for day {$day} or value not numeric");
            }

            $currentDate = strtotime('+1 day', $currentDate);
        }
    }
    
    private function calculateUseActFixed($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, &$dailyData)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - calculateUseActFixed for model_no: {$modelNo}, class: {$modelClass}");
        
        // Ambil data actual_production
        $actualData = $this->db->table('actual_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        if (!$actualData) {
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - No actual data found for model_no: {$modelNo}, class: {$modelClass}");
            return;
        }

        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found actual data: " . json_encode($actualData));

        // Konversi tanggal ke timestamp
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        // Hitung use_act untuk setiap tanggal berdasarkan rumus: actual_production × qty_assy
        $currentDate = $startTimestamp;
        while ($currentDate <= $endTimestamp) {
            $day = date('j', $currentDate); // Ambil tanggal (1-31)
            $dayColumn = 'day_' . $day;
            $dateKey = date('Y-m-d', $currentDate);

            if (isset($actualData[$dayColumn]) && is_numeric($actualData[$dayColumn])) {
                $actValue = (float)$actualData[$dayColumn];
                $useAct = $actValue * $qtyAssy; // Rumus: nilai dari actual_production × qty_assy dari master_bom

                $dailyData[$dateKey]['use_act'] = $useAct;
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Calculated use_act for {$dateKey} (day {$day}): {$actValue} × {$qtyAssy} = {$useAct}");
            } else {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - No actual data for day {$day} or value not numeric");
            }

            $currentDate = strtotime('+1 day', $currentDate);
        }
    }
    
    private function calculateEtaAndInvNoFixed($partNo, $partClass, $startDate, $endDate, &$dailyData)
    {
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - calculateEtaAndInvNoFixed for part_no: {$partNo}, class: {$partClass}");
        
        // Ambil data shipment_schedule berdasarkan tanggal eta_meina
        // Berdasarkan hardcode test: kolom part adalah 'item_no', bukan 'part_no'
        $shipmentData = $this->db->table('shipment_schedule')
            ->select('inv_no, sch_qty, eta_meina')
            ->where('item_no', $partNo) // Sudah benar: menggunakan item_no
            ->where('class', $partClass)
            ->where('eta_meina >=', $startDate)
            ->where('eta_meina <=', $endDate)
            ->get()
            ->getResultArray();
            
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($shipmentData) . " shipment records for part_no: {$partNo}, class: {$partClass}");

        foreach ($shipmentData as $shipment) {
            $etaDate = $shipment['eta_meina'];
            $invNo = $shipment['inv_no'];
            $schQty = (float)$shipment['sch_qty']; // Nilai eta_meina diambil dari sch_qty pada tabel shipment_schedule

            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing shipment: eta_date={$etaDate}, inv_no={$invNo}, sch_qty={$schQty}");

            if (isset($dailyData[$etaDate])) {
                // Tambahkan eta (dari sch_qty)
                $dailyData[$etaDate]['eta'] += $schQty;
                
                // Tambahkan inv_no (jika sudah ada, gabungkan dengan koma)
                if (!empty($dailyData[$etaDate]['inv_no'])) {
                    $dailyData[$etaDate]['inv_no'] .= ', ' . $invNo;
                } else {
                    $dailyData[$etaDate]['inv_no'] = $invNo;
                }
                
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Updated eta for {$etaDate}: +{$schQty}, total now: {$dailyData[$etaDate]['eta']}, inv_no: {$dailyData[$etaDate]['inv_no']}");
            } else {
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Date {$etaDate} not in range, skipping");
            }
        }
    }
}
