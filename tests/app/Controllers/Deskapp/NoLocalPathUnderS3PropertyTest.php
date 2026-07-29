<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 1: No local upload path under s3.
 *
 * Validates: Requirements 2.1, 2.2, 2.3
 *
 * For every render/read call site and every stored value, when the active
 * driver is the S3_Driver the resolved URL never contains the substring
 * `/assets/uploads/`:
 *
 *   - A Render_Site that resolves a non-empty Stored_Value to a non-empty URL
 *     produces a URL that does not contain `/assets/uploads/`         (Req 2.1)
 *   - A Files_Endpoint file entry's `existing_path` is a presigned-style URL
 *     (here `https://bucket/<key>?sig`) and never contains
 *     `/assets/uploads/`                                              (Req 2.2, 2.3)
 *   - The image `icon` (which mirrors `existing_path` for images and a static
 *     icon path for non-images) never contains `/assets/uploads/`.
 *
 * The core reason the property holds is that `keyFromStored()` strips the
 * `/assets/uploads/` prefix (and the origin) out of absolute legacy URLs and
 * rebuilds bare filenames as `category[/id]/file`, so the canonical key handed
 * to the S3 driver is prefix-free; the presigned URL is then built from that
 * key alone.
 *
 * giorgiosironi/eris is NOT installed, so this "fuzz harness" is a seeded
 * PHPUnit data provider that sweeps the documented value classes (bare
 * filenames, `category/id/file` relative keys, absolute `base_url` URLs,
 * empty/whitespace, adversarial `..`/null-byte). The `fileStorage` service is
 * a FAKE s3 driver double injected via `Services::injectMock` that returns a
 * presigned-style `https://bucket/<key>?sig` URL — no live AWS calls are made.
 *
 * @internal
 */
