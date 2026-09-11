<?php

namespace App\Models\Traits;

use App\Libraries\DbAdapterFactory;

/**
 * SharedAdapter trait
 *
 * Overrides GroceryCrud\Core\Model::setDatabaseConnection() so all models
 * share the single cached Laminas Adapter from DbAdapterFactory instead of
 * opening a new MySQL connection per model instantiation.
 *
 * Add `use SharedAdapter;` to any App\Models class that extends GroceryCrud\Core\Model.
 */
trait SharedAdapter
{
    public function setDatabaseConnection($databaseConfig): void
    {
        $this->adapter = DbAdapterFactory::getAdapter();
    }
}
