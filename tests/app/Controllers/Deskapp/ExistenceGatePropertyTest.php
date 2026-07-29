<?php

namespace Tests\App\Controllers\Deskapp;

use App\Services\CobranzaDashboardService;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 8: Existence gate is driver-correct.
 *
 * Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 10.8
 *
 * Property 8 (design):
 *   driver=local ⟹ gate = is_file(localPath);
 *   driver=s3    ⟹ gate = true (no local check).
 *
 * Concretely, under `local` a stored value whose file is absent on disk is
 * filtered out of `getFiles` / `resolveDocUrl` exactly as before; under `s3`
 * rows are never filtered by a local-disk check (so migrated files render).
 *
 * This test has two halves that together cover the two Render_Site families
 * named in the design (getFiles endpoints + doc resolvers):
 *
 *   A) getFiles AJAX endpoints (Concluido.php ×3, Tramites.php ×1). Those
 *      methods are tightly coupled to session + ACL guards + a live DB query,
 *      so — matching the convention already established by the sibling
 *      Property 1 test (NoLocalPathUnderS3PropertyTest) in this very
 *      directory — the driver-aware gate is reproduced faithfully in a harness
 *      and exercised against a REAL temporary filesystem (real file_exists /
 *      filesize, real present/absent rows). The harness mirrors the migrated
 *      endpoint body line-for-line:
 *        - trim-empty rows are skipped                                 (11.6)
 *        - driver=local: keep the file_exists() gate + filesize()      (10.1, 10.2, 3.3, 3.4)
 *        - driver=s3: skip the local gate, omit `size`, include the row(10.3, 10.4)
 *        - no per-file S3 exists() is ever issued                      (10.8)
 *
 *   B) The Doc_Resolver. This half exercises the REAL production code —
 *      App\Services\CobranzaDashboardService::resolveDocumentStatusUrl (the
 *      twin of ClienteTramites::resolveDocUrl) — via reflection, against a
 *      real present/absent file under FCPATH:
 *        - driver=s3:  resolves directly, no local gate (never null for a
 *          resolvable basename, even with no file on disk)             (10.5)
 *        - driver=local + present candidate: returns a URL              (10.6)
 *        - driver=local + absent candidate:  returns null               (10.7)
 *
 * giorgiosironi/eris is NOT installed, so the "fuzz harness" is a seeded
 * PHPUnit data provider that sweeps present/absent file rows across the
 * per-id categories and the documentostatus category. The `fileStorage`
 * service is a RECORDING double injected via Services::injectMock (matching
 * the sibling s3-file-storage test conventions); it records every method call
 * so the test can prove no per-file S3 existence check happens. No live AWS
 * calls are made.
 *
 * @internal
 */
final class ExistenceGatePropertyTest extends CIUnitTestCase
{
    /** Temp root standing in for FCPATH for the getFiles gate harness. */
    private string $tmpRoot = '';

    /** Files created under the real FCPATH candidate dirs (cleaned in tearDown). */
    private array $fcpathFilesToRemove = [];

    /** Dirs created under the real FCPATH (cleaned in tearDown if we made them). */
    private array $fcpathDirsToRemove = [];

    /** Original FileStorage driver, restored in tearDown. */
    private string $originalDriver = 'local';

