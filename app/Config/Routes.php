<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::login');

// Authentication Routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/register/admin', 'AuthController::registerAdmin');
$routes->post('/register/admin', 'AuthController::attemptRegisterAdmin');
$routes->get('/register/user', 'AuthController::registerUser');
$routes->post('/register/user', 'AuthController::attemptRegisterUser');
$routes->get('/logout', 'AuthController::logout');


// Redirect default dashboard to new analytics dashboard
$routes->get('admin/dashboard', 'Admin\DashboardAnalyticsController::index', ['filter' => 'auth']);

// Dashboard Analytics routes
$routes->group('admin/dashboard-analytics', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Admin\DashboardAnalyticsController::index');
    $routes->get('model-class-options', 'Admin\DashboardAnalyticsController::getModelClassOptions');
    $routes->get('sales-data', 'Admin\DashboardAnalyticsController::getSalesData');
    $routes->get('production-data', 'Admin\DashboardAnalyticsController::getProductionData');
    $routes->get('dashboard-data', 'Admin\DashboardAnalyticsController::getDashboardData');
});

// Sales planing routes
$routes->get('admin/sales/sales', 'SalesController::index', ['filter' => 'auth']);
$routes->post('admin/sales/upload', 'SalesController::upload', ['filter' => 'auth']);
$routes->post('admin/sales/add', 'SalesController::add', ['filter' => 'auth']);
$routes->get('admin/sales/get/(:num)', 'SalesController::get/$1', ['filter' => 'auth']);
$routes->post('admin/sales/update', 'SalesController::update', ['filter' => 'auth']);
$routes->get('admin/sales/delete/(:num)', 'SalesController::delete/$1', ['filter' => 'auth']);
$routes->get('admin/sales/export', 'SalesController::export', ['filter' => 'auth']);

// Sales actual routes
$routes->get('admin/sales/actual', 'SalesActualController::index', ['filter' => 'auth']);
$routes->post('admin/sales/actual/upload', 'SalesActualController::upload', ['filter' => 'auth']);
$routes->post('admin/sales/actual/add', 'SalesActualController::add', ['filter' => 'auth']);
$routes->get('admin/sales/actual/get/(:num)', 'SalesActualController::get/$1', ['filter' => 'auth']);
$routes->post('admin/sales/actual/update', 'SalesActualController::update', ['filter' => 'auth']);
$routes->post('admin/sales/actual/delete/(:num)', 'SalesActualController::delete/$1', ['filter' => 'auth']);
$routes->get('admin/sales/actual/export', 'SalesActualController::export', ['filter' => 'auth']);

// Report routes
$routes->group('admin/report', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'ReportController::index');
    $routes->get('delivery-shortage', 'ReportController::deliveryShortage');
    $routes->post('delivery-shortage/data', 'ReportController::getDeliveryShortageData');
    $routes->get('delivery-shortage/export', 'ReportController::exportDeliveryShortage');
    $routes->get('delivery-shortage/models', 'ReportController::getAvailableModels');
    $routes->get('delivery-shortage/classes', 'ReportController::getAvailableClasses');
});

// Material Shortage Report routes
$routes->group('material-shortage', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'MaterialShortageController::index');
    $routes->post('data', 'MaterialShortageController::getMaterialShortageData');
    $routes->post('export', 'MaterialShortageController::exportMaterialShortage');
    $routes->get('models', 'MaterialShortageController::getAvailableModels');
    $routes->get('parts', 'MaterialShortageController::getAvailableParts');
    $routes->get('h-classes', 'MaterialShortageController::getAvailableHClasses');
    $routes->get('classes', 'MaterialShortageController::getAvailableClasses');
});

// Test routes untuk Material Shortage debugging
$routes->get('test/material-shortage', 'MaterialShortageTestController::testConsole');
$routes->get('test/material-shortage-json', 'MaterialShortageTestController::testHardcode');
$routes->get('test/material-shortage-api', 'MaterialShortageTestController::testApiFormat');
$routes->get('test/material-shortage-main', 'MaterialShortageTestController::testMainModel');

