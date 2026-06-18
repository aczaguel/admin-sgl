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

// Alias para que /deskapp sin acción vaya al dashboard
$routes->get('/deskapp','Deskapp/Dashboard::index',['filter' => 'auth']);
$routes->get('/deskapp/','Deskapp/Dashboard::index',['filter' => 'auth']);
$routes->get('/deskapp/auth/login', 'Deskapp/Login::index');
$routes->post('/deskapp/auth/login', 'Deskapp/Login::index');
$routes->get('/deskapp/dashboard','Deskapp/Dashboard::index',['filter' => 'auth']);
$routes->post('/deskapp/dashboard','Deskapp/Dashboard::index',['filter' => 'auth']);
$routes->post('/deskapp/','Deskapp/Dashboard::index',['filter' => 'auth']);

// Dashboard Cliente
$routes->get('/deskapp/clientes/dashboard', 'Deskapp/DashboardCliente::index',['filter' => 'auth']);
$routes->get('/deskapp/clientes/dashboard_data', 'Deskapp/DashboardCliente::data',['filter' => 'auth']);



// ============ DEBUG ROUTES (Remover en producción) ============
$routes->get('/deskapp/debug/session', 'Deskapp/DebugPermisos::session',['filter' => 'auth']);
$routes->get('/deskapp/debug/permissions', 'Deskapp/DebugPermisos::permissions',['filter' => 'auth']);
$routes->get('/deskapp/debug/check-dashboard', 'Deskapp/DebugPermisos::checkDashboard',['filter' => 'auth']);

// API externa versionada
$routes->post('/api/v1/tramites', 'Api\\V1\\Tramites::create', ['filter' => 'externalapiauth']);
$routes->get('/api/v1/tramites/referencia/(:segment)', 'Api\\V1\\Tramites::showByReference/$1', ['filter' => 'externalapiauth']);
$routes->get('/api/v1/tramites/(:num)', 'Api\\V1\\Tramites::show/$1', ['filter' => 'externalapiauth']);

// Tramites Cliente
// $routes->get('/deskapp/clientes/tramites', 'Deskapp/ClienteTramites::index',['filter' => 'auth']);
// $routes->get('/deskapp/clientes/tramites/data', 'Deskapp/ClienteTramites::data',['filter' => 'auth']);
// $routes->get('/deskapp/clientes/ver/(:num)(/(:any))?', 'Deskapp/ClienteTramites::show/$1',['filter' => 'auth']);
// $routes->post('/deskapp/clientes/ver/(:num)(/(:any))?', 'Deskapp/ClienteTramites::show/$1',['filter' => 'auth']);

// $routes->add('/example/customers', 'Deskapp/Example::customers');
// $routes->add('/example/customers/(:segment)(/(:segment))?', 'Deskapp/Example::customers/$1/$2');

$routes->get('/users/users', 'Deskapp/Users::users',['filter' => 'auth']);
$routes->post('/users/users', 'Deskapp/Users::users',['filter' => 'auth']);
// $routes->get('/users/users_mapa/(:num)', 'Deskapp/Users::users_mapa/$1',['filter' => 'auth']);
// $routes->post('/users/users_mapa/(:num)', 'Deskapp/Users::users_mapa/$1',['filter' => 'auth']);
$routes->add('/users/users/(:any)', 'Deskapp/Users::users',['filter' => 'auth']);
$routes->add('/users/users/(:any)/(:any)', 'Deskapp/Users::users',['filter' => 'auth']);

$routes->get('/deskapp/users/users', 'Deskapp/Users::users',['filter' => 'auth']);
$routes->post('/deskapp/users/users', 'Deskapp/Users::users',['filter' => 'auth']);
// $routes->get('/deskapp/users/users_mapa/(:num)', 'Deskapp/Users::users_mapa/$1',['filter' => 'auth']);
// $routes->post('/deskapp/users/users_mapa/(:num)', 'Deskapp/Users::users_mapa/$1',['filter' => 'auth']);
$routes->add('/deskapp/users/users/(:any)', 'Deskapp/Users::users',['filter' => 'auth']);
$routes->add('/deskapp/users/users/(:any)/(:any)', 'Deskapp/Users::users',['filter' => 'auth']);

