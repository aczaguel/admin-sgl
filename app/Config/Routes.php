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

$routes->get('/users/users', 'Deskapp/Users::users',['filter' => 'auth']);
$routes->post('/users/users', 'Deskapp/Users::users');

$routes->get('/roles/roles', 'Deskapp/Roles::roles',['filter' => 'auth']);
$routes->post('/roles/roles', 'Deskapp/Roles::roles');

$routes->get('/roles/role_permissions', 'Deskapp/Roles::role_permissions',['filter' => 'auth']);
$routes->post('/roles/role_permissions', 'Deskapp/Roles::role_permissions');

$routes->get('/users/user_roles', 'Deskapp/Users::user_roles',['filter' => 'auth']);
$routes->post('/users/user_roles', 'Deskapp/Users::user_roles');

$routes->get('/users/manage', 'Deskapp/Users::manage',['filter' => 'auth']);
$routes->post('/users/manage', 'Deskapp/Users::manage');

$routes->get('/users/profile', 'Deskapp/Users::profile',['filter' => 'auth']);
$routes->post('/users/profile', 'Deskapp/Users::profile');

$routes->get('/users/update_profile', 'Deskapp/Users::update_profile',['filter' => 'auth']);
$routes->post('/users/update_profile', 'Deskapp/Users::update_profile');

$routes->get('/users/update_password', 'Deskapp/Users::update_password',['filter' => 'auth']);
$routes->post('/users/update_password', 'Deskapp/Users::update_password');


$routes->get('/tramites/demo_multigrid', 'Deskapp/Tramites::demo_multigrid',['filter' => 'auth']);
$routes->post('/tramites/demo_multigrid', 'Deskapp/Tramites::demo_multigrid');

$routes->get('/tramites/add', 'Deskapp/Tramites::add',['filter' => 'auth']);
$routes->post('/tramites/add', 'Deskapp/Tramites::add');

$routes->get('/tramites/insert', 'Deskapp/Tramites::insert',['filter' => 'auth']);
$routes->post('/tramites/insert', 'Deskapp/Tramites::insert');

$routes->get('/tramites/single_documentostatus', 'Deskapp/Tramites::single_documentostatus',['filter' => 'auth']);
$routes->post('/tramites/single_documentostatus', 'Deskapp/Tramites::single_documentostatus');

$routes->get('/tramites/single_documentostatus/(:id)', 'Deskapp/Tramites::single_documentostatus/(:id)',['filter' => 'auth']);
$routes->post('/tramites/single_documentostatus/(:id)', 'Deskapp/Tramites::single_documentostatus/(:id)');

$routes->get('/tramites/upload_comprobante/(:id)', 'Deskapp/Tramites::upload_comprobante/(:id)',['filter' => 'auth']);
$routes->post('/tramites/upload_comprobante/(:id)', 'Deskapp/Tramites::upload_comprobante/(:id)');

$routes->get('/tramites/delete_comprobante/(:id)', 'Deskapp/Tramites::delete_comprobante/(:id)',['filter' => 'auth']);
$routes->post('/tramites/delete_comprobante/(:id)', 'Deskapp/Tramites::delete_comprobante/(:id)');


$routes->get('/tramites/single_evidencias/(:id)', 'Deskapp/Tramites::single_evidencias/(:id)',['filter' => 'auth']);
$routes->post('/tramites/single_evidencias/(:id)', 'Deskapp/Tramites::single_evidencias/(:id)');

$routes->get('/tramites/single_pago_derechos/(:id)', 'Deskapp/Tramites::single_pago_derechos/(:id)',['filter' => 'auth']);
$routes->post('/tramites/single_pago_derechos/(:id)', 'Deskapp/Tramites::single_pago_derechos/(:id)');


$routes->get('/tramites/single_pago_gestor/(:id)', 'Deskapp/Tramites::single_pago_gestor/(:id)',['filter' => 'auth']);
$routes->post('/tramites/single_pago_gestor/(:id)', 'Deskapp/Tramites::single_pago_gestor/(:id)');


$routes->get('/tramites/single_cobro_cliente/(:id)', 'Deskapp/Tramites::single_cobro_cliente/(:id)',['filter' => 'auth']);
$routes->post('/tramites/single_cobro_cliente/(:id)', 'Deskapp/Tramites::single_cobro_cliente/(:id)');