final class NoLocalPathUnderS3PropertyTest extends CIUnitTestCase
{
    /** The forbidden substring under the S3 driver. */
    private const LOCAL_UPLOAD_MARKER = '/assets/uploads/';

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
     * Register a FAKE s3 driver as the `fileStorage` service.
     *
     * It models `S3FileStorage::url()`: it signs locally (no network) and
     * returns a presigned-style URL of the shape `https://bucket/<key>?sig`
     * built purely from the canonical key. Empty keys resolve to '' (the real
     * helper short-circuits before ever calling the service, but modelling it
     * keeps the double faithful).
     *
     * @return object exposing ->calls (array of [key, ttl]) and a url() method.
     */
    private function injectFakeS3Storage(): object
    {
        $fake = new class {
            public array $calls = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];

                if ($key === '') {
                    return '';
                }

                // Presigned-style URL: bucket + canonical key + signature query.
                return 'https://bucket/' . $key
                    . '?X-Amz-Expires=' . $ttl
                    . '&X-Amz-Signature=' . substr(hash('sha256', $key . '|' . $ttl), 0, 32);
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Property 1 (Req 2.1): every non-empty URL a Render_Site produces via
     * `file_url()` / `file_url_map()` / `file_url_list()` under the S3 driver
     * is free of the `/assets/uploads/` marker.
     *
     * @dataProvider provideStoredValueCases
     *
     * @param string[] $storedValues
     */
    public function testResolvedRenderUrlsNeverContainLocalUploadPath(
        array $storedValues,
        string $category,
        ?int $id
    ): void {
        $this->injectFakeS3Storage();

        $message = 'storedValues=' . var_export($storedValues, true)
            . " category={$category} id=" . var_export($id, true);

        // Guaranteed assertion so an empty input list is not "risky".
        $this->assertIsArray(file_url_map($storedValues, $category, $id), $message);

        // file_url() for each individual stored value.
        foreach ($storedValues as $value) {
            $url = file_url((string) $value, $category, $id);
            $this->assertNotContainsLocalUpload($url, 'file_url ' . $message);
        }

        // file_url_map(): every mapped URL.
        foreach (file_url_map($storedValues, $category, $id) as $key => $url) {
            $this->assertNotContainsLocalUpload($url, "file_url_map[{$key}] " . $message);
        }

        // file_url_list(): every entry URL.
        foreach (file_url_list($storedValues, $category, $id) as $entry) {
            $this->assertNotContainsLocalUpload($entry['url'], "file_url_list {$entry['name']} " . $message);
        }
    }

    /**
     * Property 1 (Req 2.2, 2.3): a Files_Endpoint file entry built under the
     * S3 driver has an `existing_path` that is a presigned-style URL and an
     * `icon` that is either that presigned URL (images) or a static icon path
     * (non-images); neither field ever contains `/assets/uploads/`.
     *
     * This reproduces the exact `existing_path`/`icon` construction of the
     * migrated `getFiles` endpoints (Concluido.php / Tramites.php) under s3
     * (driver-aware gate skipped; `size` omitted).
     *
     * @dataProvider provideFilesEndpointCases
     *
     * @param string[] $fileNames
     */
    public function testFilesEndpointEntriesNeverContainLocalUploadPath(
        array $fileNames,
        string $category,
        int $id
    ): void {
        $this->injectFakeS3Storage();

        $entries = $this->buildFilesEndpointEntriesUnderS3($fileNames, $category, $id);

        $message = 'fileNames=' . var_export($fileNames, true) . " category={$category} id={$id}";

        // Guaranteed assertion so an empty/all-blank input list is not "risky".
        $this->assertIsArray($entries, $message);

        foreach ($entries as $entry) {
            // Req 2.2: existing_path is the presigned-style URL (starts with the
            // fake bucket origin) whenever it is non-empty.
            if ($entry['existing_path'] !== '') {
                $this->assertStringStartsWith(
                    'https://bucket/',
                    $entry['existing_path'],
                    'existing_path must be a presigned bucket URL: ' . $message
                );
            }

            // Req 2.3: existing_path never contains /assets/uploads/.
            $this->assertNotContainsLocalUpload($entry['existing_path'], 'existing_path ' . $message);

            // Req 2.1: the icon (image => presigned URL, non-image => static
            // icon path) never contains /assets/uploads/ either.
            $this->assertNotContainsLocalUpload($entry['icon'], 'icon ' . $message);

            // Under s3 the local-disk gate is skipped => no `size` field.
            $this->assertArrayNotHasKey('size', $entry, 'size must be omitted under s3: ' . $message);
        }
    }

    /**
     * Reproduce the migrated `getFiles` endpoint entry build under the S3
     * driver: skip empty rows, resolve `existing_path` via `file_url()`, and
     * set `icon` to the resolved URL for images or a static icon path
     * otherwise. No local-disk gate, no `size`.
     *
     * @param string[] $fileNames
     *
     * @return array<int,array<string,mixed>>
     */
    private function buildFilesEndpointEntriesUnderS3(array $fileNames, string $category, int $id): array
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
                continue; // empty/whitespace rows excluded
            }

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
     * Assert a resolved URL does not contain the local upload marker.
     */
    private function assertNotContainsLocalUpload(string $url, string $context): void
    {
        $this->assertStringNotContainsString(
            self::LOCAL_UPLOAD_MARKER,
            $url,
            "Resolved URL under s3 must not contain '/assets/uploads/'. {$context}; url=" . var_export($url, true)
        );
    }

