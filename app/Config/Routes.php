<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);

// url no válida
$routes->set404Override('App\Controllers\Error404Controller::index');

// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'HomeController::index');
$routes->get('/myLista', 'HomeController::myLista');

$routes->get('test', 'HomeController::myLista');
$routes->get('datadonut', 'GraficosController::dataDonut');

$routes->group("api", ["namespace" => "App\Controllers\Api"], function ($routes) {

    $routes->group("hospitales", function ($routes) {
        $routes->get("list", "HospitalController::hospitalesList");
        $routes->get("list/(:any)", "HospitalController::hospitalSearch/$1");
        $routes->post("new", "HospitalController::hospitalNew", ['filter' => 'authFilter:admin']);
        $routes->get("detalle/(:num)", "HospitalController::hospitalDetalle/$1");
        $routes->post("update/(:num)", "HospitalController::hospitalUpdate/$1");
        $routes->get("delete/(:num)", "HospitalController::hospitalDelete/$1", ['filter' => 'authFilter:admin']);
    });

    $routes->group("medicos", function ($routes) {
        $routes->get("list", "MedicoController::medicosList");
        $routes->get("list/(:any)", "MedicoController::medicoSearch/$1");
        $routes->post("new", "MedicoController::medicoNew");
        $routes->get("detalle/(:num)", "MedicoController::medicoDetalle/$1");
        $routes->post("update/(:num)", "MedicoController::medicoUpdate/$1");
        $routes->get("delete/(:num)", "MedicoController::medicoDelete/$1");
    });

    // Auth
    $routes->post("register", "UserController::register");
    $routes->post("login", "UserController::login");
    $routes->get("refreshToken", "UserController::refreshToken");
    $routes->get("profile", "UserController::details");

    // List
    $routes->get("usrList", "UserController::usrList");
    $routes->get("usrList/(:num)", "UserController::userDetall/$1");

    // Crud
    $routes->post("userUpdate/(:num)", "UserController::userUpdate/$1", ['filter' => 'authFilter:admin,usuario']);
    $routes->get("userDelete/(:num)", "UserController::userDelete/$1", ['filter' => 'authFilter:admin']);

    // Aux
    $routes->get("existeemail/(:any)", "UserController::existeemail/$1");
    $routes->post("imgUpload", "UserController::imgUpload");
    $routes->get("userDeleteFoto/(:num)", "UserController::userDeleteFoto/$1", ['filter' => 'authFilter:admin,usuario']);

    // $routes->get("usrList", "UserController::usrList", ['filter' => 'authFilter:admin,usuario']);
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
