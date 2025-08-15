<?php

namespace App\Controllers;

use App\Models\PlanningModel;
use App\Models\ActualModel;
use App\Models\FinishGoodModel;
use App\Models\SemiFinishGoodModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PPICController extends BaseController
{
    public function __construct()
    {
        $this->session = \Config\Services::session();
    }
    
    /**
     * Fungsi untuk mengkonversi angka ke nama kolom Excel
     * Contoh: 0 => A, 1 => B, 26 => AA, dst.
     *
     * @param int $num Nomor kolom (0-based)
     * @return string
     */
    private function numToExcelCol($num) {
        $letters = '';
        while ($num >= 0) {
            $remainder = $num % 26;
            $letters = chr(65 + $remainder) . $letters;
            $num = (int)($num / 26) - 1;
        }
        return $letters;
    }

    public function index()
    {
        return view('admin/ppic/index', [
            'title' => 'PPIC Dashboard'
        ]);
    }
    
    /**
     * Menampilkan halaman Finish Good
     */
    public function finishgood()
    {
        // Debug message untuk memastikan fungsi dipanggil
        log_message('debug', 'Fungsi finishgood() dipanggil');
        
        $finishGoodModel = new FinishGoodModel();
        
        // Mengambil data finish good
        $finishGoodData = $finishGoodModel->findAll();
        
        // Mengambil data unik untuk filter - pastikan tidak error jika tabel kosong
        try {
            $criteriaList = $finishGoodModel->select('criteria')->distinct()->findAll();
            $partNoList = $finishGoodModel->select('part_no')->distinct()->findAll();
            $classList = $finishGoodModel->select('class')->distinct()->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error saat mengambil data filter: ' . $e->getMessage());
            $criteriaList = [];
            $partNoList = [];
            $classList = [];
        }
        
        // Debug menggunakan log saja
        log_message('debug', 'Jumlah data Finish Good: ' . count($finishGoodData));
        
        return view('admin/ppic/finishgood', [
            'title' => 'Finish Good',
            'finish_good' => $finishGoodData,
            'criteria_list' => $criteriaList,
            'part_no_list' => $partNoList,
            'class_list' => $classList
        ]);
    }
    
    /**
     * Menampilkan halaman Semi Finish Good
     */
    public function semifinishgood()
    {
        // Debug message untuk memastikan fungsi dipanggil
        log_message('debug', 'Fungsi semifinishgood() dipanggil');
        
        $semiFinishGoodModel = new SemiFinishGoodModel();
        
        // Mengambil data semi finish good
        $semiFinishGoodData = $semiFinishGoodModel->findAll();
        
        // Mengambil data unik untuk filter - pastikan tidak error jika tabel kosong
        try {
            $criteriaList = $semiFinishGoodModel->select('criteria')->distinct()->findAll();
            $partNoList = $semiFinishGoodModel->select('part_no')->distinct()->findAll();
            $classList = $semiFinishGoodModel->select('class')->distinct()->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error saat mengambil data filter: ' . $e->getMessage());
            $criteriaList = [];
            $partNoList = [];
            $classList = [];
        }
        
        return view('admin/ppic/semifinishgood', [
            'title' => 'Semi Finish Good',
            'semifinish_good' => $semiFinishGoodData,
            'criteria_list' => $criteriaList,
            'part_no_list' => $partNoList,
            'class_list' => $classList
        ]);
    }
    
    /**
     * Import data Finish Good dari Excel
     */
    public function uploadFinishGood()
    {
        // Validasi file Excel
        $validationRule = [
            'excelFile' => [
                'label' => 'File Excel',
                'rules' => 'uploaded[excelFile]|ext_in[excelFile,xls,xlsx]|max_size[excelFile,10240]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        $file = $this->request->getFile('excelFile');
        
        if (!$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid'
            ]);
        }

        try {
            // Membuat log untuk debugging
            log_message('info', 'Starting Finish Good Excel import process');
            
            // Baca file Excel
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            log_message('info', 'Excel file loaded. Highest row: ' . $highestRow . ', Highest column: ' . $highestColumn);
            
            // Inisialisasi model
            $finishGoodModel = new FinishGoodModel();
            
            // Hitung jumlah data yang berhasil diimport
            $importedCount = 0;
            
            // Mulai dari baris ke-2 (baris 1 adalah header)
            for ($row = 2; $row <= $highestRow; $row++) {
                // Baca data dari setiap kolom
                $criteria = $sheet->getCell('A' . $row)->getValue();
                $period = $sheet->getCell('B' . $row)->getValue();
                $description = $sheet->getCell('C' . $row)->getValue();
                $partNo = $sheet->getCell('D' . $row)->getValue();
                $class = $sheet->getCell('E' . $row)->getValue();
                $endBal = (int)$sheet->getCell('F' . $row)->getValue();
                
                // Jika part_no kosong, hentikan pemrosesan karena sudah mencapai akhir data valid
                if (empty($partNo)) {
                    log_message('info', 'Menemukan baris dengan part_no kosong pada baris ' . $row . '. Menghentikan pemrosesan.');
                    break; // Keluar dari loop
                }
                
                // Lewati baris kosong (criteria kosong)
                if (empty($criteria)) {
                    continue;
                }
                
                // Konversi format tanggal Excel ke format MySQL
                if ($period) {
                    // Jika period adalah angka Excel date
                    if (is_numeric($period)) {
                        $excelDate = (int)$period;
                        $unixDate = ($excelDate - 25569) * 86400;
                        $period = gmdate('Y-m-d', $unixDate);
                    } else {
                        // Jika format sudah string, coba parse sebagai tanggal
                        try {
                            $dateObj = new \DateTime($period);
                            $period = $dateObj->format('Y-m-d');
                        } catch (\Exception $e) {
                            log_message('error', 'Error parsing date: ' . $period . ', Exception: ' . $e->getMessage());
                            $period = null;
                        }
                    }
                }
                
                // Persiapkan data untuk disimpan
                $data = [
                    'criteria' => $criteria,
                    'period' => $period,
                    'description' => $description,
                    'part_no' => $partNo,
                    'class' => $class,
                    'end_bal' => $endBal,
                ];
                
                // Debug log untuk setiap baris
                log_message('debug', 'Processing row ' . $row . ': ' . json_encode($data));
                
                // Simpan data ke database
                if ($finishGoodModel->insert($data)) {
                    $importedCount++;
                } else {
                    log_message('error', 'Failed to insert row ' . $row . ': ' . json_encode($finishGoodModel->errors()));
                }
            }
            
            log_message('info', 'Import complete. ' . $importedCount . ' records imported.');
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Berhasil import ' . $importedCount . ' data finish good'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Excel import error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Import data Semi Finish Good dari Excel
     */
    public function uploadSemiFinishGood()
    {
        // Validasi file Excel
        $validationRule = [
            'excelFile' => [
                'label' => 'File Excel',
                'rules' => 'uploaded[excelFile]|ext_in[excelFile,xls,xlsx]|max_size[excelFile,10240]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        $file = $this->request->getFile('excelFile');
        
        if (!$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid'
            ]);
        }

        try {
            // Membuat log untuk debugging
            log_message('info', 'Starting Semi Finish Good Excel import process');
            
            // Baca file Excel
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            log_message('info', 'Excel file loaded. Highest row: ' . $highestRow . ', Highest column: ' . $highestColumn);
            
            // Inisialisasi model
            $semiFinishGoodModel = new SemiFinishGoodModel();
            
            // Hitung jumlah data yang berhasil diimport
            $importedCount = 0;
            
            // Mulai dari baris ke-2 (baris 1 adalah header)
            for ($row = 2; $row <= $highestRow; $row++) {
                // Baca data dari setiap kolom
                $criteria = $sheet->getCell('A' . $row)->getValue();
                $period = $sheet->getCell('B' . $row)->getValue();
                $description = $sheet->getCell('C' . $row)->getValue();
                $partNo = $sheet->getCell('D' . $row)->getValue();
                $class = $sheet->getCell('E' . $row)->getValue();
                $beginingBal = (int)$sheet->getCell('F' . $row)->getValue();
                
                // Jika part_no kosong, hentikan pemrosesan karena sudah mencapai akhir data valid
                if (empty($partNo)) {
                    log_message('info', 'Menemukan baris dengan part_no kosong pada baris ' . $row . '. Menghentikan pemrosesan.');
                    break; // Keluar dari loop
                }
                
                // Lewati baris kosong (criteria kosong)
                if (empty($criteria)) {
                    continue;
                }
                
                // Konversi format tanggal Excel ke format MySQL
                if ($period) {
                    // Jika period adalah angka Excel date
                    if (is_numeric($period)) {
                        $excelDate = (int)$period;
                        $unixDate = ($excelDate - 25569) * 86400;
                        $period = gmdate('Y-m-d', $unixDate);
                    } else {
                        // Jika format sudah string, coba parse sebagai tanggal
                        try {
                            $dateObj = new \DateTime($period);
                            $period = $dateObj->format('Y-m-d');
                        } catch (\Exception $e) {
                            log_message('error', 'Error parsing date: ' . $period . ', Exception: ' . $e->getMessage());
                            $period = null;
                        }
                    }
                }
                
                // Persiapkan data untuk disimpan
                $data = [
                    'criteria' => $criteria,
                    'period' => $period,
                    'description' => $description,
                    'part_no' => $partNo,
                    'class' => $class,
                    'begining_bal' => $beginingBal,
                ];
                
                // Debug log untuk setiap baris
                log_message('debug', 'Processing row ' . $row . ': ' . json_encode($data));
                
                // Simpan data ke database
                if ($semiFinishGoodModel->insert($data)) {
                    $importedCount++;
                } else {
                    log_message('error', 'Failed to insert row ' . $row . ': ' . json_encode($semiFinishGoodModel->errors()));
                }
            }
            
            log_message('info', 'Import complete. ' . $importedCount . ' records imported.');
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Berhasil import ' . $importedCount . ' data semi finish good'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Excel import error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function planning()
    {
        // Ambil model Planning
        $planningModel = new PlanningModel();
        
        // Query data planning dengan sort berdasarkan model_no (asc)
        $planningData = $planningModel->orderBy('model_no', 'asc')->findAll();
        
        // Unique values untuk dropdown filter
        $updateValues = $planningModel->distinct()->select('update_value')->findAll();
        $prdCodes = $planningModel->distinct()->select('prd_code')->findAll();
        $modelNos = $planningModel->distinct()->select('model_no')->findAll();
        $classes = $planningModel->distinct()->select('class')->findAll();
        
        return view('admin/ppic/planning', [
            'title' => 'Planning Production',
            'planning_data' => $planningData,
            'update_values' => $updateValues,
            'prd_codes' => $prdCodes,
            'model_nos' => $modelNos,
            'classes' => $classes
        ]);
    }
    
    /**
     * Mendapatkan detail data planning berdasarkan ID
     * 
     * @param int $id ID dari data planning
     * @return ResponseInterface
     */
    public function getPlanningDetail($id = null)
    {
        // Log untuk debugging
        log_message('debug', 'getPlanningDetail dipanggil dengan ID: ' . $id);
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'Request is AJAX: ' . ($this->request->isAJAX() ? 'true' : 'false'));
        
        // Matikan sementara pengecekan AJAX untuk debugging
        // if (!$this->request->isAJAX()) {
        //     return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        // }
        
        if ($id === null) {
            log_message('error', 'getPlanningDetail: ID tidak valid (null)');
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        try {
            $planningModel = new PlanningModel();
            log_message('debug', 'Mencari data planning dengan ID: ' . $id);
            
            // Debug: cek tabel planning dan primary key
            log_message('debug', 'Tabel planning: ' . $planningModel->table);
            log_message('debug', 'Primary key: ' . $planningModel->primaryKey);
            
            $planning = $planningModel->find($id);
            
            // Debug hasil query
            log_message('debug', 'Hasil query: ' . ($planning ? 'Data ditemukan' : 'Data tidak ditemukan'));
            
            if (!$planning) {
                log_message('error', 'Data planning dengan ID ' . $id . ' tidak ditemukan');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Data planning tidak ditemukan']);
            }
            
            log_message('debug', 'Data planning berhasil diambil dengan ID: ' . $id);
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $planning
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat mengambil detail planning: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Menambahkan data planning baru
     * 
     * @return ResponseInterface
     */
    public function addPlanning()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        // Validasi input
        $rules = [
            'update_value' => 'required',
            'prd_code' => 'required',
            'model_no' => 'required',
            'class' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Persiapkan data untuk disimpan
        $planningModel = new PlanningModel();
        $data = [
            'update_value' => $this->request->getPost('update_value'),
            'prd_code' => $this->request->getPost('prd_code'),
            'model_no' => $this->request->getPost('model_no'),
            'class' => $this->request->getPost('class')
        ];
        
        // Tambahkan data untuk day_1 sampai day_31
        $total = 0;
        for ($day = 1; $day <= 31; $day++) {
            $dayValue = (float)($this->request->getPost('day_' . $day) ?? 0);
            $data['day_' . $day] = $dayValue;
            $total += $dayValue;
        }
        
        // Tambahkan total ke data
        $data['total'] = $total;
        
        // Simpan ke database
        try {
            $planningModel->insert($data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data planning berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menyimpan data planning: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan data. ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Memperbarui data planning yang sudah ada
     * 
     * @param int $id ID dari data planning
     * @return ResponseInterface
     */
    public function updatePlanning($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Validasi input
        $rules = [
            'update_value' => 'required',
            'prd_code' => 'required',
            'model_no' => 'required',
            'class' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Cek apakah data ada
        $planningModel = new PlanningModel();
        $planning = $planningModel->find($id);
        
        if (!$planning) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data planning tidak ditemukan']);
        }
        
        // Persiapkan data untuk diupdate
        $data = [
            'update_value' => $this->request->getPost('update_value'),
            'prd_code' => $this->request->getPost('prd_code'),
            'model_no' => $this->request->getPost('model_no'),
            'class' => $this->request->getPost('class')
        ];
        
        // Update data untuk day_1 sampai day_31
        $total = 0;
        for ($day = 1; $day <= 31; $day++) {
            $dayValue = (float)($this->request->getPost('day_' . $day) ?? 0);
            $data['day_' . $day] = $dayValue;
            $total += $dayValue;
        }
        
        // Update total ke data
        $data['total'] = $total;
        
        // Update database
        try {
            $planningModel->update($id, $data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data planning berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat memperbarui data planning: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui data. ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Menghapus data planning
     * 
     * @param int $id ID dari data planning
     * @return ResponseInterface
     */
    public function deletePlanning($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Cek apakah data ada
        $planningModel = new PlanningModel();
        $planning = $planningModel->find($id);
        
        if (!$planning) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data planning tidak ditemukan']);
        }
        
        // Hapus data
        try {
            $planningModel->delete($id);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data planning berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menghapus data planning: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus data. ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mendapatkan detail data finish good berdasarkan ID
     * 
     * @param int $id ID dari data finish good
     * @return ResponseInterface
     */
    public function getFinishGoodDetail($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        $finishGoodModel = new FinishGoodModel();
        $finishGood = $finishGoodModel->find($id);
        
        if (!$finishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data finish good tidak ditemukan']);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $finishGood
        ]);
    }

    /**
     * Menambahkan data finish good baru
     * 
     * @return ResponseInterface
     */
    public function addFinishGood()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        // Validasi input
        $rules = [
            'criteria' => 'required',
            'part_no' => 'required',
            'class' => 'required',
            'end_bal' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Persiapkan data untuk disimpan
        $finishGoodModel = new FinishGoodModel();
        $data = [
            'criteria' => $this->request->getPost('criteria'),
            'period' => $this->request->getPost('period'),
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'end_bal' => $this->request->getPost('end_bal')
        ];
        
        // Simpan ke database
        try {
            $finishGoodModel->insert($data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data finish good berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menyimpan data finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan data. ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Memperbarui data finish good yang sudah ada
     * 
     * @param int $id ID dari data finish good
     * @return ResponseInterface
     */
    public function updateFinishGood($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Validasi input
        $rules = [
            'criteria' => 'required',
            'part_no' => 'required',
            'class' => 'required',
            'end_bal' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Cek apakah data ada
        $finishGoodModel = new FinishGoodModel();
        $finishGood = $finishGoodModel->find($id);
        
        if (!$finishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data finish good tidak ditemukan']);
        }
        
        // Persiapkan data untuk diupdate
        $data = [
            'criteria' => $this->request->getPost('criteria'),
            'period' => $this->request->getPost('period'),
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'end_bal' => $this->request->getPost('end_bal')
        ];
        
        // Update database
        try {
            $finishGoodModel->update($id, $data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data finish good berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat memperbarui data finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui data. ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Menghapus data finish good
     * 
     * @param int $id ID dari data finish good
     * @return ResponseInterface
     */
    public function deleteFinishGood($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Cek apakah data ada
        $finishGoodModel = new FinishGoodModel();
        $finishGood = $finishGoodModel->find($id);
        
        if (!$finishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data finish good tidak ditemukan']);
        }
        
        // Hapus data
        try {
            $finishGoodModel->delete($id);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data finish good berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menghapus data finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus data. ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mendapatkan detail data semi finish good berdasarkan ID
     * 
     * @param int $id ID dari data semi finish good
     * @return ResponseInterface
     */
    public function getSemiFinishGoodDetail($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        $semiFinishGoodModel = new SemiFinishGoodModel();
        $semiFinishGood = $semiFinishGoodModel->find($id);
        
        if (!$semiFinishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data semi finish good tidak ditemukan']);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $semiFinishGood
        ]);
    }

    /**
     * Menambahkan data semi finish good baru
     * 
     * @return ResponseInterface
     */
    public function addSemiFinishGood()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        // Validasi input
        $rules = [
            'criteria' => 'required',
            'part_no' => 'required',
            'class' => 'required',
            'begining_bal' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Persiapkan data untuk disimpan
        $semiFinishGoodModel = new SemiFinishGoodModel();
        $data = [
            'criteria' => $this->request->getPost('criteria'),
            'period' => $this->request->getPost('period'),
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'begining_bal' => $this->request->getPost('begining_bal')
        ];
        
        // Simpan ke database
        try {
            $semiFinishGoodModel->insert($data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data semi finish good berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menyimpan data semi finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan data. ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Memperbarui data semi finish good yang sudah ada
     * 
     * @param int $id ID dari data semi finish good
     * @return ResponseInterface
     */
    public function updateSemiFinishGood($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Validasi input
        $rules = [
            'criteria' => 'required',
            'part_no' => 'required',
            'class' => 'required',
            'begining_bal' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
            ]);
        }
        
        // Cek apakah data ada
        $semiFinishGoodModel = new SemiFinishGoodModel();
        $semiFinishGood = $semiFinishGoodModel->find($id);
        
        if (!$semiFinishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data semi finish good tidak ditemukan']);
        }
        
        // Persiapkan data untuk diupdate
        $data = [
            'criteria' => $this->request->getPost('criteria'),
            'period' => $this->request->getPost('period'),
            'description' => $this->request->getPost('description'),
            'part_no' => $this->request->getPost('part_no'),
            'class' => $this->request->getPost('class'),
            'begining_bal' => $this->request->getPost('begining_bal')
        ];
        
        // Update database
        try {
            $semiFinishGoodModel->update($id, $data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data semi finish good berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat memperbarui data semi finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui data. ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Menghapus data semi finish good
     * 
     * @param int $id ID dari data semi finish good
     * @return ResponseInterface
     */
    public function deleteSemiFinishGood($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }
        
        if ($id === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }
        
        // Cek apakah data ada
        $semiFinishGoodModel = new SemiFinishGoodModel();
        $semiFinishGood = $semiFinishGoodModel->find($id);
        
        if (!$semiFinishGood) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data semi finish good tidak ditemukan']);
        }
        
        // Hapus data
        try {
            $semiFinishGoodModel->delete($id);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data semi finish good berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menghapus data semi finish good: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus data. ' . $e->getMessage()
            ]);
        }
    }
    

    // Metode untuk upload file planning production
    public function uploadPlanning()
    {
        // Validasi file upload
        $validationRule = [
            'planning_file' => [
                'label' => 'Planning Excel File',
                'rules' => 'uploaded[planning_file]|mime_in[planning_file,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]|max_size[planning_file,5120]',
                'errors' => [
                    'uploaded' => 'Silakan pilih file Excel untuk di-upload',
                    'mime_in' => 'File harus berupa Excel (.xls atau .xlsx)',
                    'max_size' => 'Ukuran file tidak boleh lebih dari 5MB'
                ]
            ],
        ];

        if (!$this->validate($validationRule)) {
            $errors = $this->validator->getErrors();
            return redirect()->to('/admin/ppic/planning')
                ->with('error', implode('<br>', $errors));
        }
        
        // Get uploaded file
        $file = $this->request->getFile('planning_file');
        if (!$file->isValid()) {
            return redirect()->to('/admin/ppic/planning')
                ->with('error', 'File tidak valid');
        }
        
        // Process Excel file
        try {
            // Coba pendekatan alternatif untuk menangani rumus Excel yang kompleks
            // Pendekatan 1: Baca langsung dari nilai sel, bukan rumus
            $reader = IOFactory::createReaderForFile($file->getTempName());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Log sedikit info tentang file Excel
            log_message('debug', 'Excel worksheet name: ' . $worksheet->getTitle());
            log_message('debug', 'Excel highest column: ' . $worksheet->getHighestColumn());
            log_message('debug', 'Excel highest row: ' . $worksheet->getHighestRow());
            
            // Baca data mentah dari worksheet dengan menggunakan nama kolom abjad
            $rows = [];        
            $highestRow = $worksheet->getHighestRow();
            $highestCol = $worksheet->getHighestColumn();
            
            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                // Konversi dari A ke indeks numerik
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCell($colLetter . $row)->getValue();
                    
                    // Jika sel berisi rumus, ambil nilai yang dihitung
                    if ($worksheet->getCell($colLetter . $row)->getDataType() == 'f') {
                        try {
                            $calculatedValue = $worksheet->getCell($colLetter . $row)->getCalculatedValue();
                            $cellValue = $calculatedValue;
                        } catch (\Exception $e) {
                            log_message('error', "Error calculating formula at {$colLetter}{$row}: " . $e->getMessage());
                            $cellValue = 0; // Default ke 0 jika rumus gagal dievaluasi
                        }
                    }
                    
                    $rowData[$colLetter] = $cellValue;
                }
                $rows[$row] = $rowData;
            }
                
            // Debug - melihat beberapa baris data pertama
            log_message('debug', 'Excel data sample: ' . json_encode(array_slice($rows, 0, 3, true)));
            
            // Jika tidak ada data di Excel
            if (empty($rows)) {
                return redirect()->to('/admin/ppic/planning')
                    ->with('error', 'File Excel kosong, tidak ada data yang dapat diimpor.');
            }
            
            // Pastikan ada data minimum (header + 1 baris data)
            if (count($rows) < 2) {
                return redirect()->to('/admin/ppic/planning')
                    ->with('error', 'File Excel tidak memiliki cukup data. Minimal harus ada header dan 1 baris data.');
            }
            
            // Menggunakan pendekatan sederhana - asumsikan struktur kolom tetap
            // Baris pertama adalah header
            $planningModel = new PlanningModel();
            $db = db_connect();
            $db->transStart();
            
            // Truncate existing planning data
            $db->table('planning_production')->truncate();
            
            $successCount = 0;
            $errorCount = 0;
            $log = [];
            
            // Proses baris data (skip baris header)
            $rowKeys = array_keys($rows);
            array_shift($rowKeys); // Buang header (baris pertama)
            $skippedRows = [];
            $processedRows = [];
            
            log_message('debug', "Total baris Excel yang dibaca: " . count($rowKeys));
            
            foreach ($rowKeys as $rowIdx) {
                $row = $rows[$rowIdx];
                
                // Skip baris kosong
                $nonEmptyValues = array_filter($row, function($val) {
                    return $val !== null && $val !== '';
                });
                
                if (count($nonEmptyValues) < 2) { // Minimal harus ada 2 nilai non-kosong
                    $skippedRows[] = "Baris {$rowIdx} dilewati: kurang dari 2 nilai non-kosong";
                    continue;
                }
                
                // VALIDASI BARU: Cek jika ada kolom dari B sampai AN yang berisi teks "Grand Total"
                // Ini menandakan data terakhir, jadi kita harus berhenti memproses
                $foundGrandTotal = false;
                
                // Array huruf kolom Excel dari B sampai AN
                $excelColumns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN'];
                
                foreach ($excelColumns as $col) {
                    if (isset($row[$col]) && !empty($row[$col]) && 
                        (stripos($row[$col], 'Grand Total') !== false || stripos($row[$col], 'GrandTotal') !== false)) {
                        $foundGrandTotal = true;
                        break;
                    }
                }
                
                if ($foundGrandTotal) {
                    log_message('debug', "Baris {$rowIdx}: Ditemukan tanda akhir data 'Grand Total'. Menghentikan pemrosesan.");
                    break; // Keluar dari loop karena sudah selesai memproses data valid
                }
                
                // Cek data penting
                $importantValues = [isset($row['B']) ? $row['B'] : null, isset($row['C']) ? $row['C'] : null, 
                                    isset($row['D']) ? $row['D'] : null, isset($row['E']) ? $row['E'] : null];
                
                // Debug kolom penting
                $debug_values = implode(', ', array_map(function($val) { return $val === null ? 'null' : "'$val'"; }, $importantValues));
                log_message('debug', "Baris {$rowIdx} kolom penting: {$debug_values}");
                
                // PERBAIKAN: Deteksi header lebih spesifik untuk tidak melewati data valid
                // Hanya baris 1-5 yang merupakan header, dan baris dengan kombinasi kata kunci spesifik
                $rowText = strtolower(implode(' ', array_map('strval', array_values($row))));
                
                // Jika kolom B, C, D (yang berisi model number dan kode produk) kosong, ini mungkin header
                $hasCriticalData = false;
                if ((!empty($row['B']) || !empty($row['C']) || !empty($row['D'])) && 
                    // Jika baris mengandung kode produk/model (huruf+angka), kemungkinan besar ini data
                    (preg_match('/[a-z][0-9]|[0-9][a-z]/i', $rowText))) {
                    $hasCriticalData = true;
                }
                
                // Header asli biasanya mengandung frasa-frasa ini secara bersamaan
                if (!$hasCriticalData || (
                    strpos($rowText, 'update value') !== false && 
                    strpos($rowText, 'prd code') !== false && 
                    strpos($rowText, 'model') !== false && 
                    strpos($rowText, 'remarks') !== false)) {
                    $skippedRows[] = "Baris {$rowIdx} dilewati: terdeteksi sebagai header (contains: {$rowText})";
                    continue;
                }
                
                // Data dasar - menggunakan kunci huruf sesuai dengan Excel
                // Parsing update_value sebagai decimal (kolom B) jika merupakan nilai numerik
                $updateValue = $row['B'] ?? '';
                if ($updateValue !== null && $updateValue !== '') {
                    if (is_numeric($updateValue)) {
                        // Konversi ke decimal dengan 2 digit belakang koma
                        $updateValue = round((float)$updateValue, 2);
                    } else {
                        // Coba ekstrak angka jika ada format khusus
                        if (preg_match('/[\-+]?[0-9]*\.?[0-9]+/', $updateValue, $matches)) {
                            $updateValue = round((float)$matches[0], 2);
                        } else {
                            $updateValue = trim(strval($updateValue));
                        }
                    }
                }
                
                $planningData = [
                    'update_value' => $updateValue,   // Kolom B (update_value/remark) - sekarang sebagai decimal
                    'prd_code'    => trim(strval($row['C'] ?? '')),   // Kolom C (prd_code)
                    'model_no'    => trim(strval($row['D'] ?? '')),   // Kolom D (model_no)
                    'class'       => trim(strval($row['E'] ?? ''))    // Kolom E (class)
                ];
                
                // Log data mentah untuk debugging
                log_message('debug', "Raw data row {$rowIdx}: B={$row['B']}, C={$row['C']}, D={$row['D']}, E={$row['E']}");
                
                // Debug data
                log_message('debug', "Data baris {$rowIdx}: " . json_encode($planningData));
                
                // Lakukan sanitasi data dasar
                foreach ($planningData as $key => $value) {
                    // Batasi panjang string untuk mencegah error database
                    $planningData[$key] = substr($value, 0, 100);
                    
                    // Konversi karakter non-valid
                    $planningData[$key] = preg_replace('/[^\x20-\x7E]/','', $planningData[$key]);
                }
                
                // Hitung total dan isi data hari
                $total = 0;
                
                // PERBAIKAN: Sesuaikan dengan kolom hari di Excel yang sebenarnya
                // Berdasarkan data sampel, hari dimulai dari kolom F dan bergeser karena struktur Excel
                // Kita akan memetakan kolom-kolom ini secara eksplisit
                
                // Debug: Tampilkan semua nama kolom dan nilai untuk baris ini
                $columnDebug = [];
                foreach ($row as $col => $val) {
                    if ($val !== null && $val !== '' && $val !== 0) {
                        $columnDebug[] = "{$col}:{$val}";
                    }
                }
                log_message('debug', "Row {$rowIdx} column values: " . implode(", ", $columnDebug));
                
                // PERBAIKAN: Mapping eksplisit untuk kolom tanggal berdasarkan gambar dari user
                // Dalam Excel biasanya data dimulai dari kolom F = day_1, G = day_2, dst
                // Tapi dari gambar terlihat kolom F berisi data tanggal 1, G = tanggal 2, dst.
                
                // Definisikan mapping kolom Excel ke tanggal
                $dayColumnMap = [
                    1 => 'F',  // Tanggal 1 = Kolom F
                    2 => 'G',  // Tanggal 2 = Kolom G
                    3 => 'H',  // Tanggal 3 = Kolom H
                    4 => 'I',  // Tanggal 4 = Kolom I
                    5 => 'J',  // Tanggal 5 = Kolom J
                    6 => 'K',  // Tanggal 6 = Kolom K
                    7 => 'L',  // Tanggal 7 = Kolom L
                    8 => 'M',  // Tanggal 8 = Kolom M
                    9 => 'N',  // Tanggal 9 = Kolom N
                    10 => 'O', // Tanggal 10 = Kolom O
                    11 => 'P', // Tanggal 11 = Kolom P
                    12 => 'Q', // Tanggal 12 = Kolom Q
                    13 => 'R', // Tanggal 13 = Kolom R
                    14 => 'S', // Tanggal 14 = Kolom S
                    15 => 'T', // Tanggal 15 = Kolom T
                    16 => 'U', // Tanggal 16 = Kolom U
                    17 => 'V', // Tanggal 17 = Kolom V
                    18 => 'W', // Tanggal 18 = Kolom W
                    19 => 'X', // Tanggal 19 = Kolom X
                    20 => 'Y', // Tanggal 20 = Kolom Y
                    21 => 'Z', // Tanggal 21 = Kolom Z
                    22 => 'AA', // Tanggal 22 = Kolom AA
                    23 => 'AB', // Tanggal 23 = Kolom AB
                    24 => 'AC', // Tanggal 24 = Kolom AC
                    25 => 'AD', // Tanggal 25 = Kolom AD
                    26 => 'AE', // Tanggal 26 = Kolom AE
                    27 => 'AF', // Tanggal 27 = Kolom AF
                    28 => 'AG', // Tanggal 28 = Kolom AG
                    29 => 'AH', // Tanggal 29 = Kolom AH
                    30 => 'AI', // Tanggal 30 = Kolom AI
                ];
                
                // Proses data untuk setiap hari
                for ($day = 1; $day <= 30; $day++) {
                    // Ambil kolom yang sesuai dari mapping
                    $dayCol = $dayColumnMap[$day] ?? null;
                    
                    if ($dayCol === null) {
                        $planningData["day_{$day}"] = 0;
                        continue;
                    }
                    
                    // Baca nilai dan pastikan numerik
                    $rawValue = $row[$dayCol] ?? null;
                    $dayValue = 0;
                    
                    // Konversi nilai ke decimal dengan 1 digit belakang koma
                    if ($rawValue !== null && $rawValue !== '') {
                        if (is_numeric($rawValue)) {
                            // Konversi ke decimal dengan 1 digit di belakang koma
                            $dayValue = round((float)$rawValue, 1);
                        } else {
                            // Coba parse string yang mungkin berisi angka
                            if (preg_match('/[0-9]*\.?[0-9]+/', $rawValue, $matches)) {
                                $dayValue = round((float)$matches[0], 1);
                            }
                        }
                    }
                    
                    // Simpan nilai dan tambahkan ke total
                    $planningData["day_{$day}"] = $dayValue;
                    $total += $dayValue;
                    
                    // Log nilai untuk debugging (tambahkan detail lebih banyak)
                    log_message('debug', "Row {$rowIdx}, Day {$day} (kolom {$dayCol}) = {$dayValue} (raw: {$rawValue})");
                }
                
                // Tambahkan total ke data
                $planningData['total'] = $total;
                
                try {
                    // Hanya insert jika data utama tidak kosong
                    if (!empty($planningData['update_value']) || !empty($planningData['prd_code']) || !empty($planningData['model_no'])) {
                        $planningModel->insert($planningData);
                        $successCount++;
                        $processedRows[] = "Baris {$rowIdx}: {$planningData['prd_code']} - {$planningData['model_no']}";
                    } else {
                        $skippedRows[] = "Baris {$rowIdx} dilewati: tidak ada data utama yang diisi";
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $log[] = "Error baris {$rowIdx}: " . $e->getMessage() . ", Data: " . json_encode($planningData);
                }
            }
            
            // Log ringkasan baris yang diproses dan dilewati
            log_message('debug', "=== RINGKASAN IMPORT PLANNING PRODUCTION ===");
            log_message('debug', "Total baris Excel: " . count($rowKeys));
            log_message('debug', "Total baris yang berhasil diimpor: {$successCount}");
            log_message('debug', "Total baris yang dilewati: " . count($skippedRows));
            if (!empty($skippedRows)) {
                log_message('debug', "Detail baris yang dilewati: \n" . implode("\n", $skippedRows));
            }
            
            // Selalu log untuk debugging
            if (!empty($log)) {
                log_message('error', 'Planning import errors: ' . implode("\n", $log));
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return redirect()->to('/admin/ppic/planning')
                    ->with('error', "Terjadi kesalahan saat mengimpor data. {$successCount} data berhasil, {$errorCount} data gagal.");
            }
            
            return redirect()->to('/admin/ppic/planning')
                ->with('success', "Import berhasil! {$successCount} data telah diimpor ke database.");
                
        } catch (\Exception $e) {
            log_message('error', 'Planning Excel import error: ' . $e->getMessage());
            return redirect()->to('/admin/ppic/planning')
                ->with('error', 'Terjadi kesalahan saat membaca file Excel: ' . $e->getMessage());
        }
    }

    
    // Untuk export data Planning ke Excel
    public function exportPlanning()
    {
        $planningModel = new PlanningModel();
        $planningData = $planningModel->findAll();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header row
        $sheet->setCellValue('A1', 'Update Value');
        $sheet->setCellValue('B1', 'Prd Code');
        $sheet->setCellValue('C1', 'Model No');
        $sheet->setCellValue('D1', 'Class');
        
        for ($i = 1; $i <= 31; $i++) {
            // Convert column index to letter (5 = E, 6 = F, etc)
            $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $sheet->setCellValue($colIndex . '1', $i);
        }
        
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(35) . '1', 'Total');
        
        // Data rows
        $row = 2;
        foreach ($planningData as $data) {
            $sheet->setCellValue('A' . $row, $data['update_value']);
            $sheet->setCellValue('B' . $row, $data['prd_code']);
            $sheet->setCellValue('C' . $row, $data['model_no']);
            $sheet->setCellValue('D' . $row, $data['class']);
            
            for ($i = 1; $i <= 31; $i++) {
                $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
                $sheet->setCellValue($colIndex . $row, $data['day_' . $i]);
            }
            
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(35) . $row, $data['total']);
            $row++;
        }
        
        $filename = 'planning_production_' . date('Y-m-d') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    
    /**
     * Menampilkan halaman data Actual Production
     */
    public function actual()
    {
        $actualModel = new ActualModel();
        
        // Fetch all actual data
        $data = [
            'title' => 'Actual Production',
            'actual_data' => $actualModel->findAll()
        ];
        
        // Fetch distinct values for filter
        $db = db_connect();
        $data['model_list'] = $db->table('actual_production')->select('model_no')->distinct()->orderBy('model_no', 'ASC')->get()->getResultArray();
        $data['class_list'] = $db->table('actual_production')->select('class')->where('class !=', '')->distinct()->orderBy('class', 'ASC')->get()->getResultArray();
        
        return view('admin/ppic/actual', $data);
    }

    /**
     * Mendapatkan data actual berdasarkan ID
     * 
     * @param int $id ID data actual
     * @return ResponseInterface
     */
    public function getActual($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses tidak diizinkan']);
        }
        
        $actualModel = new ActualModel();
        $actual = $actualModel->find($id);
        
        if (!$actual) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $actual
        ]);
    }
    
    /**
     * Menambahkan data actual production baru
     * 
     * @return ResponseInterface
     */
    public function addActual()
    {
        log_message('debug', 'addActual dipanggil');
        log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
        
        if (!$this->request->isAJAX()) {
            log_message('error', 'Request bukan AJAX');
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses tidak diizinkan']);
        }
        
        $rules = [
            'model_no' => 'required',
            'class' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            log_message('error', 'Validasi gagal: ' . json_encode($this->validator->getErrors()));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()),
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $actualModel = new ActualModel();
        
        $data = [
            'model_no' => $this->request->getPost('model_no'),
            'class' => $this->request->getPost('class')
        ];
        
        // Calculate total from day_1 to day_31
        $total = 0;
        for ($i = 1; $i <= 31; $i++) {
            $dayValue = (float)($this->request->getPost('day_' . $i) ?? 0);
            $data['day_' . $i] = $dayValue;
            $total += $dayValue;
        }
        
        $data['total'] = $total;
        log_message('debug', 'Data yang akan ditambahkan: ' . json_encode($data));
        
        try {
            $actualModel->insert($data);
            $insertId = $actualModel->getInsertID();
            log_message('debug', 'Insert berhasil, ID: ' . $insertId);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data Actual Production berhasil ditambahkan',
                'id' => $insertId
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat insert actual: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Memperbarui data actual production
     * 
     * @param int $id ID data actual
     * @return ResponseInterface
     */
    public function updateActual($id = null)
    {
        log_message('debug', 'updateActual dipanggil, ID: ' . $id);
        log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
        
        if (!$this->request->isAJAX()) {
            log_message('error', 'Request bukan AJAX');
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses tidak diizinkan']);
        }
        
        $actualModel = new ActualModel();
        
        // Check if data exists
        $actual = $actualModel->find($id);
        if (!$actual) {
            log_message('error', 'Data actual dengan ID ' . $id . ' tidak ditemukan');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        $rules = [
            'model_no' => 'required',
            'class' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            log_message('error', 'Validasi gagal: ' . json_encode($this->validator->getErrors()));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()),
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $data = [
            'model_no' => $this->request->getPost('model_no'),
            'class' => $this->request->getPost('class')
        ];
        
        // Calculate total from day_1 to day_31
        $total = 0;
        for ($i = 1; $i <= 31; $i++) {
            $dayValue = (float)($this->request->getPost('day_' . $i) ?? 0);
            $data['day_' . $i] = $dayValue;
            $total += $dayValue;
        }
        
        $data['total'] = $total;
        log_message('debug', 'Data yang akan diupdate: ' . json_encode($data));
        
        try {
            $actualModel->update($id, $data);
            log_message('debug', 'Update berhasil untuk ID: ' . $id);
            
            // Ambil data yang sudah diupdate untuk verifikasi
            $updatedData = $actualModel->find($id);
            log_message('debug', 'Data setelah update: ' . json_encode($updatedData));
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data Actual Production berhasil diperbarui',
                'data' => $updatedData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saat update actual: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Menghapus data actual production
     * 
     * @param int $id ID data actual
     * @return ResponseInterface
     */
    public function deleteActual($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses tidak diizinkan']);
        }
        
        $actualModel = new ActualModel();
        
        // Check if data exists
        $actual = $actualModel->find($id);
        if (!$actual) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        $actualModel->delete($id);
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data Actual Production berhasil dihapus'
        ]);
    }
    
    /**
     * Method lama untuk upload file Actual (diarahkan ke importActual untuk kompatibilitas)
     * 
     * @return ResponseInterface
     */
    public function uploadActual()
    {
        return $this->importActual();
    }
    
    /**
     * Import data Actual Production dari file Excel
     * 
     * @return ResponseInterface
     */
    public function importActual()
    {
        log_message('debug', 'Import Actual dipanggil');
        log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
        log_message('debug', 'FILES data: ' . json_encode($this->request->getFiles()));
        
        // Terima request baik AJAX maupun form biasa
        $isAjax = $this->request->isAJAX();
        log_message('debug', 'Request adalah ' . ($isAjax ? 'AJAX' : 'form biasa'));
        
        // Debugging semua data yang diterima
        log_message('debug', 'POST data: ' . json_encode($_POST));
        log_message('debug', 'FILES data: ' . json_encode($_FILES));
        
        // Ambil file tanpa validasi ketat
        $file = $this->request->getFile('excelFile');
        
        // Cek apakah file ada
        if (empty($_FILES) || !$file || !$file->isValid()) {
            log_message('error', 'File tidak ditemukan atau tidak valid');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File Excel tidak ditemukan. Pastikan Anda memilih file terlebih dahulu.'
            ]);
        }
        
        // Ambil ekstensi file
        $ext = strtolower($file->getExtension());
        if ($ext != 'xls' && $ext != 'xlsx') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File harus berformat Excel (.xls atau .xlsx)'
            ]);
        }

        // Ambil file path
        $filePath = $file->getTempName();
        
        try {
            try {
                // Cara yang lebih toleran untuk membaca file Excel
                log_message('debug', 'Mencoba load file Excel: ' . $filePath);
                
                // Deteksi tipe file Excel
                $inputFileType = IOFactory::identify($filePath);
                log_message('debug', 'Tipe file Excel terdeteksi: ' . $inputFileType);
                
                // Membuat reader sesuai tipe file
                $reader = IOFactory::createReader($inputFileType);
                $reader->setReadDataOnly(true); // Hanya membaca data, abaikan formatting
                
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                
                log_message('debug', 'Berhasil membaca file Excel');
            } catch (\Exception $e) {
                log_message('error', 'Error saat load file Excel: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'File Excel tidak dapat dibaca. Pastikan format file sesuai dan tidak rusak.'
                ]);
            }
            
            // Ambil data dari sheet secara manual (lebih toleran terhadap format)
            $rows = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            // Konversi huruf kolom ke angka (misalnya: 'AJ' -> 36)
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            log_message('debug', 'Highest Row: ' . $highestRow . ', Highest Column: ' . $highestColumn . ' (Index: ' . $highestColumnIndex . ')');
            
            // Baca data baris per baris (mulai dari baris 1)
            for ($row = 1; $row <= min($highestRow, 500); $row++) {
                $rowData = [];
                
                // Baca kolom per kolom dengan cara yang kompatibel dengan semua versi PhpSpreadsheet
                for ($col = 0; $col < min($highestColumnIndex, 40); $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $cellCoordinate = $colLetter . $row;
                    $cellValue = $sheet->getCell($cellCoordinate)->getValue();
                    $rowData[] = $cellValue;
                }
                
                // Tambahkan ke array rows
                $rows[] = $rowData;
            }
            
            // Buat instance dari model
            $actualModel = new ActualModel();
            
            // Langsung proses tanpa menghapus data lama
            log_message('debug', 'Memulai proses import tanpa menghapus data lama');
            
            // Log jumlah baris dalam file Excel
            log_message('debug', 'Total baris dalam file Excel: ' . count($rows));
            log_message('debug', 'Data baris pertama: ' . json_encode($rows[0] ?? []));
            
            // Hitung jumlah data yang berhasil diimport
            $importedCount = 0;
            
            // Loop semua baris
            foreach ($rows as $rowIndex => $row) {
                // Skip baris kosong atau hanya berisi header
                if ($rowIndex < 1) {
                    continue; // Skip header row
                }
                
                // Cek apakah array memiliki cukup elemen untuk model_no
                if (!isset($row[1])) {
                    log_message('warning', 'Baris ' . ($rowIndex + 1) . ' tidak memiliki cukup kolom, proses berhenti');
                    break; // Hentikan proses jika tidak ada model_no
                }
                
                // Cek apakah model_no kosong atau 0
                if (empty($row[1]) || $row[1] === '0' || $row[1] === 0) { 
                    log_message('debug', 'Menemukan baris dengan model_no kosong atau 0 pada row ' . ($rowIndex + 1) . ', proses berhenti');
                    break; // Hentikan proses karena ini dianggap data terakhir
                }
                
                // Log data baris untuk debugging
                log_message('debug', 'Memproses baris ' . ($rowIndex + 1) . ': ' . json_encode($row));
                
                // Persiapkan data, pastikan tipe data sesuai
                $data = [
                    'model_no' => trim((string)$row[1]), // Kolom B
                    'class' => isset($row[2]) ? trim((string)$row[2]) : '', // Kolom C
                ];
                
                // Log untuk debugging
                log_message('debug', 'Processing model_no: ' . $data['model_no'] . ', class: ' . $data['class']);
                
                // Ambil data actual dari kolom D (index 3) hingga kolom AH (index 33)
                $total = 0;
                for ($i = 1; $i <= 31; $i++) {
                    // Default value 0 jika tidak ada data atau data bukan numeric
                    $colIndex = $i + 3; // Kolom E = index 4 (day_1), dst.
                    
                    // Sanitasi dan validasi nilai
                    if (isset($row[$colIndex]) && (is_numeric($row[$colIndex]) || is_numeric(str_replace(',', '.', $row[$colIndex])))) {
                        // Konversi ke float, ganti koma dengan titik jika perlu
                        $value = (float)str_replace(',', '.', $row[$colIndex]);
                        
                        // Tambahan validasi angka negatif
                        $dayValue = $value < 0 ? 0 : $value;
                    } else {
                        $dayValue = 0;
                    }
                    
                    $data['day_'.$i] = $dayValue;
                    $total += $dayValue;
                }
                
                // Set total (jumlah dari semua hari)
                $data['total'] = $total;
                
                // Insert data ke database - gunakan ignore jika terjadi duplikasi
                try {
                    // Cek apakah data sudah ada
                    $existingData = $actualModel->where('model_no', $data['model_no'])
                                              ->where('class', $data['class'])
                                              ->first();
                    
                    if ($existingData) {
                        // Update data yang sudah ada
                        $actualModel->update($existingData['id'], $data);
                    } else {
                        // Insert data baru
                        $actualModel->insert($data);
                    }
                    
                    $importedCount++;
                } catch (\Exception $e) {
                    log_message('error', 'Error saat insert/update data baris ' . ($rowIndex + 1) . ': ' . $e->getMessage());
                    // Lanjutkan ke baris berikutnya
                }
            }
            
            // Pastikan respons selalu berhasil jika sampai titik ini
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data berhasil diimport. Total data: ' . $importedCount
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error saat import Excel: ' . $e->getMessage());
            // Berikan respons yang lebih bersahabat tanpa detail error teknis
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengimport data. Pastikan format Excel sesuai dengan petunjuk.'
            ]);
        }
    }
    
    /**
     * Export data Actual Production ke file Excel
     * 
     * @return ResponseInterface
     */
    public function exportActual()
    {
        // Log debug untuk memastikan method dipanggil
        log_message('debug', 'Export Actual dipanggil');
        
        $actualModel = new ActualModel();
        $actualData = $actualModel->findAll();
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Tambahkan header
        $headerRow = 1;
        $sheet->setCellValue('A'.$headerRow, 'No.');
        $sheet->setCellValue('B'.$headerRow, 'Model No');
        $sheet->setCellValue('C'.$headerRow, 'Class');
        $sheet->setCellValue('D'.$headerRow, 'Total');
        
        // Tambahkan header untuk day_1 sampai day_31
        for ($i = 1; $i <= 31; $i++) {
            // Gunakan kolom E (index 4) dan seterusnya untuk day_1 sampai day_31
            $col = $this->numToExcelCol($i + 3); // E = index 4, F = index 5, dst.
            $sheet->setCellValue($col.$headerRow, 'Day '.$i);
        }
        
        // Tambahkan data
        $row = $headerRow + 1;
        foreach ($actualData as $index => $data) {
            $sheet->setCellValue('A'.$row, $index + 1); // Nomor urut
            $sheet->setCellValue('B'.$row, $data['model_no']);
            $sheet->setCellValue('C'.$row, $data['class']);
            $sheet->setCellValue('D'.$row, $data['total']);
            
            // Tambahkan data untuk day_1 sampai day_31
            for ($i = 1; $i <= 31; $i++) {
                $col = $this->numToExcelCol($i + 3); // E = index 4, F = index 5, dst.
                $sheet->setCellValue($col.$row, $data['day_'.$i]);
            }
            
            $row++;
        }
        
        // Auto-size column width
        $lastColumn = $this->numToExcelCol(31 + 3); // Kolom terakhir yang digunakan
        
        // Set auto-size untuk setiap kolom yang digunakan
        for ($i = 0; $i <= 31 + 3; $i++) {
            $columnLetter = $this->numToExcelCol($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }
        
        // Set filename
        $filename = 'actual_production_'.date('YmdHis').'.xlsx';
        
        // Redirect output ke browser client
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}

