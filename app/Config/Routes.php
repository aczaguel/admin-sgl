<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Deskapp/Login::index');
$routes->get('/deskapp/dashboard','Deskapp/Dashboard::index',['filter' => 'auth']);
$routes->post('/deskapp/dashboard','Deskapp/Dashboard::index');


// $routes->add('/example/customers', 'Deskapp/Example::customers');
// $routes->add('/example/customers/(:segment)(/(:segment))?', 'Deskapp/Example::customers/$1/$2');

$routes->get('/tramites/tramite', 'Deskapp/Tramites::tramite',['filter' => 'auth']);
$routes->post('/tramites/tramite', 'Deskapp/Tramites::tramite');

$routes->get('/tramites/mios', 'Deskapp/Tramites::mios',['filter' => 'auth']);
$routes->post('/tramites/mios', 'Deskapp/Tramites::mios');

$routes->get('/tramites/tipo', 'Deskapp/Tramites::tipo',['filter' => 'auth']);
$routes->post('/tramites/tipo', 'Deskapp/Tramites::tipo');

$routes->get('/tramites/status', 'Deskapp/Tramites::status',['filter' => 'auth']);
$routes->post('/tramites/status', 'Deskapp/Tramites::status');

$routes->get('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1',['filter' => 'auth']);
$routes->post('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1');

$routes->get('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1',['filter' => 'auth']);
$routes->post('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1');


$routes->get('/documentos/documento', 'Deskapp/Documentos::documento',['filter' => 'auth']);
$routes->post('/documentos/status', 'Deskapp/Documentos::status');



$routes->get('/gestores/gestores', 'Deskapp/Gestores:gestores',['filter' => 'auth']);
$routes->post('/gestores/gestor', 'Deskapp/Gestores::gestor');

$routes->get('/cliente/cliente', 'Deskapp/cliente:cliente',['filter' => 'auth']);
$routes->post('/cliente/cliente', 'Deskapp/cliente::cliente');

$routes->get('/clidirecto/clidirecto', 'Deskapp/Clidirecto:clidirecto',['filter' => 'auth']);
$routes->post('/clidirecto/clidirecto', 'Deskapp/Clidirecto::clidirecto');

$routes->get('/clidirecto/ejecutivo', 'Deskapp/Clidirecto:ejecutivo',['filter' => 'auth']);
$routes->post('/clidirecto/ejecutivo', 'Deskapp/Clidirecto::ejecutivo');


$routes->get('/tradocstatus/documento', 'Deskapp/Tradocstatus:documento',['filter' => 'auth']);
$routes->post('/tradocstatus/documento', 'Deskapp/Tradocstatus::documento');


$routes->get('/bitacora/index/(:tramite_id)', 'Deskapp/Bitacora:index',['filter' => 'auth']);
$routes->post('/bitacora/index/(:tramite_id)', 'Deskapp/Bitacora::index');



$routes->get('/proceso/final', 'Deskapp/Proceso::final',['filter' => 'auth']);
$routes->post('/proceso/final', 'Deskapp/Proceso::final');




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
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
