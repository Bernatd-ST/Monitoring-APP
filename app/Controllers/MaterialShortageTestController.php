<?php

namespace App\Controllers;

use App\Models\MaterialShortageTestModel;

class MaterialShortageTestController extends BaseController
{
    public function testHardcode()
    {
        $testModel = new MaterialShortageTestModel();
        
        // Test dengan data spesifik
        $result = $testModel->testHardcodeData('2025-08-01', '2025-08-03');
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [$result] // Wrap dalam array untuk konsistensi dengan API asli
        ]);
    }
    
    public function testConsole()
    {
        // Output ke console untuk debugging
        $testModel = new MaterialShortageTestModel();
        
        ob_start();
        $result = $testModel->testHardcodeData('2025-08-01', '2025-08-03');
        $output = ob_get_clean();
        
        // Return sebagai plain text
        return $this->response->setContentType('text/plain')->setBody($output);
    }
    
    /**
     * Test API yang menggunakan logika hardcode tapi format response seperti API asli
     */
    public function testApiFormat()
    {
        $testModel = new MaterialShortageTestModel();
        $result = $testModel->testHardcodeData('2025-08-01', '2025-08-03');
        
        // Format response seperti API asli
        return $this->response->setJSON([
            'success' => true,
            'data' => [$result], // Wrap dalam array
            'message' => 'Hardcode test data - logika sudah benar!'
        ]);
    }
    
    /**
     * Test menggunakan MaterialShortageModel asli dengan data hardcode
     */
    public function testMainModel()
    {
        $mainModel = new \App\Models\MaterialShortageModel();
        
        try {
            // Test dengan data yang sama seperti hardcode test
            $data = $mainModel->getMaterialShortageData(
                '2025-08-01',  // start_date
                '2025-08-03',  // end_date
                'A001TGA391AJ', // model_no
                null,          // h_class
                null,          // class
                false          // minus_only
            );
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => 'Main model test with hardcode data - should now work correctly!',
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Main model test failed'
            ]);
        }
    }
}
