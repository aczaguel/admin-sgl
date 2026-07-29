<?php

namespace Tests\Support\Controllers;

use App\Controllers\Deskapp\Concluido;

/**
 * Test double for the Concluido controller.
 *
 * The real constructor only loads helpers; we bypass it so the controller can
 * be instantiated in isolation and wired via initController() in tests, exactly
 * like TestableTramites / TestableTramitesn.
 */
class TestableConcluido extends Concluido
{
    public function __construct()
    {
    }
}