// Material Control routes
$routes->group('admin/material', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'MaterialController::index');
    
    // BOM routes
    $routes->get('bom', 'MaterialController::bom');
    $routes->get('add-bom', 'MaterialController::addBom');
    $routes->post('save-bom', 'MaterialController::saveBom');
    $routes->get('edit-bom/(:num)', 'MaterialController::editBom/$1');
    $routes->post('update-bom/(:num)', 'MaterialController::updateBom/$1');
    $routes->post('delete-bom/(:num)', 'MaterialController::deleteBom/$1');
    $routes->get('get-bom/(:num)', 'MaterialController::getBom/$1');
    $routes->post('import-bom', 'MaterialController::importBom');
    $routes->get('export-bom', 'MaterialController::exportBom');
    
    // Material Control routes
    $routes->get('material-control', 'MaterialController::materialControl');
    $routes->get('control', 'MaterialController::materialControl');
    $routes->get('add-material', 'MaterialController::addMaterial');
    $routes->post('save-material', 'MaterialController::saveMaterial');
    $routes->get('edit-material/(:num)', 'MaterialController::editMaterial/$1');
    $routes->post('update-material/(:num)', 'MaterialController::updateMaterial/$1');
    $routes->post('delete-material/(:num)', 'MaterialController::deleteMaterial/$1');
    $routes->get('get-material/(:num)', 'MaterialController::getMaterial/$1');
    $routes->post('import-material', 'MaterialController::importMaterial');
    $routes->get('export-material', 'MaterialController::exportMaterial');
    
    // Shipment Schedule routes
    $routes->get('shipment-schedule', 'MaterialController::shipmentSchedule');
    $routes->get('get-shipment-schedule/(:num)', 'MaterialController::getShipmentSchedule/$1');
    $routes->post('add-shipment-schedule', 'MaterialController::addShipmentSchedule');
    $routes->post('update-shipment-schedule/(:num)', 'MaterialController::updateShipmentSchedule/$1');
    $routes->post('delete-shipment-schedule/(:num)', 'MaterialController::deleteShipmentSchedule/$1');
    $routes->post('import-shipment-schedule', 'MaterialController::importShipmentSchedule');
    $routes->get('export-shipment-schedule', 'MaterialController::exportShipmentSchedule');
    
});

// PPIC routes
$routes->group('admin/ppic', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PPICController::index');

    // Routes untuk Planning Production
    $routes->get('planning', 'PPICController::planning');
    $routes->post('upload-planning', 'PPICController::uploadPlanning');
    $routes->get('export-planning', 'PPICController::exportPlanning');
    $routes->get('get-planning-detail/(:num)', 'PPICController::getPlanningDetail/$1');
    $routes->post('add-planning', 'PPICController::addPlanning');
    $routes->post('update-planning/(:num)', 'PPICController::updatePlanning/$1');
    $routes->post('delete-planning/(:num)', 'PPICController::deletePlanning/$1');
    
    // Routes untuk Actual Production
    $routes->get('actual', 'PPICController::actual');
    $routes->post('import-actual', 'PPICController::importActual');
    $routes->get('export-actual', 'PPICController::exportActual');
    $routes->get('get-actual/(:num)', 'PPICController::getActual/$1');
    $routes->post('add-actual', 'PPICController::addActual');
    $routes->post('update-actual/(:num)', 'PPICController::updateActual/$1');
    $routes->post('delete-actual/(:num)', 'PPICController::deleteActual/$1');
    
    // Route untuk Finish Good
    $routes->get('finishgood', 'PPICController::finishgood');
    $routes->post('upload-finishgood', 'PPICController::uploadFinishGood');
    $routes->get('get-finishgood-detail/(:num)', 'PPICController::getFinishGoodDetail/$1');
    $routes->post('add-finishgood', 'PPICController::addFinishGood');
    $routes->post('update-finishgood/(:num)', 'PPICController::updateFinishGood/$1');
    $routes->post('delete-finishgood/(:num)', 'PPICController::deleteFinishGood/$1');
    
    // Route untuk Semi Finish Good
    $routes->get('semifinishgood', 'PPICController::semifinishgood');
    $routes->post('upload-semifinishgood', 'PPICController::uploadSemiFinishGood');
    $routes->get('get-semifinishgood-detail/(:num)', 'PPICController::getSemiFinishGoodDetail/$1');
    $routes->post('add-semifinishgood', 'PPICController::addSemiFinishGood');
    $routes->post('update-semifinishgood/(:num)', 'PPICController::updateSemiFinishGood/$1');
    $routes->post('delete-semifinishgood/(:num)', 'PPICController::deleteSemiFinishGood/$1');
});