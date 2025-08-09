<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MasterBomModel;
use App\Models\StockMaterialModel;
use App\Models\ShipmentScheduleModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MaterialController extends BaseController
{
    protected $masterBomModel;
    protected $stockMaterialModel;
    protected $shipmentScheduleModel;
    
    public function __construct()
    {
        $this->masterBomModel = new MasterBomModel();
        $this->stockMaterialModel = new StockMaterialModel();
        $this->shipmentScheduleModel = new ShipmentScheduleModel();
    }
    
    /**
     * Halaman utama Material Control
     */
    public function index()
    {
        $data = [
            'title' => 'Material Control'
        ];
        
        return view('admin/material/index', $data);
    }
    
    /**
     * Halaman BOM (Bill of Material)
     */
    public function bom()
    {
        // Ambil parameter filter dari request
        $model_no = $this->request->getGet('model_no');
        $class = $this->request->getGet('class');
        $h_class = $this->request->getGet('h_class');
        $prd_code = $this->request->getGet('prd_code');
        
        // Log parameter filter untuk debugging
        log_message('debug', 'BOM filter parameters: ' . json_encode([
            'model_no' => $model_no,
            'class' => $class,
            'h_class' => $h_class,
            'prd_code' => $prd_code
        ]));
        
        // Buat query builder
        $query = $this->masterBomModel;
        
        // Terapkan filter jika ada
        if (!empty($model_no)) {
            $query = $query->where('model_no', $model_no);
        }
        
        if (!empty($class)) {
            $query = $query->where('class', $class);
        }
        
        if (!empty($h_class)) {
            $query = $query->where('h_class', $h_class);
        }
        
        if (!empty($prd_code)) {
            $query = $query->where('prd_code', $prd_code);
        }
        
        // Ambil data sesuai filter
        $bom_data = $query->findAll();
        
        // Log jumlah data yang ditemukan
        log_message('debug', 'BOM data found: ' . count($bom_data));
        
        $data = [
            'title' => 'Bill of Material',
            'bom_data' => $bom_data,
            // Kirim kembali parameter filter untuk mengisi form
            'filter' => [
                'model_no' => $model_no,
                'class' => $class,
                'h_class' => $h_class,
                'prd_code' => $prd_code
            ]
        ];
        
        return view('admin/material/bom', $data);
    }
    
    /**
     * Menampilkan form tambah BOM
     */
    public function addBom()
    {
        $data = [
            'title' => 'Add Bill of Material'
        ];
        
        return view('admin/material/bom_form', $data);
    }
    
    /**
     * Menyimpan data BOM baru
     */
    public function saveBom()
    {
        // Log semua data yang diterima untuk debugging
        log_message('debug', 'saveBom method called with POST data: ' . json_encode($this->request->getPost()));
        
        $data = [
            'model_no' => $this->request->getPost('model_no'),
            'h_class' => $this->request->getPost('h_class'),
            'qty_assy' => $this->request->getPost('qty_assy'),
            'part_no' => $this->request->getPost('part_no'),
            'description' => $this->request->getPost('description'),
            'prd_code' => $this->request->getPost('prd_code'),
            'class' => $this->request->getPost('class'),
            'upd_date' => $this->request->getPost('upd_date')
        ];
        
        // Filter data untuk menghapus nilai kosong
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Pastikan setidaknya model_no dan part_no tidak kosong
        if (empty($data['model_no']) || empty($data['part_no'])) {
            $errorMsg = 'Model No and Part No are required fields';
            log_message('error', 'BOM save failed: ' . $errorMsg);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg
                ]);
            } else {
                return redirect()->back()
                    ->with('error', $errorMsg)
                    ->withInput();
            }
        }
        
        try {
            if ($this->request->isAJAX()) {
                if ($this->masterBomModel->insert($data)) {
                    $insertId = $this->masterBomModel->getInsertID();
                    log_message('info', 'BOM data added successfully with ID: ' . $insertId);
                    
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'BOM data added successfully',
                        'id' => $insertId
                    ]);
                } else {
                    $errors = $this->masterBomModel->errors();
                    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Failed to add BOM data';
                    log_message('error', 'BOM save failed: ' . $errorMsg);
                    
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $errorMsg,
                        'errors' => $errors
                    ]);
                }
            } else {
                if ($this->masterBomModel->insert($data)) {
                    return redirect()->to('/admin/material/bom')
                        ->with('success', 'BOM data added successfully');
                } else {
                    $errors = $this->masterBomModel->errors();
                    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Failed to add BOM data';
                    
                    return redirect()->back()
                        ->with('error', $errorMsg)
                        ->withInput();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in saveBom: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            } else {
                return redirect()->back()
                    ->with('error', 'Database error: ' . $e->getMessage())
                    ->withInput();
            }
        }
    }
    
    /**
     * Menampilkan form edit BOM
     */
    public function editBom($id = null)
    {
        $bom = $this->masterBomModel->find($id);
        
        if (!$bom) {
            return redirect()->to('/admin/material/bom')
                ->with('error', 'BOM data not found');
        }
        
        $data = [
            'title' => 'Edit Bill of Material',
            'bom' => $bom
        ];
        
        return view('admin/material/bom_form', $data);
    }
    
    /**
     * Mengupdate data BOM
     */
    public function updateBom($id = null)
    {
        $data = [
            'id' => $id,
            'model_no' => $this->request->getPost('model_no'),
            'h_class' => $this->request->getPost('h_class'),
            'qty_assy' => $this->request->getPost('qty_assy'),
            'part_no' => $this->request->getPost('part_no'),
            'description' => $this->request->getPost('description'),
            'prd_code' => $this->request->getPost('prd_code'),
            'class' => $this->request->getPost('class'),
            'upd_date' => $this->request->getPost('upd_date')
        ];
        
        // Cek apakah request AJAX
        if ($this->request->isAJAX()) {
            if ($this->masterBomModel->save($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'BOM data updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update BOM data'
                ]);
            }
        } else {
            // Request normal (non-AJAX)
            if ($this->masterBomModel->save($data)) {
                return redirect()->to('/admin/material/bom')
                    ->with('success', 'BOM data updated successfully');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to update BOM data')
                    ->withInput();
            }
        }
    }
    
    /**
     * Menghapus data BOM
     */
    public function deleteBom($id = null)
    {
        if ($this->masterBomModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'BOM data deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete BOM data'
            ]);
        }
    }
    
    /**
     * Mendapatkan data BOM berdasarkan ID (untuk AJAX)
     */
    public function getBom($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Access denied']);
        }
        
        $bom = $this->masterBomModel->find($id);
        
        if (!$bom) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data not found'
            ]);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $bom
        ]);
    }
    
    /**
     * Import data BOM dari file Excel
     */
    public function importBom()
    {
        $file = $this->request->getFile('excel_file');
        
        if (!$file->isValid()) {
            return redirect()->to('/admin/material/bom')->with('error', 'File tidak valid');
        }
        
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        // Mengatur setReadDataOnly(true) untuk membaca nilai hasil rumus, bukan rumusnya
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        
        // Debug log untuk nama worksheet dan jumlah baris
        log_message('debug', 'Worksheet name: ' . $sheet->getTitle() . ', Highest row: ' . $highestRow);
        
        $successCount = 0;
        $errorCount = 0;
        
        // Hapus semua data BOM yang ada sebelum import (opsional)
        // $this->masterBomModel->truncate();
        
        // Gunakan transaksi database untuk memastikan konsistensi data
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $model_no = $sheet->getCell('A' . $row)->getValue(); // Column A
                
                // Skip empty rows
                if (empty($model_no)) {
                    log_message('debug', 'Skipping empty row: ' . $row);
                    continue;
                }
                
                $h_class = $sheet->getCell('B' . $row)->getValue(); // Column B
                $qty_assy = $sheet->getCell('C' . $row)->getValue(); // Column C
                $part_no = $sheet->getCell('D' . $row)->getValue(); // Column D
                
                // Khusus untuk description, coba ambil nilai yang ditampilkan di Excel
                // Gunakan beberapa metode untuk mendapatkan nilai yang benar
                $cell = $sheet->getCell('E' . $row);
                $description = $cell->getValue(); // Nilai mentah (mungkin rumus)
                
                // Jika nilai adalah rumus (dimulai dengan '=')
                if (is_string($description) && strpos($description, '=') === 0) {
                    // Coba beberapa metode untuk mendapatkan nilai yang ditampilkan
                    try {
                        // Metode 1: Coba getFormattedValue() - biasanya mengembalikan nilai yang ditampilkan
                        $formattedValue = $cell->getFormattedValue();
                        if (!empty($formattedValue) && $formattedValue !== $description) {
                            $description = $formattedValue;
                            log_message('debug', 'Using formatted value for description at row ' . $row . ': ' . $description);
                        } else {
                            // Metode 2: Coba getCalculatedValue() - mengevaluasi rumus
                            $calculatedValue = $cell->getCalculatedValue();
                            if (!empty($calculatedValue) && $calculatedValue !== $description) {
                                $description = $calculatedValue;
                                log_message('debug', 'Using calculated value for description at row ' . $row . ': ' . $description);
                            } else {
                                // Metode 3: Jika rumus mereferensikan file eksternal, ambil nilai dari cache Excel
                                $oldValue = $cell->getOldCalculatedValue();
                                if (!empty($oldValue) && $oldValue !== $description) {
                                    $description = $oldValue;
                                    log_message('debug', 'Using old calculated value for description at row ' . $row . ': ' . $description);
                                } else {
                                    log_message('warning', 'Could not resolve formula for description at row ' . $row . ': ' . $description);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to get value for description at row ' . $row . ': ' . $e->getMessage());
                    }
                }
                
                $prd_code = $sheet->getCell('F' . $row)->getValue(); // Column F
                $class = $sheet->getCell('G' . $row)->getValue(); // Column G
                $upd_date = $sheet->getCell('H' . $row)->getValue(); // Column H
                
                // Pastikan qty_assy adalah angka
                $qty_assy = is_numeric($qty_assy) ? $qty_assy : 0;
                
                // Format date dengan benar untuk database MySQL
                $formatted_date = null;
                
                // Debug log untuk melihat format tanggal asli
                log_message('debug', 'Original date format at row ' . $row . ': ' . (is_string($upd_date) ? $upd_date : gettype($upd_date)));
                
                if (is_numeric($upd_date)) {
                    // Jika tanggal dalam format Excel numeric
                    $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($upd_date);
                    $formatted_date = $dateObj->format('Y-m-d');
                    log_message('debug', 'Converted numeric Excel date: ' . $upd_date . ' to ' . $formatted_date);
                } else if ($upd_date instanceof \DateTime) {
                    // Jika sudah berupa objek DateTime
                    $formatted_date = $upd_date->format('Y-m-d');
                } else if (is_string($upd_date) && !empty($upd_date)) {
                    // Format khusus: "05 Jul 23" (dd MMM yy)
                    if (preg_match('/^(\d{2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{2})$/i', $upd_date, $matches)) {
                        $day = $matches[1];
                        $month = $matches[2];
                        $year = '20' . $matches[3]; // Asumsi tahun 2000-an
                        
                        $date = \DateTime::createFromFormat('d M Y', "$day $month $year");
                        if ($date !== false) {
                            $formatted_date = $date->format('Y-m-d');
                            log_message('debug', 'Parsed date "' . $upd_date . '" as "' . $formatted_date . '"');
                        }
                    }
                    // Format dengan timestamp: "05/07/2023 08.04.34"
                    else if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2})\.(\d{2})\.(\d{2})$/', $upd_date, $matches)) {
                        $day = $matches[1];
                        $month = $matches[2];
                        $year = $matches[3];
                        $hour = $matches[4];
                        $minute = $matches[5];
                        $second = $matches[6];
                        
                        $date = \DateTime::createFromFormat('d/m/Y H.i.s', "$day/$month/$year $hour.$minute.$second");
                        if ($date !== false) {
                            $formatted_date = $date->format('Y-m-d');
                            log_message('debug', 'Parsed timestamp "' . $upd_date . '" as date "' . $formatted_date . '"');
                        }
                    }
                    // Coba berbagai format tanggal umum lainnya
                    else {
                        $formats = [
                            'd-M-y',     // 22-Jul-24
                            'Y-m-d',     // 2024-07-22
                            'd/m/Y',     // 22/07/2024
                            'm/d/Y',     // 07/22/2024
                            'd-m-Y',     // 22-07-2024
                            'Y/m/d'      // 2024/07/22
                        ];
                        
                        foreach ($formats as $format) {
                            $date = \DateTime::createFromFormat($format, $upd_date);
                            if ($date !== false) {
                                $formatted_date = $date->format('Y-m-d');
                                log_message('debug', 'Parsed with format "' . $format . '" date "' . $upd_date . '" as "' . $formatted_date . '"');
                                break;
                            }
                        }
                        
                        // Jika belum berhasil, coba parse secara umum
                        if ($formatted_date === null) {
                            try {
                                $date = new \DateTime($upd_date);
                                $formatted_date = $date->format('Y-m-d');
                                log_message('debug', 'Parsed with DateTime constructor: "' . $upd_date . '" as "' . $formatted_date . '"');
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to parse date: "' . $upd_date . '" at row ' . $row . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
                
                // Jika semua metode gagal, gunakan tanggal hari ini
                if ($formatted_date === null) {
                    $formatted_date = date('Y-m-d');
                    log_message('warning', 'Using today as fallback for unparseable date: "' . $upd_date . '" at row ' . $row);
                }
            
                $rowData = [
                    'model_no' => $model_no,
                    'h_class' => $h_class,
                    'qty_assy' => $qty_assy,
                    'part_no' => $part_no,
                    'description' => $description,
                    'prd_code' => $prd_code,
                    'class' => $class,
                    'upd_date' => $formatted_date
                ];
                
                try {
                    if ($this->masterBomModel->save($rowData)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        log_message('error', 'Failed to save BOM data at row ' . $row . ': ' . json_encode($this->masterBomModel->errors()));
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    log_message('error', 'Exception when saving BOM data at row ' . $row . ': ' . $e->getMessage());
                }
            }
            
            // Commit transaksi jika ada data yang berhasil disimpan
            if ($successCount > 0) {
                $db->transCommit();
                return redirect()->to('/admin/material/bom')
                    ->with('success', "Import completed: {$successCount} records added successfully, {$errorCount} failed");
            } else {
                // Rollback jika tidak ada data yang berhasil disimpan
                $db->transRollback();
                return redirect()->to('/admin/material/bom')
                    ->with('error', "Import failed: No records were imported successfully. Please check the log for details.");
            }
        } catch (\Exception $e) {
            // Rollback jika terjadi exception
            $db->transRollback();
            log_message('error', 'Exception during BOM import: ' . $e->getMessage());
            return redirect()->to('/admin/material/bom')
                ->with('error', "Import failed: " . $e->getMessage());
        }
    }
    
    /**
     * Export data BOM ke file Excel
     */
    public function exportBom()    
    {
        $bomData = $this->masterBomModel->findAll();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header row
        $sheet->setCellValue('A1', 'Model No');
        $sheet->setCellValue('B1', 'H Class');
        $sheet->setCellValue('C1', 'Qty Assy');
        $sheet->setCellValue('D1', 'Part No');
        $sheet->setCellValue('E1', 'Description');
        $sheet->setCellValue('F1', 'Prd Code');
        $sheet->setCellValue('G1', 'Class');
        $sheet->setCellValue('H1', 'Upd Date');
        
        // Style the header row
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA']
            ]
        ];
        
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        
        // Fill data rows
        $row = 2;
        foreach ($bomData as $bom) {
            $sheet->setCellValue('A' . $row, $bom['model_no']);
            $sheet->setCellValue('B' . $row, $bom['h_class']);
            $sheet->setCellValue('C' . $row, $bom['qty_assy']);
            $sheet->setCellValue('D' . $row, $bom['part_no']);
            $sheet->setCellValue('E' . $row, $bom['description']);
            $sheet->setCellValue('F' . $row, $bom['prd_code']);
            $sheet->setCellValue('G' . $row, $bom['class']);
            $sheet->setCellValue('H' . $row, $bom['upd_date']);
            $row++;
        }
        
        // Auto size columns
        for ($col = 'A'; $col <= 'H'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'bom_export_' . date('YmdHis') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Halaman Material Control
     */
    public function materialControl()
    {
        // Ambil parameter filter dari request
        $ckd = $this->request->getGet('ckd');
        $part_no = $this->request->getGet('part_no');
        $description = $this->request->getGet('description');
        $class = $this->request->getGet('class');
        
        // Buat array filter
        $filters = [];
        if (!empty($ckd)) $filters['ckd'] = $ckd;
        if (!empty($part_no)) $filters['part_no'] = $part_no;
        if (!empty($description)) $filters['description'] = $description;
        if (!empty($class)) $filters['class'] = $class;
        
        // Simpan filter ke session untuk digunakan kembali pada export
        session()->set('material_filters', $filters);
        
        // Log parameter filter untuk debugging
        log_message('debug', 'Material Control filter parameters: ' . json_encode($filters));
        
        // Ambil data menggunakan fungsi getMaterials dari model
        $material_data = $this->stockMaterialModel->getMaterials($filters);
        
        // Log jumlah data yang ditemukan
        log_message('debug', 'Material Control data found: ' . count($material_data));
        
        // Jika tidak ada data ditemukan, coba periksa database tanpa filter
        if (count($material_data) === 0 && !empty($filters)) {
            log_message('debug', 'No data found with filters, checking if any data exists in the table');
            $all_data = $this->stockMaterialModel->findAll();
            log_message('debug', 'Total records in table without filter: ' . count($all_data));
        }
        
        $data = [
            'title' => 'Material Control',
            'material_data' => $material_data,
            // Kirim kembali parameter filter untuk mengisi form
            'filter' => [
                'ckd' => $ckd,
                'part_no' => $part_no,
                'description' => $description,
                'class' => $class
            ]
        ];
        
        return view('admin/material/material_control', $data);
    }
    
    /**
     * Menampilkan form tambah Material
     */
    public function addMaterial()
    {
        $data = [
            'title' => 'Add Material Control'
        ];
        
        return view('admin/material/material_form', $data);
    }
    
    /**
     * Menyimpan data Material baru
     */
    public function saveMaterial()
    {
        // Log semua data yang diterima untuk debugging
        log_message('debug', 'saveMaterial method called with POST data: ' . json_encode($this->request->getPost()));
        
        // Format tanggal period jika ada
        $period = $this->request->getPost('period');
        if (!empty($period)) {
            $period = $this->stockMaterialModel->formatExcelDate($period);
        }
        
        $data = [
            'ckd' => $this->request->getPost('ckd'),
            'period' => $period,
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'beginning' => $this->request->getPost('beginning')
        ];
        
        // Filter data untuk menghapus nilai kosong
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Pastikan setidaknya ckd dan part_no tidak kosong
        if (empty($data['ckd']) || empty($data['part_no'])) {
            $errorMsg = 'CKD and Part No are required fields';
            log_message('error', 'Material save failed: ' . $errorMsg);
            return redirect()->back()->withInput()
                ->with('error', $errorMsg);
        }
        
        try {
            // Simpan data ke database
            if ($this->stockMaterialModel->insert($data)) {
                log_message('info', 'Material saved successfully: ' . json_encode($data));
                return redirect()->to('/admin/material/material-control')
                    ->with('success', 'Material data saved successfully');
            } else {
                $errors = $this->stockMaterialModel->errors();
                $errorMsg = implode(', ', $errors);
                log_message('error', 'Material save failed: ' . $errorMsg);
                return redirect()->back()->withInput()
                    ->with('error', 'Failed to save material data: ' . $errorMsg);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during Material save: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while saving material data: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan form edit Material
     */
    public function editMaterial($id = null)
    {
        if ($id === null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Material ID is required'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'Material ID is required');
        }
        
        $material = $this->stockMaterialModel->find($id);
        log_message('debug', 'editMaterial: Looking for material with ID ' . $id . ', result: ' . ($material ? 'found' : 'not found'));
        
        if (!$material) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Material not found'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'Material not found');
        }
        
        $data = [
            'title' => 'Edit Material Control',
            'material' => $material
        ];
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'data' => $material
            ]);
        }
        
        return view('admin/material/edit_material', $data);
    }
    
    /**
     * Mengupdate data Material
     */
    public function updateMaterial($id = null)
    {
        if ($id === null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Material ID is required'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'Material ID is required');
        }
        
        // Cek apakah material dengan ID tersebut ada
        $material = $this->stockMaterialModel->find($id);
        if (!$material) {
            log_message('error', 'Material not found for update: ID ' . $id);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Material data not found'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'Material data not found');
        }
        
        // Log semua data yang diterima untuk debugging
        log_message('debug', 'updateMaterial method called with POST data: ' . json_encode($this->request->getPost()));
        
        // Format tanggal period jika ada
        $period = $this->request->getPost('period');
        if (!empty($period)) {
            $period = $this->stockMaterialModel->formatExcelDate($period);
        }
        
        $data = [
            'ckd' => $this->request->getPost('ckd'),
            'period' => $period,
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'beginning' => $this->request->getPost('beginning')
        ];
        
        // Filter data untuk menghapus nilai kosong
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Pastikan setidaknya ckd dan part_no tidak kosong
        if (empty($data['ckd']) || empty($data['part_no'])) {
            $errorMsg = 'CKD and Part No are required fields';
            log_message('error', 'Material update failed: ' . $errorMsg);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMsg
                ]);
            }
            return redirect()->back()->withInput()
                ->with('error', $errorMsg);
        }
        
        try {
            // Update data di database
            if ($this->stockMaterialModel->update($id, $data)) {
                log_message('info', 'Material updated successfully: ' . json_encode($data));
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Material data updated successfully',
                        'data' => $this->stockMaterialModel->find($id)
                    ]);
                }
                return redirect()->to('/admin/material/material-control')
                    ->with('success', 'Material data updated successfully');
            } else {
                $errors = $this->stockMaterialModel->errors();
                $errorMsg = implode(', ', $errors);
                log_message('error', 'Material update failed: ' . $errorMsg);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to update material data: ' . $errorMsg
                    ]);
                }
                return redirect()->back()->withInput()
                    ->with('error', 'Failed to update material data: ' . $errorMsg);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during Material update: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while updating material data'
                ]);
            }
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while updating material data: ' . $e->getMessage());
        }
    }
    
    /**
     * Menghapus data Material
     */
    public function deleteMaterial($id = null)
    {
        if ($id === null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Material ID is required'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'Material ID is required');
        }
        
        try {
            // Cek apakah material dengan ID tersebut ada
            $material = $this->stockMaterialModel->find($id);
            if (!$material) {
                log_message('error', 'Material not found for delete: ID ' . $id);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Material data not found'
                    ]);
                }
                return redirect()->to('/admin/material/material-control')
                    ->with('error', 'Material data not found');
            }
            
            if ($this->stockMaterialModel->delete($id)) {
                log_message('info', 'Material deleted successfully: ID ' . $id);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Material data deleted successfully'
                    ]);
                }
                return redirect()->to('/admin/material/material-control')
                    ->with('success', 'Material data deleted successfully');
            } else {
                log_message('error', 'Material delete failed: ID ' . $id);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to delete material data'
                    ]);
                }
                return redirect()->to('/admin/material/material-control')
                    ->with('error', 'Failed to delete material data');
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during Material delete: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while deleting material data'
                ]);
            }
            return redirect()->to('/admin/material/material-control')
                ->with('error', 'An error occurred while deleting material data: ' . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan data Material berdasarkan ID (untuk AJAX)
     */
    public function getMaterial($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Material ID is required'
            ]);
        }
        
        $material = $this->stockMaterialModel->find($id);
        
        if (!$material) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Material not found'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $material
        ]);
    }
    
    /**
     * Import data Material dari file Excel
     */
    public function importMaterial()
    {
        // Untuk AJAX request
        if ($this->request->isAJAX()) {
            $file = $this->request->getFile('excel_file');
            
            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid file upload'
                ]);
            }
            
            try {
                // Memulai transaksi database
                $db = \Config\Database::connect();
                $db->transBegin();
                
                // Baca file Excel
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                // Mengatur setReadDataOnly(true) untuk membaca nilai hasil rumus, bukan rumusnya
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getTempName());
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                
                // Debug log untuk nama worksheet dan jumlah baris
                log_message('debug', 'Worksheet name: ' . $sheet->getTitle() . ', Highest row: ' . $highestRow);
                
                $successCount = 0;
                $errorCount = 0;
                
                for ($row = 2; $row <= $highestRow; $row++) {
                    $ckd = $sheet->getCell('A' . $row)->getValue(); // Column A
                    
                    // Skip empty rows
                    if (empty($ckd)) {
                        log_message('debug', 'Skipping empty row: ' . $row);
                        continue;
                    }
                    
                    // Break jika menemukan baris dengan CKD kosong (menandakan akhir data)
                    if (empty($ckd)) {
                        log_message('debug', 'Found empty CKD at row ' . $row . ', stopping import');
                        break;
                    }
                    
                    // Ambil data dari kolom lain
                    $periodRaw = $sheet->getCell('B' . $row)->getValue(); // Column B
                    $description = $sheet->getCell('C' . $row)->getValue(); // Column C
                    $part_no = $sheet->getCell('D' . $row)->getValue(); // Column D
                    $class = $sheet->getCell('E' . $row)->getValue(); // Column E
                    $beginning = $sheet->getCell('F' . $row)->getValue(); // Column F
                    
                    // Skip jika part_no kosong (data penting)
                    if (empty($part_no)) {
                        log_message('warning', 'Row ' . $row . ' skipped: Missing Part No');
                        $errorCount++;
                        continue;
                    }
                    
                    // Format tanggal period dengan benar
                    $period = null;
                    
                    if (is_numeric($periodRaw)) {
                        // Jika tanggal dalam format Excel numeric
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodRaw);
                        $period = $dateObj->format('Y-m-d');
                        log_message('debug', 'Converted numeric Excel date: ' . $periodRaw . ' to ' . $period);
                    } else if ($periodRaw instanceof \DateTime) {
                        // Jika sudah berupa objek DateTime
                        $period = $periodRaw->format('Y-m-d');
                    } else if (is_string($periodRaw) && !empty($periodRaw)) {
                        // Coba berbagai format tanggal umum
                        $formats = [
                            'd/m/y',     // 01/05/25
                            'd-m-y',     // 01-05-25
                            'Y-m-d',     // 2025-05-01
                            'd/m/Y',     // 01/05/2025
                            'm/d/Y',     // 05/01/2025
                            'd-m-Y'      // 01-05-2025
                        ];
                        
                        foreach ($formats as $format) {
                            $date = \DateTime::createFromFormat($format, $periodRaw);
                            if ($date !== false) {
                                $period = $date->format('Y-m-d');
                                log_message('debug', 'Parsed with format "' . $format . '" date "' . $periodRaw . '" as "' . $period . '"');
                                break;
                            }
                        }
                        
                        // Jika belum berhasil, coba parse secara umum
                        if ($period === null) {
                            try {
                                $date = new \DateTime($periodRaw);
                                $period = $date->format('Y-m-d');
                                log_message('debug', 'Parsed with DateTime constructor: "' . $periodRaw . '" as "' . $period . '"');
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to parse date: "' . $periodRaw . '" at row ' . $row . ': ' . $e->getMessage());
                            }
                        }
                    }
                    
                    // Jika semua metode gagal, gunakan tanggal hari ini
                    if ($period === null) {
                        $period = date('Y-m-d');
                        log_message('warning', 'Using today as fallback for unparseable date: "' . $periodRaw . '" at row ' . $row);
                    }
                    
                    // Pastikan class adalah angka
                    $class = is_numeric($class) ? $class : null;
                    
                    $rowData = [
                        'ckd' => $ckd,
                        'period' => $period,
                        'description' => $description,
                        'part_no' => $part_no,
                        'class' => $class,
                        'beginning' => $beginning
                    ];
                    
                    try {
                        if ($this->stockMaterialModel->save($rowData)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            log_message('error', 'Failed to save Material data at row ' . $row . ': ' . json_encode($this->stockMaterialModel->errors()));
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        log_message('error', 'Exception when saving Material data at row ' . $row . ': ' . $e->getMessage());
                    }
                }
                
                // Commit transaksi jika ada data yang berhasil disimpan
                if ($successCount > 0) {
                    $db->transCommit();
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => "Import completed: {$successCount} records added successfully, {$errorCount} failed"
                    ]);
                } else {
                    // Rollback jika tidak ada data yang berhasil disimpan
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => "Import failed: No records were imported successfully. Please check the log for details."
                    ]);
                }
            } catch (\Exception $e) {
                // Rollback jika terjadi exception
                if (isset($db) && $db->transStatus() === false) {
                    $db->transRollback();
                }
                log_message('error', 'Exception during Material import: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Import failed: " . $e->getMessage()
                ]);
            }
        } else {
            // Untuk form submission biasa
            $file = $this->request->getFile('excel_file');
            
            if (!$file->isValid()) {
                return redirect()->to('/admin/material/material-control')->with('error', 'Invalid file');
            }
            
            try {
                // Memulai transaksi database
                $db = \Config\Database::connect();
                $db->transBegin();
                
                // Baca file Excel
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getTempName());
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                
                $successCount = 0;
                $errorCount = 0;
                
                for ($row = 2; $row <= $highestRow; $row++) {
                    $ckd = $sheet->getCell('A' . $row)->getValue(); // Column A
                    
                    // Skip empty rows
                    if (empty($ckd)) {
                        continue;
                    }
                    
                    // Break jika menemukan baris dengan CKD kosong (menandakan akhir data)
                    if (empty($ckd)) {
                        break;
                    }
                    
                    // Ambil data dari kolom lain
                    $periodRaw = $sheet->getCell('B' . $row)->getValue(); // Column B
                    $description = $sheet->getCell('C' . $row)->getValue(); // Column C
                    $part_no = $sheet->getCell('D' . $row)->getValue(); // Column D
                    $class = $sheet->getCell('E' . $row)->getValue(); // Column E
                    $beginning = $sheet->getCell('F' . $row)->getValue(); // Column F
                    
                    // Skip jika part_no kosong (data penting)
                    if (empty($part_no)) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Format tanggal period dengan benar
                    $period = null;
                    
                    if (is_numeric($periodRaw)) {
                        // Jika tanggal dalam format Excel numeric
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodRaw);
                        $period = $dateObj->format('Y-m-d');
                    } else if ($periodRaw instanceof \DateTime) {
                        // Jika sudah berupa objek DateTime
                        $period = $periodRaw->format('Y-m-d');
                    } else if (is_string($periodRaw) && !empty($periodRaw)) {
                        // Coba berbagai format tanggal umum
                        $formats = [
                            'd/m/y',     // 01/05/25
                            'd-m-y',     // 01-05-25
                            'Y-m-d',     // 2025-05-01
                            'd/m/Y',     // 01/05/2025
                            'm/d/Y',     // 05/01/2025
                            'd-m-Y'      // 01-05-2025
                        ];
                        
                        foreach ($formats as $format) {
                            $date = \DateTime::createFromFormat($format, $periodRaw);
                            if ($date !== false) {
                                $period = $date->format('Y-m-d');
                                break;
                            }
                        }
                        
                        // Jika belum berhasil, coba parse secara umum
                        if ($period === null) {
                            try {
                                $date = new \DateTime($periodRaw);
                                $period = $date->format('Y-m-d');
                            } catch (\Exception $e) {
                                // Gagal parse tanggal
                                log_message('error', 'Failed to parse date: ' . $periodRaw . ' - ' . $e->getMessage());
                            }
                        }
                    }
                    
                    // Jika semua metode gagal, gunakan tanggal hari ini
                    if ($period === null) {
                        $period = date('Y-m-d');
                        log_message('warning', 'Using today as fallback for unparseable date at row ' . $row);
                    }
                    
                    // Pastikan class adalah angka
                    $class = is_numeric($class) ? $class : null;
                    
                    $rowData = [
                        'ckd' => $ckd,
                        'period' => $period,
                        'description' => $description,
                        'part_no' => $part_no,
                        'class' => $class,
                        'beginning' => $beginning
                    ];
                    
                    try {
                        if ($this->stockMaterialModel->save($rowData)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            log_message('error', 'Failed to save Material data at row ' . $row . ': ' . json_encode($this->stockMaterialModel->errors()));
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        log_message('error', 'Exception when saving Material data at row ' . $row . ': ' . $e->getMessage());
                    }
                }
                
                // Commit transaksi jika ada data yang berhasil disimpan
                if ($successCount > 0) {
                    $db->transCommit();
                    return redirect()->to('/admin/material/material-control')
                        ->with('success', "Import completed: {$successCount} records added successfully, {$errorCount} failed");
                } else {
                    // Rollback jika tidak ada data yang berhasil disimpan
                    $db->transRollback();
                    return redirect()->to('/admin/material/material-control')
                        ->with('error', "Import failed: No records were imported successfully. Please check the log for details.");
                }
            } catch (\Exception $e) {
                // Rollback jika terjadi exception
                if (isset($db) && $db->transStatus() === false) {
                    $db->transRollback();
                }
                return redirect()->to('/admin/material/material-control')
                    ->with('error', "Import failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Export data Material ke file Excel
     */
    public function exportMaterial()
    {
        // Ambil parameter filter dari request
        $ckd = $this->request->getGet('ckd');
        $part_no = $this->request->getGet('part_no');
        $class = $this->request->getGet('class');
        $period_start = $this->request->getGet('period_start');
        $period_end = $this->request->getGet('period_end');
        
        // Siapkan filter
        $filters = [];
        if (!empty($ckd)) $filters['ckd'] = $ckd;
        if (!empty($part_no)) $filters['part_no'] = $part_no;
        if (!empty($class)) $filters['class'] = $class;
        if (!empty($period_start)) $filters['period_start'] = $period_start;
        if (!empty($period_end)) $filters['period_end'] = $period_end;
        
        // Simpan filter ke session untuk digunakan kembali
        session()->set('material_filters', $filters);
        
        // Ambil data material berdasarkan filter
        $materials = $this->stockMaterialModel->getMaterials($filters);
        
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Material Control');
        
        // Set header
        $headers = ['CKD', 'Period', 'Description', 'Part No', 'Class', 'Beginning'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        foreach ($columns as $index => $column) {
            $sheet->setCellValue($column . '1', $headers[$index]);
        }
        
        // Style header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Isi data
        $row = 2;
        foreach ($materials as $material) {
            $sheet->setCellValue('A' . $row, $material['ckd'] ?? '');
            
            // Format tanggal jika ada
            if (!empty($material['period'])) {
                try {
                    $date = new \DateTime($material['period']);
                    $sheet->setCellValue('B' . $row, $date->format('d/m/y'));
                } catch (\Exception $e) {
                    $sheet->setCellValue('B' . $row, $material['period']);
                }
            } else {
                $sheet->setCellValue('B' . $row, '');
            }
            
            $sheet->setCellValue('C' . $row, $material['description'] ?? '');
            $sheet->setCellValue('D' . $row, $material['part_no'] ?? '');
            $sheet->setCellValue('E' . $row, $material['class'] ?? '');
            $sheet->setCellValue('F' . $row, $material['beginning'] ?? '');
            
            $row++;
        }
        
        // Style untuk data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        // Auto size kolom
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set border untuk semua data
        $lastRow = count($materials) > 0 ? ($row - 1) : 1;
        $dataRange = 'A1:F' . $lastRow;
        $sheet->getStyle($dataRange)->applyFromArray($dataStyle);
        
        // Set nama file
        $filename = 'material_control_export_' . date('YmdHis') . '.xlsx';
        
        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Halaman Shipment Schedule
     */
    public function shipmentSchedule()
    {
        // Ambil parameter filter dari request
        $inv_no = $this->request->getGet('inv_no');
        $item_no = $this->request->getGet('item_no');
        $class = $this->request->getGet('class');
        $sch_qty = $this->request->getGet('sch_qty');

        $filter = [];
        if (!empty($inv_no)) {
            $filter['inv_no'] = $inv_no;
        }
        if (!empty($item_no)) {
            $filter['item_no'] = $item_no;
        }
        if (!empty($class)) {
            $filter['class'] = $class;
        }
        if (!empty($sch_qty)) {
            $filter['sch_qty'] = $sch_qty;
        }

        session()->set('shipment_schedule_filter', $filter);
        log_message('info', 'Shipment Schedule filter: ' . json_encode($filter));

        $shipmentSchedule = $this->shipmentScheduleModel->getMaterials($filter);

        // Dapatkan nilai unik untuk dropdown filter
        $uniqueInvNos = $this->shipmentScheduleModel->getUniqueValues('inv_no');
        $uniqueItemNos = $this->shipmentScheduleModel->getUniqueValues('item_no');
        $uniqueClasses = $this->shipmentScheduleModel->getUniqueValues('class');
        $uniqueSchQtys = $this->shipmentScheduleModel->getUniqueValues('sch_qty');

        if (count($shipmentSchedule) === 0 && !empty($filter)) {
            log_message('info', 'No data found with filters, checking if any data exists in the table');
            $all_data = $this->shipmentScheduleModel->findAll();
            log_message('info', 'Total records in table without filter: ' . count($all_data));
        }

        $data = [
            'title' => 'Shipment Schedule',
            'shipmentSchedule' => $shipmentSchedule,
            'filter' => $filter,
            'uniqueInvNos' => $uniqueInvNos,
            'uniqueItemNos' => $uniqueItemNos,
            'uniqueClasses' => $uniqueClasses,
            'uniqueSchQtys' => $uniqueSchQtys
        ];

        return view('admin/material/shipment_schedule', $data);
    }
    
    /**
     * Mendapatkan data Shipment Schedule berdasarkan ID (untuk AJAX)
     */
    public function getShipmentSchedule($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID tidak valid'
            ]);
        }
        
        $shipment = $this->shipmentScheduleModel->find($id);
        
        if ($shipment) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $shipment
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
    
    /**
     * Menambah data Shipment Schedule baru
     */
    public function addShipmentSchedule()
    {
        // Log data yang diterima untuk debugging
        log_message('debug', 'addShipmentSchedule called with POST data: ' . json_encode($this->request->getPost()));
        
        // Validasi input
        $rules = [
            'inv_no' => 'required',
            'item_no' => 'required',
            'class' => 'required',
            'sch_qty' => 'required|numeric'
            // Tanggal tidak wajib diisi
        ];
        
        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Validation failed: ' . json_encode($errors));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $errors
            ]);
        }
        
        $data = [
            'inv_no' => $this->request->getPost('inv_no'),
            'item_no' => $this->request->getPost('item_no'),
            'class' => $this->request->getPost('class'),
            'sch_qty' => $this->request->getPost('sch_qty')
        ];
        
        // Tambahkan tanggal jika ada
        $etd_date = $this->request->getPost('etd_date');
        if (!empty($etd_date)) {
            $data['etd_date'] = $etd_date;
        }
        
        $eta_date = $this->request->getPost('eta_date');
        if (!empty($eta_date)) {
            $data['eta_date'] = $eta_date;
        }

        $eta_meina = $this->request->getPost('eta_meina');
        if (!empty($eta_meina)) {
            $data['eta_meina'] = $eta_meina;
        }
        
        try {
            if ($this->shipmentScheduleModel->insert($data)) {
                log_message('info', 'Shipment schedule added successfully: ' . json_encode($data));
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Data shipment schedule berhasil ditambahkan'
                ]);
            } else {
                $errors = $this->shipmentScheduleModel->errors();
                log_message('error', 'Failed to add shipment schedule: ' . json_encode($errors));
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan data: ' . implode(', ', $errors)
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception when adding shipment schedule: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mengupdate data Shipment Schedule
     */
    public function updateShipmentSchedule($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID tidak valid'
            ]);
        }
        
        // Validasi input
        $rules = [
            'inv_no' => 'required',
            'item_no' => 'required',
            'class' => 'required',
            'sch_qty' => 'required|numeric',
            'etd_date' => 'required',
            'eta_date' => 'required',
            'eta_meina' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }
        
        $data = [
            'inv_no' => $this->request->getPost('inv_no'),
            'item_no' => $this->request->getPost('item_no'),
            'class' => $this->request->getPost('class'),
            'sch_qty' => $this->request->getPost('sch_qty'),
            'etd_date' => $this->request->getPost('etd_date'),
            'eta_date' => $this->request->getPost('eta_date'),
            'eta_meina' => $this->request->getPost('eta_meina')
        ];
        
        try {
            $this->shipmentScheduleModel->update($id, $data);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data shipment schedule berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error updating shipment schedule: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Menghapus data Shipment Schedule
     */
    public function deleteShipmentSchedule($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID tidak valid'
            ]);
        }
        
        try {
            $this->shipmentScheduleModel->delete($id);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data shipment schedule berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting shipment schedule: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Import data Shipment Schedule dari file Excel
     */
    public function importShipmentSchedule()
    {
        $file = $this->request->getFile('excel_file');
        
        // Validasi file
        if (!$file->isValid() || $file->getError() > 0) {
            session()->setFlashdata('error', 'File tidak valid atau terjadi kesalahan saat upload');
            return redirect()->to('admin/material/shipment-schedule');
        }
        
        $ext = $file->getClientExtension();
        if ($ext != 'xlsx' && $ext != 'xls') {
            session()->setFlashdata('error', 'Format file harus Excel (.xlsx atau .xls)');
            return redirect()->to('admin/material/shipment-schedule');
        }
        
        try {
            // Pindahkan file ke temporary directory
            $file->move(WRITEPATH . 'uploads');
            $filepath = WRITEPATH . 'uploads/' . $file->getName();
            
            // Baca file Excel
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($ext));
            $spreadsheet = $reader->load($filepath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Mulai dari baris ke-2 (asumsikan baris 1 adalah header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $inv_no = $sheet->getCell('A' . $row)->getValue(); // Kolom A
                
                // Hentikan proses jika menemukan baris kosong
                if (empty($inv_no)) {
                    log_message('info', 'Import stopped at row ' . $row . ' due to empty inv_no');
                    break;
                }
                
                $item_no = $sheet->getCell('B' . $row)->getValue(); // Kolom B
                $class = $sheet->getCell('C' . $row)->getValue(); // Kolom C
                $sch_qty = $sheet->getCell('D' . $row)->getValue(); // Kolom D
                $etd_date_raw = $sheet->getCell('E' . $row)->getValue(); // Kolom E
                $eta_date_raw = $sheet->getCell('F' . $row)->getValue(); // Kolom F
                $eta_meina_raw = $sheet->getCell('G' . $row)->getValue(); // Kolom G
                
                // Konversi tanggal Excel ke format MySQL
                $etd_date = null;
                $eta_date = null;
                $eta_meina = null;
                
                // Konversi tanggal ETD
                if (!empty($etd_date_raw)) {
                    if (is_numeric($etd_date_raw)) {
                        // Jika tanggal dalam format Excel numeric
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($etd_date_raw);
                        $etd_date = $dateObj->format('Y-m-d');
                    } else {
                        $etd_date = $this->shipmentScheduleModel->formatExcelDate($etd_date_raw);
                    }
                }
                
                // Konversi tanggal ETA
                if (!empty($eta_date_raw)) {
                    if (is_numeric($eta_date_raw)) {
                        // Jika tanggal dalam format Excel numeric
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($eta_date_raw);
                        $eta_date = $dateObj->format('Y-m-d');
                    } else {
                        $eta_date = $this->shipmentScheduleModel->formatExcelDate($eta_date_raw);
                    }
                }
                
                // Konversi tanggal ETA Meina
                if (!empty($eta_meina_raw)) {
                    if (is_numeric($eta_meina_raw)) {
                        // Jika tanggal dalam format Excel numeric
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($eta_meina_raw);
                        $eta_meina = $dateObj->format('Y-m-d');
                    } else {
                        $eta_meina = $this->shipmentScheduleModel->formatExcelDate($eta_meina_raw);
                    }
                }
                
                // Validasi data
                if (empty($inv_no) || empty($item_no) || empty($class) || empty($sch_qty)) {
                    $errorCount++;
                    $errors[] = 'Baris ' . $row . ': Data tidak lengkap';
                    continue;
                }
                
                // Siapkan data untuk disimpan
                $data = [
                    'inv_no' => $inv_no,
                    'item_no' => $item_no,
                    'class' => $class,
                    'sch_qty' => $sch_qty,
                    'etd_date' => $etd_date,
                    'eta_date' => $eta_date,
                    'eta_meina' => $eta_meina
                ];
                
                // Cek apakah data sudah ada (berdasarkan kombinasi inv_no dan item_no)
                $existingData = $this->shipmentScheduleModel->where('inv_no', $inv_no)
                                                         ->where('item_no', $item_no)
                                                         ->first();
                
                if ($existingData) {
                    // Update data yang sudah ada
                    $this->shipmentScheduleModel->update($existingData['id'], $data);
                } else {
                    // Tambahkan data baru
                    $this->shipmentScheduleModel->insert($data);
                }
                
                $successCount++;
            }
            
            // Hapus file temporary
            unlink($filepath);
            
            // Set flashdata untuk notifikasi
            if ($errorCount > 0) {
                $errorMessage = 'Import selesai dengan ' . $successCount . ' data berhasil dan ' . $errorCount . ' data gagal. Error: ' . implode(', ', $errors);
                session()->setFlashdata('error', $errorMessage);
            } else {
                session()->setFlashdata('success', 'Import berhasil! ' . $successCount . ' data telah diimport.');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error importing shipment schedule: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        return redirect()->to('admin/material/shipment-schedule');
    }
    
    /**
     * Export data Shipment Schedule ke file Excel
     */
    public function exportShipmentSchedule()
    {
        // Ambil filter dari session jika ada
        $filter = session()->get('shipment_schedule_filter') ?? [];
        
        // Ambil data sesuai filter
        $shipmentSchedule = $this->shipmentScheduleModel->getMaterials($filter);
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $headers = ['Invoice No', 'Part No', 'Class', 'Schedule Qty', 'ETD Date', 'ETA Date', 'ETA Meina'];
        
        foreach (array_combine($columns, $headers) as $column => $header) {
            $sheet->setCellValue($column . '1', $header);
        }
        
        // Style untuk header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4e73df'],
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
        
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Isi data
        $row = 2;
        foreach ($shipmentSchedule as $item) {
            $sheet->setCellValue('A' . $row, $item['inv_no'] ?? '');
            $sheet->setCellValue('B' . $row, $item['item_no'] ?? '');
            $sheet->setCellValue('C' . $row, $item['class'] ?? '');
            $sheet->setCellValue('D' . $row, $item['sch_qty'] ?? '');
            
            // Format tanggal ETD jika ada
            if (!empty($item['etd_date'])) {
                try {
                    $date = new \DateTime($item['etd_date']);
                    $sheet->setCellValue('E' . $row, $date->format('d/m/y'));
                } catch (\Exception $e) {
                    $sheet->setCellValue('E' . $row, $item['etd_date']);
                }
            } else {
                $sheet->setCellValue('E' . $row, '');
            }
            
            // Format tanggal ETA jika ada
            if (!empty($item['eta_date'])) {
                try {
                    $date = new \DateTime($item['eta_date']);
                    $sheet->setCellValue('F' . $row, $date->format('d/m/y'));
                } catch (\Exception $e) {
                    $sheet->setCellValue('F' . $row, $item['eta_date']);
                }
            } else {
                $sheet->setCellValue('F' . $row, '');
            }
            
            // Format tanggal ETA Meina jika ada
            if (!empty($item['eta_meina'])) {
                try {
                    $date = new \DateTime($item['eta_meina']);
                    $sheet->setCellValue('G' . $row, $date->format('d/m/y'));
                } catch (\Exception $e) {
                    $sheet->setCellValue('G' . $row, $item['eta_meina']);
                }
            } else {
                $sheet->setCellValue('G' . $row, '');
            }
            
            $row++;
        }
        
        // Style untuk data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        
        // Auto size kolom
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set border untuk semua data
        $lastRow = count($shipmentSchedule) > 0 ? ($row - 1) : 1;
        $dataRange = 'A1:G' . $lastRow;
        $sheet->getStyle($dataRange)->applyFromArray($dataStyle);
        
        // Set nama file
        $filename = 'shipment_schedule_export_' . date('YmdHis') . '.xlsx';
        
        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
