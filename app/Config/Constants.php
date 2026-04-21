<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
 | --------------------------------------------------------------------------
 | Business ID Maps
 | --------------------------------------------------------------------------
 |
 | IDs funcionales usados por el flujo principal de tramites. Mantener este
 | bloque como la fuente central para evitar comparaciones numericas sueltas.
 |
 */
defined('SGL_TRA_STATUS_RECOLECCION_DCTOS') || define('SGL_TRA_STATUS_RECOLECCION_DCTOS', 11);
defined('SGL_TRA_STATUS_CONCLUIDO') || define('SGL_TRA_STATUS_CONCLUIDO', 20);
defined('SGL_TRA_STATUS_CANCELADO') || define('SGL_TRA_STATUS_CANCELADO', 21);
defined('SGL_TRA_STATUS_DCTOS_COMPLETOS') || define('SGL_TRA_STATUS_DCTOS_COMPLETOS', 22);
defined('SGL_TRA_STATUS_PAGO_GESTOR') || define('SGL_TRA_STATUS_PAGO_GESTOR', 23);
defined('SGL_TRA_STATUS_SOLICITUD') || define('SGL_TRA_STATUS_SOLICITUD', 24);
defined('SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION') || define('SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION', 25);
defined('SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA') || define('SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA', 26);
defined('SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS') || define('SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS', 27);
defined('SGL_TRA_STATUS_COBRO_CLIENTE') || define('SGL_TRA_STATUS_COBRO_CLIENTE', 28);
defined('SGL_TRA_STATUS_COTIZACION') || define('SGL_TRA_STATUS_COTIZACION', 29);

defined('SGL_TRA_STATUS_IDS') || define('SGL_TRA_STATUS_IDS', [
	'recoleccion_dctos' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
	'concluido' => SGL_TRA_STATUS_CONCLUIDO,
	'cancelado' => SGL_TRA_STATUS_CANCELADO,
	'dctos_completos' => SGL_TRA_STATUS_DCTOS_COMPLETOS,
	'pago_gestor' => SGL_TRA_STATUS_PAGO_GESTOR,
	'solicitud' => SGL_TRA_STATUS_SOLICITUD,
	'pago_derechos_cotizacion' => SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION,
	'pago_derechos_linea_captura' => SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA,
	'pago_derechos_documentos' => SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS,
	'cobro_cliente' => SGL_TRA_STATUS_COBRO_CLIENTE,
	'cotizacion' => SGL_TRA_STATUS_COTIZACION,
]);

defined('SGL_TRA_STATUS_LOCKED_IDS') || define('SGL_TRA_STATUS_LOCKED_IDS', [
	SGL_TRA_STATUS_CONCLUIDO,
	SGL_TRA_STATUS_CANCELADO,
]);

defined('SGL_TRA_STATUS_POST_APPROVAL_IDS') || define('SGL_TRA_STATUS_POST_APPROVAL_IDS', [
	SGL_TRA_STATUS_PAGO_GESTOR,
	SGL_TRA_STATUS_COBRO_CLIENTE,
	SGL_TRA_STATUS_CONCLUIDO,
	SGL_TRA_STATUS_CANCELADO,
]);

defined('SGL_TRA_STATUS_EDIT_STAGE_MAP') || define('SGL_TRA_STATUS_EDIT_STAGE_MAP', [
	SGL_TRA_STATUS_RECOLECCION_DCTOS => 1,
	SGL_TRA_STATUS_DCTOS_COMPLETOS => 2,
	SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION => 3,
	SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA => 3,
	SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS => 3,
	SGL_TRA_STATUS_PAGO_GESTOR => 4,
	SGL_TRA_STATUS_COBRO_CLIENTE => 5,
	SGL_TRA_STATUS_CONCLUIDO => 6,
	SGL_TRA_STATUS_CANCELADO => 7,
]);

defined('SGL_REEMBOLSO_STATUS_PENDING_IDS') || define('SGL_REEMBOLSO_STATUS_PENDING_IDS', [21, 22]);

defined('SGL_COBRO_STATUS_PENDIENTE_ALTA') || define('SGL_COBRO_STATUS_PENDIENTE_ALTA', 1);
defined('SGL_COBRO_STATUS_PENDIENTE') || define('SGL_COBRO_STATUS_PENDIENTE', 22);
defined('SGL_COBRO_STATUS_COBRADO') || define('SGL_COBRO_STATUS_COBRADO', 23);

defined('SGL_COBRO_STATUS_IDS') || define('SGL_COBRO_STATUS_IDS', [
	'pendiente_alta' => SGL_COBRO_STATUS_PENDIENTE_ALTA,
	'pendiente' => SGL_COBRO_STATUS_PENDIENTE,
	'cobrado' => SGL_COBRO_STATUS_COBRADO,
]);

defined('SGL_COBRO_STATUS_PENDING_IDS') || define('SGL_COBRO_STATUS_PENDING_IDS', [
	SGL_COBRO_STATUS_PENDIENTE,
]);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
