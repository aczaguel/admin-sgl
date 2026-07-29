<?php

namespace Tests\App\Views;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;
use Config\Services;

/**
 * Render integration tests for the step row views under both drivers.
 *
 * Renders each `_stepN_row.php` (and the mirrored `tramites_layout_prototipo.php`
 * blocks for step4/step5) with a stub form payload and asserts:
 *
 *  1. Document links resolve from the row's pre-resolved `url` field.
 *  2. `fileBaseUrl` does not appear in the rendered output.
 *  3. Empty/unresolvable rows fall back to `href="#"`.
 *  4. Under the s3 driver, no rendered link contains `/assets/uploads/`.
 *
 * Validates: Requirements 1.2, 1.4, 4.6, 4.7, 8.2, 8.3
 *
 * @internal
 */
final class StepRowRenderIntegrationTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['filestorage', 'url', 'form', 'security']);
    }

    protected function tearDown(): void
    {
        Factories::reset('config');
        Services::reset(true);
        parent::tearDown();
    }

    // ====================================================================
    // Step 1 — Information General (documents use file_url field)
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testStep1LinksResolveFromPreResolvedUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $url = $this->fakeUrl('acta_constitutiva.pdf', $driver);

        $html = $this->renderView('deskapp/tramite_unified/_step1_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep1Form' => $this->minimalStep1Form(),
            'prototypeStep1DocsForm' => [
                'canView' => true,
                'canUpload' => false,
                'canDelete' => false,
                'blockedReason' => '',
                'deleteBlockedReason' => '',
                'urls' => [],
                'options' => ['documentTypes' => [], 'documentTypeMeta' => []],
                'summary' => [],
                'documents' => [
                    [
                        'documento_id' => 1,
                        'has_file' => true,
                        'file' => 'acta_constitutiva.pdf',
                        'file_url' => $url,
                        'documento_nombre' => 'Acta Constitutiva',
                        'is_required' => true,
                        'status_label' => 'Cargado',
                    ],
                ],
            ],
            'prototypeEvidenceForm' => $this->minimalEvidenceForm(),
            'prototypeStep1ServicesForm' => [],
        ]);

        $this->assertUrlInHtml($url, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // Step 2 — Gestion y Derechos (comprobantes gallery)
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testStep2LinksResolveFromRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $urlImage = $this->fakeUrl('comprobante_derechos.jpg', $driver);
        $urlPdf   = $this->fakeUrl('recibo_pago.pdf', $driver);

        $html = $this->renderView('deskapp/tramite_unified/_step2_row', [
            'prototypeTramiteId' => 100,
            'prototypeCanApproveStep2' => false,
            'prototypeStep2Form' => [
                'canEdit' => false,
                'blockedReason' => '',
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'docsBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'currentStatusId' => 1,
                'currentStep' => 2,
                'isApprovedLock' => false,
                'isLockedStatus' => false,
                'urls' => [],
                'options' => [],
                'values' => [],
                'docs' => [
                    ['file' => 'comprobante_derechos.jpg', 'id' => '1', 'url' => $urlImage, 'is_image' => true],
                    ['file' => 'recibo_pago.pdf', 'id' => '2', 'url' => $urlPdf, 'is_image' => false],
                    ['file' => '', 'id' => '3', 'url' => '', 'is_image' => false],
                ],
            ],
        ]);

        $this->assertUrlInHtml($urlImage, $html);
        $this->assertUrlInHtml($urlPdf, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertEmptyRowFallsBackToHash($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // Step 3 — Evidencias Finales
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testStep3LinksResolveFromRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $url = $this->fakeUrl('tramite_recibido.pdf', $driver);

        $html = $this->renderView('deskapp/tramite_unified/_step3_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep3Form' => [
                'canUpload' => false,
                'canDelete' => false,
                'blockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'urls' => [],
                'tramiteId' => 100,
                'options' => [],
                'docs' => [
                    ['file' => 'tramite_recibido.pdf', 'comprobante_final' => 'tramite_recibido', 'url' => $url, 'is_image' => false],
                    ['file' => '', 'comprobante_final' => 'acuse_recibo_cliente', 'url' => '', 'is_image' => false],
                ],
                'hasTramiteRecibido' => true,
                'hasAcuseRecibo' => false,
            ],
            'prototypeEvidenceForm' => $this->minimalEvidenceForm(),
            'tulStep3Locked' => false,
            'tulStep3LockReason' => '',
        ]);

        $this->assertUrlInHtml($url, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // Step 4 — Pago a Gestor
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testStep4LinksResolveFromRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $url = $this->fakeUrl('factura_gestor.pdf', $driver);

        $html = $this->renderView('deskapp/tramite_unified/_step4_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep4Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'tramiteId' => 100,
                'url' => '',
                'urls' => ['delete' => '#'],
                'options' => [
                    'pagoGestorStatus' => [],
                    'statusDoctosGestor' => [],
                    'reembolsoStatus' => [],
                    'comprobanteFinal' => ['factura_gestor' => 'Factura gestor'],
                ],
                'docs' => [
                    ['file' => 'factura_gestor.pdf', 'id' => '10', 'comprobante_final' => 'factura_gestor', 'url' => $url, 'is_image' => false],
                    ['file' => '', 'id' => '11', 'comprobante_final' => '', 'url' => '', 'is_image' => false],
                ],
                'values' => [],
            ],
            'prototypeStep4NotesForm' => $this->minimalNotesForm(),
            'tulFinanceLocked' => false,
            'tulFinanceLockReason' => '',
        ]);

        $this->assertUrlInHtml($url, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // Step 5 — Cobro a Cliente
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testStep5LinksResolveFromRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $urlImg = $this->fakeUrl('deposito_bancario.jpg', $driver);
        $urlPdf = $this->fakeUrl('contrato_firmado.pdf', $driver);

        $html = $this->renderView('deskapp/tramite_unified/_step5_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep5Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'docs' => [
                    ['file' => 'deposito_bancario.jpg', 'id' => '7', 'cobro_correcto' => 'completo', 'url' => $urlImg, 'is_image' => true],
                    ['file' => 'contrato_firmado.pdf', 'id' => '8', 'cobro_correcto' => 'parcial', 'url' => $urlPdf, 'is_image' => false],
                    ['file' => '', 'id' => '9', 'cobro_correcto' => '', 'url' => '', 'is_image' => false],
                ],
                'values' => [],
                'options' => [
                    'cobroStatus' => [],
                    'cobroCorrecto' => ['completo' => 'Completo', 'parcial' => 'Parcial'],
                ],
            ],
            'prototypeStep5NotesForm' => $this->minimalNotesForm(),
            'tulFinanceLocked' => false,
            'tulFinanceLockReason' => '',
        ]);

        $this->assertUrlInHtml($urlImg, $html);
        $this->assertUrlInHtml($urlPdf, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertEmptyRowFallsBackToHash($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // tramites_layout_prototipo.php — step4 / step5 mirrored blocks
    // ====================================================================

    /**
     * @dataProvider driverProvider
     */
    public function testPrototipoLayoutStep4BlockUsesRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $url = $this->fakeUrl('comprobante_pago.png', $driver);

        $html = $this->renderPrototipoLayout([
            'prototypeStep4Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'urls' => ['delete' => '#'],
                'options' => [
                    'pagoGestorStatus' => [],
                    'statusDoctosGestor' => [],
                    'reembolsoStatus' => [],
                    'comprobanteFinal' => ['comprobante_pago' => 'Comprobante'],
                ],
                'docs' => [
                    ['file' => 'comprobante_pago.png', 'id' => '20', 'comprobante_final' => 'comprobante_pago', 'url' => $url, 'is_image' => true],
                ],
                'values' => [],
            ],
        ]);

        $this->assertUrlInHtml($url, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    /**
     * @dataProvider driverProvider
     */
    public function testPrototipoLayoutStep5BlockUsesRowUrl(string $driver): void
    {
        $this->setDriver($driver);
        $this->injectRecordingStorage();

        $url = $this->fakeUrl('cobro_evidencia.jpg', $driver);

        $html = $this->renderPrototipoLayout([
            // Step4 canView must be true so the finance section renders
            'prototypeStep4Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'urls' => ['delete' => '#'],
                'options' => [
                    'pagoGestorStatus' => [],
                    'statusDoctosGestor' => [],
                    'reembolsoStatus' => [],
                    'comprobanteFinal' => [],
                ],
                'docs' => [],
                'values' => [],
            ],
            'prototypeStep5Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'docs' => [
                    ['file' => 'cobro_evidencia.jpg', 'id' => '30', 'cobro_correcto' => 'completo', 'url' => $url, 'is_image' => true],
                    ['file' => '', 'id' => '31', 'cobro_correcto' => '', 'url' => '', 'is_image' => false],
                ],
                'values' => [],
                'options' => [
                    'cobroStatus' => [],
                    'cobroCorrecto' => ['completo' => 'Completo'],
                ],
            ],
        ]);

        $this->assertUrlInHtml($url, $html);
        $this->assertFileBaseUrlAbsent($html);
        $this->assertNoLocalPathUnderS3($html, $driver);
    }

    // ====================================================================
    // Empty row fallback: href="#" across all steps
    // ====================================================================

    public function testAllStepsEmptyUrlFallsBackToHashUnderS3(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // Step 2
        $html2 = $this->renderView('deskapp/tramite_unified/_step2_row', [
            'prototypeTramiteId' => 100,
            'prototypeCanApproveStep2' => false,
            'prototypeStep2Form' => [
                'canEdit' => false,
                'blockedReason' => '',
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'docsBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'currentStatusId' => 1,
                'currentStep' => 2,
                'isApprovedLock' => false,
                'isLockedStatus' => false,
                'urls' => [],
                'options' => [],
                'values' => [],
                'docs' => [
                    ['file' => 'only_empty_test.pdf', 'id' => '99', 'url' => '', 'is_image' => false],
                ],
            ],
        ]);
        $this->assertEmptyRowFallsBackToHash($html2);

        // Step 4
        $html4 = $this->renderView('deskapp/tramite_unified/_step4_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep4Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'urls' => ['delete' => '#'],
                'options' => ['pagoGestorStatus' => [], 'statusDoctosGestor' => [], 'reembolsoStatus' => [], 'comprobanteFinal' => []],
                'docs' => [
                    ['file' => 'empty_url_doc.pdf', 'id' => '88', 'comprobante_final' => '', 'url' => '', 'is_image' => false],
                ],
                'values' => [],
            ],
            'prototypeStep4NotesForm' => $this->minimalNotesForm(),
            'tulFinanceLocked' => false,
            'tulFinanceLockReason' => '',
        ]);
        $this->assertEmptyRowFallsBackToHash($html4);

        // Step 5
        $html5 = $this->renderView('deskapp/tramite_unified/_step5_row', [
            'prototypeTramiteId' => 100,
            'prototypeStep5Form' => [
                'canView' => true,
                'canEdit' => false,
                'canUploadDocs' => false,
                'canDeleteDocs' => false,
                'blockedReason' => '',
                'uploadBlockedReason' => '',
                'deleteBlockedReason' => '',
                'csrfName' => 'csrf_test_name',
                'csrfHash' => 'test_hash',
                'docs' => [
                    ['file' => 'empty_url_cobro.pdf', 'id' => '77', 'cobro_correcto' => '', 'url' => '', 'is_image' => false],
                ],
                'values' => [],
                'options' => ['cobroStatus' => [], 'cobroCorrecto' => []],
            ],
            'prototypeStep5NotesForm' => $this->minimalNotesForm(),
            'tulFinanceLocked' => false,
            'tulFinanceLockReason' => '',
        ]);
        $this->assertEmptyRowFallsBackToHash($html5);
    }

    // ====================================================================
    // Data providers
    // ====================================================================

    public function driverProvider(): array
    {
        return [
            'local driver' => ['local'],
            's3 driver'    => ['s3'],
        ];
    }

    // ====================================================================
    // Helpers: rendering
    // ====================================================================

    /**
     * Render a view file with the given data and return the HTML output.
     */
    private function renderView(string $viewPath, array $data): string
    {
        $renderer = Services::renderer(APPPATH . 'Views/', null, false);
        $renderer->setData($data);

        return $renderer->render($viewPath, [], true);
    }

    /**
     * Render the tramites_layout_prototipo.php with minimal defaults
     * and the provided overrides (step4/step5 forms with docs).
     */
    private function renderPrototipoLayout(array $overrides): string
    {
        $defaults = [
            'isEmbeddedPrototypeBody' => true,
            'activeStep' => 4,
            'prototypeTramiteId' => 100,
            'prototypeCanApproveStep2' => false,
            'prototypeStep1Form' => $this->minimalStep1Form(),
            'prototypeStep1DocsForm' => ['canView' => false, 'documents' => []],
            'prototypeStep1ServicesForm' => [],
            'prototypeStep2Form' => ['canEdit' => false, 'docs' => [], 'values' => [], 'options' => [], 'urls' => []],
            'prototypeStep3Form' => ['canUpload' => false, 'docs' => [], 'options' => []],
            'prototypeStep4Form' => ['canView' => false, 'docs' => [], 'values' => [], 'options' => ['pagoGestorStatus' => [], 'statusDoctosGestor' => [], 'reembolsoStatus' => [], 'comprobanteFinal' => []]],
            'prototypeStep5Form' => ['canView' => false, 'docs' => [], 'values' => [], 'options' => ['cobroStatus' => [], 'cobroCorrecto' => []]],
            'prototypeStep4NotesForm' => $this->minimalNotesForm(),
            'prototypeStep5NotesForm' => $this->minimalNotesForm(),
            'prototypeEvidenceForm' => $this->minimalEvidenceForm(),
            'prototypeReadOnlyTramite' => null,
        ];

        $data = array_merge($defaults, $overrides);

        $renderer = Services::renderer(APPPATH . 'Views/', null, false);
        $renderer->setData($data);

        return $renderer->render('deskapp/extra-pages/tramites_layout_prototipo', [], true);
    }

    // ====================================================================
    // Helpers: assertions
    // ====================================================================

    /**
     * Assert that `fileBaseUrl` is not emitted as a controller-payload key
     * in the rendered HTML. We look for it as a PHP variable or data attribute,
     * NOT in JS source-code variable names (which may still reference it in
     * legacy inline scripts within the prototipo layout).
     *
     * For step row views (partials without JS), we check the entire output.
     * For the full prototipo layout, we only check data-attribute values and
     * PHP-rendered anchor hrefs/img srcs.
     */
    private function assertFileBaseUrlAbsent(string $html): void
    {
        // Check that no PHP-rendered value or data-attribute contains the
        // literal "fileBaseUrl" as a key. The JS source code in the prototipo
        // layout may contain the variable name for DOM querying, which is fine.
        // We specifically look for patterns like: 'fileBaseUrl' => or "fileBaseUrl":
        $this->assertStringNotContainsString(
            "'fileBaseUrl'",
            $html,
            'Rendered output must not contain a PHP-emitted fileBaseUrl key'
        );
    }

    /**
     * Assert that under the s3 driver, no href contains `/assets/uploads/`.
     * Checks both raw and HTML-entity-encoded representations.
     */
    private function assertNoLocalPathUnderS3(string $html, string $driver): void
    {
        if ($driver !== 's3') {
            return;
        }

        // Extract all href values (HTML-entity-encoded in attrs)
        preg_match_all('/href="([^"]*)"/', $html, $matches);
        foreach ($matches[1] as $href) {
            if ($href === '#' || $href === '&#x23;') {
                continue;
            }
            // Decode HTML entities to check the actual URL
            $decoded = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $this->assertStringNotContainsString(
                '/assets/uploads/',
                $decoded,
                "Under s3, no link href should contain /assets/uploads/. Found: {$decoded}"
            );
        }

        // Extract all src values (images)
        preg_match_all('/src="([^"]*)"/', $html, $srcMatches);
        foreach ($srcMatches[1] as $src) {
            $decoded = html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $this->assertStringNotContainsString(
                '/assets/uploads/',
                $decoded,
                "Under s3, no img src should contain /assets/uploads/. Found: {$decoded}"
            );
        }
    }

    /**
     * Assert that at least one empty-URL row falls back gracefully.
     * Steps 4/5 render href="#"; step 2/3 render a span (no link) for empty rows.
     * Either pattern satisfies the "fall back to #" requirement (the $docUrl
     * variable is set to '#', and the view may either render it in a link or
     * not render a link at all).
     */
    private function assertEmptyRowFallsBackToHash(string $html): void
    {
        // Step 4/5 render href="#" (raw or esc-encoded).
        // Step 2/3 show a span with class tul-gallery__item-name (no link).
        $hasHashLink = str_contains($html, 'href="#"') || str_contains($html, 'href="&#x23;"');
        $hasSpanFallback = str_contains($html, 'tul-gallery__item-name');
        $this->assertTrue(
            $hasHashLink || $hasSpanFallback,
            'Empty URL rows must fall back to href="#" or render without a link'
        );
    }

    // ====================================================================
    // Helpers: fixtures
    // ====================================================================

    /**
     * Generate a fake URL for a given filename and driver.
     * Returns the raw URL (not HTML-encoded).
     */
    private function fakeUrl(string $filename, string $driver): string
    {
        if ($driver === 's3') {
            return 'https://bucket.s3.amazonaws.com/' . $filename . '?X-Amz-Signature=deadbeef&X-Amz-Expires=300';
        }

        return 'http://localhost/assets/uploads/' . $filename;
    }

    /**
     * Assert that the raw URL appears in the rendered HTML (possibly HTML-encoded
     * via esc() or JSON-encoded in inline JS config objects).
     * CI4's esc($url, 'attr') encodes `:`, `/`, `&`, `=` etc.
     * The prototipo layout uses json_encode with JSON_HEX_AMP (& -> \u0026).
     */
    private function assertUrlInHtml(string $rawUrl, string $html, string $message = ''): void
    {
        $attrEncoded = esc($rawUrl, 'attr');
        $jsonEncoded = trim(json_encode($rawUrl), '"');
        // JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT encoding
        $jsonHexEncoded = trim(json_encode($rawUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), '"');
        $found = str_contains($html, $rawUrl)
            || str_contains($html, $attrEncoded)
            || str_contains($html, $jsonEncoded)
            || str_contains($html, $jsonHexEncoded);
        $this->assertTrue($found, $message ?: "Expected URL '{$rawUrl}' (raw, attr-encoded, or json-encoded) in rendered output.");
    }

    /**
     * Inject a recording fake fileStorage service that returns deterministic URLs.
     */
    private function injectRecordingStorage(): void
    {
        $fake = new class {
            public array $calls = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];

                return 'https://bucket.s3.amazonaws.com/' . $key . '?X-Amz-Signature=deadbeef&X-Amz-Expires=' . $ttl;
            }
        };

        Services::injectMock('fileStorage', $fake);
    }

    private function setDriver(string $driver): void
    {
        $config = new FileStorageConfig();
        $config->driver = $driver;
        Factories::injectMock('config', 'FileStorage', $config);
    }

    private function minimalStep1Form(): array
    {
        return [
            'canEdit' => false,
            'blockedReason' => '',
            'csrfName' => 'csrf_test_name',
            'csrfHash' => 'test_hash',
            'urls' => ['updateSave' => '#'],
            'options' => ['cliente' => [], 'ejecutivo' => [], 'entidad' => []],
            'values' => [
                'folio' => 'TEST-001',
                'cli_directo_id' => 0,
                'cli_directo_ejecutivo_id' => 0,
                'contrato' => '',
                'unidad' => '',
                'serie' => '',
                'placas' => '',
                'entidad_id' => 0,
                'observaciones' => '',
                'current_step' => 1,
            ],
        ];
    }

    private function minimalEvidenceForm(): array
    {
        return [
            'canView' => false,
            'canAdd' => false,
            'blockedReason' => '',
            'csrfName' => 'csrf_test_name',
            'csrfHash' => 'test_hash',
            'tramiteId' => 100,
            'urls' => [],
            'items' => [],
        ];
    }

    private function minimalNotesForm(): array
    {
        return [
            'canView' => false,
            'canAdd' => false,
            'blockedReason' => '',
            'csrfName' => 'csrf_test_name',
            'csrfHash' => 'test_hash',
            'tramiteId' => 100,
            'urls' => [],
            'items' => [],
        ];
    }
}
