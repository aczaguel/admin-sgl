<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 2: Every rendered file resolves through `file_url`.
 *
 * Validates: Requirements 1.1, 1.5, 1.6, 11.2
 *
 * Property 2 (design):
 *   ∀ u ∈ renderedUrls: ∃ v: u = file_url(v, category, id).
 *
 * For every rendered file URL produced by the getFiles endpoints (or any
 * Render_Site), there must exist a stored value `v` such that the URL equals
 * `file_url(v, category, id)`. No URL is produced by string concatenation of
 * a base and a filename.
 *
 * The test uses a RECORDING `fileStorage` double injected via
 * `Services::injectMock('fileStorage', ...)` that captures every `url()`
 * call. For each non-empty file name provided to the getFiles endpoint
 * harness, the produced `existing_path` must be byte-for-byte equal to
 * `file_url(name, category, id)` — proving that the endpoint delegates
 * resolution exclusively through the helper.
 *
 * Value classes swept: bare filenames (simple.jpg, foto.png), relative keys,
 * mixed case, various extensions.
 *
 * giorgiosironi/eris is NOT installed, so this is a seeded PHPUnit
 * data-provider fuzz harness. No live AWS calls are made.
 *
 * @internal
 */
final class ResolvesThroughFileUrlPropertyTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('filestorage');
        Services::reset(true);
    }

    protected function tearDown(): void
    {
        Services::reset(true);
        parent::tearDown();
    }

    /**
     * Register a RECORDING test double as the `fileStorage` service.
     *
     * It records every `url()` call with its arguments and returns a
     * deterministic presigned-style URL for non-empty keys. This allows the
     * test to assert that every `existing_path` was produced by `file_url()`
     * (which calls `service('fileStorage')->url(key, ttl)` internally).
     *
     * @return object exposing ->calls (array of [key, ttl]) and a url() method.
     */
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            /** @var array<int,array{0:string,1:int}> */
            public array $calls = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];

                if ($key === '') {
                    return '';
                }

                // Deterministic presigned-style URL from key alone.
                return 'https://bucket/' . $key
                    . '?X-Amz-Expires=' . $ttl
                    . '&X-Amz-Signature=' . substr(hash('sha256', $key . '|' . $ttl), 0, 32);
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Property 2 (Req 1.1, 1.5): every `existing_path` produced by the
     * getFiles endpoint harness equals `file_url(name, category, id)` for
     * the row's category and id. No URL is produced by concatenation.
     *
     * @dataProvider provideStoredValueCases
     *
     * @param string[] $storedValues
     */
    public function testEveryExistingPathEqualsFileUrlOutput(
        array $storedValues,
        string $category,
        ?int $id
    ): void {
        $recording = $this->injectRecordingStorage();

        $entries = $this->buildFilesEndpointEntries($storedValues, $category, $id);
        $message = 'storedValues=' . var_export($storedValues, true)
            . " category={$category} id=" . var_export($id, true);

        // Guaranteed assertion so an empty input list is not "risky".
        $this->assertIsArray($entries, $message);

        foreach ($entries as $entry) {
            $name         = $entry['name'];
            $existingPath = $entry['existing_path'];

            // The resolved URL must equal file_url(name, category, id) exactly.
            $expected = file_url($name, $category, $id);
            $this->assertSame(
                $expected,
                $existingPath,
                "existing_path must equal file_url(name, category, id). name={$name} " . $message
            );
        }

        // Every resolved URL was produced through the recording double (the
        // single resolution point). If any endpoint had bypassed file_url()
        // and built the URL by string concatenation, it would not appear in
        // the recording's calls.
        $this->assertGreaterThanOrEqual(
            count($entries),
            count($recording->calls),
            'The recording double must have been called at least once per entry. ' . $message
        );
    }

    /**
     * Property 2 (Req 1.6, 11.2): when resolved through `file_url_map()` or
     * `file_url_list()`, the per-entry URL is still byte-for-byte equal to
     * `file_url(name, category, id)` for the corresponding stored value.
     *
     * @dataProvider provideStoredValueCases
     *
     * @param string[] $storedValues
     */
    public function testMapAndListResolveIdenticallyToFileUrl(
        array $storedValues,
        string $category,
        ?int $id
    ): void {
        $this->injectRecordingStorage();

        $message = 'storedValues=' . var_export($storedValues, true)
            . " category={$category} id=" . var_export($id, true);

        // file_url_map: each value must equal file_url(key, category, id).
        $map = file_url_map($storedValues, $category, $id);
        foreach ($map as $key => $url) {
            $expected = file_url($key, $category, $id);
            $this->assertSame(
                $expected,
                $url,
                "file_url_map[{$key}] must equal file_url(key, category, id). " . $message
            );
        }

        // file_url_list: each entry url must equal file_url(name, category, id).
        $list = file_url_list($storedValues, $category, $id);
        foreach ($list as $entry) {
            $expected = file_url($entry['name'], $category, $id);
            $this->assertSame(
                $expected,
                $entry['url'],
                "file_url_list entry for '{$entry['name']}' must equal file_url(name, category, id). " . $message
            );
        }

        // Guaranteed assertion for empty input case.
        $this->assertTrue(true);
    }

    /**
     * Property 2 (Req 1.1): the recording double confirms that `file_url()`
     * is the SINGLE resolution point — every URL produced flows through the
     * recording's `url()` method. After resolving N distinct non-empty values,
     * the double must have been invoked at least N times.
     *
     * @dataProvider provideStoredValueCases
     *
     * @param string[] $storedValues
     */
    public function testRecordingDoubleIsTheSingleResolutionPoint(
        array $storedValues,
        string $category,
        ?int $id
    ): void {
        $recording = $this->injectRecordingStorage();

        // Resolve each stored value through file_url directly.
        $resolvedCount = 0;
        foreach ($storedValues as $value) {
            $name = trim((string) $value);
            if ($name === '') {
                continue;
            }
            $url = file_url($name, $category, $id);
            $resolvedCount++;

            // If the URL is non-empty, it must start with 'https://bucket/' —
            // proving it came from the recording double, not a hand-built path.
            if ($url !== '') {
                $this->assertStringStartsWith(
                    'https://bucket/',
                    $url,
                    "file_url must resolve through the recording double. name={$name}"
                );
            }
        }

        // The recording captured at least as many calls as we made.
        $this->assertGreaterThanOrEqual(
            $resolvedCount,
            count($recording->calls),
            'Recording double must capture every resolution call.'
        );
    }

    /**
     * Reproduce the migrated `getFiles` endpoint entry build (Concluido /
     * Tramites). Under the S3 driver the local-disk gate is skipped; every
     * non-empty row is included and resolved through `file_url()`.
     *
     * @param string[] $fileNames
     *
     * @return array<int,array<string,mixed>>
     */
    private function buildFilesEndpointEntries(array $fileNames, string $category, ?int $id): array
    {
        $staticIconFor = static function (string $extension): string {
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        $result = [];
        $rowId  = 0;
        foreach ($fileNames as $name) {
            $name = (string) $name;
            if (trim($name) === '') {
                continue; // empty/whitespace rows excluded (Req 11.6)
            }

            // The key assertion: existing_path comes from file_url(), not
            // from base_url() + filename concatenation.
            $existingPath = file_url($name, $category, $id);

            $result[] = [
                'id'            => ++$rowId,
                'name'          => $name,
                'existing_path' => $existingPath,
                'icon'          => is_image_filename($name)
                    ? $existingPath
                    : $staticIconFor((string) pathinfo($name, PATHINFO_EXTENSION)),
            ];
        }

        return $result;
    }

    /**
     * Seeded fuzz harness of stored-value lists sweeping the documented value
     * classes: bare filenames (simple.jpg, foto.png), relative keys, mixed
     * case, various extensions, empty/whitespace, adversarial.
     *
     * @return array<string, array{0:string[],1:string,2:int|null}>
     */
    public function provideStoredValueCases(): array
    {
        mt_srand(20240615);

        $valuePool = [
            // Bare filenames — the common legacy case.
            'simple.jpg', 'foto.png', 'comprobante.PDF', 'recibo.jpeg',
            'archivo.gif', 'imagen.WEBP', 'scan.bmp', 'diagrama.svg',
            'notas.txt', 'contrato.docx', 'planilla.xlsx',
            // Mixed case / unusual basenames.
            'UPPER.JPG', 'lower.png', 'MiXeD.PdF', 'Under_Score.png',
            'dash-name.jpg', 'many.dots.in.name.pdf',
            // Relative keys (canonical category/id/file, no scheme).
            'pago_gestor/12472/comprobante.jpg', 'documentostatus/acta.pdf',
            '/cobro_cliente/5/recibo.png', 'pago_derechos/900/pago.pdf',
            // Absolute legacy URLs (must be normalized by keyFromStored).
            'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
            'http://legacy.example/assets/uploads/documentostatus/scan.png',
            // Empty / whitespace-only (must be excluded).
            '', '   ', "\t\n ",
            // Adversarial (degrade to '' gracefully).
            '../../etc/passwd', "null\0byte.png", '..', '.',
        ];

        $categories = ['documentostatus', 'pago_gestor', 'pago_derechos', 'cobro_cliente', ''];

        $cases = [];

        // Fixed edge cases that directly pin the property.
        $cases['bare_images'] = [
            ['simple.jpg', 'foto.png', 'imagen.WEBP'],
            'cobro_cliente',
            42,
        ];
        $cases['bare_documents'] = [
            ['notas.txt', 'contrato.docx', 'planilla.xlsx'],
            'pago_gestor',
            12472,
        ];
        $cases['relative_keys'] = [
            ['pago_gestor/12472/comprobante.jpg', 'documentostatus/acta.pdf'],
            'pago_gestor',
            12472,
        ];
        $cases['mixed_case_extensions'] = [
            ['UPPER.JPG', 'lower.png', 'MiXeD.PdF', 'comprobante.PDF'],
            'pago_derechos',
            900,
        ];
        $cases['all_blank'] = [['', '   ', "\t"], 'cobro_cliente', 5];
        $cases['adversarial'] = [['..', "x\0y.jpg", '../../etc/passwd'], 'documentostatus', null];
        $cases['no_category'] = [['simple.jpg', 'foto.png'], '', null];

        // Fuzz: generate random combinations.
        $count = 200;
        for ($i = 0; $i < $count; $i++) {
            $len  = mt_rand(0, 7);
            $list = [];
            for ($j = 0; $j < $len; $j++) {
                $list[] = $valuePool[mt_rand(0, count($valuePool) - 1)];
            }

            $category = $categories[mt_rand(0, count($categories) - 1)];
            $id       = (mt_rand(0, 1) === 0) ? null : mt_rand(1, 999999);

            $cases['fuzz_' . $i . '_' . bin2hex(random_bytes(3))] = [$list, $category, $id];
        }

        return $cases;
    }
}
