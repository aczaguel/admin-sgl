<?php

namespace Tests\Support\Controllers;

use App\Controllers\Api\V1\Tramites;
use App\Services\ExternalTramiteService;

class TestableApiV1Tramites extends Tramites
{
    public static $service;

    protected function makeExternalTramiteService(): ExternalTramiteService
    {
        return static::$service;
    }
}