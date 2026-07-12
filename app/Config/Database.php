<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
	/**
	 * The directory that holds the Migrations
	 * and Seeds directories.
	 *
	 * @var string
	 */
	public $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';

	/**
	 * The default database connection.
	 *
	 * @var array
	 */
	public $default = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => 'RootAdmin',
		'database' => 'procedures',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 *
	 * @var array
	 */
	public $tests = [
		'DSN'      => '',
		'hostname' => '127.0.0.1',
		'username' => '',
		'password' => '',
		'database' => ':memory:',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	//--------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		// Ensure that we always set the database group to 'tests' if
		// we are currently running an automated test suite, so that
		// we don't overwrite live data on accident.
		//
		// This MUST short-circuit before any secret resolution: the test
		// suite uses the in-memory SQLite `tests` group and must never
		// reach out to AWS Secrets Manager (or any provider). Returning
		// here guarantees resolveRdsInto() is never called under testing.
		if (ENVIRONMENT === 'testing')
		{
			$this->defaultGroup = 'tests';

			return;
		}

		// Resolve the RDS credentials into the `default` connection group
		// before the first database connection is opened. This is the single
		// point CI4 instantiates Config\Database (on both the web front
		// controller and the `spark` CLI), so credentials are always in place
		// before any Database\Connection opens.
		//
		// When SECRETS_PROVIDER is absent/empty or `env` (the default) this is
		// behavior-identical to today's plain-.env configuration; when it is
		// `aws` the values come from AWS Secrets Manager. Any resolution error
		// is intentionally NOT caught so it propagates and the application fails
		// closed — it never connects with partial or unknown credentials.
		service('secrets')->resolveRdsInto($this->default);

		// Docker host override — MUST run AFTER secret resolution. In `env`
		// mode resolveRdsInto() repopulates hostname from
		// database.default.hostname (e.g. "localhost"), which the app container
		// cannot reach; applying this override afterward points the connection
		// at the host machine (Docker Desktop). In prod (`aws`) DOCKER_DB_HOST
		// is unset, so the resolved RDS endpoint is preserved untouched.
		$dockerDbHost = trim((string) env('DOCKER_DB_HOST', ''));
		if ($dockerDbHost !== '')
		{
			$this->default['hostname'] = $dockerDbHost;
		}
	}

	//--------------------------------------------------------------------

}
