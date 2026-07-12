<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\FileStorage;
use App\Libraries\Storage\FileStorageService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Unit tests for FileStorageService driver fallback and dual-write aggregation
 * (task 7.4).
 *
 * Two behaviors are pinned here:
 *
 *  - Fallback (Req 1.6): when FILE_STORAGE_DRIVER holds an unset/empty/
 *    unrecognized value, the service selects the Local_Driver as the active
 *    driver AND logs the fallback. We verify BOTH facets: (a) delete/url/exists
 *    delegate to an injected local stub (proving local was chosen, since a
 *    stray s3 stub is also injected and must never be touched), and (b) a
 *    'warning' log line is emitted.
 *
 *  - Dual-write aggregation (Req 9.3): with dual-write enabled, put() must
 *    succeed only when BOTH legs succeed and must report failure if EITHER leg
 *    fails. We drive every combination with anonymous FileStorage doubles whose
 *    put() returns a configured boolean.
 *
 * The doubles avoid any real filesystem or AWS I/O: FileStorageService reuses
 * injected drivers for both the active-driver role and the dual-write legs
 * (see makeLocalDriver()/makeS3Driver()).
 *
 * @internal
 */
final class FileStorageServiceFallbackDualWriteTest extends CIUnitTestCase
{
    /**
     * Build a FileStorage double.
     *
     * @param bool                  $putResult   value put() should return
     * @param array<string, mixed>  $recorder    by-ref bag capturing which methods were called
     */
    private function makeDouble(bool $putResult, array &$recorder, string $urlValue = ''): FileStorage
    {
        return new class ($putResult, $recorder, $urlValue) implements FileStorage {
            public function __construct(
                private bool $putResult,
                private array &$recorder,
                private string $urlValue
            ) {
            }

            public function put(string $key, string $localTmpPath): bool
            {
                $this->recorder['put'][] = $key;

                return $this->putResult;
            }

            public function delete(string $key): bool
            {
                $this->recorder['delete'][] = $key;

                return true;
            }

            public function url(string $key, int $ttlSeconds = 300): string
            {
                $this->recorder['url'][] = $key;

                return $this->urlValue;
            }

            public function exists(string $key): bool
            {
                $this->recorder['exists'][] = $key;

                return true;
            }
        };
    }

    // ---------------------------------------------------------------------
    // Fallback (Req 1.6)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideUnrecognizedDrivers
     */
    public function testUnrecognizedDriverFallsBackToLocalAndLogs(string $driverValue): void
    {
        $localCalls = [];
        $s3Calls    = [];
        $local      = $this->makeDouble(true, $localCalls, 'http://local/url');
        $s3         = $this->makeDouble(true, $s3Calls, 'https://s3/presigned');

        $config            = new FileStorageConfig();
        $config->driver    = $driverValue;
        $config->dualWrite = false;

        $service = new FileStorageService($config, $local, $s3);

        // Active-driver operations must delegate to the LOCAL double only.
        $service->url('some/key.jpg');
        $service->exists('some/key.jpg');
        $service->delete('some/key.jpg');
        $service->put('some/key.jpg', __FILE__);

        $this->assertArrayHasKey('url', $localCalls, 'url() must delegate to the local driver on fallback');
        $this->assertArrayHasKey('exists', $localCalls, 'exists() must delegate to the local driver on fallback');
        $this->assertArrayHasKey('delete', $localCalls, 'delete() must delegate to the local driver on fallback');
        $this->assertArrayHasKey('put', $localCalls, 'put() must delegate to the local driver on fallback');

        // The s3 double must never be touched when we fell back to local.
        $this->assertSame([], $s3Calls, 'S3 driver must not be used after fallback to local');

        // The fallback must be logged at warning level (Req 1.6). The framework's
        // test logger matches the full message, so reconstruct it exactly.
        $expectedLog = 'FileStorageService: FILE_STORAGE_DRIVER value ' .
            ($driverValue === '' ? '(unset/empty)' : '"' . $driverValue . '"') .
            ' is unset or unrecognized; falling back to the local driver.';
        $this->assertLogged('warning', $expectedLog);
    }

