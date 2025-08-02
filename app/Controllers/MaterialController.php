<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MasterBomModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MaterialController extends BaseController
{
    protected $masterBomModel;
    
    public function __construct()
    {
        $this->masterBomModel = new MasterBomModel();
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
        $data = [
            'title' => 'Material Control'
        ];
        
        return view('admin/material/material_control', $data);
    }
    
    /**
     * Halaman Shipment Schedule
     */
    public function shipmentSchedule()
    {
        $data = [
            'title' => 'Shipment Schedule'
        ];
        
        return view('admin/material/shipment_schedule', $data);
    }
}
