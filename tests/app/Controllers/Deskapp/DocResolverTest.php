<?php

namespace Tests\App\Controllers\Deskapp;

use App\Services\CobranzaDashboardService;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

/**
 * Unit tests for the migrated document resolvers (task 4.3).
 *
 * The read/render path exposes two structurally-identical `documentostatus`
 * /`docstatus` resolvers:
 *
 *   - `App\Services\CobranzaDashboardService::resolveDocumentStatusUrl()`
 *     — a private instance method (reachable here via reflection), AND
 *   - `App\Controllers\Deskapp\ClienteTramites::show()`'s inner
 *     `$resolveDocUrl` — a *static closure* declared inside the method body.
 *
 * Both implement the same algorithm required by the design:
 *   * reject empty / `.` / `..` / null-byte / `..`-segment basenames  (Req 12.1-12.4)
 *   * under the LOCAL driver, probe the SAME candidate directories
 *     `[documentostatus, docstatus]` with `is_file()` and return today's
 *     `base_url('/assets/uploads/<cat>/<base>')` (or null)             (Req 3.5, 3.6, 10.6, 10.7)
 *   * under the S3 driver, resolve directly to a presigned canonical
 *     `documentostatus/<base>` URL with NO local-disk gate             (Req 2.4, 2.5, 10.5)
 *
 * The `ClienteTramites` closure is a runtime local inside `show()` and is not
 * reachable in isolation (there is no method/function handle to reflect on);
 * its byte-identical algorithm is validated here through the reflectable
 * `CobranzaDashboardService` twin, and exercised end-to-end by the render
 * integration tests (task 8.3).
 *
 * The `fileStorage` service double is injected via `Services::injectMock`
 * (matching the sibling s3-file-storage tests). Under `local` the real
 * `LocalFileStorage` driver is used so URLs are byte-identical to today; under
 * `s3` a fake driver returns a presigned-style `https://bucket/<key>?sig` URL
 * — no live AWS calls are made.
 *
 * @internal
 */
final class DocResolverTest extends CIUnitTestCase
{
    private const LOCAL_UPLOAD_MARKER = '/assets/uploads/';

    /** Original FileStorage driver, restored in tearDown to avoid cross-test leakage. */
    private string $originalDriver = 'local';