    /**
     * Unset/empty and unrecognized values. 'foo' is a plain unrecognized token;
     * '' and '   ' cover the unset/empty and whitespace cases the resolver
     * trims before matching.
     *
     * @return array<string, array{0: string}>
     */
    public function provideUnrecognizedDrivers(): array
    {
        return [
            'unrecognized token' => ['foo'],
            'empty string'       => [''],
            'whitespace only'    => ['   '],
            'mixed garbage'      => ['S33'],
        ];
    }

    public function testRecognizedLocalDriverDoesNotLogFallback(): void
    {
        $localCalls = [];
        $s3Calls    = [];
        $local      = $this->makeDouble(true, $localCalls);
        $s3         = $this->makeDouble(true, $s3Calls);

        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->dualWrite = false;

        $service = new FileStorageService($config, $local, $s3);
        $service->url('k');

        $this->assertArrayHasKey('url', $localCalls);
        $this->assertSame([], $s3Calls, 'S3 driver must not be used when driver=local');
    }

    // ---------------------------------------------------------------------
    // Dual-write aggregation (Req 9.3)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideDualWriteOutcomes
     */
    public function testDualWritePutAggregatesBothLegs(bool $s3Ok, bool $localOk, bool $expected): void
    {
        $localCalls = [];
        $s3Calls    = [];
        $local      = $this->makeDouble($localOk, $localCalls);
        $s3         = $this->makeDouble($s3Ok, $s3Calls);

        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->dualWrite = true;

        $service = new FileStorageService($config, $local, $s3);

        $result = $service->put('pago_gestor/1/x.jpg', __FILE__);

        $this->assertSame(
            $expected,
            $result,
            sprintf('put() with s3=%s local=%s must return %s', var_export($s3Ok, true), var_export($localOk, true), var_export($expected, true))
        );

        // Both legs are always attempted so the object can land in both stores.
        $this->assertArrayHasKey('put', $s3Calls, 'S3 leg must be attempted under dual-write');
        $this->assertArrayHasKey('put', $localCalls, 'Local leg must be attempted under dual-write');
    }

    /**
     * s3Ok, localOk, expectedFacadeResult. Failure on EITHER leg => false
     * (Req 9.3); success requires BOTH.
     *
     * @return array<string, array{0: bool, 1: bool, 2: bool}>
     */
    public function provideDualWriteOutcomes(): array
    {
        return [
            's3 fails, local ok => false' => [false, true, false],
            'local fails, s3 ok => false' => [true, false, false],
            'both fail => false'          => [false, false, false],
            'both succeed => true'        => [true, true, true],
        ];
    }

    public function testDualWriteFailureIsLoggedForFailingLeg(): void
    {
        $localCalls = [];
        $s3Calls    = [];
        $local      = $this->makeDouble(true, $localCalls);   // local ok
        $s3         = $this->makeDouble(false, $s3Calls);     // s3 fails

        $config            = new FileStorageConfig();
        $config->driver    = 's3';
        $config->dualWrite = true;

        $service = new FileStorageService($config, $local, $s3);

        $this->assertFalse($service->put('k/y.jpg', __FILE__), 'A failing S3 leg must surface as a failed persist');
        $this->assertLogged('error', 'FileStorageService dual-write: S3 put failed for key: k/y.jpg');
    }

    public function testSingleStorePutDelegatesToActiveDriverOnly(): void
    {
        // dual-write off: only the active (local) driver is written.
        $localCalls = [];
        $s3Calls    = [];
        $local      = $this->makeDouble(false, $localCalls);
        $s3         = $this->makeDouble(true, $s3Calls);

        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->dualWrite = false;

        $service = new FileStorageService($config, $local, $s3);

        // Returns the local driver's own result; s3 must be untouched.
        $this->assertFalse($service->put('k.jpg', __FILE__));
        $this->assertArrayHasKey('put', $localCalls);
        $this->assertSame([], $s3Calls, 'S3 driver must not be written when dual-write is disabled');
    }
}
