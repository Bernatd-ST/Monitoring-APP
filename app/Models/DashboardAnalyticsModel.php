<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardAnalyticsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get unique model and class combinations for filters
     * 
     * @return array
     */
    public function getModelClassOptions()
    {
        $builder = $this->db->table('sales');
        $builder->select('model_no, class');
        $builder->distinct();
        $builder->orderBy('model_no', 'ASC');
        $builder->orderBy('class', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get dashboard data for the specified filters
     * 
     * @param string $startDate Start date in Y-m-d format
     * @param string $endDate End date in Y-m-d format
     * @param string $modelNo Optional model number filter
     * @param string $class Optional class filter
     * @return array
     */
    public function getDashboardData($startDate, $endDate, $modelNo = null, $class = null)
    {
        // Initialize result structure
        $result = [
            'labels' => [],
            'salesPlan' => [],
            'salesActual' => [],
            'productionPlan' => [],
            'productionActual' => [],
            'summary' => [
                'sales' => [
                    'totalPlan' => 0,
                    'totalActual' => 0,
                    'achievement' => 0
                ],
                'production' => [
                    'totalPlan' => 0,
                    'totalActual' => 0,
                    'efficiency' => 0
                ]
            ]
        ];

        // Generate date range
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        // Extract dates for labels
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayNum = (int)$date->format('d');
            $result['labels'][] = $dateStr;
            
            // Get data for each date
            $salesPlan = $this->getSalesPlan($modelNo, $class, $dayNum);
            $salesActual = $this->getSalesActual($modelNo, $class, $dayNum);
            $productionPlan = $this->getProductionPlan($modelNo, $class, $dayNum);
            $productionActual = $this->getProductionActual($modelNo, $class, $dayNum);
            
            // Add to arrays
            $result['salesPlan'][] = $salesPlan;
            $result['salesActual'][] = $salesActual;
            $result['productionPlan'][] = $productionPlan;
            $result['productionActual'][] = $productionActual;
            
            // Add to totals
            $result['summary']['sales']['totalPlan'] += $salesPlan;
            $result['summary']['sales']['totalActual'] += $salesActual;
            $result['summary']['production']['totalPlan'] += $productionPlan;
            $result['summary']['production']['totalActual'] += $productionActual;
        }
        
        // Calculate percentages
        if ($result['summary']['sales']['totalPlan'] > 0) {
            $result['summary']['sales']['achievement'] = round(
                ($result['summary']['sales']['totalActual'] / $result['summary']['sales']['totalPlan']) * 100
            );
        }
        
        if ($result['summary']['production']['totalPlan'] > 0) {
            $result['summary']['production']['efficiency'] = round(
                ($result['summary']['production']['totalActual'] / $result['summary']['production']['totalPlan']) * 100
            );
        }
        
        return $result;
    }

    /**
     * Get sales plan data for a specific day
     * 
     * @param string|null $modelNo Model number
     * @param string|null $class Class
     * @param int $day Day number
     * @return int
     */
    private function getSalesPlan($modelNo, $class, $day)
    {
        $builder = $this->db->table('sales');
        $builder->selectSum("day_{$day}");
        
        if ($modelNo) {
            $builder->where('model_no', $modelNo);
        }
        
        if ($class) {
            $builder->where('class', $class);
        }
        
        $result = $builder->get()->getRowArray();
        return (int)($result["day_{$day}"] ?? 0);
    }

    /**
     * Get sales actual data for a specific day
     * 
     * @param string|null $modelNo Model number
     * @param string|null $class Class
     * @param int $day Day number
     * @return int
     */
    private function getSalesActual($modelNo, $class, $day)
    {
        $builder = $this->db->table('actual_sales');
        $builder->selectSum('act_qty');
        
        // Extract day from shp_date field and filter by the specific day
        // Ignore month and year, only filter by day number
        $builder->where("DAY(shp_date) = {$day}");
        
        if ($modelNo) {
            $builder->where('model_no', $modelNo);
        }
        
        if ($class) {
            $builder->where('class', $class);
        }
        
        $result = $builder->get()->getRowArray();
        return (int)($result['act_qty'] ?? 0);
    }

    /**
     * Get production plan data for a specific day
     * 
     * @param string|null $modelNo Model number
     * @param string|null $class Class
     * @param int $day Day number
     * @return int
     */
    private function getProductionPlan($modelNo, $class, $day)
    {
        $builder = $this->db->table('planning_production');
        $builder->selectSum("day_{$day}");
        
        if ($modelNo) {
            $builder->where('model_no', $modelNo);
        }
        
        if ($class) {
            $builder->where('class', $class);
        }
        
        $result = $builder->get()->getRowArray();
        return (int)($result["day_{$day}"] ?? 0);
    }

    /**
     * Get production actual data for a specific day
     * 
     * @param string|null $modelNo Model number
     * @param string|null $class Class
     * @param int $day Day number
     * @return int
     */
    private function getProductionActual($modelNo, $class, $day)
    {
        $builder = $this->db->table('actual_production');
        $builder->selectSum("day_{$day}");
        
        if ($modelNo) {
            $builder->where('model_no', $modelNo);
        }
        
        if ($class) {
            $builder->where('class', $class);
        }
        
        $result = $builder->get()->getRowArray();
        return (int)($result["day_{$day}"] ?? 0);
    }
}
