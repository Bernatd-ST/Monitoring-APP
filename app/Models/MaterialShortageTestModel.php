<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialShortageTestModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Hardcode test untuk Material Shortage Report
     * Model: A001TGA391AJ
     * Part: A834D08401
     */
    public function testHardcodeData($startDate = '2025-08-01', $endDate = '2025-08-03')
    {
        echo "=== HARDCODE TEST MATERIAL SHORTAGE ===\n";
        echo "Model: A001TGA391AJ\n";
        echo "Part: A834D08401\n";
        echo "Date Range: {$startDate} to {$endDate}\n\n";

        $testModelNo = 'A001TGA391AJ';
        $testPartNo = 'A834D08401';

        // Step 1: Cek planning_production untuk model ini
        echo "1. CHECKING PLANNING_PRODUCTION:\n";
        $planningData = $this->db->table('planning_production')
            ->where('model_no', $testModelNo)
            ->get()
            ->getResultArray();

        if (empty($planningData)) {
            echo "   ❌ No planning data found for model: {$testModelNo}\n";
            return;
        }

        foreach ($planningData as $planning) {
            echo "   ✅ Found: Model={$planning['model_no']}, Class={$planning['class']}\n";
            echo "      Day_1: " . ($planning['day_1'] ?? 'NULL') . "\n";
            echo "      Day_2: " . ($planning['day_2'] ?? 'NULL') . "\n";
            echo "      Day_3: " . ($planning['day_3'] ?? 'NULL') . "\n";
        }

        $modelClass = $planningData[0]['class'];
        echo "\n";

        // Step 2: Cek master_bom untuk relasi yang benar
        echo "2. CHECKING MASTER_BOM RELATIONSHIP:\n";
        $bomData = $this->db->table('master_bom')
            ->where('model_no', $testModelNo)
            ->where('h_class', $modelClass) // Relasi yang benar: planning.class -> bom.h_class
            ->where('part_no', $testPartNo)
            ->get()
            ->getResultArray();

        if (empty($bomData)) {
            echo "   ❌ No BOM data found for model: {$testModelNo}, h_class: {$modelClass}, part: {$testPartNo}\n";
            
            // Cek apakah ada BOM untuk model ini tanpa filter part
            $allBomForModel = $this->db->table('master_bom')
                ->where('model_no', $testModelNo)
                ->where('h_class', $modelClass)
                ->get()
                ->getResultArray();
            
            echo "   📋 All BOM for this model+h_class: " . count($allBomForModel) . " parts\n";
            foreach (array_slice($allBomForModel, 0, 3) as $bom) {
                echo "      - Part: {$bom['part_no']}, H_Class: {$bom['h_class']}, Class: {$bom['class']}, Qty: {$bom['qty_assy']}\n";
            }
            return;
        }

        $bomItem = $bomData[0];
        echo "   ✅ Found BOM: Part={$bomItem['part_no']}, H_Class={$bomItem['h_class']}, Class={$bomItem['class']}, Qty_Assy={$bomItem['qty_assy']}\n\n";

        // Step 3: Cek stock_material
        echo "3. CHECKING STOCK_MATERIAL:\n";
        $stockData = $this->db->table('stock_material')
            ->where('part_no', $testPartNo)
            ->get()
            ->getRowArray();

        $beginStock = $stockData ? $stockData['beginning'] : 0;
        echo "   Begin Stock for {$testPartNo}: {$beginStock}\n\n";

        // Step 4: Hitung data harian
        echo "4. CALCULATING DAILY DATA:\n";
        $dailyData = [];

        // Inisialisasi tanggal
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        while ($currentDate <= $endTimestamp) {
            $dateKey = date('Y-m-d', $currentDate);
            $day = date('j', $currentDate); // Ambil hari (1-31)
            
            echo "   Processing date: {$dateKey} (day {$day})\n";

            // Step 4.1: Hitung use_plan
            $dayColumn = 'day_' . $day;
            $planValue = isset($planningData[0][$dayColumn]) ? (float)$planningData[0][$dayColumn] : 0;
            $usePlan = $planValue * (float)$bomItem['qty_assy'];
            
            echo "      Use Plan: {$planValue} × {$bomItem['qty_assy']} = {$usePlan}\n";

            // Step 4.2: Hitung use_act (dari actual_production)
            $actualData = $this->db->table('actual_production')
                ->where('model_no', $testModelNo)
                ->where('class', $modelClass)
                ->get()
                ->getRowArray();

            $actualValue = 0;
            if ($actualData && isset($actualData[$dayColumn])) {
                $actualValue = (float)$actualData[$dayColumn];
            }
            $useAct = $actualValue * (float)$bomItem['qty_assy'];
            
            echo "      Use Act: {$actualValue} × {$bomItem['qty_assy']} = {$useAct}\n";

            // Step 4.3: Cek struktur tabel shipment_schedule terlebih dahulu
            echo "      Checking shipment_schedule structure...\n";
        
            // Cek apakah tabel shipment_schedule ada dan strukturnya
            try {
                $shipmentSample = $this->db->table('shipment_schedule')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($shipmentSample) {
                    echo "      Shipment table columns: " . implode(', ', array_keys($shipmentSample)) . "\n";
                    
                    // Cari kolom yang mirip dengan part_no
                    $partColumn = null;
                    $etaColumn = null;
                    $qtyColumn = null;
                    $invColumn = null;
                    
                    foreach (array_keys($shipmentSample) as $column) {
                        if (stripos($column, 'part') !== false) $partColumn = $column;
                        if (stripos($column, 'eta') !== false || stripos($column, 'date') !== false) $etaColumn = $column;
                        if (stripos($column, 'qty') !== false || stripos($column, 'sch') !== false) $qtyColumn = $column;
                        if (stripos($column, 'inv') !== false) $invColumn = $column;
                    }
                    
                    echo "      Detected columns - Part: {$partColumn}, ETA: {$etaColumn}, Qty: {$qtyColumn}, Inv: {$invColumn}\n";
                    
                    // Gunakan kolom yang terdeteksi
                    if ($partColumn && $etaColumn) {
                        $shipmentData = $this->db->table('shipment_schedule')
                            ->where($partColumn, $testPartNo)
                            ->where($etaColumn, $dateKey)
                            ->get()
                            ->getRowArray();
                        
                        $eta = ($shipmentData && $qtyColumn) ? (float)$shipmentData[$qtyColumn] : 0;
                        $invNo = ($shipmentData && $invColumn) ? $shipmentData[$invColumn] : '';
                    } else {
                        echo "      ⚠️  Could not find matching columns, using default values\n";
                        $eta = 0;
                        $invNo = '';
                    }
                } else {
                    echo "      ⚠️  Shipment table is empty, using default values\n";
                    $eta = 0;
                    $invNo = '';
                }
            } catch (\Exception $e) {
                echo "      ❌ Error accessing shipment_schedule: " . $e->getMessage() . "\n";
                $eta = 0;
                $invNo = '';
            }
            
            echo "      ETA: {$eta}, INV_NO: {$invNo}\n";

            // Step 4.4: Hitung stock_plan dan stock_act
            $stockPlan = $beginStock - $usePlan + $eta;
            $stockAct = $beginStock - $useAct + $eta;
            
            echo "      Stock Plan: {$beginStock} - {$usePlan} + {$eta} = {$stockPlan}\n";
            echo "      Stock Act: {$beginStock} - {$useAct} + {$eta} = {$stockAct}\n";

            $dailyData[$dateKey] = [
                'use_plan' => $usePlan,
                'use_act' => $useAct,
                'eta' => $eta,
                'inv_no' => $invNo,
                'stock_plan' => $stockPlan,
                'stock_act' => $stockAct
            ];

            echo "\n";
            $currentDate = strtotime('+1 day', $currentDate);
        }

        // Step 5: Format hasil akhir
        echo "5. FINAL RESULT:\n";
        $result = [
            'model_no' => $testModelNo,
            'h_class' => $bomItem['h_class'],
            'part_no' => $testPartNo,
            'description' => $bomItem['description'],
            'class' => $bomItem['class'],
            'begin_stock' => $beginStock,
            'daily_data' => $dailyData
        ];

        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "\n\n=== TEST COMPLETE ===\n";

        return $result;
    }
}