    protected function setUp(): void
    {
        parent::setUp();
        helper('filestorage');
        Services::reset(true);

        $this->originalDriver = (string) config('FileStorage')->driver;

        $this->tmpRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'sgl-existence-gate-' . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
        @mkdir($this->tmpRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        // Restore the driver we may have mutated.
        config('FileStorage')->driver = $this->originalDriver;

        // Remove any file we created under the real FCPATH candidate dirs.
        foreach ($this->fcpathFilesToRemove as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        // Remove dirs we created (deepest first), only if now empty.
        foreach (array_reverse($this->fcpathDirsToRemove) as $dir) {
            if (is_dir($dir)) {
                @rmdir($dir);
            }
        }
        $this->fcpathFilesToRemove = [];
        $this->fcpathDirsToRemove  = [];

        $this->removeTree($this->tmpRoot);

        Services::reset(true);
        parent::tearDown();
    }

    /**
     * Register a recording test double as the `fileStorage` service.
     *
     * `url()` returns a deterministic non-empty URL for any non-empty key and
     * '' for the empty key (mirroring the real helper short-circuit). Every
     * method invocation is recorded so the test can prove the render path
     * never issues a per-file `exists()` (Req 10.8).
     *
     * @return object exposing ->urlCalls, ->existsCalls and the methods.
     */
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            public array $urlCalls    = [];
            public int   $existsCalls = 0;

            public function url(string $key, int $ttl = 300): string
            {
                $this->urlCalls[] = [$key, $ttl];

                if ($key === '') {
                    return '';
                }

                return 'https://cdn.example/' . $key . '?ttl=' . $ttl;
            }

            public function exists(string $key): bool
            {
                // The render path must NEVER call this per file (Req 10.8).
                $this->existsCalls++;

                return true;
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    // ------------------------------------------------------------------
    // Part A — getFiles endpoint gate (faithful harness, real filesystem)
    // ------------------------------------------------------------------

    /**
     * Property 8 (Req 10.1, 10.2, 3.3, 3.4, 11.6): under the LOCAL driver the
     * file_exists() gate filters out rows whose file is absent on disk, keeps
     * present rows, attaches a `size` (== filesize), and skips empty rows —
     * exactly as the pre-change code did.
     *
     * @dataProvider provideFileRowSets
     *
     * @param array<int,array{name:string,present:bool}> $rows
     */
    public function testGetFilesGateExcludesAbsentFilesUnderLocal(array $rows, string $category, int $id): void
    {
        $fake = $this->injectRecordingStorage();
        config('FileStorage')->driver = 'local';

        $this->materializePresentFiles($rows, $category, $id);

        $entries = $this->buildFilesEndpointEntries($rows, $category, $id, 'local');

        // Compute which filenames exist on disk after materialization.
        // If filename X was materialized (present=true) in ANY row, the
        // physical file is on disk and ALL subsequent occurrences of the
        // same filename also pass the file_exists() gate.
        $materializedNames = [];
        foreach ($rows as $row) {
            if ($row['present'] && trim($row['name']) !== '') {
                $materializedNames[trim($row['name'])] = true;
            }
        }

        $expectedNames = [];
        foreach ($rows as $row) {
            $name = trim($row['name']);
            if ($name === '') {
                continue;              // Req 11.6: empty rows excluded.
            }
            // Under local, file_exists() is the gate. A file is on disk if
            // ANY row with that name was marked present (materialized).
            if (isset($materializedNames[$name])) {
                $expectedNames[] = $row['name']; // Req 10.1/10.2: present on disk.
            }
        }

        $gotNames = array_column($entries, 'name');
        $message  = 'rows=' . var_export($rows, true) . " category={$category} id={$id}";

        // Every present, non-empty row is included; every absent row is filtered out.
        $this->assertSame($expectedNames, $gotNames, 'local gate must keep exactly the present rows: ' . $message);

        foreach ($entries as $entry) {
            // Req 3.4: local entries carry a numeric `size` equal to filesize().
            $this->assertArrayHasKey('size', $entry, 'local entry must carry size: ' . $message);
            $this->assertIsInt($entry['size']);
            $this->assertGreaterThanOrEqual(0, $entry['size']);
            // existing_path resolved through the helper (never empty for a resolvable name).
            $this->assertNotSame('', $entry['existing_path']);
        }

        // Req 10.8: no per-file S3 existence check on the render path.
        $this->assertSame(0, $fake->existsCalls, 'no per-file exists() may be issued: ' . $message);
    }

    /**
     * Property 8 (Req 10.3, 10.4, 10.8): under the S3 driver NO local-disk gate
     * is applied — every non-empty row is included even when its file is absent
     * on the local disk — the `size` field is omitted, and no per-file S3
     * exists() check is issued.
     *
     * @dataProvider provideFileRowSets
     *
     * @param array<int,array{name:string,present:bool}> $rows
     */
    public function testGetFilesGateIsSkippedUnderS3(array $rows, string $category, int $id): void
    {
        $fake = $this->injectRecordingStorage();
        config('FileStorage')->driver = 's3';

        // Deliberately DO NOT create any file on disk: under s3 presence is irrelevant.
        $entries = $this->buildFilesEndpointEntries($rows, $category, $id, 's3');

        $expectedNames = [];
        foreach ($rows as $row) {
            $name = trim($row['name']);
            if ($name === '') {
                continue;              // Req 11.6: empty rows excluded.
            }
            $expectedNames[] = $row['name']; // Req 10.3: present AND absent both included.
        }

        $gotNames = array_column($entries, 'name');
        $message  = 'rows=' . var_export($rows, true) . " category={$category} id={$id}";

        $this->assertSame($expectedNames, $gotNames, 's3 must include every non-empty row regardless of disk: ' . $message);

        foreach ($entries as $entry) {
            // Req 10.4: `size` is omitted under s3 (no filesize() on local path).
            $this->assertArrayNotHasKey('size', $entry, 's3 entry must omit size: ' . $message);
            $this->assertNotSame('', $entry['existing_path']);
        }

        // Req 10.8: the s3 render loop must not perform a per-file existence check.
        $this->assertSame(0, $fake->existsCalls, 'no per-file S3 exists() may be issued under s3: ' . $message);
    }

    // ------------------------------------------------------------------
    // Part B — Doc_Resolver (real production code via reflection)
    // ------------------------------------------------------------------

    /**
     * Property 8 (Req 10.5): under the S3 driver the real Doc_Resolver resolves
     * directly through file_url() with NO local-disk gate — a resolvable
     * basename yields a non-null URL even though nothing exists on the local
     * disk. Adversarial basenames still degrade to null (traversal defense).
     *
     * @dataProvider provideResolverNames
     */
    public function testDocResolverSkipsLocalGateUnderS3(string $fileName, bool $resolvable): void
    {
        $this->injectRecordingStorage();
        config('FileStorage')->driver = 's3';

        $result = $this->invokeResolveDocumentStatusUrl($fileName);
        $message = 'fileName=' . var_export($fileName, true);

        if ($resolvable) {
            // No file was created on disk; s3 resolves anyway (Req 10.5).
            $this->assertNotNull($result, 's3 resolver must not gate on local disk: ' . $message);
            $this->assertNotSame('', $result, $message);
        } else {
            $this->assertNull($result, 'adversarial/empty basename must degrade to null: ' . $message);
        }
    }

    /**
     * Property 8 (Req 10.6, 10.7): under the LOCAL driver the real Doc_Resolver
     * applies the is_file() candidate-directory gate — it returns a URL only
     * when a candidate file is present on disk, and null when no candidate
     * exists.
     *
     * @dataProvider provideResolverNames
     */
    public function testDocResolverGatesOnDiskUnderLocal(string $fileName, bool $resolvable): void
    {
        $this->injectRecordingStorage();
        config('FileStorage')->driver = 'local';

        $base = basename(trim($fileName));

        // --- Absent case: no candidate file on disk ⇒ null (Req 10.7). ---
        $absent = $this->invokeResolveDocumentStatusUrl($fileName);
        $this->assertNull($absent, 'local resolver must return null when no candidate exists: fileName=' . var_export($fileName, true));

        if (! $resolvable || $base === '' || $base === '.' || $base === '..') {
            return; // adversarial names never resolve regardless of disk.
        }

        // --- Present case: create the candidate file ⇒ non-null URL (Req 10.6). ---
        $created = $this->createFcpathCandidate('assets/uploads/documentostatus/', $base);
        if ($created === null) {
            // Could not write under FCPATH in this environment; the absent-case
            // assertion above already exercised the local gate. Skip the present half.
            $this->markTestSkipped('FCPATH candidate dir is not writable for ' . $base);
        }

        $present = $this->invokeResolveDocumentStatusUrl($fileName);
        $this->assertNotNull($present, 'local resolver must resolve a present candidate: base=' . $base);
        $this->assertNotSame('', $present);
    }

    // ------------------------------------------------------------------
    // Harness + helpers
    // ------------------------------------------------------------------

    /**
     * Faithful reproduction of the migrated getFiles endpoint body (Concluido /
     * Tramites). The only substitution is the disk root: FCPATH is replaced by
     * a temp root so the test never touches the repo tree — the gate logic
     * (driver branch, file_exists, filesize, size omission) is identical.
     *
     * @param array<int,array{name:string,present:bool}> $rows
     *
     * @return array<int,array<string,mixed>>
     */
    private function buildFilesEndpointEntries(array $rows, string $category, int $id, string $driver): array
    {
        $ds                  = DIRECTORY_SEPARATOR;
        $storeFolderSpecific = 'assets/uploads/' . $category . '/' . $id . $ds;

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
        foreach ($rows as $row) {
            $name = (string) $row['name'];

            // Req 11.6: skip empty / whitespace-only rows.
            if (trim($name) === '') {
                continue;
            }

            // Driver-aware existence gate (mirrors the migrated endpoint).
            $absoluteFilePath = null;
            if ($driver === 'local') {
                $absoluteFilePath = $this->tmpRoot . $storeFolderSpecific . $name;
                if (! file_exists($absoluteFilePath)) {
                    continue; // Req 10.2: absent local file ⇒ excluded.
                }
            }

            $existingPath = file_url($name, $category, $id);

            $obj         = [];
            $obj['id']   = ++$rowId;
            $obj['name'] = $name;
            if ($driver === 'local') {
                $obj['size'] = filesize($absoluteFilePath); // Req 3.4
            }
            $obj['existing_path'] = $existingPath;
            $obj['icon']          = is_image_filename($name)
                ? $existingPath
                : $staticIconFor((string) pathinfo($name, PATHINFO_EXTENSION));

            $result[] = $obj;
        }

        return $result;
    }

    /**
     * Create the real files on the temp disk for every present, non-empty row
     * so the local file_exists()/filesize() gate has something to find.
     *
     * @param array<int,array{name:string,present:bool}> $rows
     */
    private function materializePresentFiles(array $rows, string $category, int $id): void
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = $this->tmpRoot . 'assets/uploads/' . $category . '/' . $id . $ds;

        foreach ($rows as $row) {
            $name = (string) $row['name'];
            if (trim($name) === '' || ! $row['present']) {
                continue;
            }
            // Only materialize plain basenames (the fuzz names never contain a slash).
            @mkdir($dir, 0777, true);
            file_put_contents($dir . $name, 'x'); // 1-byte payload ⇒ filesize() > 0
        }
    }

    /**
     * Invoke the real private resolver on the production service via reflection.
     */
    private function invokeResolveDocumentStatusUrl(string $fileName): ?string
    {
        $service = new CobranzaDashboardService();
        $method  = (new \ReflectionClass($service))->getMethod('resolveDocumentStatusUrl');
        $method->setAccessible(true);

        /** @var string|null $result */
        $result = $method->invoke($service, $fileName);

        return $result;
    }

    /**
     * Create a real candidate file under the actual FCPATH so the local
     * resolver's is_file() probe succeeds. Returns the file path, or null when
     * the location is not writable. Tracks created files/dirs for cleanup.
     */
    private function createFcpathCandidate(string $relativeDir, string $base): ?string
    {
        $dir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativeDir;

        $createdDir = false;
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
                return null;
            }
            $createdDir = true;
        }

        $path = $dir . $base;
        if (@file_put_contents($path, 'x') === false) {
            if ($createdDir) {
                @rmdir($dir);
            }

            return null;
        }

        $this->fcpathFilesToRemove[] = $path;
        if ($createdDir) {
            $this->fcpathDirsToRemove[] = $dir;
        }

        return $path;
    }

