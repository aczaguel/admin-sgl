<?php

namespace Tests\App\Helpers;

use App\Libraries\Storage\FileStorageService;
use App\Libraries\Storage\LocalFileStorage;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Property-based test for Property 3: Local output is byte-identical to today.
 *
 * Validates: Requirements 3.1, 3.2, 3.5
 *
 * Under driver=local, for every stored value `v`, `file_url(v, category, id)`
 * must return the EXACT same string as
 * `base_url('/assets/uploads/' . keyFromStored(v, category, id))` with
 * per-segment rawurlencode applied to the normalized key — matching the
 * pre-change code's output byte-for-byte.
 *
 * For empty, whitespace-only, or unresolvable inputs, both sides must yield ''.
 *
 * This test uses the REAL LocalFileStorage driver (not a fake) injected via
 * Services::injectMock('fileStorage', ...) so that the url() method exercises
 * the actual per-segment encoding logic. Config is forced to driver='local'.
 *
 * giorgiosironi/eris is not installed, so the fuzz harness is implemented as a
 * seeded PHPUnit data provider sweeping the four historical value classes:
 *   - Bare filenames (photo.jpg, comprobante.pdf)
 *   - Relative keys (pago_gestor/12472/file.jpg)
 *   - Absolute URLs (https://old.host/assets/uploads/pago_gestor/12472/file.jpg)
 *   - Empty/whitespace/unresolvable
 *
 * No live AWS calls are made. No files are written to disk.
 *
 * @internal
 */
final class LocalByteIdenticalPropertyTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['filestorage', 'url']);
        Services::reset(true);
    }

    protected function tearDown(): void
    {
        Services::reset(true);
        parent::tearDown();
    }

    /**
     * Inject the REAL LocalFileStorage driver configured for driver=local.
     *
     * We build a FileStorageConfig with driver='local' and localRoot pointing
     * to the standard FCPATH.'assets/uploads' (the url() method never touches
     * the filesystem — it only builds the URL string). Then we inject the
     * resulting FileStorageService (which wraps LocalFileStorage) so that
     * service('fileStorage') returns the real local driver.
     */
    private function injectRealLocalDriver(): void
    {
        $config = new FileStorageConfig();
        $config->driver    = 'local';
        $config->dualWrite = false;
        // localRoot defaults to FCPATH.'assets/uploads' in the config class.

        $service = new FileStorageService($config);
        Services::injectMock('fileStorage', $service);
    }

    /**
     * Compute the expected "pre-change" URL that the old code would produce:
     * base_url('/assets/uploads/' . per-segment-encoded key).
     *
     * For an empty or unresolvable key (keyFromStored returns ''), the expected
     * output is '' (short-circuit in file_url).
     */
    private function expectedLegacyUrl(string $storedValue, string $category, ?int $id): string
    {
        $key = keyFromStored($storedValue, $category, $id);
        if ($key === '') {
            return '';
        }

        // Per-segment rawurlencode, matching LocalFileStorage::url().
        $encoded = implode('/', array_map('rawurlencode', explode('/', $key)));

        return base_url('/assets/uploads/' . $encoded);
    }

    /**
     * Property 3 (Req 3.1, 3.2): for every non-empty normalizable stored
     * value under driver=local, file_url() output is byte-identical to the
     * pre-change base_url('/assets/uploads/' + per-segment-encoded key).
     *
     * @dataProvider provideResolvableValues
     */
    public function testFileUrlMatchesLegacyOutputForResolvableValues(
        string $storedValue,
        string $category,
        ?int $id
    ): void {
        $this->injectRealLocalDriver();

        $actual   = file_url($storedValue, $category, $id);
        $expected = $this->expectedLegacyUrl($storedValue, $category, $id);

        $message = sprintf(
            'file_url(%s, %s, %s) must be byte-identical to legacy URL under local driver',
            var_export($storedValue, true),
            var_export($category, true),
            var_export($id, true)
        );

        $this->assertSame($expected, $actual, $message);
        // Under local the URL must always contain /assets/uploads/ when non-empty.
        if ($actual !== '') {
            $this->assertStringContainsString('/assets/uploads/', $actual, $message);
        }
    }

    /**
     * Property 3 (Req 3.2, 3.5): for empty, whitespace-only, or unresolvable
     * inputs under driver=local, file_url() returns '' — the same result the
     * pre-change code produces for these inputs.
     *
     * @dataProvider provideUnresolvableValues
     */
    public function testFileUrlMatchesLegacyOutputForUnresolvableValues(
        string $storedValue,
        string $category,
        ?int $id
    ): void {
        $this->injectRealLocalDriver();

        $actual   = file_url($storedValue, $category, $id);
        $expected = $this->expectedLegacyUrl($storedValue, $category, $id);

        $message = sprintf(
            'file_url(%s, %s, %s) must match pre-change output (empty) for unresolvable inputs',
            var_export($storedValue, true),
            var_export($category, true),
            var_export($id, true)
        );

        $this->assertSame($expected, $actual, $message);
        $this->assertSame('', $actual, $message);
    }

    /**
     * Property 3 (Req 3.1): per-segment encoding is applied correctly —
     * filenames with special characters produce the same percent-encoded URL
     * segments that base_url + rawurlencode would produce.
     *
     * @dataProvider provideSpecialCharacterValues
     */
    public function testPerSegmentEncodingMatchesLegacy(
        string $storedValue,
        string $category,
        ?int $id
    ): void {
        $this->injectRealLocalDriver();

        $actual   = file_url($storedValue, $category, $id);
        $expected = $this->expectedLegacyUrl($storedValue, $category, $id);

        $message = sprintf(
            'Per-segment encoding mismatch for file_url(%s, %s, %s)',
            var_export($storedValue, true),
            var_export($category, true),
            var_export($id, true)
        );

        $this->assertSame($expected, $actual, $message);
    }

    /**
     * Property 3 fuzz sweep: exhaustive sweep over randomly generated values
     * from all value classes under local driver — every single one must match
     * the expected legacy output.
     *
     * @dataProvider provideFuzzValues
     */
    public function testFuzzSweepByteIdentical(
        string $storedValue,
        string $category,
        ?int $id
    ): void {
        $this->injectRealLocalDriver();

        $actual   = file_url($storedValue, $category, $id);
        $expected = $this->expectedLegacyUrl($storedValue, $category, $id);

        $message = sprintf(
            'Fuzz: file_url(%s, %s, %s) expected=%s actual=%s',
            var_export($storedValue, true),
            var_export($category, true),
            var_export($id, true),
            var_export($expected, true),
            var_export($actual, true)
        );

        $this->assertSame($expected, $actual, $message);
    }

    // =========================================================================
    // Data Providers
    // =========================================================================

    /**
     * Resolvable values: bare filenames, relative keys, and absolute URLs that
     * normalize to a valid key under the standard categories.
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     */
    public function provideResolvableValues(): array
    {
        return [
            // --- Bare filenames (most common legacy case) ---
            'bare_jpg' => ['photo.jpg', 'documentostatus', null],
            'bare_pdf' => ['comprobante.pdf', 'pago_gestor', 12472],
            'bare_png' => ['recibo.png', 'pago_derechos', 900],
            'bare_gif' => ['firma.gif', 'cobro_cliente', 1],
            'bare_no_ext' => ['documento', 'documentostatus', null],
            'bare_multi_dot' => ['comprobante.v2.final.pdf', 'pago_gestor', 5000],
            'bare_uppercase' => ['PHOTO.JPG', 'documentostatus', null],
            'bare_with_spaces_category' => ['invoice.pdf', 'evidencias', null],

            // --- Relative keys (contain a '/') ---
            'rel_pago_gestor' => ['pago_gestor/12472/file.jpg', 'pago_gestor', 12472],
            'rel_documentostatus' => ['documentostatus/doc.pdf', 'documentostatus', null],
            'rel_cobro_cliente' => ['cobro_cliente/5/receipt.png', 'cobro_cliente', 5],
            'rel_pago_derechos' => ['pago_derechos/900/pagare.pdf', 'pago_derechos', 900],
            'rel_leading_slash' => ['/pago_gestor/12472/file.jpg', 'pago_gestor', 12472],
            'rel_nested' => ['sub/dir/file.pdf', 'documentostatus', null],

            // --- Absolute URLs with /assets/uploads/ prefix ---
            'abs_pago_gestor' => [
                'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
                'pago_gestor', 12472,
            ],
            'abs_documentostatus' => [
                'https://admin.example.com/assets/uploads/documentostatus/doc.pdf',
                'documentostatus', null,
            ],
            'abs_cobro_http' => [
                'http://old.host/assets/uploads/cobro_cliente/5/receipt.png',
                'cobro_cliente', 5,
            ],
            'abs_pago_derechos' => [
                'https://example.org/assets/uploads/pago_derechos/900/pagare.pdf',
                'pago_derechos', 900,
            ],

            // --- Absolute URLs without /assets/uploads/ prefix ---
            'abs_no_prefix' => [
                'https://cdn.example.com/documentostatus/doc.pdf',
                'documentostatus', null,
            ],
        ];
    }

    /**
     * Unresolvable values: empty, whitespace-only, and inputs whose
     * keyFromStored output is '' — all must yield '' from file_url().
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     */
    public function provideUnresolvableValues(): array
    {
        return [
            'empty' => ['', 'documentostatus', null],
            'space' => [' ', 'pago_gestor', 12472],
            'spaces' => ['   ', 'cobro_cliente', 1],
            'tab' => ["\t", 'pago_derechos', 900],
            'newline' => ["\n", 'documentostatus', null],
            'mixed_ws' => ["  \t\r\n ", 'evidencias', null],
            'empty_no_category' => ['', '', null],
            'whitespace_no_category' => ['   ', '', null],
        ];
    }

    /**
     * Values with special characters that exercise per-segment rawurlencode:
     * spaces, unicode, and URL-special characters in filenames.
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     */
    public function provideSpecialCharacterValues(): array
    {
        return [
            'space_in_filename' => ['my file.jpg', 'documentostatus', null],
            'parens_in_filename' => ['doc(1).pdf', 'pago_gestor', 100],
            'plus_in_filename' => ['a+b.png', 'cobro_cliente', 5],
            'hash_in_filename' => ['doc#2.pdf', 'documentostatus', null],
            'ampersand_in_filename' => ['a&b.jpg', 'pago_derechos', 200],
            'percent_in_filename' => ['25%.pdf', 'documentostatus', null],
            'unicode_filename' => ['comprobante_ñ.pdf', 'pago_gestor', 300],
            'accented_filename' => ['factura_número_1.pdf', 'documentostatus', null],
            'at_sign_filename' => ['user@file.jpg', 'cobro_cliente', 10],
            'exclamation_filename' => ['urgent!.pdf', 'documentostatus', null],
        ];
    }

    /**
     * Seeded fuzz harness generating random stored values from all four value
     * classes. Deterministic seed ensures reproducible counterexamples.
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     */
    public function provideFuzzValues(): array
    {
        mt_srand(20240715);

        $bareFilenames = [
            'photo.jpg', 'comprobante.pdf', 'recibo.png', 'firma.gif',
            'doc.webp', 'scan.bmp', 'icon.svg', 'archivo.txt',
            'factura_12345.pdf', 'IMG_20240101.jpeg', 'UPPER.PDF',
            'no_extension', 'double..dot.jpg', 'a.b.c.d.pdf',
        ];

        $relativeKeys = [
            'pago_gestor/12472/file.jpg',
            'documentostatus/doc.pdf',
            'cobro_cliente/5/receipt.png',
            'pago_derechos/900/pagare.pdf',
            'sub/dir/file.pdf',
            '/leading/slash/file.jpg',
        ];

        $absoluteUrls = [
            'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
            'https://admin.example.com/assets/uploads/documentostatus/doc.pdf',
            'http://old.host/assets/uploads/cobro_cliente/5/receipt.png',
            'https://cdn.example.com/documentostatus/doc.pdf',
            'https://example.org/assets/uploads/pago_derechos/900/pagare.pdf',
        ];

        $emptyValues = ['', ' ', '   ', "\t", "\n", "  \t\r\n "];

        $allValues = array_merge($bareFilenames, $relativeKeys, $absoluteUrls, $emptyValues);

        $categories = [
            ['documentostatus', null],
            ['pago_gestor', 12472],
            ['pago_derechos', 900],
            ['cobro_cliente', 5],
            ['evidencias', null],
            ['', null],
        ];

        $cases = [];
        $count = 200;

        for ($i = 0; $i < $count; $i++) {
            $value    = $allValues[mt_rand(0, count($allValues) - 1)];
            $catEntry = $categories[mt_rand(0, count($categories) - 1)];

            $label = 'fuzz_' . $i . '_' . substr(md5($value . $catEntry[0] . ($catEntry[1] ?? '')), 0, 6);
            $cases[$label] = [$value, $catEntry[0], $catEntry[1]];
        }

        return $cases;
    }
}
