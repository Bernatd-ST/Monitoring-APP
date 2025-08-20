<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class SalesController extends BaseController
{
    protected $salesModel;
    
    public function __construct()
    {
        $this->salesModel = new SalesModel();
    }
    public function index()
    {
        $salesModel = new SalesModel();
        $data['sales_data'] = $salesModel->findAll();
        
        // Mengambil daftar unik model_no untuk dropdown filter
        $db = db_connect();
        $data['model_list'] = $db->table('sales')
                             ->select('model_no')
                             ->distinct()
                             ->orderBy('model_no', 'ASC')
                             ->get()
                             ->getResultArray();
        
        // Mengambil daftar unik class untuk dropdown filter
        $data['class_list'] = $db->table('sales')
                             ->select('class')
                             ->where('class !=', '')
                             ->distinct()
                             ->orderBy('class', 'ASC')
                             ->get()
                             ->getResultArray();
        
        return view('admin/sales/sales', [
            'title' => 'Sales Data', 
            'sales_data' => $data['sales_data'],
            'model_list' => $data['model_list'],
            'class_list' => $data['class_list']
        ]);
    }
    
    public function add()
    {
        if ($this->request->isAJAX()) {
            // Validasi input
            $rules = [
                'model_no' => 'required',
                'class' => 'required'
            ];
            
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
                ]);
            }
            
            // Siapkan data untuk disimpan
            $data = [
                'model_no' => $this->request->getPost('model_no'),
                'class' => $this->request->getPost('class')
            ];
            
            // Tambahkan data schedule
            $total = 0;
            for ($i = 1; $i <= 31; $i++) {
                $value = (int)$this->request->getPost("schedule_{$i}") ?? 0;
                $data["schedule_{$i}"] = $value;
                $total += $value;
            }
            
            // Tambahkan total
            $data['total'] = $total;
            
            // Simpan data
            if ($this->salesModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Data berhasil ditambahkan'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menambahkan data'
                ]);
            }
        } else {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'message' => 'Akses ditolak'
            ]);
        }
    }
    
    public function get($id = null)
    {
        if ($this->request->isAJAX()) {
            if ($id === null) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            
            $data = $this->salesModel->find($id);
            
            if ($data) {
                return $this->response->setJSON([
                    'status' => true,
                    'data' => $data
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        } else {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'message' => 'Akses ditolak'
            ]);
        }
    }
    
    public function update()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            
            if (!$id) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            
            // Validasi input
            $rules = [
                'model_no' => 'required',
                'class' => 'required'
            ];
            
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors())
                ]);
            }
            
            // Siapkan data untuk diupdate
            $data = [
                'model_no' => $this->request->getPost('model_no'),
                'class' => $this->request->getPost('class')
            ];
            
            // Update data schedule
            $total = 0;
            for ($i = 1; $i <= 31; $i++) {
                $value = (int)$this->request->getPost("schedule_{$i}") ?? 0;
                $data["schedule_{$i}"] = $value;
                $total += $value;
            }
            
            // Update total
            $data['total'] = $total;
            
            // Update data
            if ($this->salesModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Data berhasil diperbarui'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal memperbarui data'
                ]);
            }
        } else {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'message' => 'Akses ditolak'
            ]);
        }
    }
    
    public function delete($id = null)
    {
        if ($this->request->isAJAX()) {
            if ($id === null) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            
            if ($this->salesModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menghapus data'
                ]);
            }
        } else {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'message' => 'Akses ditolak'
            ]);
        }
    }
    
    public function export()
    {
        // Ambil semua data sales
        $data = $this->salesModel->findAll();
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'Model No');
        $sheet->setCellValue('B1', 'Class');
        
        // Set header tanggal (1-31)
        $col = 'C';
        for ($i = 1; $i <= 31; $i++) {
            $sheet->setCellValue($col . '1', $i);
            $col++;
        }
        
        // Set header total
        $sheet->setCellValue('AH1', 'Total');
        
        // Style header
        $lastCol = 'AH';
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        
        // Isi data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['model_no']);
            $sheet->setCellValue('B' . $row, $item['class']);
            
            // Isi data schedule
            $col = 'C';
            for ($i = 1; $i <= 31; $i++) {
                $sheet->setCellValue($col . $row, $item["schedule_{$i}"] ?? 0);
                $col++;
            }
            
            // Isi total
            $sheet->setCellValue('AH' . $row, $item['total']);
            
            $row++;
        }
        
        // Auto size kolom - menggunakan metode manual untuk mendukung kolom multi-karakter (AA, AB, dll)
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
                  'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 
                  'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH'];
        
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Sales_Planning_Export_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output dan keluar
        $writer = new WriterXlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
    //     $salesModel = new SalesModel();

    //     // 1. Validasi File
    //     if ($file && $file->isValid() && !$file->hasMoved()) {
    //         $ext = $file->getClientExtension();
    //         if ($ext == 'xlsx' || $ext == 'xls') {
                
    //             $reader = new Xlsx();
    //             $spreadsheet = $reader->load($file->getTempName());
    //             $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //             // DEBUG: Lihat data dari Excel
    //             print("<pre>");
    //             print_r($sheet);
    //             print("</pre>");
    //             exit; // Hentikan eksekusi untuk melihat output

    //             $db = \Config\Database::connect();
    //             $db->transStart(); // mulai transaksi


    //             try {
    //                 //kosongkan tabel sebelum import data baru
    //                 $salesModel->truncate();

    //                 $firstRow = true;
    //                 foreach ($sheet as $row) {
    //                     if ($firstRow) {
    //                         $firstRow = false;
    //                         continue;
    //                     }

    //                     $dataToInsert = [
    //                         'model_no' => $row['A'] ?? null,
    //                         'class'    => $row['B'] ?? null,
    //                     ];

    //                     //Ambil data schedule dari kolom c (index 3) sampai AG (index 33)
    //                     $col = 'C';
    //                     for ($i = 1; $i <=31; $i++) {
    //                         $dataToInsert["schedule_{$i}"] = $row[$col] ?? null;
    //                         $col++; //pindah ke kolom berikutnya ( D, E, F, dst)
    //                     }

    //                     // Hanya insert jika model_no tidak kosong

    //                     if(!empty($dataToInsert['model_no'])) {
    //                         $salesModel->insert($dataToInsert);
    //                     }
    //                 }

    //                 $db->transComplete(); //selesaikan transaksi

    //                 if($db->transStatus() === false) {
    //                     return redirect()->to('/admin/sales/sales')->with('error', 'Terjadi kesalahan saat menyimpan data ke database.');
    //                 }

    //                 return redirect()->to('/admin/sales/sales')->with('success', 'File Excel berhasil di-upload dan data telah diperbarui.');
                
    //             } catch (\Exception $e) {
    //                 return redirect()->to('/admin/sales/sales')->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
    //             }
    //         } else {
    //             return redirect()->to('/admin/sales/sales')->with('error', 'Format file tidak didukung. Harap upload file .xlsx atau .xls');
    //         }
    //     } else {
    //         return redirect()->to('/admin/sales/sales')->with('error', 'Gagal meng-upload file. Silakan coba lagi.');
    //     }
    // }

    // Tambahkan debuging untuk melihat data yang akan dimasukkan
    public function upload()
    {
        $file = $this->request->getFile('excel_file');
        $salesModel = new SalesModel();
        
        // Buat log file untuk debugging
        $log_path = WRITEPATH . 'logs/excel_import_debug.log';
        file_put_contents($log_path, "=== Excel Import Debug Log ===\n", FILE_APPEND);

        // 1. Validasi File
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();
            if ($ext == 'xlsx' || $ext == 'xls') {
                
                $reader = new Xlsx();
                $spreadsheet = $reader->load($file->getTempName());
                $sheet = $spreadsheet->getActiveSheet();
                
                // Mengambil data baris per baris
                $highestRow = $sheet->getHighestRow();
                file_put_contents($log_path, "Highest Row: $highestRow\n", FILE_APPEND);
                
                $db = \Config\Database::connect();
                $db->transStart(); // mulai transaksi

                try {
                    // Kosongkan tabel sebelum import data baru
                    file_put_contents($log_path, "Table truncated\n", FILE_APPEND);

                    $dataCount = 0;
                    // Mulai dari baris 2 (asumsikan baris 1 adalah header)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        // Gunakan kolom B sebagai model_no karena itu yang memiliki data
                        $model_no = $sheet->getCell('B' . $row)->getValue();
                        // Kolom C berisi class
                        $class = $sheet->getCell('C' . $row)->getValue();
                        
                        file_put_contents($log_path, "Row $row - Model: $model_no, Class: $class\n", FILE_APPEND);
                        
                        // Hanya lanjutkan jika model_no tidak kosong dan bukan header
                        if (!empty($model_no) && $model_no !== "ModelNo.") {
                            $dataToInsert = [
                                'model_no' => $model_no,
                                'class'    => $class,
                            ];
                            
                            // Baca schedule untuk kolom D sampai AH (kolom 4 sampai 34)
                            $columnIndex = 4; // Mulai dari kolom D (indeks 3) untuk tanggal 1
                            $total = 0; // Inisialisasi total
                            
                            for ($i = 1; $i <= 31; $i++) {
                                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                                $schedule_value = $sheet->getCell($columnLetter . $row)->getValue();
                                $value = $schedule_value ?: 0;
                                $dataToInsert["schedule_{$i}"] = $value;
                                $total += (int)$value; // Tambahkan ke total
                                file_put_contents($log_path, "  Schedule $i ($columnLetter): " . ($value) . "\n", FILE_APPEND);
                                $columnIndex++;
                            }
                            
                            // Tambahkan total ke data yang akan diinsert
                            $dataToInsert['total'] = $total;
                            file_put_contents($log_path, "  Total: $total\n", FILE_APPEND);
                            
                            // Log data yang akan diinsert
                            file_put_contents($log_path, "Inserting data: " . json_encode($dataToInsert) . "\n", FILE_APPEND);
                            
                            $result = $salesModel->insert($dataToInsert);
                            file_put_contents($log_path, "Insert result: " . ($result ? "Success" : "Failed") . "\n", FILE_APPEND);
                            
                            if ($result) {
                                $dataCount++;
                            } else {
                                // Log error jika ada
                                file_put_contents($log_path, "DB Error: " . print_r($db->error(), true) . "\n", FILE_APPEND);
                            }
                        }
                    }

                    file_put_contents($log_path, "Total data inserted: $dataCount\n", FILE_APPEND);

                    $db->transComplete(); // selesaikan transaksi

                    if ($db->transStatus() === false) {
                        file_put_contents($log_path, "Transaction failed\n", FILE_APPEND);
                        return redirect()->to('/admin/sales/sales')->with('error', 'Terjadi kesalahan saat menyimpan data ke database.');
                    }

                    file_put_contents($log_path, "Transaction completed successfully\n", FILE_APPEND);
                    return redirect()->to('/admin/sales/sales')->with('success', "File Excel berhasil di-upload dan $dataCount data telah diperbarui. Data akan ditampilkan di bawah.");
                
                } catch (\Exception $e) {
                    file_put_contents($log_path, "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
                    return redirect()->to('/admin/sales/sales')->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
                }
            } else {
                file_put_contents($log_path, "Invalid file extension: $ext\n", FILE_APPEND);
                return redirect()->to('/admin/sales/sales')->with('error', 'Format file tidak didukung. Harap upload file .xlsx atau .xls');
            }
        } else {
            file_put_contents($log_path, "File upload failed or invalid\n", FILE_APPEND);
            return redirect()->to('/admin/sales/sales')->with('error', 'Gagal meng-upload file. Silakan coba lagi.');
        }
    }
}