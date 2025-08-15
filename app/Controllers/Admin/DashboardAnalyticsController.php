<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use App\Models\ActualSalesModel;
use App\Models\PlanningModel;
use App\Models\ActualModel;

class DashboardAnalyticsController extends BaseController
{
    protected $salesModel;
    protected $actualSalesModel;
    protected $planningModel;
    protected $actualModel;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
        $this->actualSalesModel = new ActualSalesModel();
        $this->planningModel = new PlanningModel();
        $this->actualModel = new ActualModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Analytics Dashboard',
        ];

        return view('admin/analytics/dashboard', $data);
    }

    /**
     * Get models and classes for filter dropdowns
     */
    public function getModelClassOptions()
    {
        // Get unique model_no and class combinations from sales table
        $models = $this->salesModel->select('model_no, class')
            ->groupBy('model_no, class')
            ->orderBy('model_no', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $models
        ]);
    }

    /**
     * Get sales data (plan vs actual) for the dashboard
     */
    public function getSalesData()
    {
        $modelNo = $this->request->getGet('model_no') ?? '';
        $class = $this->request->getGet('class') ?? '';
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01'); // Default to first day of current month
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t'); // Default to last day of current month

        // Parse dates to get day numbers
        $startDay = (int)date('d', strtotime($startDate));
        $endDay = (int)date('d', strtotime($endDate));
        $month = date('m', strtotime($startDate));
        $year = date('Y', strtotime($startDate));

        // Initialize response data structure
        $responseData = [
            'labels' => [], // Days (1-31)
            'salesPlan' => [],
            'salesActual' => [],
            'summary' => [
                'totalPlan' => 0,
                'totalActual' => 0,
                'achievement' => 0
            ]
        ];

        // Generate labels for the chart (days)
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['labels'][] = $day;
        }

        // Get sales plan data
        $salesPlanQuery = $this->salesModel->select('*');
        if (!empty($modelNo)) {
            $salesPlanQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $salesPlanQuery->where('class', $class);
        }
        $salesPlanData = $salesPlanQuery->findAll();

        // Process sales plan data
        $salesPlanByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($salesPlanData as $plan) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $scheduleKey = "schedule_{$day}";
                if (isset($plan[$scheduleKey])) {
                    $salesPlanByDay[$day] += (int)$plan[$scheduleKey];
                }
            }
        }

        // Get sales actual data
        $salesActualQuery = $this->actualSalesModel->select('shp_date, SUM(act_qty) as total_act_qty')
            ->groupBy('shp_date');
        
        if (!empty($modelNo)) {
            $salesActualQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $salesActualQuery->where('class', $class);
        }
        
        // Filter by date range
        $salesActualQuery->where('shp_date >=', $startDate)
            ->where('shp_date <=', $endDate);
            
        $salesActualData = $salesActualQuery->findAll();

        // Process sales actual data
        $salesActualByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($salesActualData as $actual) {
            $day = (int)date('d', strtotime($actual['shp_date']));
            if ($day >= $startDay && $day <= $endDay) {
                $salesActualByDay[$day] = (int)$actual['total_act_qty'];
            }
        }

        // Format data for response
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['salesPlan'][] = $salesPlanByDay[$day];
            $responseData['salesActual'][] = $salesActualByDay[$day];
            
            // Update totals
            $responseData['summary']['totalPlan'] += $salesPlanByDay[$day];
            $responseData['summary']['totalActual'] += $salesActualByDay[$day];
        }

        // Calculate achievement percentage
        if ($responseData['summary']['totalPlan'] > 0) {
            $responseData['summary']['achievement'] = round(
                ($responseData['summary']['totalActual'] / $responseData['summary']['totalPlan']) * 100, 
                2
            );
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    /**
     * Get production data (plan vs actual) for the dashboard
     */
    public function getProductionData()
    {
        $modelNo = $this->request->getGet('model_no') ?? '';
        $class = $this->request->getGet('class') ?? '';
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01'); // Default to first day of current month
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t'); // Default to last day of current month

        // Parse dates to get day numbers
        $startDay = (int)date('d', strtotime($startDate));
        $endDay = (int)date('d', strtotime($endDate));

        // Initialize response data structure
        $responseData = [
            'labels' => [], // Days (1-31)
            'productionPlan' => [],
            'productionActual' => [],
            'summary' => [
                'totalPlan' => 0,
                'totalActual' => 0,
                'efficiency' => 0
            ]
        ];

        // Generate labels for the chart (days)
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['labels'][] = $day;
        }

        // Get production plan data
        $productionPlanQuery = $this->planningModel->select('*');
        if (!empty($modelNo)) {
            $productionPlanQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $productionPlanQuery->where('class', $class);
        }
        $productionPlanData = $productionPlanQuery->findAll();

        // Process production plan data
        $productionPlanByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($productionPlanData as $plan) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $dayKey = "day_{$day}";
                if (isset($plan[$dayKey])) {
                    $productionPlanByDay[$day] += (int)$plan[$dayKey];
                }
            }
        }

        // Get production actual data
        $productionActualQuery = $this->actualModel->select('*');
        if (!empty($modelNo)) {
            $productionActualQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $productionActualQuery->where('class', $class);
        }
        $productionActualData = $productionActualQuery->findAll();

        // Process production actual data
        $productionActualByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($productionActualData as $actual) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $dayKey = "day_{$day}";
                if (isset($actual[$dayKey])) {
                    $productionActualByDay[$day] += (int)$actual[$dayKey];
                }
            }
        }

        // Format data for response
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['productionPlan'][] = $productionPlanByDay[$day];
            $responseData['productionActual'][] = $productionActualByDay[$day];
            
            // Update totals
            $responseData['summary']['totalPlan'] += $productionPlanByDay[$day];
            $responseData['summary']['totalActual'] += $productionActualByDay[$day];
        }

        // Calculate efficiency percentage
        if ($responseData['summary']['totalPlan'] > 0) {
            $responseData['summary']['efficiency'] = round(
                ($responseData['summary']['totalActual'] / $responseData['summary']['totalPlan']) * 100, 
                2
            );
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    /**
     * Get combined dashboard data (sales and production)
     */
    public function getDashboardData()
    {
        $modelNo = $this->request->getGet('model_no') ?? '';
        $class = $this->request->getGet('class') ?? '';
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        // Get sales data
        $salesData = $this->getSalesDataArray($modelNo, $class, $startDate, $endDate);
        
        // Get production data
        $productionData = $this->getProductionDataArray($modelNo, $class, $startDate, $endDate);

        // Combine data
        $responseData = [
            'labels' => $salesData['labels'],
            'salesPlan' => $salesData['salesPlan'],
            'salesActual' => $salesData['salesActual'],
            'productionPlan' => $productionData['productionPlan'],
            'productionActual' => $productionData['productionActual'],
            'summary' => [
                'sales' => $salesData['summary'],
                'production' => $productionData['summary']
            ]
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    /**
     * Helper method to get sales data as array
     */
    private function getSalesDataArray($modelNo, $class, $startDate, $endDate)
    {
        // Parse dates to get day numbers
        $startDay = (int)date('d', strtotime($startDate));
        $endDay = (int)date('d', strtotime($endDate));
        $month = date('m', strtotime($startDate));
        $year = date('Y', strtotime($startDate));

        // Initialize response data structure
        $responseData = [
            'labels' => [], // Days (1-31)
            'salesPlan' => [],
            'salesActual' => [],
            'summary' => [
                'totalPlan' => 0,
                'totalActual' => 0,
                'achievement' => 0
            ]
        ];

        // Generate labels for the chart (days)
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['labels'][] = $day;
        }

        // Get sales plan data
        $salesPlanQuery = $this->salesModel->select('*');
        if (!empty($modelNo)) {
            $salesPlanQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $salesPlanQuery->where('class', $class);
        }
        $salesPlanData = $salesPlanQuery->findAll();

        // Process sales plan data
        $salesPlanByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($salesPlanData as $plan) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $scheduleKey = "schedule_{$day}";
                if (isset($plan[$scheduleKey])) {
                    $salesPlanByDay[$day] += (int)$plan[$scheduleKey];
                }
            }
        }

        // Get sales actual data
        $salesActualQuery = $this->actualSalesModel->select('shp_date, SUM(act_qty) as total_act_qty')
            ->groupBy('shp_date');
        
        if (!empty($modelNo)) {
            $salesActualQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $salesActualQuery->where('class', $class);
        }
        
        // Filter by date range
        $salesActualQuery->where('shp_date >=', $startDate)
            ->where('shp_date <=', $endDate);
            
        $salesActualData = $salesActualQuery->findAll();

        // Process sales actual data
        $salesActualByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($salesActualData as $actual) {
            $day = (int)date('d', strtotime($actual['shp_date']));
            if ($day >= $startDay && $day <= $endDay) {
                $salesActualByDay[$day] = (int)$actual['total_act_qty'];
            }
        }

        // Format data for response
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['salesPlan'][] = $salesPlanByDay[$day];
            $responseData['salesActual'][] = $salesActualByDay[$day];
            
            // Update totals
            $responseData['summary']['totalPlan'] += $salesPlanByDay[$day];
            $responseData['summary']['totalActual'] += $salesActualByDay[$day];
        }

        // Calculate achievement percentage
        if ($responseData['summary']['totalPlan'] > 0) {
            $responseData['summary']['achievement'] = round(
                ($responseData['summary']['totalActual'] / $responseData['summary']['totalPlan']) * 100, 
                2
            );
        }

        return $responseData;
    }

    /**
     * Helper method to get production data as array
     */
    private function getProductionDataArray($modelNo, $class, $startDate, $endDate)
    {
        // Parse dates to get day numbers
        $startDay = (int)date('d', strtotime($startDate));
        $endDay = (int)date('d', strtotime($endDate));

        // Initialize response data structure
        $responseData = [
            'labels' => [], // Days (1-31)
            'productionPlan' => [],
            'productionActual' => [],
            'summary' => [
                'totalPlan' => 0,
                'totalActual' => 0,
                'efficiency' => 0
            ]
        ];

        // Generate labels for the chart (days)
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['labels'][] = $day;
        }

        // Get production plan data
        $productionPlanQuery = $this->planningModel->select('*');
        if (!empty($modelNo)) {
            $productionPlanQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $productionPlanQuery->where('class', $class);
        }
        $productionPlanData = $productionPlanQuery->findAll();

        // Process production plan data
        $productionPlanByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($productionPlanData as $plan) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $dayKey = "day_{$day}";
                if (isset($plan[$dayKey])) {
                    $productionPlanByDay[$day] += (int)$plan[$dayKey];
                }
            }
        }

        // Get production actual data
        $productionActualQuery = $this->actualModel->select('*');
        if (!empty($modelNo)) {
            $productionActualQuery->where('model_no', $modelNo);
        }
        if (!empty($class)) {
            $productionActualQuery->where('class', $class);
        }
        $productionActualData = $productionActualQuery->findAll();

        // Process production actual data
        $productionActualByDay = array_fill($startDay, $endDay - $startDay + 1, 0);
        foreach ($productionActualData as $actual) {
            for ($day = $startDay; $day <= $endDay; $day++) {
                $dayKey = "day_{$day}";
                if (isset($actual[$dayKey])) {
                    $productionActualByDay[$day] += (int)$actual[$dayKey];
                }
            }
        }

        // Format data for response
        for ($day = $startDay; $day <= $endDay; $day++) {
            $responseData['productionPlan'][] = $productionPlanByDay[$day];
            $responseData['productionActual'][] = $productionActualByDay[$day];
            
            // Update totals
            $responseData['summary']['totalPlan'] += $productionPlanByDay[$day];
            $responseData['summary']['totalActual'] += $productionActualByDay[$day];
        }

        // Calculate efficiency percentage
        if ($responseData['summary']['totalPlan'] > 0) {
            $responseData['summary']['efficiency'] = round(
                ($responseData['summary']['totalActual'] / $responseData['summary']['totalPlan']) * 100, 
                2
            );
        }

        return $responseData;
    }
}
