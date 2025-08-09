<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActualSalesModel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SalesActualController extends BaseController
{
    public function index()
    {
        $actualSalesModel = new ActualSalesModel();
        $data['sales_data'] = $actualSalesModel->findAll();
        
        // Mengambil daftar unik model_no untuk dropdown filter
        $db = db_connect();
        $data['model_list'] = $db->table('actual_sales')
                             ->select('model_no')
                             ->distinct()
                             ->orderBy('model_no', 'ASC')
                             ->get()
                             ->getResultArray();
        
        // Mengambil daftar unik class untuk dropdown filter
        $data['class_list'] = $db->table('actual_sales')
                             ->select('class')
                             ->where('class !=', '')
                             ->distinct()
                             ->orderBy('class', 'ASC')
                             ->get()
                             ->getResultArray();
        
        // Mengambil daftar unik product code untuk dropdown filter
        $data['prd_cd_list'] = $db->table('actual_sales')
                             ->select('prd_cd')
                             ->where('prd_cd !=', '')
                             ->distinct()
                             ->orderBy('prd_cd', 'ASC')
                             ->get()
                             ->getResultArray();
        
        return view('admin/sales/actual', [
            'title' => 'Actual Sales Data', 
            'sales_data' => $data['sales_data'],
            'model_list' => $data['model_list'],
            'class_list' => $data['class_list'],
            'prd_cd_list' => $data['prd_cd_list']
        ]);
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

    public function upload()
    {
        $file = $this->request->getFile('excel_file');
        $actualSalesModel = new ActualSalesModel();
        
        // Buat log file untuk debugging
        $log_path = WRITEPATH . 'logs/actual_sales_import_debug.log';
        file_put_contents($log_path, "=== Actual Sales Excel Import Debug Log ===\n", FILE_APPEND);

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
                    $dataCount = 0;
                    // Mulai dari baris 2 (asumsikan baris 1 adalah header)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        // Kolom A berisi model_no
                        $model_no = $sheet->getCell('A' . $row)->getValue();
                        // Kolom B berisi class
                        $class = $sheet->getCell('B' . $row)->getValue();
                        // Kolom C berisi sch_qty
                        $sch_qty = $sheet->getCell('C' . $row)->getValue() ?: 0;
                        // Kolom D berisi act_qty
                        $act_qty = $sheet->getCell('D' . $row)->getValue() ?: 0;
                        // Kolom E berisi prd_cd
                        $prd_cd = $sheet->getCell('E' . $row)->getValue();
                        // Kolom F berisi content
                        $content = $sheet->getCell('F' . $row)->getValue();
                        // Kolom G berisi shp_date (format: 20-Jun-25)
                        $shp_date_value = $sheet->getCell('G' . $row)->getValue();
                        
                        file_put_contents($log_path, "Row $row - Model: $model_no, Class: $class, ShipDate: $shp_date_value\n", FILE_APPEND);
                        
                        // Hanya lanjutkan jika model_no tidak kosong
                        if (!empty($model_no)) {
                            // Format tanggal dengan benar untuk database MySQL
                            $formatted_date = null;
                            
                            if (is_numeric($shp_date_value)) {
                                // Jika tanggal dalam format Excel numeric
                                $dateObj = Date::excelToDateTimeObject($shp_date_value);
                                $formatted_date = $dateObj->format('Y-m-d');
                                file_put_contents($log_path, "  Converted numeric date: $formatted_date\n", FILE_APPEND);
                            } else if (is_string($shp_date_value)) {
                                // Coba parse format tanggal seperti '20-Jun-25'
                                try {
                                    // Konversi format dd-MMM-yy ke Y-m-d
                                    $date = \DateTime::createFromFormat('d-M-y', $shp_date_value);
                                    if ($date) {
                                        $formatted_date = $date->format('Y-m-d');
                                        file_put_contents($log_path, "  Converted string date: $formatted_date\n", FILE_APPEND);
                                    } else {
                                        // Coba format lain jika gagal
                                        $date = date_create_from_format('d-M-Y', $shp_date_value);
                                        if ($date) {
                                            $formatted_date = date_format($date, 'Y-m-d');
                                            file_put_contents($log_path, "  Converted string date (alt format): $formatted_date\n", FILE_APPEND);
                                        } else {
                                            file_put_contents($log_path, "  Failed to parse date: $shp_date_value\n", FILE_APPEND);
                                        }
                                    }
                                } catch (\Exception $dateEx) {
                                    file_put_contents($log_path, "  Date parsing error: " . $dateEx->getMessage() . "\n", FILE_APPEND);
                                }
                            }
                            
                            $dataToInsert = [
                                'model_no' => $model_no,
                                'class'    => $class,
                                'sch_qty'  => $sch_qty,
                                'act_qty'  => $act_qty,
                                'prd_cd'   => $prd_cd,
                                'content'  => $content,
                                'shp_date' => $formatted_date
                            ];
                            
                            // Log data yang akan diinsert
                            file_put_contents($log_path, "Inserting data: " . json_encode($dataToInsert) . "\n", FILE_APPEND);
                            
                            $result = $actualSalesModel->insert($dataToInsert);
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
                        return redirect()->to('/admin/sales/actual')->with('error', 'Terjadi kesalahan saat menyimpan data ke database.');
                    }

                    file_put_contents($log_path, "Transaction completed successfully\n", FILE_APPEND);
                    return redirect()->to('/admin/sales/actual')->with('success', "File Excel berhasil di-upload dan $dataCount data telah diperbarui. Data akan ditampilkan di bawah.");
                
                } catch (\Exception $e) {
                    file_put_contents($log_path, "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
                    return redirect()->to('/admin/sales/actual')->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
                }
            } else {
                file_put_contents($log_path, "Invalid file extension: $ext\n", FILE_APPEND);
                return redirect()->to('/admin/sales/actual')->with('error', 'Format file tidak didukung. Harap upload file .xlsx atau .xls');
            }
        } else {
            file_put_contents($log_path, "File upload failed or invalid\n", FILE_APPEND);
            return redirect()->to('/admin/sales/actual')->with('error', 'Gagal meng-upload file. Silakan coba lagi.');
        }
    }
}