<?php

namespace Tests\App\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

class FullFormHelperCobroClienteTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper(['form', 'permissions', 'wizard_form', 'full_form']);
        session()->remove(['id', 'user_roles', 'user_permissions']);
    }

    public function testRenderFullFormShowsSubmitForPaso5WithListCobroClienteAndEditarFinal(): void
    {
        $session = session();
        $session->set([
            'id' => 99,
            'user_roles' => ['Admin'],
            'user_permissions' => ['list_cobro_cliente', 'editar_final'],
        ]);

        $html = render_full_form(
            'final',
            '/deskapp/tramites/update_final_save/123',
            'finalForm',
            '/tramites/tramite',
            'editar_final',
            [],
            $session,
            SGL_TRA_STATUS_COBRO_CLIENTE,
            0,
            0,
            5
        );

        $this->assertStringContainsString('Guardar', $html);
    }

    public function testRenderFullFormHidesSubmitForPaso5WithoutCobroClienteSurfaceAccess(): void
    {
        $session = session();
        $session->set([
            'id' => 99,
            'user_roles' => ['Admin'],
            'user_permissions' => ['editar_final'],
        ]);

        $html = render_full_form(
            'final',
            '/deskapp/tramites/update_final_save/123',
            'finalForm',
            '/tramites/tramite',
            'editar_final',
            [],
            $session,
            SGL_TRA_STATUS_COBRO_CLIENTE,
            0,
            0,
            5
        );

        $this->assertStringNotContainsString('Guardar', $html);
    }
}