$routes->get('/roles/roles', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->post('/roles/roles', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->add('/roles/roles/(:any)', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->add('/roles/roles/(:any)/(:any)', 'Deskapp/Roles::roles',['filter' => 'auth']);

// Toggle AJAX rol-permiso (para mapa de roles)
$routes->post('/roles/toggle_permission', 'Deskapp/Roles::toggle_permission',['filter' => 'auth']);
$routes->post('/deskapp/roles/toggle_permission', 'Deskapp/Roles::toggle_permission',['filter' => 'auth']);

// $routes->get('/roles/roles_mapa/(:num)', 'Deskapp/Roles::roles_mapa/$1',['filter' => 'auth']);
// $routes->post('/roles/roles_mapa/(:num)', 'Deskapp/Roles::roles_mapa/$1',['filter' => 'auth']);

// $routes->get('/deskapp/roles/roles_mapa/(:num)', 'Deskapp/Roles::roles_mapa/$1',['filter' => 'auth']);
// $routes->post('/deskapp/roles/roles_mapa/(:num)', 'Deskapp/Roles::roles_mapa/$1',['filter' => 'auth']);

$routes->get('/roles/role_permissions', 'Deskapp/Roles::role_permissions',['filter' => 'auth']);
$routes->post('/roles/role_permissions', 'Deskapp/Roles::role_permissions',['filter' => 'auth']);

$routes->get('/deskapp/roles/roles', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->post('/deskapp/roles/roles', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->add('/deskapp/roles/roles/(:any)', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->add('/deskapp/roles/roles/(:any)/(:any)', 'Deskapp/Roles::roles',['filter' => 'auth']);

$routes->get('/users/user_roles', 'Deskapp/Users::user_roles',['filter' => 'auth']);
$routes->post('/users/user_roles', 'Deskapp/Users::user_roles',['filter' => 'auth']);

// Toggle AJAX user-status (users.status)
$routes->post('/users/toggle_status', 'Deskapp/Users::toggle_status',['filter' => 'auth']);
$routes->post('/deskapp/users/toggle_status', 'Deskapp/Users::toggle_status',['filter' => 'auth']);

// Toggle AJAX user-permission override (us_user_permissions)
$routes->post('/users/toggle_user_permission', 'Deskapp/Users::toggle_user_permission',['filter' => 'auth']);
$routes->post('/deskapp/users/toggle_user_permission', 'Deskapp/Users::toggle_user_permission',['filter' => 'auth']);

$routes->get('/users/manage', 'Deskapp/Users::manage',['filter' => 'auth']);
$routes->post('/users/manage', 'Deskapp/Users::manage',['filter' => 'auth']);

$routes->get('/users/profile', 'Deskapp/Users::profile',['filter' => 'auth']);
$routes->post('/users/profile', 'Deskapp/Users::profile',['filter' => 'auth']);

$routes->get('/users/update_profile', 'Deskapp/Users::update_profile',['filter' => 'auth']);
$routes->post('/users/update_profile', 'Deskapp/Users::update_profile',['filter' => 'auth']);

$routes->get('/users/update_password', 'Deskapp/Users::update_password',['filter' => 'auth']);
$routes->post('/users/update_password', 'Deskapp/Users::update_password',['filter' => 'auth']);

$routes->post('/users/switch_debug_role', 'Deskapp/Users::switch_debug_role',['filter' => 'auth']);
$routes->post('/deskapp/users/switch_debug_role', 'Deskapp/Users::switch_debug_role',['filter' => 'auth']);


$routes->get('/tramites/demo_multigrid', 'Deskapp/Tramites::demo_multigrid',['filter' => 'auth']);
$routes->post('/tramites/demo_multigrid', 'Deskapp/Tramites::demo_multigrid',['filter' => 'auth']);

$routes->get('/tramites/add', 'Deskapp/Tramites::add',['filter' => 'auth']);
$routes->post('/tramites/add', 'Deskapp/Tramites::add',['filter' => 'auth']);

$routes->get('/tramites/insert', 'Deskapp/Tramites::insert',['filter' => 'auth']);
$routes->post('/tramites/insert', 'Deskapp/Tramites::insert',['filter' => 'auth']);

$routes->get('/tramites/single_documentostatus', 'Deskapp/Tramites::single_documentostatus',['filter' => 'auth']);
$routes->post('/tramites/single_documentostatus', 'Deskapp/Tramites::single_documentostatus',['filter' => 'auth']);

$routes->get('/tramites/single_documentostatus/(:id)', 'Deskapp/Tramites::single_documentostatus/$1',['filter' => 'auth']);
$routes->post('/tramites/single_documentostatus/(:id)', 'Deskapp/Tramites::single_documentostatus/$1',['filter' => 'auth']);

$routes->get('/tramites/upload_comprobante/(:id)', 'Deskapp/Tramites::upload_comprobante/$1',['filter' => 'auth']);
$routes->post('/tramites/upload_comprobante/(:id)', 'Deskapp/Tramites::upload_comprobante/$1',['filter' => 'auth']);

$routes->get('/tramites/delete_comprobante/(:id)', 'Deskapp/Tramites::delete_comprobante/$1',['filter' => 'auth']);
$routes->post('/tramites/delete_comprobante/(:id)', 'Deskapp/Tramites::delete_comprobante/$1',['filter' => 'auth']);


$routes->get('/tramites/single_evidencias/(:id)', 'Deskapp/Tramites::single_evidencias/$1',['filter' => 'auth']);
$routes->post('/tramites/single_evidencias/(:id)', 'Deskapp/Tramites::single_evidencias/$1',['filter' => 'auth']);

$routes->get('/tramites/single_pago_derechos/(:id)', 'Deskapp/Tramites::single_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/tramites/single_pago_derechos/(:id)', 'Deskapp/Tramites::single_pago_derechos/$1',['filter' => 'auth']);


$routes->get('/tramites/single_pago_gestor/(:id)', 'Deskapp/Tramites::single_pago_gestor/$1',['filter' => 'auth']);
$routes->post('/tramites/single_pago_gestor/(:id)', 'Deskapp/Tramites::single_pago_gestor/$1',['filter' => 'auth']);


$routes->get('/tramites/single_cobro_cliente/(:id)', 'Deskapp/Tramites::single_cobro_cliente/$1',['filter' => 'auth']);
$routes->post('/tramites/single_cobro_cliente/(:id)', 'Deskapp/Tramites::single_cobro_cliente/$1',['filter' => 'auth']);



$routes->get('/tramites/getEjecutivosByClienteId/(:num)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1',['filter' => 'auth']);
$routes->post('/tramites/getEjecutivosByClienteId/(:num)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1',['filter' => 'auth']);
$routes->get('/deskapp/tramites/getEjecutivosByClienteId/(:num)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1',['filter' => 'auth']);
$routes->post('/deskapp/tramites/getEjecutivosByClienteId/(:num)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1',['filter' => 'auth']);



$routes->get('/deskapp/tramites_masivos/import', 'Deskapp/TramitesMasivos::import',['filter' => 'auth']);
$routes->post('/deskapp/tramites_masivos/preview', 'Deskapp/TramitesMasivos::preview',['filter' => 'auth']);
$routes->post('/deskapp/tramites_masivos/save_row', 'Deskapp/TramitesMasivos::save_row',['filter' => 'auth']);

$routes->get('/tramites/update_save/(:id)', 'Deskapp/Tramites::update_save/$1',['filter' => 'auth']);
$routes->post('/tramites/update_save/(:id)', 'Deskapp/Tramites::update_save/$1',['filter' => 'auth']);

$routes->get('/tramites/tramite', 'Deskapp/Tramites::tramite',['filter' => 'auth']);
$routes->post('/tramites/tramite', 'Deskapp/Tramites::tramite',['filter' => 'auth']);

$routes->get('/tramites/tramite_2024', 'Deskapp/Tramites::tramite_2024',['filter' => 'auth']);
$routes->post('/tramites/tramite_2024', 'Deskapp/Tramites::tramite_2024',['filter' => 'auth']);

$routes->get('/tramites/tramite_2025', 'Deskapp/Tramites::tramite_2025',['filter' => 'auth']);
$routes->post('/tramites/tramite_2025', 'Deskapp/Tramites::tramite_2025',['filter' => 'auth']);

$routes->get('/tramites/mios', 'Deskapp/Tramites::mios',['filter' => 'auth']);
$routes->post('/tramites/mios', 'Deskapp/Tramites::mios',['filter' => 'auth']);

$routes->get('/wizard', 'Deskapp/Wizard::index',['filter' => 'auth']);
$routes->post('/wizard/step1', 'Deskapp/Wizard::step1',['filter' => 'auth']);
$routes->post('/wizard/step2', 'Deskapp/Wizard::step2',['filter' => 'auth']);
$routes->post('/wizard/step3', 'Deskapp/Wizard::step3',['filter' => 'auth']);
$routes->post('/wizard/complete', 'Deskapp/Wizard::complete',['filter' => 'auth']);

$routes->get('/tramites/recoleccion', 'Deskapp/Tramites::recoleccion',['filter' => 'auth']);
$routes->post('/tramites/recoleccion', 'Deskapp/Tramites::recoleccion',['filter' => 'auth']);

$routes->get('/tramites/en_tramite', 'Deskapp/Tramites::en_tramite',['filter' => 'auth']);
$routes->post('/tramites/en_tramite', 'Deskapp/Tramites::en_tramite',['filter' => 'auth']);

$routes->get('/tramites/autorizar', 'Deskapp/Tramites::autorizar',['filter' => 'auth']);
$routes->post('/tramites/autorizar', 'Deskapp/Tramites::autorizar',['filter' => 'auth']);

$routes->get('/tramites/tipo', 'Deskapp/Tramites::tipo',['filter' => 'auth']);
$routes->post('/tramites/tipo', 'Deskapp/Tramites::tipo',['filter' => 'auth']);

$routes->get('/tramites/status', 'Deskapp/Tramites::status',['filter' => 'auth']);
$routes->post('/tramites/status', 'Deskapp/Tramites::status',['filter' => 'auth']);

// Ruta de auditoría del trámite
$routes->get('/tramites/audit_search', 'Deskapp/Tramites::audit_search',['filter' => 'auth']);
$routes->get('/tramites/audit_timeline/(:num)', 'Deskapp/Tramites::audit_timeline/$1',['filter' => 'auth']);
$routes->post('/tramites/buscar_por_folio', 'Deskapp/Tramites::buscar_por_folio',['filter' => 'auth']);

$routes->get('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1',['filter' => 'auth']);
$routes->post('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1',['filter' => 'auth']);

$routes->get('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1',['filter' => 'auth']);
$routes->post('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1',['filter' => 'auth']);

$routes->get('/documentos/documento', 'Deskapp/Documentos::documento',['filter' => 'auth']);
$routes->post('/documentos/status', 'Deskapp/Documentos::status',['filter' => 'auth']);

$routes->get('/documentos/tp_doctos_tramite', 'Deskapp/Documentos::tp_doctos_tramite',['filter' => 'auth']);
$routes->post('/documentos/tp_doctos_tramite', 'Deskapp/Documentos::tp_doctos_tramite',['filter' => 'auth']);

$routes->get('/gestores/gestores', 'Deskapp/Gestores::gestores',['filter' => 'auth']);
$routes->post('/gestores/gestor', 'Deskapp/Gestores::gestor',['filter' => 'auth']);

// Gestión de clientes (controlador Deskapp\Cliente)
$routes->get('/cliente/cliente', 'Deskapp/Cliente::cliente',['filter' => 'auth']);
$routes->post('/cliente/cliente', 'Deskapp/Cliente::cliente',['filter' => 'auth']);

// Ruta usada en el sidebar: deskapp/clientes/cliente
$routes->get('/deskapp/clientes/cliente', 'Deskapp/Cliente::cliente',['filter' => 'auth']);
$routes->post('/deskapp/clientes/cliente', 'Deskapp/Cliente::cliente',['filter' => 'auth']);

$routes->get('/clidirecto/clidirecto', 'Deskapp/Clidirecto::clidirecto',['filter' => 'auth']);
$routes->post('/clidirecto/clidirecto', 'Deskapp/Clidirecto::clidirecto',['filter' => 'auth']);

$routes->get('/clidirecto/ejecutivo', 'Deskapp/Clidirecto::ejecutivo',['filter' => 'auth']);
$routes->post('/clidirecto/ejecutivo', 'Deskapp/Clidirecto::ejecutivo',['filter' => 'auth']);

$routes->get('/tradocstatus/documento', 'Deskapp/Tradocstatus::documento',['filter' => 'auth']);
$routes->post('/tradocstatus/documento', 'Deskapp/Tradocstatus::documento',['filter' => 'auth']);

$routes->get('/bitacora/index/(:tramite_id)', 'Deskapp/Bitacora::index/$1',['filter' => 'auth']);
$routes->post('/bitacora/index/(:tramite_id)', 'Deskapp/Bitacora::index',['filter' => 'auth']);
$routes->get('/bitacora/search', 'Deskapp/Bitacora::search',['filter' => 'auth']);
$routes->get('/bitacora/timeline', 'Deskapp/Bitacora::timeline',['filter' => 'auth']);

$routes->get('/proceso/final', 'Deskapp/Proceso::final',['filter' => 'auth']);
$routes->post('/proceso/final', 'Deskapp/Proceso::final',['filter' => 'auth']);

$routes->get('/proceso/cobro_cliente', 'Deskapp/Proceso::cobro_cliente',['filter' => 'auth']);
$routes->post('/proceso/cobro_cliente', 'Deskapp/Proceso::cobro_cliente',['filter' => 'auth']);

$routes->get('/tramites/update_final/(:id)', 'Deskapp/Tramites::update_final/$1',['filter' => 'auth']);
$routes->post('/tramites/update_final/(:id)', 'Deskapp/Tramites::update_final/$1',['filter' => 'auth']);

$routes->get('/tramites/cancelado/(:id)', 'Deskapp/Tramites::cancelado/$1',['filter' => 'auth']);
$routes->post('/tramites/cancelado/(:id)', 'Deskapp/Tramites::cancelado/$1',['filter' => 'auth']);

$routes->get('/tramites/finalizados/(:id)', 'Deskapp/Tramites::finalizados/$1',['filter' => 'auth']);
$routes->post('/tramites/finalizados/(:id)', 'Deskapp/Tramites::finalizados/$1',['filter' => 'auth']);

$routes->get('/tramites/tenencias/', 'Deskapp/Tramites::tenencias/',['filter' => 'auth']);
$routes->post('/tramites/tenencias/', 'Deskapp/Tramites::tenencias/',['filter' => 'auth']);

$routes->get('/tramites/cotizaciones/', 'Deskapp/Tramites::cotizaciones/',['filter' => 'auth']);
$routes->post('/tramites/cotizaciones/', 'Deskapp/Tramites::cotizaciones/',['filter' => 'auth']);

$routes->get('/tramites/get_service_types/', 'Deskapp/Tramites::get_service_types/',['filter' => 'auth']);
$routes->post('/tramites/get_service_types/', 'Deskapp/Tramites::get_service_types/',['filter' => 'auth']);

$routes->get('/tramites/sincronizarTramites/', 'Deskapp/Tramites::sincronizarTramites/',['filter' => 'auth']);
$routes->post('/tramites/sincronizarTramites/', 'Deskapp/Tramites::sincronizarTramites/',['filter' => 'auth']);



$routes->get('/tramites/get_services_by_tramite/(:id)', 'Deskapp/Tramites::get_services_by_tramite/$1',['filter' => 'auth']);
$routes->post('/tramites/get_services_by_tramite/(:id)', 'Deskapp/Tramites::get_services_by_tramite/$1',['filter' => 'auth']);

$routes->get('/tramites/get_service_costs_by_tramite/(:id)', 'Deskapp/Tramites::get_service_costs_by_tramite/$1',['filter' => 'auth']);
$routes->post('/tramites/get_service_costs_by_tramite/(:id)', 'Deskapp/Tramites::get_service_costs_by_tramite/$1',['filter' => 'auth']);


// ============================================================================
// Flujo nuevo de Trámites (clonado) - pruebas
// ============================================================================

$routes->group('deskapp', ['namespace' => 'App\\Controllers\\Deskapp', 'filter' => 'auth'], function($routes) {
	// Clientes - listado y detalle (ruta fija para evitar AutoRoute a Clientes::tramites)
	$routes->get('clientes/tramites', 'ClienteTramites::index');
	$routes->post('clientes/tramites', 'ClienteTramites::index');
	$routes->get('clientes/tramites/data', 'ClienteTramites::data');
	$routes->get('clientes/ver/(:num)', 'ClienteTramites::show/$1');
	$routes->post('clientes/ver/(:num)', 'ClienteTramites::show/$1');
	// Trámites - Nuevo flujo (Tramitesn)
	$routes->get('tramitesn', 'Tramitesn::tramite');
	$routes->get('tramitesn/tramite', 'Tramitesn::tramite');
	$routes->post('tramitesn/tramite', 'Tramitesn::tramite');
	$routes->get('tramitesn/prototipo-layout', 'Tramitesn::prototipo_layout');
	$routes->get('tramitesn/prototipo-layout/paso-1', 'Tramitesn::prototipo_layout_paso_1');
	$routes->get('tramitesn/prototipo-layout/paso-2', 'Tramitesn::prototipo_layout_paso_2');
	$routes->get('tramitesn/prototipo-layout/paso-3', 'Tramitesn::prototipo_layout_paso_3');
	$routes->get('tramitesn/prototipo-layout/paso-4', 'Tramitesn::prototipo_layout_paso_4');
	$routes->get('tramitesn/prototipo-layout/paso-5', 'Tramitesn::prototipo_layout_paso_5');
	$routes->get('tramitesn/prototipo-layout/paso/(:num)', 'Tramitesn::prototipo_layout/$1');
	$routes->get('tramitesn/search', 'Tramitesn::search');
	$routes->post('tramitesn/search', 'Tramitesn::search');
	$routes->get('tramitesn/cobro_cliente', 'Tramitesn::cobro_cliente');
	$routes->get('tramitesn/cobro_cliente/(:num)', 'Tramitesn::cobro_cliente_ver/$1');
	$routes->get('flotillas/import', 'Flotillas::import');
	$routes->post('flotillas/preview', 'Flotillas::preview');
	$routes->post('flotillas/import', 'Flotillas::store');

	$routes->get('tramitesn/update/(:num)', 'Tramitesn::update/$1');
	$routes->post('tramitesn/update/(:num)', 'Tramitesn::update/$1');
	$routes->get('tramitesn/single_evidencias/(:num)', 'Tramitesn::single_evidencias/$1');
	$routes->post('tramitesn/single_evidencias/(:num)', 'Tramitesn::single_evidencias/$1');
	$routes->get('tramitesn/ver_seccion_evidencias_finales/(:num)', 'Tramitesn::ver_seccion_evidencias_finales/$1');
	$routes->post('tramitesn/ver_seccion_evidencias_finales/(:num)', 'Tramitesn::ver_seccion_evidencias_finales/$1');
	$routes->get('tramitesn/ver_seccion_pago_gestor/(:num)', 'Tramitesn::ver_seccion_pago_gestor/$1');
	$routes->post('tramitesn/ver_seccion_pago_gestor/(:num)', 'Tramitesn::ver_seccion_pago_gestor/$1');
	$routes->get('tramitesn/ver_seccion_cobro_cliente/(:num)', 'Tramitesn::ver_seccion_cobro_cliente/$1');
	$routes->post('tramitesn/ver_seccion_cobro_cliente/(:num)', 'Tramitesn::ver_seccion_cobro_cliente/$1');
	$routes->get('tramitesn/getCobroClienteFiles/(:num)', 'Tramitesn::getCobroClienteFiles/$1');
	$routes->post('tramitesn/getCobroClienteFiles/(:num)', 'Tramitesn::getCobroClienteFiles/$1');
	$routes->get('tramitesn/upload_pago_gestor/(:num)', 'Tramitesn::upload_pago_gestor/$1');
	$routes->post('tramitesn/upload_pago_gestor/(:num)', 'Tramitesn::upload_pago_gestor/$1');
	$routes->get('tramitesn/upload_cobro_cliente/(:num)', 'Tramitesn::upload_cobro_cliente');
	$routes->post('tramitesn/upload_cobro_cliente/(:num)', 'Tramitesn::upload_cobro_cliente');
	$routes->post('tramitesn/delete_pago_gestor', 'Tramitesn::delete_pago_gestor');
	$routes->post('tramitesn/delete_cobro_cliente', 'Tramitesn::delete_cobro_cliente');

	// Prototipo de layout - notas y bitácora (rutas explícitas para no depender de AutoRoute)
	$routes->post('tramitesn/prototype_evidencias_add/(:num)', 'Tramitesn::prototype_evidencias_add/$1');
	$routes->post('tramitesn/prototype_step4_notes_add/(:num)', 'Tramitesn::prototype_step4_notes_add/$1');
	$routes->post('tramitesn/prototype_step5_notes_add/(:num)', 'Tramitesn::prototype_step5_notes_add/$1');
	$routes->post('tramitesn/upload_step1_doc/(:num)', 'Tramitesn::upload_step1_doc/$1');
	$routes->post('tramitesn/delete_step1_doc', 'Tramitesn::delete_step1_doc');

	// Guardado (reutiliza lógica heredada de Tramites::update_save)
	$routes->post('tramitesn/update_save/(:num)', 'Tramitesn::update_save');
	$routes->post('tramitesn/update_gestor_save/(:num)', 'Tramitesn::update_gestor_save');
	$routes->post('tramitesn/update_derechos_save/(:num)', 'Tramitesn::update_derechos_save');
	$routes->post('tramitesn/update_pago_gestor/(:num)', 'Tramitesn::update_pago_gestor');
	$routes->post('tramitesn/update_final_save/(:num)', 'Tramitesn::update_final_save');

	// Tipos de trámite asociados (tra_tramite_asociado)
	$routes->get('tramitesn/services/(:num)', 'Tramitesn::services/$1');
	$routes->post('tramitesn/services/add', 'Tramitesn::services_add');
	$routes->post('tramitesn/services/update', 'Tramitesn::services_update');
	$routes->post('tramitesn/services/delete', 'Tramitesn::services_delete');

	// Update del tipo de trámite principal
	$routes->post('tramitesn/principal/update_tipo', 'Tramitesn::principal_update_tipo');
	$routes->get('tramitesn/get_service_costs_by_tramite/(:num)', 'Tramitesn::get_service_costs_by_tramite/$1');
	$routes->post('tramitesn/get_service_costs_by_tramite/(:num)', 'Tramitesn::get_service_costs_by_tramite/$1');
	$routes->post('tramitesn/update_service_cost', 'Tramitesn::update_service_cost');
	$routes->post('tramitesn/upload_final_doc/(:num)/(:num)', 'Tramitesn::upload_final_doc/$1/$2');
	$routes->post('tramitesn/delete_final_doc', 'Tramitesn::delete_final_doc');

	$routes->get('tramitesn/add', 'Tramitesn::add');
	$routes->post('tramitesn/add', 'Tramitesn::add');

	$routes->get('tramitesn/insert', 'Tramitesn::insert');
	$routes->post('tramitesn/insert', 'Tramitesn::insert');

	$routes->get('/users/users_mapa/(:num)', 'Users::users_mapa/$1');
	$routes->post('/users/users_mapa/(:num)', 'Users::users_mapa/$1');

	$routes->get('/roles/roles_mapa/(:num)', 'Roles::roles_mapa/$1');
	$routes->post('/roles/roles_mapa/(:num)', 'Roles::roles_mapa/$1');
	$routes->post('/roles/toggle_permission', 'Roles::toggle_permission');

	$routes->get('/deskapp/tramites/getDependentData/(:segment)/(:num)', 'Tramites::getDependentData/$1/$2');
	$routes->post('/deskapp/tramites/getDependentData/(:segment)/(:num)', 'Tramites::getDependentData/$1/$2');

	$routes->get('/deskapp/tramites/getGestoresByEmpresaId/(:num)', 'Tramites::getGestoresByEmpresaId/$1');
	$routes->post('/deskapp/tramites/getGestoresByEmpresaId/(:num)', 'Tramites::getGestoresByEmpresaId/$1');

	// Centro de Cobranza
	$routes->get('/deskapp/cobranza', 'Deskapp/Cobranza::index');
	$routes->get('/deskapp/cobranza/expediente/(:num)', 'Deskapp/Cobranza::expediente/$1');
	$routes->post('/deskapp/cobranza/expediente/(:num)/abrir', 'Deskapp/Cobranza::abrirExpediente/$1');
	$routes->post('/deskapp/cobranza/expediente/(:num)/gestiones', 'Deskapp/Cobranza::registrarGestion/$1');
	$routes->post('/deskapp/cobranza/expediente/(:num)/promesas', 'Deskapp/Cobranza::registrarPromesa/$1');
	$routes->post('/deskapp/cobranza/expediente/(:num)/pagos', 'Deskapp/Cobranza::registrarPago/$1');
	$routes->post('/deskapp/cobranza/expediente/(:num)/pagos/(:num)/confirmar', 'Deskapp/Cobranza::confirmarPago/$1/$2');
});



$routes->get('/proceso/final_documentostatus/(:id)', 'Deskapp/Proceso::final_documentostatus/$1',['filter' => 'auth']);
$routes->post('/proceso/final_documentostatus/(:id)', 'Deskapp/Proceso::final_documentostatus/$1',['filter' => 'auth']);

$routes->get('/proceso/final_evidencias/(:id)', 'Deskapp/Proceso::final_evidencias/$1',['filter' => 'auth']);
$routes->post('/proceso/final_evidencias/(:id)', 'Deskapp/Proceso::final_evidencias/$1',['filter' => 'auth']);

$routes->get('/proceso/final_evidencias_finales/(:id)', 'Deskapp/Proceso::final_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/proceso/final_evidencias_finales/(:id)', 'Deskapp/Proceso::final_evidencias_finales/$1',['filter' => 'auth']);

$routes->get('/proceso/final_pago_derechos/(:id)', 'Deskapp/Proceso::final_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/proceso/final_pago_derechos/(:id)', 'Deskapp/Proceso::final_pago_derechos/$1',['filter' => 'auth']);


$routes->get('/proceso/concluido', 'Deskapp/Proceso::concluido',['filter' => 'auth']);
$routes->post('/proceso/concluido', 'Deskapp/Proceso::concluido',['filter' => 'auth']);

$routes->get('/proceso/concluido_documentostatus/(:id)', 'Deskapp/Proceso::concluido_documentostatus/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_documentostatus/(:id)', 'Deskapp/Proceso::concluido_documentostatus/$1',['filter' => 'auth']);

$routes->get('/proceso/concluido_evidencias/(:id)', 'Deskapp/Proceso::concluido_evidencias/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_evidencias/(:id)', 'Deskapp/Proceso::concluido_evidencias/$1',['filter' => 'auth']);

$routes->get('/proceso/concluido_evidencias_finales/(:id)', 'Deskapp/Proceso::concluido_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_evidencias_finales/(:id)', 'Deskapp/Proceso::concluido_evidencias_finales/$1',['filter' => 'auth']);

$routes->get('/proceso/concluido_pago_derechos/(:id)', 'Deskapp/Proceso::concluido_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_pago_derechos/(:id)', 'Deskapp/Proceso::concluido_pago_derechos/$1',['filter' => 'auth']);


$routes->get('/cancelado/cancelado', 'Deskapp/Tramites::cancelados',['filter' => 'auth']);
$routes->post('/cancelado/cancelado', 'Deskapp/Tramites::cancelados',['filter' => 'auth']);

$routes->get('/cancelado/cancelado_documentostatus/(:id)', 'Deskapp/Cancelado::cancelado_documentostatus/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_documentostatus/(:id)', 'Deskapp/Cancelado::cancelado_documentostatus/$1',['filter' => 'auth']);

$routes->get('/cancelado/cancelado_evidencias/(:id)', 'Deskapp/Cancelado::cancelado_evidencias/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_evidencias/(:id)', 'Deskapp/Cancelado::cancelado_evidencias/$1',['filter' => 'auth']);

$routes->get('/cancelado/cancelado_evidencias_finales/(:id)', 'Deskapp/Cancelado::cancelado_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_evidencias_finales/(:id)', 'Deskapp/Cancelado::cancelado_evidencias_finales/$1',['filter' => 'auth']);

$routes->get('/cancelado/cancelado_pago_derechos/(:id)', 'Deskapp/Cancelado::cancelado_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_pago_derechos/(:id)', 'Deskapp/Cancelado::cancelado_pago_derechos/$1',['filter' => 'auth']);

$routes->get('/concluido/final', 'Deskapp/Concluido::final',['filter' => 'auth']);
$routes->post('/concluido/final', 'Deskapp/Concluido::final',['filter' => 'auth']);

$routes->get('/concluido/ver/(:id)', 'Deskapp/Concluido::ver/$1',['filter' => 'auth']);
$routes->post('/concluido/ver/(:id)', 'Deskapp/Concluido::ver/$1',['filter' => 'auth']);




$routes->get('/customers/list', 'Deskapp/Customers::list',['filter' => 'auth']);
$routes->post('/customers/list', 'Deskapp/Customers::list',['filter' => 'auth']);


$routes->get('/customers/tramite/(:id)', 'Deskapp/Customers::tramite/$1',['filter' => 'auth']);
$routes->post('/customers/tramite/(:id)', 'Deskapp/Customers::tramite/$1',['filter' => 'auth']);

$routes->get('/customers/proceso_documentostatus/(:id)', 'Deskapp/Customers::proceso_documentostatus/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_documentostatus/(:id)', 'Deskapp/Customers::proceso_documentostatus/$1',['filter' => 'auth']);

$routes->get('/customers/proceso_evidencias/(:id)', 'Deskapp/Customers::proceso_evidencias/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_evidencias/(:id)', 'Deskapp/Customers::proceso_evidencias/$1',['filter' => 'auth']);

$routes->get('/customers/proceso_evidencias_finales/(:id)', 'Deskapp/Customers::proceso_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_evidencias_finales/(:id)', 'Deskapp/Customers::proceso_evidencias_finales/$1',['filter' => 'auth']);

$routes->get('/customers/proceso_pago_derechos/(:id)', 'Deskapp/Customers::proceso_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_pago_derechos/(:id)', 'Deskapp/Customers::proceso_pago_derechos/$1',['filter' => 'auth']);

// ============================================================================
// Dashboard Administrativo - Rutas
// ============================================================================
$routes->get('/deskapp/dashboardadmin', 'Deskapp/DashboardAdmin::index',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin', 'Deskapp/DashboardAdmin::index',['filter' => 'auth']);

$routes->get('/deskapp/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas',['filter' => 'auth']);

$routes->get('/deskapp/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero',['filter' => 'auth']);

$routes->get('/deskapp/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes',['filter' => 'auth']);

$routes->get('/deskapp/dashboardadmin/por_cliente', 'Deskapp/DashboardAdmin::por_cliente',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/por_cliente', 'Deskapp/DashboardAdmin::por_cliente',['filter' => 'auth']);

$routes->get('/deskapp/dashboardadmin/detalle_cliente/(:num)', 'Deskapp/DashboardAdmin::detalle_cliente/$1',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/detalle_cliente/(:num)', 'Deskapp/DashboardAdmin::detalle_cliente/$1',['filter' => 'auth']);

// APIs para datos en tiempo real (JSON)
$routes->get('/deskapp/dashboardadmin/api_metricas', 'Deskapp/DashboardAdmin::api_metricas',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_alertas', 'Deskapp/DashboardAdmin::api_alertas',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_graficas', 'Deskapp/DashboardAdmin::api_graficas',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_kpis', 'Deskapp/DashboardAdmin::api_kpis',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_comparativas', 'Deskapp/DashboardAdmin::api_comparativas',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_rankings', 'Deskapp/DashboardAdmin::api_rankings',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/api_financiero', 'Deskapp/DashboardAdmin::api_financiero',['filter' => 'auth']);

// Exportar reportes
$routes->get('/deskapp/dashboardadmin/exportar_excel', 'Deskapp/DashboardAdmin::exportar_excel',['filter' => 'auth']);
$routes->get('/deskapp/dashboardadmin/exportar_pdf', 'Deskapp/DashboardAdmin::exportar_pdf',['filter' => 'auth']);
// ============================================================================

// ============================================================================
// Dashboard Administrativo - Alias sin prefijo /deskapp (compatibilidad prod)
// ============================================================================
$routes->get('/dashboardadmin', 'Deskapp/DashboardAdmin::index',['filter' => 'auth']);
$routes->post('/dashboardadmin', 'Deskapp/DashboardAdmin::index',['filter' => 'auth']);

$routes->get('/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas',['filter' => 'auth']);
$routes->post('/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas',['filter' => 'auth']);

$routes->get('/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero',['filter' => 'auth']);
$routes->post('/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero',['filter' => 'auth']);

$routes->get('/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes',['filter' => 'auth']);
$routes->post('/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes',['filter' => 'auth']);

$routes->get('/dashboardadmin/por_cliente', 'Deskapp/DashboardAdmin::por_cliente',['filter' => 'auth']);
$routes->post('/dashboardadmin/por_cliente', 'Deskapp/DashboardAdmin::por_cliente',['filter' => 'auth']);

$routes->get('/dashboardadmin/detalle_cliente/(:num)', 'Deskapp/DashboardAdmin::detalle_cliente/$1',['filter' => 'auth']);
$routes->post('/dashboardadmin/detalle_cliente/(:num)', 'Deskapp/DashboardAdmin::detalle_cliente/$1',['filter' => 'auth']);

$routes->get('/dashboardadmin/exportar_excel', 'Deskapp/DashboardAdmin::exportar_excel',['filter' => 'auth']);
$routes->get('/dashboardadmin/exportar_pdf', 'Deskapp/DashboardAdmin::exportar_pdf',['filter' => 'auth']);
// ============================================================================

// ============================================================================
// Notificaciones - Rutas
// ============================================================================
$routes->get('/notifications', 'Deskapp/Notifications::index',['filter' => 'auth']);
$routes->get('/notifications/api_unread', 'Deskapp/Notifications::api_unread',['filter' => 'auth']);
$routes->get('/notifications/api_count', 'Deskapp/Notifications::api_count',['filter' => 'auth']);
$routes->get('/notifications/api_mark_read/(:num)', 'Deskapp/Notifications::api_mark_read/$1',['filter' => 'auth']);
$routes->post('/notifications/api_mark_read/(:num)', 'Deskapp/Notifications::api_mark_read/$1',['filter' => 'auth']);
$routes->post('/notifications/api_mark_all_read', 'Deskapp/Notifications::api_mark_all_read',['filter' => 'auth']);
$routes->delete('/notifications/api_delete/(:num)', 'Deskapp/Notifications::api_delete/$1',['filter' => 'auth']);
$routes->get('/notifications/api_load_more', 'Deskapp/Notifications::api_load_more',['filter' => 'auth']);
// ============================================================================

// Corrección de Trámites - Rutas (Solo Admin)
// ============================================================================
$routes->get('correccion-tramites', 'Deskapp\CorrecionTramites::index', ['filter' => 'auth']);
$routes->post('correccion-tramites', 'Deskapp\CorrecionTramites::index', ['filter' => 'auth']);
$routes->get('correccion-tramites/historial', 'Deskapp\CorrecionTramites::historial', ['filter' => 'auth']);
$routes->get('correccion-tramites/buscar', 'Deskapp\CorrecionTramites::buscar', ['filter' => 'auth']);

// API routes para Grocery CRUD
$routes->match(['get', 'post', 'put', 'delete'], 'deskapp/correccion-tramites/crud_api/(:any)', 'Deskapp\CorrecionTramites::crud_api/$1', ['filter' => 'auth']);
$routes->match(['get', 'post', 'put', 'delete'], 'deskapp/correccion-tramites/crud_api', 'Deskapp\CorrecionTramites::crud_api', ['filter' => 'auth']);
// ============================================================================

// ============================================================================
// WIZARD DE TRÁMITES - Módulo Moderno
// ============================================================================
// Vista principal del wizard
$routes->get('/deskapp/tramitewizard', 'Deskapp/TramiteWizard::index',['filter' => 'auth']);
$routes->get('/deskapp/tramitewizard/index', 'Deskapp/TramiteWizard::index',['filter' => 'auth']);

// Listado de trámites creados con wizard
$routes->get('/deskapp/tramitewizard/listado', 'Deskapp/TramiteWizard::listado',['filter' => 'auth']);

// Guardar trámite completo
$routes->post('/deskapp/tramitewizard/guardar', 'Deskapp/TramiteWizard::guardar',['filter' => 'auth']);

// Guardar borrador (auto-save)
$routes->post('/deskapp/tramitewizard/guardar_borrador', 'Deskapp/TramiteWizard::guardar_borrador',['filter' => 'auth']);

// Recuperar borrador guardado
$routes->get('/deskapp/tramitewizard/recuperar_borrador', 'Deskapp/TramiteWizard::recuperar_borrador',['filter' => 'auth']);

// Exportar a Excel
$routes->get('/deskapp/tramitewizard/exportar_excel', 'Deskapp/TramiteWizard::exportar_excel',['filter' => 'auth']);

// APIs AJAX para selectores dependientes
$routes->post('/deskapp/tramitewizard/get_municipios', 'Deskapp/TramiteWizard::get_municipios',['filter' => 'auth']);
$routes->post('/deskapp/tramitewizard/get_ejecutivos_cliente', 'Deskapp/TramiteWizard::get_ejecutivos_cliente',['filter' => 'auth']);
$routes->post('/deskapp/tramitewizard/get_gestores', 'Deskapp/TramiteWizard::get_gestores',['filter' => 'auth']);
// ============================================================================


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