    /**
     * Seeded fuzz harness of stored-value lists across the in-scope categories,
     * mixing the documented value classes: bare filenames, relative keys,
     * absolute legacy URLs (with and without the /assets/uploads/ prefix),
     * empty/whitespace, and adversarial (`..`, null-byte).
     *
     * @return array<string, array{0:string[],1:string,2:int|null}>
     */
    public function provideStoredValueCases(): array
    {
        mt_srand(20240612);

        $valuePool = [
            // Bare filenames (legacy common case).
            'a.jpg', 'b.pdf', 'comprobante.PNG', 'documento_5001.pdf', 'foto.jpeg',
            'recibo.gif', 'archivo.WEBP', 'scan.bmp', 'diagrama.svg', 'notas.txt',
            // Relative keys (canonical category/id/file, no scheme).
            'pago_gestor/12472/file.jpg', 'documentostatus/doc.pdf',
            '/cobro_cliente/5/x.png', 'pago_derechos/900/recibo.pdf',
            // Absolute legacy URLs WITH the /assets/uploads/ prefix (must be stripped).
            'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
            'http://legacy.example/assets/uploads/documentostatus/scan.png',
            'https://cdn.host/assets/uploads/cobro_cliente/5/comprobante.jpeg',
            // Absolute URLs WITHOUT the prefix (only origin stripped).
            'http://old.host/documentostatus/doc.pdf',
            'https://files.example/pago_gestor/1/thing.pdf',
            // Empty / whitespace-only.
            '', '   ', "\t\n ", '  ',
            // Adversarial.
            '../../etc/passwd', "null\0byte.png", '..', '.', 'foo/../bar.jpg',
        ];

        $categories = ['documentostatus', 'pago_gestor', 'pago_derechos', 'cobro_cliente', 'evidencias', ''];

        $cases = [];

        // Fixed edge cases that directly stress the marker-stripping guarantee.
        $cases['legacy_urls_with_marker'] = [
            [
                'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
                'http://legacy.example/assets/uploads/documentostatus/scan.png',
            ],
            'pago_gestor',
            12472,
        ];
        $cases['bare_filenames'] = [['a.jpg', 'b.pdf', 'c.png'], 'documentostatus', null];
        $cases['all_blank']      = [['', '   ', "\t"], 'cobro_cliente', 5];
        $cases['adversarial']    = [['..', "x\0y.jpg", 'foo/../bar.pdf'], 'pago_derechos', 900];

        $count = 250;
        for ($i = 0; $i < $count; $i++) {
            $len  = mt_rand(0, 7);
            $list = [];
            for ($j = 0; $j < $len; $j++) {
                $list[] = $valuePool[mt_rand(0, count($valuePool) - 1)];
            }

            $category = $categories[mt_rand(0, count($categories) - 1)];
            $id       = (mt_rand(0, 1) === 0) ? null : mt_rand(1, 999999);

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$list, $category, $id];
        }

        return $cases;
    }

    /**
     * Seeded fuzz harness of per-id file-name lists for the Files_Endpoint
     * modelling (per-id categories always carry a positive id).
     *
     * @return array<string, array{0:string[],1:string,2:int}>
     */
    public function provideFilesEndpointCases(): array
    {
        mt_srand(20240613);

        $namePool = [
            'a.jpg', 'b.pdf', 'comprobante.PNG', 'recibo.jpeg', 'scan.gif',
            'documento.webp', 'foto.BMP', 'firma.svg', 'planilla.xlsx',
            'contrato.docx', 'notas.txt', 'paquete.zip', 'legacy.rar',
            'sin_extension', '.hidden', '', '   ',
            'https://old.host/assets/uploads/cobro_cliente/5/x.jpg',
        ];

        $categories = ['pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];

        $cases['images_and_docs'] = [['a.jpg', 'b.pdf', 'c.png', 'd.txt'], 'cobro_cliente', 5];
        $cases['with_blanks']     = [['a.jpg', '', '   ', 'b.pdf'], 'pago_gestor', 12472];
        $cases['legacy_url_row']  = [['https://old.host/assets/uploads/cobro_cliente/5/x.jpg'], 'cobro_cliente', 5];

        $count = 120;
        for ($i = 0; $i < $count; $i++) {
            $len  = mt_rand(0, 6);
            $list = [];
            for ($j = 0; $j < $len; $j++) {
                $list[] = $namePool[mt_rand(0, count($namePool) - 1)];
            }

            $category = $categories[mt_rand(0, count($categories) - 1)];
            $id       = mt_rand(1, 999999);

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$list, $category, $id];
        }

        return $cases;
    }
}
