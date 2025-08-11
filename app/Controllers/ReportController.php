<?php

namespace App\Controllers;

use App\Models\SalesModel;
use App\Models\ActualSalesModel;
use App\Models\PlanningModel;
use App\Models\ActualModel;
use App\Models\FinishGoodModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends BaseController
{
    protected $salesModel;
    protected $actualSalesModel;
    protected $planningModel;
    protected $actualModel;
    protected $finishGoodModel;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
        $this->actualSalesModel = new ActualSalesModel();
        $this->planningModel = new PlanningModel();
        $this->actualModel = new ActualModel();
        $this->finishGoodModel = new FinishGoodModel();
    }

    public function index()
    {
        // Redirect to delivery shortage report as default
        return redirect()->to('/admin/report/delivery-shortage');
    }

    public function deliveryShortage()
    {
        $data = [
            'title' => 'Delivery Shortage Report',
        ];

        return view('admin/report/delivery_shortage', $data);
    }

    public function getDeliveryShortageData()
    {
        $request = $this->request;
        
        // Log raw POST data untuk debugging
        $rawPostData = $request->getPost();
        log_message('debug', "CADILA3 - Raw POST data: " . json_encode($rawPostData));
        
        $startDate = $request->getPost('start_date');
        $endDate = $request->getPost('end_date');
        $modelNo = $request->getPost('model_no');
        $class = $request->getPost('class');
        $minusOnly = $request->getPost('minus_only') === 'true';
        
        // Pastikan model_no benar-benar kosong jika dikirim sebagai string kosong
        if ($modelNo === '') {
            $modelNo = null;
            log_message('debug', "CADILA3 - Model_no kosong, diubah menjadi null");
        }
        
        // Tambahan log untuk debugging
        log_message('debug', "CADILA3 - Model_no sebelum diproses: '" . $modelNo . "'");
        
        // Cek apakah ada hardcoded value atau override yang tidak diinginkan
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        log_message('debug', "CADILA3 - Backtrace: " . json_encode(array_map(function($item) {
            return isset($item['function']) ? $item['function'] : 'unknown';
        }, $backtrace)));
        
        // Pastikan tidak ada override dari tempat lain
        // Hapus baris ini setelah debugging selesai
        // $modelNo = 'A383A622RJ'; // Cek apakah ini yang terjadi di suatu tempat

        // Log dengan tag khusus untuk memudahkan pencarian
        log_message('debug', "BERNATD - Start Date: {$startDate}, End Date: {$endDate}, Model: {$modelNo}, Class: {$class}, Minus Only: " . ($minusOnly ? 'true' : 'false'));

        // Debug: Check if tables have data
        $db = \Config\Database::connect();
        $salesCount = $db->table('sales')->countAllResults();
        log_message('debug', "Sales table has {$salesCount} records");

        // Check if finish_good table has data
        $finishGoodCount = $db->table('finish_good')->countAllResults();
        log_message('debug', "finish_good table has {$finishGoodCount} records");

        // Check if actual_sales table has data
        $actualSalesCount = $db->table('actual_sales')->countAllResults();
        log_message('debug', "actual_sales table has {$actualSalesCount} records");

        // Check if planning_production table has data
        $planningProductionCount = $db->table('planning_production')->countAllResults();
        log_message('debug', "planning_production table has {$planningProductionCount} records");

        // Check if actual_production table has data
        $actualProductionCount = $db->table('actual_production')->countAllResults();
        log_message('debug', "actual_production table has {$actualProductionCount} records");

        if (empty($startDate) || empty($endDate)) {
            return $this->response->setJSON(['error' => 'Start date and end date are required']);
        }

        try {
            $startDateObj = new \DateTime($startDate);
            $endDateObj = new \DateTime($endDate);

            if ($startDateObj > $endDateObj) {
                return $this->response->setJSON(['error' => 'Start date cannot be after end date']);
            }

            $interval = $startDateObj->diff($endDateObj);
            $daysDiff = $interval->days + 1; // Include both start and end dates

            if ($daysDiff > 31) {
                return $this->response->setJSON(['error' => 'Date range cannot exceed 31 days']);
            }

            // Get unique model_no and class combinations
            $modelClassCombinations = $this->getUniqueModelClassCombinations($modelNo, $class);

            log_message('debug', 'Found ' . count($modelClassCombinations) . ' model/class combinations');
            log_message('debug', 'Filter model_no value: "' . $modelNo . '"');
            log_message('debug', 'SQL query used: ' . $db->getLastQuery());

            if (empty($modelClassCombinations)) {
                log_message('debug', 'No model/class combinations found, returning empty data');
                return $this->response->setJSON([
                    'data' => [],
                    'dates' => [],
                    'success' => true
                ]);
            }

            // Generate dates array
            $dates = [];
            $currentDate = clone $startDateObj;
            while ($currentDate <= $endDateObj) {
                $dates[] = $currentDate->format('j'); // Day number only (1-31)
                $currentDate->modify('+1 day');
            }

            $data = [];
            $processedCombos = []; // Track kombinasi yang sudah diproses untuk mencegah duplikasi

            foreach ($modelClassCombinations as $combo) {
                $comboModelNo = $combo['model_no'];
                $comboClass = $combo['class'];
                
                log_message('debug', "CADILA3 - Original filter model_no: '{$modelNo}', combo model_no: '{$comboModelNo}'");
                
                // Buat kunci unik untuk kombinasi model_no dan class
                $comboKey = $comboModelNo . '_' . $comboClass;
                
                // Skip jika kombinasi ini sudah diproses sebelumnya
                if (isset($processedCombos[$comboKey])) {
                    log_message('debug', "CADILA3 - Skipping duplicate model: {$comboModelNo}, class: {$comboClass}");
                    continue;
                }
                
                // Tandai kombinasi ini sebagai sudah diproses
                $processedCombos[$comboKey] = true;

                log_message('debug', "Processing model: {$comboModelNo}, class: {$comboClass}");

                // Get begin stock
                $beginStock = $this->getBeginStock($comboModelNo, $comboClass);
                log_message('debug', "Begin stock for model {$comboModelNo}, class {$comboClass}: {$beginStock}");

                // Get delivery plan and actual data
                $dlvPlan = $this->getDlvPlan($comboModelNo, $comboClass, $startDateObj, $endDateObj);
                log_message('debug', "Delivery plan for model {$comboModelNo}, class {$comboClass}: " . json_encode(array_slice($dlvPlan, 0, 5)) . "...");

                $dlvAct = $this->getDlvAct($comboModelNo, $comboClass, $startDateObj, $endDateObj);
                log_message('debug', "Delivery actual for model {$comboModelNo}, class {$comboClass}: " . json_encode(array_slice($dlvAct, 0, 5)) . "...");

                // Get production plan and actual data
                $prdPlan = $this->getPrdPlan($comboModelNo, $comboClass, $startDateObj, $endDateObj);
                log_message('debug', "Production plan for model {$comboModelNo}, class {$comboClass}: " . json_encode(array_slice($prdPlan, 0, 5)) . "...");

                $prdAct = $this->getPrdAct($comboModelNo, $comboClass, $startDateObj, $endDateObj);
                log_message('debug', "Production actual for model {$comboModelNo}, class {$comboClass}: " . json_encode(array_slice($prdAct, 0, 5)) . "...");

                // Calculate stock plan and actual
                $stockPlan = [];
                $stockAct = [];
                $currentStockPlan = $beginStock;
                $currentStockAct = $beginStock;

                $hasNegativeStock = false;

                for ($i = 0; $i < count($dates); $i++) {
                    $day = $dates[$i];

                    // Calculate stock plan
                    $currentStockPlan = $currentStockPlan - ($dlvPlan[$i] ?? 0) + ($prdPlan[$i] ?? 0);
                    $stockPlan[] = $currentStockPlan;

                    // Calculate stock actual
                    $currentStockAct = $currentStockAct - ($dlvAct[$i] ?? 0) + ($prdAct[$i] ?? 0);
                    $stockAct[] = $currentStockAct;

                    // Check if any stock is negative
                    if ($currentStockPlan < 0 || $currentStockAct < 0) {
                        $hasNegativeStock = true;
                    }
                }

                // Skip if minus_only is true and there's no negative stock
                if ($minusOnly && !$hasNegativeStock) {
                    continue;
                }

                $data[] = [
                    'model_no' => $comboModelNo, // Gunakan $comboModelNo untuk memastikan nilai model_no tetap ada
                    'class' => $comboClass, // Gunakan $comboClass untuk konsistensi
                    'begin_stock' => $beginStock,
                    'dlv_plan' => $dlvPlan,
                    'dlv_act' => $dlvAct,
                    'prd_plan' => $prdPlan,
                    'prd_act' => $prdAct,
                    'stock_plan' => $stockPlan,
                    'stock_act' => $stockAct
                ];
            }

            log_message('debug', 'Final data count before filtering: ' . count($data));
            
            // Filter data berdasarkan model_no jika parameter model_no tidak kosong
            if (!empty($modelNo)) {
                log_message('debug', "CADILA32 - Filtering data for model_no: '{$modelNo}'");
                
                // Simpan data original untuk logging
                $originalCount = count($data);
                
                // Filter array $data agar hanya berisi item dengan model_no yang sesuai
                $filteredData = array_filter($data, function($item) use ($modelNo) {
                    return $item['model_no'] === $modelNo;
                });
                
                // Reindex array setelah filtering
                $data = array_values($filteredData);
                
                log_message('debug', "CADILA32 - After filtering: {$originalCount} items reduced to " . count($data) . " items");
            }
            
            // Log untuk memeriksa apakah filter model_no bekerja dengan benar
            log_message('debug', "CADILA32 - Final data count after filtering: " . count($data));
            
            // Log beberapa data pertama untuk debugging
            if (count($data) > 0) {
                log_message('debug', "CADILA32 - First data item model_no: '{$data[0]['model_no']}'");
                log_message('debug', "CADILA32 - First data item class: '{$data[0]['class']}'");
            } else {
                log_message('debug', "CADILA32 - No data found after filtering for model_no: '{$modelNo}'");
            }

            return $this->response->setJSON([
                'data' => $data,
                'dates' => $dates,
                'success' => true
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getDeliveryShortageData: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    private function getUniqueModelClassCombinations($filterModelNo = null, $filterClass = null)
    {
        $db = \Config\Database::connect();
        
        // Log parameter yang diterima dengan detail
        log_message('debug', "CADILA3 - getUniqueModelClassCombinations called with filterModelNo: '" . $filterModelNo . "', type: " . gettype($filterModelNo));
        log_message('debug', "CADILA3 - filterModelNo empty check: " . (empty($filterModelNo) ? 'true' : 'false'));
        log_message('debug', "CADILA3 - filterModelNo null check: " . (is_null($filterModelNo) ? 'true' : 'false'));
        log_message('debug', "CADILA3 - filterModelNo === '' check: " . ($filterModelNo === '' ? 'true' : 'false'));

        // Start with a base query to get unique model_no and class combinations from sales table
        $query = $db->table('sales')
            ->select('model_no, class')
            ->distinct();

        // Apply filters if provided - dengan pengecekan yang lebih ketat
        if (!is_null($filterModelNo) && $filterModelNo !== '') {
            log_message('debug', "CADILA3 - Filtering by model_no: '{$filterModelNo}'");
            $query->where('model_no', $filterModelNo);
        } else {
            log_message('debug', "CADILA3 - No model_no filter applied, showing all models");
        }

        if (!empty($filterClass)) {
            log_message('debug', "Filtering by class: {$filterClass}");
            $query->where('class', $filterClass);
        }

        // Get the SQL query for debugging
        $sql = $query->getCompiledSelect();
        log_message('debug', "getUniqueModelClassCombinations SQL Query: {$sql}");

        $result = $query->get()->getResultArray();

        log_message('debug', 'getUniqueModelClassCombinations found ' . count($result) . ' combinations');
        if (count($result) > 0) {
            log_message('debug', 'First few combinations: ' . json_encode(array_slice($result, 0, 5)));
        }

        return $result;
    }

    private function getBeginStock($modelNo, $class)
    {
        log_message('debug', "getBeginStock called with modelNo: {$modelNo}, class: {$class}");
        
        $db = \Config\Database::connect();
        $query = $db->table('finish_good')
            ->select('end_bal')
            ->where('part_no', $modelNo)
            ->where('class', (int)$class)
            ->get();
        
        $result = $query->getRowArray();
        
        if ($result) {
            log_message('debug', "getBeginStock found end_bal: {$result['end_bal']}");
            return $result['end_bal'];
        } else {
            log_message('debug', "getBeginStock: No data found for model_no {$modelNo}, class {$class}. Returning 0.");
            return 0;
        }
    }

    private function getDlvPlan($modelNo, $class, $startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Debug log
        log_message('debug', "getDlvPlan called with modelNo: {$modelNo}, class: {$class}, startDate: {$startDate->format('Y-m-d')}, endDate: {$endDate->format('Y-m-d')}");

        $query = $db->table('sales')
            ->select('schedule_1, schedule_2, schedule_3, schedule_4, schedule_5, schedule_6, schedule_7, schedule_8, schedule_9, schedule_10, schedule_11, schedule_12, schedule_13, schedule_14, schedule_15, schedule_16, schedule_17, schedule_18, schedule_19, schedule_20, schedule_21, schedule_22, schedule_23, schedule_24, schedule_25, schedule_26, schedule_27, schedule_28, schedule_29, schedule_30, schedule_31')
            ->where('model_no', $modelNo)
            ->where('class', $class)
            ->get();

        $result = $query->getRowArray();

        // Debug log
        log_message('debug', "getDlvPlan result: " . ($result ? "Data found" : "No data found"));

        $dlvPlan = [];
        for ($i = 1; $i <= 31; $i++) {
            $dlvPlan[] = $result['schedule_' . $i] ?? 0;
        }

        return $dlvPlan;
    }

    private function getDlvAct($modelNo, $class, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        
        // Debug log
        log_message('debug', "getDlvAct called with modelNo: {$modelNo}, class: {$class}, startDate: {$startDate->format('Y-m-d')}, endDate: {$endDate->format('Y-m-d')}");
        
        $query = $db->table('actual_sales')
            ->select('shp_date, act_qty')
            ->where('model_no', $modelNo)
            ->where('class', (int)$class)
            ->where('shp_date >=', $startDate->format('Y-m-d'))
            ->where('shp_date <=', $endDate->format('Y-m-d'))
            ->get();
        
        $result = $query->getResultArray();
        
        // Debug log
        log_message('debug', "getDlvAct found " . count($result) . " records");
        
        $dlvAct = array_fill(0, 31, 0);
        foreach ($result as $row) {
            try {
                $day = intval(date('j', strtotime($row['shp_date'])));
                $dayIndex = $day - 1; // Konversi ke 0-based index
                if ($dayIndex >= 0 && $dayIndex < 31) {
                    $dlvAct[$dayIndex] = (int)$row['act_qty'];
                    log_message('debug', "getDlvAct: Added {$row['act_qty']} to day {$day} (index {$dayIndex})");
                }
            } catch (\Exception $e) {
                log_message('error', "Error processing date in getDlvAct: " . $e->getMessage());
            }
        }
        
        return $dlvAct;
    }

    private function getPrdPlan($modelNo, $class, $startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Debug log
        log_message('debug', "getPrdPlan called with modelNo: {$modelNo}, class: {$class}, startDate: {$startDate->format('Y-m-d')}, endDate: {$endDate->format('Y-m-d')}");

        $query = $db->table('planning_production')
            ->select('day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10, day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20, day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31')
            ->where('model_no', $modelNo)
            ->where('class', $class)
            ->get();

        $result = $query->getRowArray();

        // Debug log
        log_message('debug', "getPrdPlan result: " . ($result ? "Data found" : "No data found"));

        $prdPlan = [];
        for ($i = 1; $i <= 31; $i++) {
            $prdPlan[] = $result['day_' . $i] ?? 0;
        }

        return $prdPlan;
    }

    private function getPrdAct($modelNo, $class, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        
        // Debug log
        log_message('debug', "getPrdAct called with modelNo: {$modelNo}, class: {$class}, startDate: {$startDate->format('Y-m-d')}, endDate: {$endDate->format('Y-m-d')}");
        
        $query = $db->table('actual_production')
            ->select('day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10, day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20, day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31')
            ->where('model_no', $modelNo)
            ->where('class', $class)
            ->get();
        
        $result = $query->getRowArray();
        
        // Debug log
        log_message('debug', "getPrdAct result: " . ($result ? "Data found" : "No data found"));
        
        $prdAct = [];
        for ($i = 1; $i <= 31; $i++) {
            $prdAct[] = $result['day_' . $i] ?? 0;
        }
        
        return $prdAct;
    }

    public function exportDeliveryShortage()
    {
        // Get filter parameters from request
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $class = $this->request->getGet('class');
        $modelNo = $this->request->getGet('model_no'); // Ubah 'model' menjadi 'model_no' untuk konsistensi
        $minusOnly = $this->request->getGet('minus_only') === 'true';

        // Log dengan tag khusus untuk memudahkan pencarian
        log_message('debug', "[EXCEL_DEBUG] Export parameters - Start Date: {$startDate}, End Date: {$endDate}, Model: '{$modelNo}', Class: '{$class}', Minus Only: " . ($minusOnly ? 'true' : 'false'));

        // Parse dates to get day numbers
        $startDay = intval(date('j', strtotime($startDate)));
        $endDay = intval(date('j', strtotime($endDate)));
        $month = date('F', strtotime($startDate));
        $year = date('Y', strtotime($startDate));
        
        // Pastikan startDay dan endDay valid
        if ($startDay <= 0 || $endDay <= 0) {
            log_message('error', "[EXCEL_DEBUG] Invalid day values: startDay={$startDay}, endDay={$endDay}");
            return $this->response->setJSON(['error' => 'Invalid date range']);
        }
        
        log_message('debug', "[EXCEL_DEBUG] Parsed dates - startDay: {$startDay}, endDay: {$endDay}, month: {$month}, year: {$year}");

        // Get all unique model_no and class combinations
        $models = $this->getUniqueModelClassCombinations($modelNo, $class);

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Monitoring App')
            ->setLastModifiedBy('Monitoring App')
            ->setTitle('Delivery Shortage Report')
            ->setSubject('Delivery Shortage Report')
            ->setDescription('Delivery Shortage Report generated on ' . date('Y-m-d H:i:s'));

        // Add title
        log_message('debug', "[EXCEL_DEBUG] Menambahkan judul report");
        $sheet->setCellValue('A1', 'DELIVERY SHORTAGE REPORT');
        
        // Hitung jumlah kolom
        $lastColumnIndex = 4 + $endDay - $startDay + 1;
        log_message('debug', "[EXCEL_DEBUG] Menghitung jumlah kolom: 4 + {$endDay} - {$startDay} + 1 = {$lastColumnIndex}");
        
        // Dapatkan nama kolom terakhir
        $lastColumnName = $this->getColumnName($lastColumnIndex);
        log_message('debug', "[EXCEL_DEBUG] Nama kolom terakhir: {$lastColumnName}");
        
        // Merge cells untuk judul
        $mergeRange = "A1:{$lastColumnName}1";
        log_message('debug', "[EXCEL_DEBUG] Merge range untuk judul: {$mergeRange}");
        $sheet->mergeCells($mergeRange);
        
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add date filter info
        log_message('debug', "[EXCEL_DEBUG] Menambahkan info filter tanggal");
        $sheet->setCellValue('A2', 'Date Range: ' . date('d F Y', strtotime($startDate)) . ' - ' . date('d F Y', strtotime($endDate)));
        
        // Hitung range merge untuk filter tanggal
        $dateFilterMergeRange = "A2:{$lastColumnName}2";
        log_message('debug', "[EXCEL_DEBUG] Merge range untuk filter tanggal: {$dateFilterMergeRange}");
        $sheet->mergeCells($dateFilterMergeRange);
        
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add header row
        log_message('debug', "[EXCEL_DEBUG] Menambahkan header row");
        $sheet->setCellValue('A4', 'Model No');
        log_message('debug', "[EXCEL_DEBUG] Set cell A4 = 'Model No'");
        
        $sheet->setCellValue('B4', 'Class');
        log_message('debug', "[EXCEL_DEBUG] Set cell B4 = 'Class'");
        
        $sheet->setCellValue('C4', '');
        log_message('debug', "[EXCEL_DEBUG] Set cell C4 = ''");
        
        $sheet->setCellValue('D4', 'Begin Stock');
        log_message('debug', "[EXCEL_DEBUG] Set cell D4 = 'Begin Stock'");

        // Add date headers
        log_message('debug', "[EXCEL_DEBUG] Menambahkan date headers dari hari {$startDay} sampai {$endDay}");
        $col = 5;
        for ($day = $startDay; $day <= $endDay; $day++) {
            log_message('debug', "[EXCEL_DEBUG] Memproses header untuk hari {$day}, kolom index {$col}");
            $colName = $this->getColumnName($col);
            log_message('debug', "[EXCEL_DEBUG] Nama kolom untuk hari {$day}: {$colName}");
            
            $cellCoordinate = $colName . '4';
            log_message('debug', "[EXCEL_DEBUG] Cell coordinate untuk hari {$day}: {$cellCoordinate}");
            
            $sheet->setCellValue($cellCoordinate, $day);
            log_message('debug', "[EXCEL_DEBUG] Set cell {$cellCoordinate} = {$day}");
            
            $col++;
        }

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:' . $this->getColumnName(4 + $endDay - $startDay + 1) . '4')->applyFromArray($headerStyle);

        // Add data rows
        $row = 5;
        foreach ($models as $modelData) {
            $modelNo = $modelData['model_no'];
            $classValue = $modelData['class'];

            // Get begin stock from finish_good table
            $beginStock = $this->getBeginStock($modelNo, $classValue);

            // Calculate values for each day in the range
            $stockPlan = $beginStock;
            $stockAct = $beginStock;

            $dayValues = [];
            $hasNegativeStock = false;

            for ($day = $startDay; $day <= $endDay; $day++) {
                // Konversi $day menjadi objek DateTime untuk parameter startDate dan endDate
                $dayDate = \DateTime::createFromFormat('Y-m-d', date('Y-m-') . sprintf('%02d', $day));
                $dayIndex = $day - 1; // Konversi ke 0-based index untuk array
                log_message('debug', "[EXCEL_DEBUG] Memproses data untuk hari {$day}, index {$dayIndex}");
                
                // Get delivery plan from sales table - ambil nilai untuk hari tertentu saja
                log_message('debug', "[EXCEL_DEBUG] Memanggil getDlvPlan untuk hari {$day}");
                $dlvPlanArray = $this->getDlvPlan($modelNo, $classValue, $dayDate, $dayDate);
                $dlvPlan = isset($dlvPlanArray[$dayIndex]) ? (int)$dlvPlanArray[$dayIndex] : 0;
                log_message('debug', "[EXCEL_DEBUG] Nilai dlvPlan untuk hari {$day}: {$dlvPlan}");

                // Get delivery actual from actual_sales table - ambil nilai untuk hari tertentu saja
                log_message('debug', "[EXCEL_DEBUG] Memanggil getDlvAct untuk hari {$day}");
                $dlvActArray = $this->getDlvAct($modelNo, $classValue, $dayDate, $dayDate);
                $dlvAct = isset($dlvActArray[$dayIndex]) ? (int)$dlvActArray[$dayIndex] : 0;
                log_message('debug', "[EXCEL_DEBUG] Nilai dlvAct untuk hari {$day}: {$dlvAct}");

                // Get production plan from planning_production table - ambil nilai untuk hari tertentu saja
                log_message('debug', "[EXCEL_DEBUG] Memanggil getPrdPlan untuk hari {$day}");
                $prdPlanArray = $this->getPrdPlan($modelNo, $classValue, $dayDate, $dayDate);
                $prdPlan = isset($prdPlanArray[$dayIndex]) ? (int)$prdPlanArray[$dayIndex] : 0;
                log_message('debug', "[EXCEL_DEBUG] Nilai prdPlan untuk hari {$day}: {$prdPlan}");

                // Get production actual from actual_production table - ambil nilai untuk hari tertentu saja
                log_message('debug', "[EXCEL_DEBUG] Memanggil getPrdAct untuk hari {$day}");
                $prdActArray = $this->getPrdAct($modelNo, $classValue, $dayDate, $dayDate);
                $prdAct = isset($prdActArray[$dayIndex]) ? (int)$prdActArray[$dayIndex] : 0;
                log_message('debug', "[EXCEL_DEBUG] Nilai prdAct untuk hari {$day}: {$prdAct}");

                // Calculate stock plan and stock actual
                $stockPlan = $stockPlan - $dlvPlan + $prdPlan;
                $stockAct = $stockAct - $dlvAct + $prdAct;

                if ($stockPlan < 0 || $stockAct < 0) {
                    $hasNegativeStock = true;
                }

                $dayValues[$day] = [
                    'dlv_plan' => $dlvPlan,
                    'dlv_act' => $dlvAct,
                    'prd_plan' => $prdPlan,
                    'prd_act' => $prdAct,
                    'stock_plan' => $stockPlan,
                    'stock_act' => $stockAct
                ];
            }

            // Skip this model if minus_only is true and there's no negative stock
            if ($minusOnly && !$hasNegativeStock) {
                continue;
            }

            // Model and class info
            $sheet->setCellValue('A' . $row, $modelNo);
            $sheet->setCellValue('B' . $row, $classValue);
            $sheet->mergeCells('A' . $row . ':A' . ($row + 5));
            $sheet->mergeCells('B' . $row . ':B' . ($row + 5));
            $sheet->getStyle('A' . $row . ':B' . ($row + 5))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Row labels
            $rowLabels = ['Dlv Plan', 'Dlv Act', 'Prd Plan', 'Prd Act', 'Stock Plan', 'Stock Act'];
            for ($i = 0; $i < 6; $i++) {
                $sheet->setCellValue('C' . ($row + $i), $rowLabels[$i]);
                $sheet->getStyle('C' . ($row + $i))->getFont()->setBold(true);
            }

            // Begin stock (only for Stock Plan and Stock Act rows)
            $sheet->setCellValue('D' . ($row + 4), $beginStock);
            $sheet->setCellValue('D' . ($row + 5), $beginStock);

            // Day values
            $col = 5;
            for ($day = $startDay; $day <= $endDay; $day++) {
                $colLetter = $this->getColumnName($col);

                // Dlv Plan
                $sheet->setCellValue($colLetter . $row, $dayValues[$day]['dlv_plan']);

                // Dlv Act
                $sheet->setCellValue($colLetter . ($row + 1), $dayValues[$day]['dlv_act']);

                // Prd Plan
                $sheet->setCellValue($colLetter . ($row + 2), $dayValues[$day]['prd_plan']);

                // Prd Act
                $sheet->setCellValue($colLetter . ($row + 3), $dayValues[$day]['prd_act']);

                // Stock Plan
                $sheet->setCellValue($colLetter . ($row + 4), $dayValues[$day]['stock_plan']);
                if ($dayValues[$day]['stock_plan'] < 0) {
                    $sheet->getStyle($colLetter . ($row + 4))->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFCCCC');
                }

                // Stock Act
                $sheet->setCellValue($colLetter . ($row + 5), $dayValues[$day]['stock_act']);
                if ($dayValues[$day]['stock_act'] < 0) {
                    $sheet->getStyle($colLetter . ($row + 5))->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFCCCC');
                }

                $col++;
            }

            // Apply borders to all cells in this model group
            $sheet->getStyle('A' . $row . ':' . $this->getColumnName(4 + $endDay - $startDay + 1) . ($row + 5))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Move to next model group
            $row += 6;
        }

        // Auto-size columns - perbaikan untuk menangani kolom lebih dari Z
        $lastColumnIndex = 4 + $endDay - $startDay + 1;
        $lastColumnName = $this->getColumnName($lastColumnIndex);
        
        log_message('debug', "[EXCEL_DEBUG] Auto-sizing columns from A to {$lastColumnName} (index: {$lastColumnIndex})");
        
        // Gunakan pendekatan iterasi numerik daripada range() untuk menghindari masalah multi-byte
        for ($colIndex = 1; $colIndex <= $lastColumnIndex; $colIndex++) {
            $colName = $this->getColumnName($colIndex);
            $sheet->getColumnDimension($colName)->setAutoSize(true);
            log_message('debug', "[EXCEL_DEBUG] Auto-sizing column {$colName}");
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'delivery_shortage_report_' . date('YmdHis') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getColumnName($columnNumber)
    {
        // Log untuk debugging
        log_message('debug', "[EXCEL_DEBUG] getColumnName dipanggil dengan columnNumber: {$columnNumber}");
        
        // Tambahkan validasi untuk menghindari error pada nilai 0 atau negatif
        if ($columnNumber <= 0) {
            log_message('error', "[EXCEL_DEBUG] Invalid column number: {$columnNumber}, returning 'A'");
            return 'A'; // Default ke kolom A jika invalid
        }
        
        $dividend = $columnNumber;
        $columnName = '';

        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnName = chr(65 + $modulo) . $columnName;
            $dividend = floor(($dividend - $modulo) / 26);
            
            // Log untuk debugging
            log_message('debug', "[EXCEL_DEBUG] Loop konversi: dividend={$dividend}, modulo={$modulo}, current columnName={$columnName}");
        }

        log_message('debug', "[EXCEL_DEBUG] Column number {$columnNumber} converted to column name: {$columnName}");
        return $columnName;
    }

    public function getAvailableModels()
    {
        $db = \Config\Database::connect();

        log_message('debug', 'getAvailableModels called');

        // Ambil model_no langsung dari sales tanpa join
        $query = $db->table('sales')
            ->select('model_no')
            ->distinct()
            ->orderBy('model_no', 'ASC');

        // Get the SQL query for debugging
        $sql = $query->getCompiledSelect();
        log_message('debug', "getAvailableModels SQL Query: {$sql}");

        $result = $query->get()->getResultArray();

        log_message('debug', 'getAvailableModels found ' . count($result) . ' models');
        
        // Pastikan format data sesuai dengan yang diharapkan oleh frontend
        $formattedModels = [];
        foreach ($result as $row) {
            if (isset($row['model_no']) && !empty($row['model_no'])) {
                $formattedModels[] = [
                    'model_no' => $row['model_no']
                ];
            }
        }
        
        if (count($formattedModels) > 0) {
            log_message('debug', 'First few formatted models: ' . json_encode(array_slice($formattedModels, 0, 5)));
        } else {
            log_message('debug', 'No models found or all models were empty');
            // Jika tidak ada data valid, berikan array kosong
            $formattedModels = [];
        }

        // Pastikan respons JSON memiliki format yang benar
        $response = [
            'models' => $formattedModels
        ];

        log_message('debug', 'Final response: ' . json_encode($response));

        // Pastikan header content-type diatur dengan benar
        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setJSON($response);
    }

    public function getAvailableClasses()
    {
        $db = \Config\Database::connect();

        log_message('debug', 'getAvailableClasses called');

        // Ambil class langsung dari sales tanpa join
        $query = $db->table('sales')
            ->select('class')
            ->distinct()
            ->orderBy('class', 'ASC');

        // Get the SQL query for debugging
        $sql = $query->getCompiledSelect();
        log_message('debug', "getAvailableClasses SQL Query: {$sql}");

        $result = $query->get()->getResultArray();

        log_message('debug', 'getAvailableClasses found ' . count($result) . ' classes');
        if (count($result) > 0) {
            log_message('debug', 'First few classes: ' . json_encode(array_slice($result, 0, 5)));
        } else {
            log_message('debug', 'No classes found in sales table');
        }

        return $this->response->setJSON(['classes' => $result]);
    }
}