$routes->get('/tramites/getEjecutivosByClienteId/(:cliente_directo_id)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1',['filter' => 'auth']);
$routes->post('/tramites/getEjecutivosByClienteId/(:cliente_directo_id)', 'Deskapp/Tramites::getEjecutivosByClienteId/$1');

$routes->get('/tramites/getGestoresByEmpresaId/(:emp_gestora_id)', 'Deskapp/Tramites::getGestoresByEmpresaId/$1',['filter' => 'auth']);
$routes->post('/tramites/getGestoresByEmpresaId/(:emp_gestora_id)', 'Deskapp/Tramites::getGestoresByEmpresaId/$1');

$routes->get('/tramites/getDependentData/(:table)/(:id)', 'Deskapp/Tramites::getDependentData/$1/$2',['filter' => 'auth']);
$routes->post('/tramites/getDependentData/(:table)/(:id)', 'Deskapp/Tramites::getDependentData/$1/$2');

$routes->get('/tramites/update_save/(:id)', 'Deskapp/Tramites::update_save/$1',['filter' => 'auth']);
$routes->post('/tramites/update_save/(:id)', 'Deskapp/Tramites::update_save/$1');

$routes->get('/tramites/tramite', 'Deskapp/Tramites::tramite',['filter' => 'auth']);
$routes->post('/tramites/tramite', 'Deskapp/Tramites::tramite');

$routes->get('/tramites/tramite_2024', 'Deskapp/Tramites::tramite_2024',['filter' => 'auth']);
$routes->post('/tramites/tramite_2024', 'Deskapp/Tramites::tramite_2024');

$routes->get('/tramites/tramite_2025', 'Deskapp/Tramites::tramite_2025',['filter' => 'auth']);
$routes->post('/tramites/tramite_2025', 'Deskapp/Tramites::tramite_2025');

$routes->get('/tramites/mios', 'Deskapp/Tramites::mios',['filter' => 'auth']);
$routes->post('/tramites/mios', 'Deskapp/Tramites::mios');

$routes->get('/wizard', 'Deskapp/Wizard::index',['filter' => 'auth']);
$routes->post('/wizard/step1', 'Deskapp/Wizard::step1');
$routes->post('/wizard/step2', 'Deskapp/Wizard::step2');
$routes->post('/wizard/step3', 'Deskapp/Wizard::step3');
$routes->post('/wizard/complete', 'Deskapp/Wizard::complete');

$routes->get('/tramites/recoleccion', 'Deskapp/Tramites::recoleccion',['filter' => 'auth']);
$routes->post('/tramites/recoleccion', 'Deskapp/Tramites::recoleccion');

$routes->get('/tramites/en_tramite', 'Deskapp/Tramites::en_tramite',['filter' => 'auth']);
$routes->post('/tramites/en_tramite', 'Deskapp/Tramites::en_tramite');

$routes->get('/tramites/autorizar', 'Deskapp/Tramites::autorizar',['filter' => 'auth']);
$routes->post('/tramites/autorizar', 'Deskapp/Tramites::autorizar');

$routes->get('/tramites/tipo', 'Deskapp/Tramites::tipo',['filter' => 'auth']);
$routes->post('/tramites/tipo', 'Deskapp/Tramites::tipo');

$routes->get('/tramites/status', 'Deskapp/Tramites::status',['filter' => 'auth']);
$routes->post('/tramites/status', 'Deskapp/Tramites::status');

// Ruta de auditoría del trámite
$routes->get('/tramites/audit_search', 'Deskapp/Tramites::audit_search',['filter' => 'auth']);
$routes->get('/tramites/audit_timeline/(:num)', 'Deskapp/Tramites::audit_timeline/$1',['filter' => 'auth']);
$routes->post('/tramites/buscar_por_folio', 'Deskapp/Tramites::buscar_por_folio',['filter' => 'auth']);

$routes->get('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1',['filter' => 'auth']);
$routes->post('/tramites/documentostatus/(:tramite_id)', 'Deskapp/Tramites::documentostatus/$1');

$routes->get('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1',['filter' => 'auth']);
$routes->post('/tramites/evidencias/(:tramite_id)', 'Deskapp/Tramites::evidencias/$1');