    /** Absolute paths of files created on the local disk, removed in tearDown. */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['filestorage', 'url']);

        $this->originalDriver = (string) config('FileStorage')->driver;
        $this->createdFiles   = [];

        Services::reset(true);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->createdFiles = [];

        config('FileStorage')->driver = $this->originalDriver;
        Services::reset(true);

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Local driver
    // ---------------------------------------------------------------------

    /**
     * Req 3.5 / 10.6: under local, a value present under `documentostatus/`
     * resolves to today's byte-identical `base_url('/assets/uploads/...')` URL.
     */
    public function testLocalResolvesPresentDocumentostatusToTodaysUrl(): void
    {
        $this->useLocalDriver();
        $name = 'doc_' . uniqid('', false) . '.pdf';
        $this->createLocalDoc('documentostatus', $name);

        $url = $this->resolve($name);

        $expected = base_url('/assets/uploads/documentostatus/' . rawurlencode($name));
        $this->assertSame($expected, $url);
        $this->assertStringContainsString('/assets/uploads/documentostatus/', (string) $url);
    }

    /**
     * Req 3.6 / 10.6: under local, when the value is absent from
     * `documentostatus/` but present under the sibling `docstatus/` candidate,
     * the resolver falls through to the second candidate — proving the SAME
     * candidate set is evaluated as the pre-change code.
     */
    public function testLocalResolvesPresentDocstatusFallbackCandidate(): void
    {
        $this->useLocalDriver();
        $name = 'doc_' . uniqid('', false) . '.pdf';
        $this->createLocalDoc('docstatus', $name); // only the fallback candidate exists

        $url = $this->resolve($name);

        $expected = base_url('/assets/uploads/docstatus/' . rawurlencode($name));
        $this->assertSame($expected, $url);
    }

    /**
     * Req 3.6: when the file is present under BOTH candidate directories, the
     * resolver returns the `documentostatus` URL — the first candidate wins,
     * matching the pre-change probing order.
     */
    public function testLocalEvaluatesDocumentostatusBeforeDocstatus(): void
    {
        $this->useLocalDriver();
        $name = 'doc_' . uniqid('', false) . '.pdf';
        $this->createLocalDoc('documentostatus', $name);
        $this->createLocalDoc('docstatus', $name);

        $url = $this->resolve($name);

        $this->assertSame(
            base_url('/assets/uploads/documentostatus/' . rawurlencode($name)),
            $url
        );
    }

    /**
     * Req 10.7: under local, when NO candidate directory contains the file the
     * resolver returns null (identical to the pre-change behavior).
     */
    public function testLocalReturnsNullWhenNoCandidatePresent(): void
    {
        $this->useLocalDriver();
        $name = 'missing_' . uniqid('', false) . '.pdf';

        $this->assertNull($this->resolve($name));
    }

    // ---------------------------------------------------------------------
    // S3 driver
    // ---------------------------------------------------------------------

    /**
     * Req 2.4 / 2.5 / 10.5: under s3 the resolver returns a presigned canonical
     * `documentostatus/<base>` URL WITHOUT any local-disk gate — the file need
     * not exist on the local disk for a URL to be produced.
     */
    public function testS3ResolvesPresignedCanonicalUrlWithoutLocalGate(): void
    {
        $this->useS3Driver();
        $name = 'never_on_local_disk_' . uniqid('', false) . '.pdf';

        $url = $this->resolve($name);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://bucket/', (string) $url);
        $this->assertStringContainsString('documentostatus/' . $name, (string) $url);
        $this->assertStringNotContainsString(self::LOCAL_UPLOAD_MARKER, (string) $url);
    }

    /**
     * Req 2.4: under s3 a stored value that carries a path is reduced to its
     * basename and resolved against the canonical `documentostatus` prefix
     * (never the legacy `docstatus/` location it may have been stored under).
     */
    public function testS3ReducesPathToCanonicalDocumentostatusBasename(): void
    {
        $this->useS3Driver();
        $name = 'scan_' . uniqid('', false) . '.png';

        $url = $this->resolve('docstatus/' . $name);

        $this->assertNotNull($url);
        $this->assertStringContainsString('documentostatus/' . $name, (string) $url);
        $this->assertStringNotContainsString('docstatus/' . $name, (string) $url);
        $this->assertStringNotContainsString(self::LOCAL_UPLOAD_MARKER, (string) $url);
    }

    // ---------------------------------------------------------------------
    // Path-traversal safety (Req 12.1, 12.2, 12.3) — both drivers
    // ---------------------------------------------------------------------

    /**
     * Req 12.1 / 12.3: an empty, `.`, `..`, null-byte, or `..`-segment value
     * resolves to null under BOTH drivers, never producing a URL.
     *
     * @dataProvider provideUnsafeValues
     */
    public function testUnsafeBasenamesReturnNullUnderBothDrivers(string $value): void
    {
        $this->useLocalDriver();
        $this->assertNull($this->resolve($value), "local driver, value=" . var_export($value, true));

        $this->useS3Driver();
        $this->assertNull($this->resolve($value), "s3 driver, value=" . var_export($value, true));
    }

    /**
     * @return array<string, array{0:string}>
     */
    public function provideUnsafeValues(): array
    {
        return [
            'empty'              => [''],
            'whitespace_only'    => ["   \t\n "],
            'single_dot'         => ['.'],
            'double_dot'         => ['..'],
            'null_byte'          => ["null\0byte.png"],
            'parent_segment'     => ['../../etc/passwd'],
            'embedded_parent'    => ['foo/../bar.jpg'],
            'basename_is_dotdot' => ['some/dir/..'],
        ];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Invoke the private CobranzaDashboardService::resolveDocumentStatusUrl().
     */
    private function resolve(string $fileName): ?string
    {
        $service = new CobranzaDashboardService(\Config\Database::connect());
        $method  = new ReflectionMethod($service, 'resolveDocumentStatusUrl');
        $method->setAccessible(true);

        return $method->invoke($service, $fileName);
    }

    /**
     * Switch to the local driver and rebuild the real (AWS-free) storage service.
     */
    private function useLocalDriver(): void
    {
        config('FileStorage')->driver = 'local';
        Services::reset(true);
        // Real FileStorageService -> LocalFileStorage; base_url() paths only.
    }

    /**
     * Switch to the s3 driver and inject a fake presigned URL provider so no
     * live AWS call is ever made.
     */
    private function useS3Driver(): void
    {
        config('FileStorage')->driver = 's3';
        Services::reset(true);

        $fake = new class {
            public function url(string $key, int $ttl = 300): string
            {
                if ($key === '') {
                    return '';
                }

                return 'https://bucket/' . $key
                    . '?X-Amz-Expires=' . $ttl
                    . '&X-Amz-Signature=' . substr(hash('sha256', $key . '|' . $ttl), 0, 32);
            }
        };

        Services::injectMock('fileStorage', $fake);
    }

    /**
     * Create a real file on the local disk under the given upload category so
     * the local candidate `is_file()` gate finds it. Tracked for cleanup.
     */
    private function createLocalDoc(string $category, string $name): void
    {
        $dir = FCPATH . 'assets/uploads/' . $category;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $path = $dir . '/' . $name;
        file_put_contents($path, 'test-content');
        $this->createdFiles[] = $path;
    }
}
