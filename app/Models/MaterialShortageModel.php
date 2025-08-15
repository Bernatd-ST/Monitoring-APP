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

        // Step 1: Ambil semua model_no dari planning_production (atau filter berdasarkan modelNo jika ada)
        $modelQuery = $this->db->table('planning_production')
            ->select('model_no, class')
            ->distinct();

        if ($modelNo) {
            $modelQuery->where('model_no', $modelNo);
        }

        $models = $modelQuery->get()->getResultArray();
        log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($models) . " unique model_no combinations");

        // Hasil akhir
        $result = [];

        // Step 2: Untuk setiap model_no, ambil part_no dari master_bom
        foreach ($models as $model) {
            $modelNo = $model['model_no'];
            $modelClass = $model['class'];

            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing model_no: {$modelNo}, class: {$modelClass}");

            $bomQuery = $this->db->table('master_bom')
                ->select('master_bom.part_no, master_bom.description, master_bom.h_class, master_bom.class, master_bom.qty_assy')
                ->where('master_bom.model_no', $modelNo);

            if ($hClass) {
                $bomQuery->where('master_bom.h_class', $hClass);
            }

            if ($class) {
                $bomQuery->where('master_bom.class', $class);
            }

            $bomItems = $bomQuery->get()->getResultArray();
            log_message('debug', "MATERIAL_SHORTAGE_MODEL - Found " . count($bomItems) . " BOM items for model_no: {$modelNo}");

            // Step 3: Untuk setiap part_no, hitung data harian
            foreach ($bomItems as $bomItem) {
                $partNo = $bomItem['part_no'];
                $partHClass = $bomItem['h_class'];
                $partClass = $bomItem['class'];
                $qtyAssy = $bomItem['qty_assy'];

                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Processing part_no: {$partNo}, h_class: {$partHClass}, class: {$partClass}, qty_assy: {$qtyAssy}");

                // Step 3.1: Ambil begin_stock dari stock_material
                $beginStock = $this->getBeginStock($partNo, $partClass);
                log_message('debug', "MATERIAL_SHORTAGE_MODEL - Begin stock for part_no {$partNo}: {$beginStock}");

                // Step 3.2: Hitung data harian
                $dailyData = $this->calculateDailyData($modelNo, $modelClass, $partNo, $partClass, $qtyAssy, $startDate, $endDate);

                // Step 3.3: Hitung stock plan dan stock act secara kumulatif
                $stockPlan = $beginStock;
                $stockAct = $beginStock;

                $currentDate = strtotime($startDate);
                $endDateTimestamp = strtotime($endDate);

                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);

                    // Inisialisasi jika belum ada
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

                    // Hitung stock plan: stock sebelumnya - use plan + eta
                    $stockPlan = $stockPlan - $dailyData[$dateKey]['use_plan'] + $dailyData[$dateKey]['eta'];
                    $dailyData[$dateKey]['stock_plan'] = $stockPlan;

                    // Hitung stock act: stock sebelumnya - use act + eta
                    $stockAct = $stockAct - $dailyData[$dateKey]['use_act'] + $dailyData[$dateKey]['eta'];
                    $dailyData[$dateKey]['stock_act'] = $stockAct;

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
                        continue; // Skip jika tidak ada nilai negatif dan minusOnly = true
                    }
                }

                // Step 3.5: Tambahkan ke hasil
                $result[] = [
                    'model_no' => $modelNo,
                    'h_class' => $partHClass,
                    'part_no' => $partNo,
                    'description' => $bomItem['description'],
                    'class' => $partClass,
                    'begin_stock' => $beginStock,
                    'daily_data' => $dailyData
                ];
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

    private function calculateUsePlan($modelNo, $modelClass, $partNo, $qtyAssy, $startDate, $endDate, &$dailyData)
    {
        // Ambil data planning_production
        $planningData = $this->db->table('planning_production')
            ->where('model_no', $modelNo)
            ->where('class', $modelClass)
            ->get()
            ->getRowArray();

        if (!$planningData) {
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
                $usePlan = $planValue * $qtyAssy;

                $dateKey = date('Y-m-d', $currentDate);
                $dailyData[$dateKey]['use_plan'] = $usePlan;
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
                $useAct = $actValue * $qtyAssy;

                $dateKey = date('Y-m-d', $currentDate);
                $dailyData[$dateKey]['use_act'] = $useAct;
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

        foreach ($shipmentData as $shipment) {
            $etaDate = $shipment['eta_meina'];
            $invNo = $shipment['inv_no'];
            $schQty = (float)$shipment['sch_qty'];

            if (isset($dailyData[$etaDate])) {
                $dailyData[$etaDate]['eta'] += $schQty;
                
                // Jika sudah ada inv_no, tambahkan dengan koma
                if (!empty($dailyData[$etaDate]['inv_no'])) {
                    $dailyData[$etaDate]['inv_no'] .= ', ' . $invNo;
                } else {
                    $dailyData[$etaDate]['inv_no'] = $invNo;
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
}
