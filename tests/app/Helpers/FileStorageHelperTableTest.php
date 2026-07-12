<?php

namespace Tests\App\Helpers;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Table-driven unit tests for keyFromStored() and file_url() (task 3.6).
 *
 * These tests exercise the legacy normalizer and the URL helper across the
 * full matrix of historical stored-value shapes using PHPUnit data providers:
 *  - bare filename (flat + per-id categories)
 *  - relative key (already canonical, with/without a leading '/')
 *  - absolute URL WITH the /assets/uploads/ prefix
 *  - absolute URL WITHOUT the /assets/uploads/ prefix
 *  - empty / whitespace-only input
 *
 * keyFromStored() cases assert the recovered canonical key directly.
 * file_url() cases inject a recording fileStorage double (Services::injectMock)
 * and assert the helper delegates with the normalizer-recovered key + ttl.
 *
 * _Requirements: 4.1, 4.7, 5.5 (plus 4.3, 4.4, 4.5, 4.6, 4.8, 4.9 exercised by the table)._
 *
 * @internal
 */
final class FileStorageHelperTableTest extends CIUnitTestCase
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
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            public array $calls = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];

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
                throw new \LogicException('storage service must not be invoked for empty/whitespace values');
            }
        };

        Services::injectMock('fileStorage', $fake);
    }

    // ---------------------------------------------------------------------
    // keyFromStored() — table-driven normalization
    // ---------------------------------------------------------------------

    /**
     * @return array<string, array{0:string,1:string,2:int|null,3:string}>
     *               [storedValue, category, id, expectedKey]
     */
    public static function keyFromStoredProvider(): array
    {
        return [
            // --- bare filename, flat categories ---------------------------
            'bare filename / documentostatus (flat)' => [
                'documento_5001.pdf', 'documentostatus', null, 'documentostatus/documento_5001.pdf',
            ],
            'bare filename / evidencias (flat)' => [
                'foto.jpg', 'evidencias', null, 'evidencias/foto.jpg',
            ],
            'bare filename / avatars (flat)' => [
                'avatar_9.png', 'avatars', null, 'avatars/avatar_9.png',
            ],
            'bare filename / tramites (flat)' => [
                'acta.pdf', 'tramites', null, 'tramites/acta.pdf',
            ],

            // --- bare filename, per-id categories -------------------------
            'bare filename / pago_gestor (per-id)' => [
                'comprobante.jpg', 'pago_gestor', 12472, 'pago_gestor/12472/comprobante.jpg',
            ],
            'bare filename / pago_derechos (per-id)' => [
                'recibo.pdf', 'pago_derechos', 88, 'pago_derechos/88/recibo.pdf',
            ],
            'bare filename / cobro_cliente (per-id)' => [
                'cobro.png', 'cobro_cliente', 5, 'cobro_cliente/5/cobro.png',
            ],

            // --- relative key already (contains '/', no scheme) -----------
            'relative key / per-id used as-is' => [
                'pago_gestor/12472/file.jpg', 'pago_gestor', 12472, 'pago_gestor/12472/file.jpg',
            ],
            'relative key / flat used as-is' => [
                'documentostatus/documento_5001.pdf', 'documentostatus', null, 'documentostatus/documento_5001.pdf',
            ],
            'relative key / leading slash stripped' => [
                '/pago_gestor/12472/file.jpg', 'pago_gestor', 12472, 'pago_gestor/12472/file.jpg',
            ],

            // --- absolute URL WITH /assets/uploads/ prefix ----------------
            'absolute URL with prefix / per-id' => [
                'https://old.host/assets/uploads/pago_gestor/12472/file.jpg', 'pago_gestor', 12472,
                'pago_gestor/12472/file.jpg',
            ],
            'absolute URL with prefix / flat' => [
                'http://old.host/assets/uploads/documentostatus/doc.pdf', 'documentostatus', null,
                'documentostatus/doc.pdf',
            ],
            'absolute URL with prefix / subpath origin' => [
                'https://old.host/app/assets/uploads/evidencias/e.jpg', 'evidencias', null,
                'evidencias/e.jpg',
            ],

            // --- absolute URL WITHOUT /assets/uploads/ prefix -------------
            'absolute URL without prefix / origin stripped' => [
                'https://old.host/pago_gestor/12472/file.jpg', 'pago_gestor', 12472,
                'pago_gestor/12472/file.jpg',
            ],
            'absolute URL without prefix / flat origin stripped' => [
                'http://old.host/documentostatus/doc.pdf', 'documentostatus', null,
                'documentostatus/doc.pdf',
            ],

            // --- empty / whitespace-only ----------------------------------
            'empty string' => [
                '', 'documentostatus', null, '',
            ],
            'whitespace only' => [
                "   \t\n ", 'pago_gestor', 12472, '',
            ],
        ];
    }

    /**
     * @dataProvider keyFromStoredProvider
     */
    public function testKeyFromStoredNormalizesEachShape(
        string $storedValue,
        string $category,
        ?int $id,
        string $expectedKey
    ): void {
        $this->assertSame($expectedKey, keyFromStored($storedValue, $category, $id));
    }

    /**
     * keyFromStored() must be a fixed point over its own canonical output:
     * re-applying it to a recovered key returns that same key unchanged
     * (Req 4.6).
     *
     * @dataProvider keyFromStoredProvider
     */
    public function testKeyFromStoredIsFixedPointOverItsOutput(
        string $storedValue,
        string $category,
        ?int $id,
        string $expectedKey
    ): void {
        $once  = keyFromStored($storedValue, $category, $id);
        $twice = keyFromStored($once, $category, $id);

        $this->assertSame($once, $twice);
    }

    // ---------------------------------------------------------------------
    // file_url() — table-driven delegation
    // ---------------------------------------------------------------------

    /**
     * Non-empty cases: file_url must normalize then delegate to the service
     * with the recovered key and forwarded ttl.
     *
     * @return array<string, array{0:string,1:string,2:int|null,3:int,4:string}>
     *               [storedValue, category, id, ttl, expectedKey]
     */
    public static function fileUrlDelegationProvider(): array
    {
        return [
            'bare filename / flat / default ttl' => [
                'documento_5001.pdf', 'documentostatus', null, 300, 'documentostatus/documento_5001.pdf',
            ],
            'bare filename / per-id / default ttl' => [
                'comprobante.jpg', 'pago_gestor', 12472, 300, 'pago_gestor/12472/comprobante.jpg',
            ],
            'bare filename / per-id / custom ttl' => [
                'recibo.pdf', 'pago_derechos', 88, 900, 'pago_derechos/88/recibo.pdf',
            ],
            'relative key / used as-is' => [
                'cobro_cliente/5/cobro.png', 'cobro_cliente', 5, 300, 'cobro_cliente/5/cobro.png',
            ],
            'absolute URL with prefix / normalized' => [
                'https://old.host/assets/uploads/pago_gestor/12472/file.jpg', 'pago_gestor', 12472, 300,
                'pago_gestor/12472/file.jpg',
            ],
            'absolute URL without prefix / origin stripped' => [
                'http://old.host/documentostatus/doc.pdf', 'documentostatus', null, 300,
                'documentostatus/doc.pdf',
            ],
        ];
    }

    /**
     * @dataProvider fileUrlDelegationProvider
     */
    public function testFileUrlNormalizesThenDelegates(
        string $storedValue,
        string $category,
        ?int $id,
        int $ttl,
        string $expectedKey
    ): void {
        $fake = $this->injectRecordingStorage();

        $url = file_url($storedValue, $category, $id, $ttl);

        $this->assertSame('https://cdn.example/' . $expectedKey . '?ttl=' . $ttl, $url);
        $this->assertSame([[$expectedKey, $ttl]], $fake->calls);
    }

    /**
     * Empty / whitespace-only stored values short-circuit to '' WITHOUT
     * invoking the normalizer or the storage service (Req 4.1, 4.7, 5.5).
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     *               [storedValue, category, id]
     */
    public static function fileUrlEmptyProvider(): array
    {
        return [
            'empty string / flat'          => ['', 'documentostatus', null],
            'empty string / per-id'        => ['', 'pago_gestor', 12472],
            'spaces only'                  => ['     ', 'documentostatus', null],
            'tabs and newlines only'       => ["\t\n\r ", 'pago_gestor', 12472],
        ];
    }

    /**
     * @dataProvider fileUrlEmptyProvider
     */
    public function testFileUrlEmptyOrWhitespaceShortCircuits(
        string $storedValue,
        string $category,
        ?int $id
    ): void {
        $this->injectExplodingStorage();

        $this->assertSame('', file_url($storedValue, $category, $id));
    }
}
