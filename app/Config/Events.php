<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', function () {
	if (ENVIRONMENT !== 'testing')
	{
		if (ini_get('zlib.output_compression'))
		{
			throw FrameworkException::forEnabledZlibOutputCompression();
		}

		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		ob_start(function ($buffer) {
			return $buffer;
		});
	}

	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners.
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 */
	if (CI_DEBUG)
	{
		Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
		Services::toolbar()->respond();
	}
});

/*
 * --------------------------------------------------------------------
 * MySQL Session Timezone Sync (FR-01)
 * --------------------------------------------------------------------
 * Sets the MySQL session time_zone to match the application timezone
 * (America/Mexico_City = UTC-6) immediately after the first DB connection
 * is established. This prevents the ~6 hour drift between PHP dates and
 * MySQL NOW()/CURRENT_TIMESTAMP values.
 */
Events::on('post_controller_constructor', static function () {
	if (ENVIRONMENT === 'testing') {
		return;
	}
	try {
		$db = \Config\Database::connect();
		$db->query("SET time_zone = '-06:00'");
	} catch (\Throwable $e) {
		log_message('warning', '[FR-01] Could not set MySQL session timezone: ' . $e->getMessage());
	}
});