    /**
     * Recursively delete a directory tree (temp root only).
     */
    private function removeTree(string $path): void
    {
        if ($path === '' || ! is_dir($path)) {
            return;
        }

        $items = scandir($path) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($full)) {
                $this->removeTree($full);
            } else {
                @unlink($full);
            }
        }
        @rmdir($path);
    }

    // ------------------------------------------------------------------
    // Fuzz data providers (seeded, deterministic)
    // ------------------------------------------------------------------

    /**
     * Seeded fuzz harness of file-row sets mixing present/absent files, image
     * and non-image extensions, empty/whitespace rows, across the per-id
     * categories. Each row is {name, present}.
     *
     * @return array<string, array{0:array<int,array{name:string,present:bool}>,1:string,2:int}>
     */
    public function provideFileRowSets(): array
    {
        mt_srand(20240614);

        $namePool = [
            'a.jpg', 'b.pdf', 'comprobante.PNG', 'recibo.jpeg', 'scan.gif',
            'documento.webp', 'foto.BMP', 'firma.svg', 'planilla.xlsx',
            'contrato.docx', 'notas.txt', 'paquete.zip', 'legacy.rar',
            'sin_extension',
        ];
        $blankPool     = ['', '   ', "\t", "\n "];
        $categories    = ['pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];

        // Fixed edge cases pinning the core behaviors.
        $cases['all_present'] = [
            [
                ['name' => 'a.jpg', 'present' => true],
                ['name' => 'b.pdf', 'present' => true],
            ],
            'cobro_cliente',
            5,
        ];
        $cases['all_absent'] = [
            [
                ['name' => 'gone1.jpg', 'present' => false],
                ['name' => 'gone2.pdf', 'present' => false],
            ],
            'pago_gestor',
            12472,
        ];
        $cases['mixed_present_absent'] = [
            [
                ['name' => 'here.jpg', 'present' => true],
                ['name' => 'missing.pdf', 'present' => false],
                ['name' => 'also_here.png', 'present' => true],
            ],
            'pago_derechos',
            900,
        ];
        $cases['with_blanks'] = [
            [
                ['name' => '', 'present' => false],
                ['name' => '   ', 'present' => false],
                ['name' => 'real.jpg', 'present' => true],
            ],
            'cobro_cliente',
            7,
        ];

        $count = 120;
        for ($i = 0; $i < $count; $i++) {
            $len  = mt_rand(0, 6);
            $rows = [];
            for ($j = 0; $j < $len; $j++) {
                if (mt_rand(0, 6) === 0) {
                    // Occasionally inject a blank row.
                    $rows[] = ['name' => $blankPool[mt_rand(0, count($blankPool) - 1)], 'present' => false];
                    continue;
                }
                // Unique-ish name per slot so present/absent files don't collide.
                $base   = $namePool[mt_rand(0, count($namePool) - 1)];
                $name   = ($j % 2 === 0 ? '' : ('u' . $i . '_' . $j . '_')) . $base;
                $rows[] = ['name' => $name, 'present' => (mt_rand(0, 1) === 1)];
            }

            $category = $categories[mt_rand(0, count($categories) - 1)];
            $id       = mt_rand(1, 999999);

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$rows, $category, $id];
        }

        return $cases;
    }

    /**
     * Seeded fuzz harness of Doc_Resolver input names: resolvable basenames
     * (with and without a leading path) and adversarial values that must
     * always degrade to null (empty, whitespace, `.`, `..`, null-byte,
     * `..`-segment). Each case is {fileName, resolvable}.
     *
     * @return array<string, array{0:string,1:bool}>
     */
    public function provideResolverNames(): array
    {
        $cases = [
            // Resolvable basenames.
            'plain_pdf'        => ['acta.pdf', true],
            'plain_image'      => ['scan.png', true],
            'upper_ext'        => ['DOC.PDF', true],
            'with_path'        => ['sub/dir/recibo.jpg', true],  // basename() ⇒ recibo.jpg
            'digits'           => ['documento_5001.pdf', true],
            // Adversarial / non-normalizable ⇒ always null.
            'empty'            => ['', false],
            'space'            => ['   ', false],
            'dot'              => ['.', false],
            'dotdot'           => ['..', false],
            'dotdot_path'      => ['../../etc/passwd', false],
            'null_byte'        => ["a\0b.pdf", false],
            'dotdot_segment'   => ['foo/../bar.pdf', false],
        ];

        return $cases;
    }
}
