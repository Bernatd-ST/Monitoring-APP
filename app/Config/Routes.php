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


$routes->get('admin/dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Sales routes
$routes->get('admin/sales', 'SalesController::index', ['filter' => 'auth']);
$routes->post('admin/sales/upload', 'SalesController::upload', ['filter' => 'auth']);

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
    
    // Shipment Schedule routes
    $routes->get('shipment-schedule', 'MaterialController::shipmentSchedule');
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