<?php

namespace App\Libraries;

use Laminas\Db\Adapter\Adapter;
use Config\Database as ConfigDatabase;

/**
 * DbAdapterFactory — singleton Laminas Adapter per PHP process.
 *
 * GroceryCrud models extend GroceryCrud\Core\Model which calls
 * `new Adapter($config)` in every constructor, opening a new MySQL
 * connection each time. With ~10 models per request and concurrent
 * users, this quickly exhausts max_connections (#1040).
 *
 * This factory caches a single Adapter instance for the lifetime of
 * the request. All models that receive $db2 from _getDbData() should
 * use this factory to get the shared adapter.
 */
class DbAdapterFactory
{
    /** @var Adapter|null */
    private static ?Adapter $instance = null;

    /** @var array|null Cached config array */
    private static ?array $config = null;

    /**
     * Returns the shared Laminas Adapter, creating it once per process.
     */
    public static function getAdapter(): Adapter
    {
        if (self::$instance === null) {
            self::$instance = new Adapter(self::buildConfig());
        }

        return self::$instance;
    }

    /**
     * Returns the db config array (same shape as _getDbData()['adapter']).
     * Suitable for passing directly to GroceryCrud\Core\Model constructors
     * when you cannot inject the adapter directly.
     */
    public static function getDbConfig(): array
    {
        if (self::$config === null) {
            self::$config = ['adapter' => self::buildConfig()];
        }

        return self::$config;
    }

    /**
     * Reuse the existing Laminas connection on a pre-constructed Model.
     * Call this when you have a model that already accepted $db2 in its
     * constructor but you want to swap its adapter for the shared one.
     *
     * @param \GroceryCrud\Core\Model $model
     */
    public static function injectAdapter(object $model): void
    {
        if (method_exists($model, 'setDatabaseConnection')) {
            // Swap the internal adapter to the shared instance
            $model->adapter = self::getAdapter();
        }
    }

    /**
     * Build the Laminas adapter config from CI4's database config.
     */
    private static function buildConfig(): array
    {
        $db = (new ConfigDatabase())->default;

        return [
            'driver'         => 'Mysqli',
            'host'           => $db['hostname'],
            'database'       => $db['database'],
            'username'       => $db['username'],
            'password'       => $db['password'],
            'charset'        => 'utf8',
            'driver_options' => [
                MYSQLI_INIT_COMMAND => "SET time_zone = '-06:00'",
            ],
        ];
    }

    /** Prevent instantiation */
    private function __construct() {}
}
