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

// PPIC routes
$routes->group('admin/ppic', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PPICController::index');
    $routes->get('planning', 'PPICController::planning');
    $routes->get('actual', 'PPICController::actual');
    $routes->post('upload-planning', 'PPICController::uploadPlanning');
    $routes->post('upload-actual', 'PPICController::uploadActual');
    $routes->get('export-planning', 'PPICController::exportPlanning');
    $routes->get('export-actual', 'PPICController::exportActual');
    
    // Route untuk CRUD Planning
    $routes->get('get-planning-detail/(:num)', 'PPICController::getPlanningDetail/$1');
    $routes->post('add-planning', 'PPICController::addPlanning');
    $routes->post('update-planning/(:num)', 'PPICController::updatePlanning/$1');
    $routes->post('delete-planning/(:num)', 'PPICController::deletePlanning/$1');
    
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