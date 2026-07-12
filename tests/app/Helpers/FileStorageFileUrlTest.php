<?php

namespace Tests\App\Helpers;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for the file_url() helper (task 3.5).
 *
 * Covers:
 *  - Empty / null-ish / whitespace-only short-circuit to '' WITHOUT invoking
 *    the normalizer or the storage service (Req 4.7 / 5.5-adjacent).
 *  - Delegation to service('fileStorage')->url(key, ttl) with the
 *    normalizer-recovered key for bare filenames, relative keys and absolute
 *    URLs (Req 4.1, 4.2, 5.3).
 *  - A legacy value that cannot be resolved returns '' without an unhandled
 *    error (Req 5.5).
 *
 * @internal
 */
final class FileStorageFileUrlTest extends CIUnitTestCase
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
     * Register a recording test double as the `fileStorage` service.
     *
     * @return object exposing ->calls (array of [key, ttl]) and a url() method.
     */
    private function injectRecordingStorage(bool $throw = false): object
    {
        $fake = new class ($throw) {
            public array $calls = [];
            private bool $throw;

            public function __construct(bool $throw)
            {
                $this->throw = $throw;
            }

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];
                if ($this->throw) {
                    throw new \RuntimeException('cannot resolve');
                }

                return 'https://cdn.example/' . $key . '?ttl=' . $ttl;
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * A service that must never be touched; any call fails the test.
     */
    private function injectExplodingStorage(): void
    {
        $fake = new class {
            public function url(string $key, int $ttl = 300): string
            {
                throw new \LogicException('storage service must not be invoked for empty values');
            }
        };

        Services::injectMock('fileStorage', $fake);
    }

    public function testEmptyStringReturnsEmptyWithoutTouchingService(): void
    {
        $this->injectExplodingStorage();
        $this->assertSame('', file_url('', 'documentostatus'));
    }

    public function testWhitespaceOnlyReturnsEmptyWithoutTouchingService(): void
    {
        $this->injectExplodingStorage();
        $this->assertSame('', file_url("   \t\n ", 'pago_gestor', 12472));
    }

    public function testBareFilenameDelegatesWithRebuiltPerIdKey(): void
    {
        $fake = $this->injectRecordingStorage();

        $url = file_url('comprobante_ab12.jpg', 'pago_gestor', 12472);

        $this->assertSame('https://cdn.example/pago_gestor/12472/comprobante_ab12.jpg?ttl=300', $url);
        $this->assertSame([['pago_gestor/12472/comprobante_ab12.jpg', 300]], $fake->calls);
    }

    public function testBareFilenameWithoutIdDelegatesWithCategoryKey(): void
    {
        $fake = $this->injectRecordingStorage();

        $url = file_url('documento_5001.pdf', 'documentostatus');

        $this->assertSame('https://cdn.example/documentostatus/documento_5001.pdf?ttl=300', $url);
        $this->assertSame([['documentostatus/documento_5001.pdf', 300]], $fake->calls);
    }

    public function testRelativeKeyUsedAsIsAndCustomTtlForwarded(): void
    {
        $fake = $this->injectRecordingStorage();

        $url = file_url('pago_gestor/12472/file.jpg', 'pago_gestor', 12472, 900);

        $this->assertSame('https://cdn.example/pago_gestor/12472/file.jpg?ttl=900', $url);
        $this->assertSame([['pago_gestor/12472/file.jpg', 900]], $fake->calls);
    }

    public function testAbsoluteUrlWithUploadsPrefixIsNormalizedThenDelegated(): void
    {
        $fake = $this->injectRecordingStorage();

        $url = file_url('https://old.host/assets/uploads/pago_gestor/12472/file.jpg', 'pago_gestor', 12472);

        $this->assertSame('https://cdn.example/pago_gestor/12472/file.jpg?ttl=300', $url);
        $this->assertSame([['pago_gestor/12472/file.jpg', 300]], $fake->calls);
    }

    public function testUnresolvableLegacyValueReturnsEmptyWithoutUnhandledError(): void
    {
        // Storage throws when asked to resolve; helper must degrade to ''.
        $this->injectRecordingStorage(true);

        $this->assertSame('', file_url('legacy_broken.jpg', 'documentostatus'));
    }

    public function testMissingServiceReturnsEmptyWithoutUnhandledError(): void
    {
        // No fileStorage service registered (task 7.1 not yet implemented):
        // service() resolution failure must be caught and degrade to ''.
        $this->assertSame('', file_url('some_file.jpg', 'documentostatus'));
    }
}