$routes->get('/documentos/documento', 'Deskapp/Documentos::documento',['filter' => 'auth']);
$routes->post('/documentos/status', 'Deskapp/Documentos::status');

$routes->get('/documentos/tp_doctos_tramite', 'Deskapp/Documentos::tp_doctos_tramite',['filter' => 'auth']);
$routes->post('/documentos/tp_doctos_tramite', 'Deskapp/Documentos::tp_doctos_tramite');

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

$routes->get('/proceso/cobro_cliente', 'Deskapp/Proceso::cobro_cliente',['filter' => 'auth']);
$routes->post('/proceso/cobro_cliente', 'Deskapp/Proceso::cobro_cliente');

$routes->get('/tramites/update_final/(:id)', 'Deskapp/Tramites::update_final/$1',['filter' => 'auth']);
$routes->post('/tramites/update_final/(:id)', 'Deskapp/Tramites::update_final/$1');

$routes->get('/tramites/cancelado/(:id)', 'Deskapp/Tramites::cancelado/$1',['filter' => 'auth']);
$routes->post('/tramites/cancelado/(:id)', 'Deskapp/Tramites::cancelado/$1');

$routes->get('/tramites/finalizados/(:id)', 'Deskapp/Tramites::finalizados/$1',['filter' => 'auth']);
$routes->post('/tramites/finalizados/(:id)', 'Deskapp/Tramites::finalizados/$1');

$routes->get('/tramites/tenencias/', 'Deskapp/Tramites::tenencias/',['filter' => 'auth']);
$routes->post('/tramites/tenencias/', 'Deskapp/Tramites::tenencias/');

$routes->get('/tramites/cotizaciones/', 'Deskapp/Tramites::cotizaciones/',['filter' => 'auth']);
$routes->post('/tramites/cotizaciones/', 'Deskapp/Tramites::cotizaciones/');

$routes->get('/tramites/get_service_types/', 'Deskapp/Tramites::get_service_types/',['filter' => 'auth']);
$routes->post('/tramites/get_service_types/', 'Deskapp/Tramites::get_service_types/');

$routes->get('/tramites/sincronizarTramites/', 'Deskapp/Tramites::sincronizarTramites/',['filter' => 'auth']);
$routes->post('/tramites/sincronizarTramites/', 'Deskapp/Tramites::sincronizarTramites/');



$routes->get('/tramites/get_services_by_tramite/(:id)', 'Deskapp/Tramites::get_services_by_tramite/$1',['filter' => 'auth']);
$routes->post('/tramites/get_services_by_tramite/(:id)', 'Deskapp/Tramites::get_services_by_tramite/$1');

$routes->get('/tramites/get_service_costs_by_tramite/(:id)', 'Deskapp/Tramites::get_service_costs_by_tramite/$1',['filter' => 'auth']);
$routes->post('/tramites/get_service_costs_by_tramite/(:id)', 'Deskapp/Tramites::get_service_costs_by_tramite/$1');



$routes->get('/proceso/final_documentostatus/(:id)', 'Deskapp/Proceso::final_documentostatus/$1',['filter' => 'auth']);
$routes->post('/proceso/final_documentostatus/(:id)', 'Deskapp/Proceso::final_documentostatus/$1');

$routes->get('/proceso/final_evidencias/(:id)', 'Deskapp/Proceso::final_evidencias/$1',['filter' => 'auth']);
$routes->post('/proceso/final_evidencias/(:id)', 'Deskapp/Proceso::final_evidencias/$1');

$routes->get('/proceso/final_evidencias_finales/(:id)', 'Deskapp/Proceso::final_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/proceso/final_evidencias_finales/(:id)', 'Deskapp/Proceso::final_evidencias_finales/$1');

$routes->get('/proceso/final_pago_derechos/(:id)', 'Deskapp/Proceso::final_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/proceso/final_pago_derechos/(:id)', 'Deskapp/Proceso::final_pago_derechos/$1');


$routes->get('/proceso/concluido', 'Deskapp/Proceso::concluido',['filter' => 'auth']);
$routes->post('/proceso/concluido', 'Deskapp/Proceso::concluido');

$routes->get('/proceso/concluido_documentostatus/(:id)', 'Deskapp/Proceso::concluido_documentostatus/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_documentostatus/(:id)', 'Deskapp/Proceso::concluido_documentostatus/$1');

