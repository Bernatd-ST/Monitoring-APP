<?php

namespace App\Controllers;

use App\Models\MaterialShortageModelFixed;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MaterialShortageController extends BaseController
{
    protected $materialShortageModel;

    public function __construct()
    {
        $this->materialShortageModel = new MaterialShortageModelFixed();
    }

    public function index()
    {
        $data = [
            'title' => 'Material Shortage Report',
        ];

        return view('admin/report/material_shortage', $data);
    }

    public function getMaterialShortageData()
    {
        $request = $this->request;
        
        // Log raw POST data untuk debugging
        $rawPostData = $request->getPost();
        log_message('debug', "MATERIAL_SHORTAGE - Raw POST data: " . json_encode($rawPostData));
        
        $startDate = $request->getPost('start_date');
        $endDate = $request->getPost('end_date');
        $modelNo = $request->getPost('model_no');
        $hClass = $request->getPost('h_class');
        $class = $request->getPost('class');
        $minusOnly = $request->getPost('minus_only') === 'true';
        
        // Pastikan model_no benar-benar kosong jika dikirim sebagai string kosong
        if ($modelNo === '') {
            $modelNo = null;
            log_message('debug', "MATERIAL_SHORTAGE - Model_no kosong, diubah menjadi null");
        }
        
        // Log untuk debugging
        log_message('debug', "MATERIAL_SHORTAGE - Parameters: Start={$startDate}, End={$endDate}, Model: {$modelNo}, H Class: {$hClass}, Class: {$class}, Minus Only: " . ($minusOnly ? 'true' : 'false'));
        
        // Validasi tanggal (frontend sudah mengirim format YYYY-MM-DD yang clean)
        if (!$startDate || !$endDate) {
            log_message('error', "MATERIAL_SHORTAGE - Missing dates: startDate={$startDate}, endDate={$endDate}");
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Please provide valid start and end dates.'
            ]);
        }
        
        // Validasi format tanggal YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            log_message('error', "MATERIAL_SHORTAGE - Invalid date format: startDate={$startDate}, endDate={$endDate}");
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid date format. Please use YYYY-MM-DD format.'
            ]);
        }

        try {
            // Ambil data dari model
            $data = $this->materialShortageModel->getMaterialShortageData(
                $startDate, 
                $endDate, 
                $modelNo, 
                $hClass, 
                $class, 
                $minusOnly
            );
            
            // Log jumlah data yang ditemukan
            log_message('debug', "MATERIAL_SHORTAGE - Found " . count($data) . " records");
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', "MATERIAL_SHORTAGE - Error: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error retrieving material shortage data: ' . $e->getMessage()
            ]);
        }
    }

    public function getAvailableModels()
    {
        try {
            $models = $this->materialShortageModel->getAvailableModels();
            return $this->response->setJSON([
                'success' => true,
                'models' => $models
            ]);
        } catch (\Exception $e) {
            log_message('error', "MATERIAL_SHORTAGE - Error getting models: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error retrieving models: ' . $e->getMessage()
            ]);
        }
    }

    public function getAvailableHClasses()
    {
        try {
            $hClasses = $this->materialShortageModel->getAvailableHClasses();
            return $this->response->setJSON([
                'success' => true,
                'h_classes' => $hClasses
            ]);
        } catch (\Exception $e) {
            log_message('error', "MATERIAL_SHORTAGE - Error getting H Classes: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error retrieving H Classes: ' . $e->getMessage()
            ]);
        }
    }

    public function getAvailableClasses()
    {
        try {
            $classes = $this->materialShortageModel->getAvailableClasses();
            return $this->response->setJSON([
                'success' => true,
                'classes' => $classes
            ]);
        } catch (\Exception $e) {
            log_message('error', "MATERIAL_SHORTAGE - Error getting Classes: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error retrieving Classes: ' . $e->getMessage()
            ]);
        }
    }

    public function exportMaterialShortage()
    {
        $request = $this->request;
        
        $startDate = $request->getPost('start_date');
        $endDate = $request->getPost('end_date');
        $modelNo = $request->getPost('model_no');
        $hClass = $request->getPost('h_class');
        $class = $request->getPost('class');
        $minusOnly = $request->getPost('minus_only') === 'true';
        
        log_message('debug', "MATERIAL_SHORTAGE_EXPORT - Parameters: Start Date: {$startDate}, End Date: {$endDate}, Model: {$modelNo}, H Class: {$hClass}, Class: {$class}, Minus Only: " . ($minusOnly ? 'true' : 'false'));
        
        try {
            // Ambil data dari model
            $data = $this->materialShortageModel->getMaterialShortageData(
                $startDate, 
                $endDate, 
                $modelNo, 
                $hClass, 
                $class, 
                $minusOnly
            );
            
            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul report
            $sheet->setCellValue('A1', 'Material Shortage Report');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Set periode
            $sheet->setCellValue('A2', 'Period: ' . date('d-M-Y', strtotime($startDate)) . ' to ' . date('d-M-Y', strtotime($endDate)));
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()->setBold(true);
            
            // Set header kolom
            $sheet->setCellValue('A4', 'MODEL_NO');
            $sheet->setCellValue('B4', 'H Class');
            $sheet->setCellValue('C4', 'PART_NO');
            $sheet->setCellValue('D4', 'Desc');
            $sheet->setCellValue('E4', 'Class');
            $sheet->setCellValue('F4', '');
            $sheet->setCellValue('G4', 'Begin_Stock');
            
            // Set header tanggal
            $currentDate = strtotime($startDate);
            $endDateTimestamp = strtotime($endDate);
            $columnIndex = 8; // Mulai dari kolom H
            
            while ($currentDate <= $endDateTimestamp) {
                $dateStr = date('d-M', $currentDate);
                $columnName = $this->getColumnName($columnIndex);
                $sheet->setCellValue($columnName . '4', $dateStr);
                $currentDate = strtotime('+1 day', $currentDate);
                $columnIndex++;
            }
            
            // Style untuk header
            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'CCCCCC',
                    ],
                ],
            ];
            
            $lastColumn = $this->getColumnName($columnIndex - 1);
            $sheet->getStyle('A4:' . $lastColumn . '4')->applyFromArray($headerStyle);
            
            // Isi data
            $row = 5;
            $prevModelNo = '';
            $prevPartNo = '';
            
            foreach ($data as $item) {
                // Jika model_no berubah, tambahkan baris baru
                if ($prevModelNo != $item['model_no']) {
                    $sheet->setCellValue('A' . $row, $item['model_no']);
                    $sheet->setCellValue('B' . $row, $item['h_class']);
                    $sheet->setCellValue('C' . $row, $item['part_no']);
                    $sheet->setCellValue('D' . $row, $item['description']);
                    $sheet->setCellValue('E' . $row, $item['class']);
                    $prevModelNo = $item['model_no'];
                    $prevPartNo = $item['part_no'];
                } 
                // Jika part_no berubah tapi model_no sama
                else if ($prevPartNo != $item['part_no']) {
                    $sheet->setCellValue('C' . $row, $item['part_no']);
                    $sheet->setCellValue('D' . $row, $item['description']);
                    $sheet->setCellValue('E' . $row, $item['class']);
                    $prevPartNo = $item['part_no'];
                }
                
                // Tambahkan 6 baris untuk setiap part_no
                $sheet->setCellValue('F' . $row, 'Use Plan');
                $sheet->setCellValue('G' . $row, '');
                
                // Isi data Use Plan per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['use_plan'])) {
                        $sheet->setCellValue($columnName . $row, $item['daily_data'][$dateKey]['use_plan']);
                    } else {
                        $sheet->setCellValue($columnName . $row, 0);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // Use Act
                $sheet->setCellValue('F' . $row, 'Use Act');
                $sheet->setCellValue('G' . $row, '');
                
                // Isi data Use Act per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['use_act'])) {
                        $sheet->setCellValue($columnName . $row, $item['daily_data'][$dateKey]['use_act']);
                    } else {
                        $sheet->setCellValue($columnName . $row, 0);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // ETA_MEAINA
                $sheet->setCellValue('F' . $row, 'ETA_MEAINA');
                $sheet->setCellValue('G' . $row, '');
                
                // Isi data ETA per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['eta'])) {
                        $sheet->setCellValue($columnName . $row, $item['daily_data'][$dateKey]['eta']);
                    } else {
                        $sheet->setCellValue($columnName . $row, 0);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // INV_NO
                $sheet->setCellValue('F' . $row, 'INV_NO');
                $sheet->setCellValue('G' . $row, '');
                
                // Isi data INV_NO per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['inv_no'])) {
                        $sheet->setCellValue($columnName . $row, $item['daily_data'][$dateKey]['inv_no']);
                    } else {
                        $sheet->setCellValue($columnName . $row, '');
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // Stock Plan
                $sheet->setCellValue('F' . $row, 'Stock Plan');
                $sheet->setCellValue('G' . $row, $item['begin_stock']);
                
                // Isi data Stock Plan per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['stock_plan'])) {
                        $value = $item['daily_data'][$dateKey]['stock_plan'];
                        $sheet->setCellValue($columnName . $row, $value);
                        
                        // Highlight nilai negatif dengan warna merah
                        if ($value < 0) {
                            $sheet->getStyle($columnName . $row)->getFont()->getColor()->setRGB('FF0000');
                        }
                    } else {
                        $sheet->setCellValue($columnName . $row, 0);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // Stock Act
                $sheet->setCellValue('F' . $row, 'Stock Act');
                $sheet->setCellValue('G' . $row, $item['begin_stock']);
                
                // Isi data Stock Act per tanggal
                $currentDate = strtotime($startDate);
                $columnIndex = 8;
                while ($currentDate <= $endDateTimestamp) {
                    $dateKey = date('Y-m-d', $currentDate);
                    $columnName = $this->getColumnName($columnIndex);
                    
                    if (isset($item['daily_data'][$dateKey]['stock_act'])) {
                        $value = $item['daily_data'][$dateKey]['stock_act'];
                        $sheet->setCellValue($columnName . $row, $value);
                        
                        // Highlight nilai negatif dengan warna merah
                        if ($value < 0) {
                            $sheet->getStyle($columnName . $row)->getFont()->getColor()->setRGB('FF0000');
                        }
                    } else {
                        $sheet->setCellValue($columnName . $row, 0);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                    $columnIndex++;
                }
                $row++;
                
                // Tambahkan baris kosong setelah setiap part_no
                $row++;
            }
            
            // Auto-size kolom
            for ($col = 'A'; $col <= $lastColumn; $col++) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set nama file
            $filename = 'Material_Shortage_Report_' . date('Ymd_His') . '.xlsx';
            
            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', "MATERIAL_SHORTAGE_EXPORT - Error: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error exporting material shortage data: ' . $e->getMessage()
            ]);
        }
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
}