$routes->get('/proceso/concluido_evidencias/(:id)', 'Deskapp/Proceso::concluido_evidencias/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_evidencias/(:id)', 'Deskapp/Proceso::concluido_evidencias/$1');

$routes->get('/proceso/concluido_evidencias_finales/(:id)', 'Deskapp/Proceso::concluido_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_evidencias_finales/(:id)', 'Deskapp/Proceso::concluido_evidencias_finales/$1');

$routes->get('/proceso/concluido_pago_derechos/(:id)', 'Deskapp/Proceso::concluido_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/proceso/concluido_pago_derechos/(:id)', 'Deskapp/Proceso::concluido_pago_derechos/$1');


$routes->get('/cancelado/cancelado', 'Deskapp/Cancelado::cancelado',['filter' => 'auth']);
$routes->post('/cancelado/cancelado', 'Deskapp/Cancelado::cancelado');

$routes->get('/cancelado/cancelado_documentostatus/(:id)', 'Deskapp/Cancelado::cancelado_documentostatus/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_documentostatus/(:id)', 'Deskapp/Cancelado::cancelado_documentostatus/$1');

$routes->get('/cancelado/cancelado_evidencias/(:id)', 'Deskapp/Cancelado::cancelado_evidencias/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_evidencias/(:id)', 'Deskapp/Cancelado::cancelado_evidencias/$1');

$routes->get('/cancelado/cancelado_evidencias_finales/(:id)', 'Deskapp/Cancelado::cancelado_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_evidencias_finales/(:id)', 'Deskapp/Cancelado::cancelado_evidencias_finales/$1');

$routes->get('/cancelado/cancelado_pago_derechos/(:id)', 'Deskapp/Cancelado::cancelado_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/cancelado/cancelado_pago_derechos/(:id)', 'Deskapp/Cancelado::cancelado_pago_derechos/$1');

$routes->get('/concluido/final', 'Deskapp/Concluido::final',['filter' => 'auth']);
$routes->post('/concluido/final', 'Deskapp/Concluido::final');

$routes->get('/concluido/ver/(:id)', 'Deskapp/Concluido::ver/$1',['filter' => 'auth']);
$routes->post('/concluido/ver/(:id)', 'Deskapp/Concluido::ver/$1');




$routes->get('/customers/list', 'Deskapp/Customers::list',['filter' => 'auth']);
$routes->post('/customers/list', 'Deskapp/Customers::list');


$routes->get('/customers/tramite/(:id)', 'Deskapp/Customers::tramite/$1',['filter' => 'auth']);
$routes->post('/customers/tramite/(:id)', 'Deskapp/Customers::tramite/$1');

$routes->get('/customers/proceso_documentostatus/(:id)', 'Deskapp/Customers::proceso_documentostatus/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_documentostatus/(:id)', 'Deskapp/Customers::proceso_documentostatus/$1');

$routes->get('/customers/proceso_evidencias/(:id)', 'Deskapp/Customers::proceso_evidencias/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_evidencias/(:id)', 'Deskapp/Customers::proceso_evidencias/$1');

$routes->get('/customers/proceso_evidencias_finales/(:id)', 'Deskapp/Customers::proceso_evidencias_finales/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_evidencias_finales/(:id)', 'Deskapp/Customers::proceso_evidencias_finales/$1');

$routes->get('/customers/proceso_pago_derechos/(:id)', 'Deskapp/Customers::proceso_pago_derechos/$1',['filter' => 'auth']);
$routes->post('/customers/proceso_pago_derechos/(:id)', 'Deskapp/Customers::proceso_pago_derechos/$1');

// ============================================================================
// Dashboard Administrativo - Rutas
// ============================================================================
$routes->get('/deskapp/dashboardadmin', 'Deskapp/DashboardAdmin::index',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin', 'Deskapp/DashboardAdmin::index');

$routes->get('/deskapp/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/alertas', 'Deskapp/DashboardAdmin::alertas');

$routes->get('/deskapp/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/financiero', 'Deskapp/DashboardAdmin::financiero');

$routes->get('/deskapp/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes',['filter' => 'auth']);
$routes->post('/deskapp/dashboardadmin/reportes', 'Deskapp/DashboardAdmin::reportes');

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
