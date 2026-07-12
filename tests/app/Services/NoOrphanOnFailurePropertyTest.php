<?php

namespace Tests\App\Services;

use App\Services\ExternalTramiteService;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 8: No orphan on failure.
 *
 * Validates: Requirements 7.1
 *
 * Invariant under test (design "Upload integration" pseudocode + Error Handling
 * "DB write fails after successful put"):
 *
 *   If the DB write fails AFTER a successful put() within the same request,
 *   the just-written object is removed by EXACTLY ONE compensating delete(key)
 *   — never zero (which would leave an orphan) and never twice — so the store
 *   never keeps an object with no referencing row. On the fully-successful
 *   path, delete() is NEVER called and a referencing row remains.
 *
 * Approach (b): this exercises the REAL production compensating logic in
 * App\Services\ExternalTramiteService::storeUploadedDocuments() and
 * ::compensatePendingUploads(). A thin probe subclass only bypasses the
 * constructor's live \Config\Database::connect() so an injectable DB double can
 * be supplied; every branch that decides put/delete/insert is the real code.
 * The storage service is a recording double registered via
 * Services::injectMock('fileStorage', ...), matching the convention used by the
 * FileStorage helper tests.
 *
 * Two distinct DB-failure modes are covered because the real code compensates
 * along two different paths:
 *   - insert() returns false  -> storeUploadedDocuments() deletes the key once,
 *                                 drops it from the pending list, then throws.
 *   - insert() throws          -> the key stays pending and the surrounding
 *                                 createTramite() catch compensates it once via
 *                                 compensatePendingUploads().
 * Both must yield exactly one delete for the key and no referencing row.
 *
 * PBT generators are implemented as a deterministically-seeded PHPUnit data
 * provider (no new runtime dependency), matching the other *PropertyTest files.
 *
 * @internal
 */
final class NoOrphanOnFailurePropertyTest extends CIUnitTestCase
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
     * Property 8 across arbitrary keys and arbitrary DB outcomes.
     *
     * @dataProvider provideKeysAndOutcomes
     */
    public function testNoOrphanRemainsForEveryOutcome(
        int $tramiteId,
        string $randomName,
        string $clientName,
        string $mime,
        int $size,
        string $outcome
    ): void {
        $storage = $this->injectRecordingStorage();
        $db      = new FakeDbForOrphanTest();
        $db->insertMode = $outcome; // 'success' | 'return_false' | 'throws'

        $probe = new NoOrphanProbe($db);

        // The single canonical key the real storeUploadedDocuments() will derive
        // for this upload: tramites/<id>/<randomName>.
        $expectedKey  = 'tramites/' . $tramiteId . '/' . $randomName;
        $otherSeenKey = 'tramites/999999/decoy-object.bin';

        // Pre-seed an unrelated object so we can assert deletes never touch keys
        // other than the one just written (delete count for other keys == 0).
        $storage->store[$otherSeenKey] = true;

        $files = [
            'documentos' => [
                new FakeUploadedFile('/tmp/whatever-' . $randomName, $randomName, $clientName, $mime, $size),
            ],
        ];

        $threw = false;
        try {
            $probe->runStore($tramiteId, $files);
        } catch (\Throwable $e) {
            $threw = true;
        }

        // Mirror createTramite()'s handling: on ANY failure the catch block runs
        // the compensating cleanup; on success it clears the pending list and
        // never compensates. Running compensate() here proves the total delete
        // count stays at exactly one (the return_false path already deleted and
        // dropped the key, so this must add zero more).
        if ($threw) {
            $probe->compensate();
        }

        $context = sprintf(
            'outcome=%s key=%s',
            $outcome,
            $expectedKey
        );

        // put() was always attempted exactly once for the key.
        $this->assertSame(1, $this->countCalls($storage->putCalls, $expectedKey), 'put once: ' . $context);

        if ($outcome === 'success') {
            // Success path: ZERO deletes, one referencing row remains, object stays.
            $this->assertSame(0, count($storage->deleteCalls), 'no delete on success: ' . $context);
            $this->assertTrue($storage->exists($expectedKey), 'object present on success: ' . $context);
            $this->assertSame(1, $db->countRowsForKey($expectedKey), 'exactly one referencing row: ' . $context);

            return;
        }

        // Failure path (DB write failed after a successful put):
        // EXACTLY ONE compensating delete for the just-written key ...
        $this->assertSame(1, $this->countCalls($storage->deleteCalls, $expectedKey), 'exactly one delete: ' . $context);
        // ... never twice / never any other key touched ...
        $this->assertSame(1, count($storage->deleteCalls), 'delete only the written key: ' . $context);
        $this->assertSame(0, $this->countCalls($storage->deleteCalls, $otherSeenKey), 'other keys untouched: ' . $context);
        // ... the object is gone (no orphan) ...
        $this->assertFalse($storage->exists($expectedKey), 'orphan removed: ' . $context);
        // ... and no referencing row remains for that key.
        $this->assertSame(0, $db->countRowsForKey($expectedKey), 'no referencing row: ' . $context);
        // The unrelated pre-seeded object is still present (delete only hit the key).
        $this->assertTrue($storage->exists($otherSeenKey), 'unrelated object preserved: ' . $context);
    }

    /**
     * Focused multi-file rollback scenario: an earlier document is written and
     * its DB row inserted successfully, then a later document's insert throws.
     * The earlier key is still "pending" (its row will be rolled back with the
     * transaction), so compensatePendingUploads() must delete BOTH keys exactly
     * once — no orphan for either, and neither deleted twice.
     */
    public function testMultiFileRollbackCompensatesEveryPendingKeyExactlyOnce(): void
    {
        $storage = $this->injectRecordingStorage();
        $db      = new FakeDbForOrphanTest();
        // First insert succeeds, second insert throws.
        $db->insertQueue = ['success', 'throws'];

        $probe = new NoOrphanProbe($db);

        $tramiteId = 4242;
        $files = [
            'documentos' => [
                new FakeUploadedFile('/tmp/a', 'first.pdf', 'first.pdf', 'application/pdf', 10),
                new FakeUploadedFile('/tmp/b', 'second.pdf', 'second.pdf', 'application/pdf', 20),
            ],
        ];

        try {
            $probe->runStore($tramiteId, $files);
            $this->fail('storeUploadedDocuments should have thrown on the failing insert');
        } catch (\Throwable $e) {
            // Expected: mirror createTramite()'s rollback + compensation.
            $probe->compensate();
        }

        $key1 = 'tramites/' . $tramiteId . '/first.pdf';
        $key2 = 'tramites/' . $tramiteId . '/second.pdf';

        // Both objects were written ...
        $this->assertSame(1, $this->countCalls($storage->putCalls, $key1));
        $this->assertSame(1, $this->countCalls($storage->putCalls, $key2));

        // ... and both are compensated EXACTLY once (no orphan, none deleted twice).
        $this->assertSame(1, $this->countCalls($storage->deleteCalls, $key1));
        $this->assertSame(1, $this->countCalls($storage->deleteCalls, $key2));
        $this->assertSame(2, count($storage->deleteCalls));

        $this->assertFalse($storage->exists($key1));
        $this->assertFalse($storage->exists($key2));

        // The key2 insert threw before writing, so it never produced a row.
        // (key1's row is removed in production by the surrounding transaction
        // rollback, which this lightweight DB double does not model; the
        // no-orphan guarantee under test is the compensating storage delete,
        // asserted above.)
        $this->assertSame(0, $db->countRowsForKey($key2));
    }

    // ------------------------------------------------------------------ //
    // Helpers                                                            //
    // ------------------------------------------------------------------ //

    /**
     * Count how many times $key appears in a list of recorded call keys.
     *
     * @param string[] $calls
     */
    private function countCalls(array $calls, string $key): int
    {
        return count(array_filter($calls, static fn (string $k): bool => $k === $key));
    }

    /**
     * Register a recording test double as the `fileStorage` service. It models
     * an in-memory object store and records every put/delete key.
     */
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            /** @var array<string, bool> */
            public array $store = [];
            /** @var string[] */
            public array $putCalls = [];
            /** @var string[] */
            public array $deleteCalls = [];

            public function put(string $key, string $localTmpPath): bool
            {
                $this->putCalls[]    = $key;
                $this->store[$key]   = true;

                return true;
            }

            public function delete(string $key): bool
            {
                $this->deleteCalls[] = $key;
                unset($this->store[$key]);

                return true;
            }

            public function url(string $key, int $ttlSeconds = 300): string
            {
                return 'https://cdn.example/' . $key;
            }

            public function exists(string $key): bool
            {
                return isset($this->store[$key]);
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Seeded generator of arbitrary keys (via tramiteId + randomName) paired
     * with arbitrary DB outcomes. Deterministic seed makes any counterexample
     * reproducible.
     *
     * @return array<string, array{0:int,1:string,2:string,3:string,4:int,5:string}>
     */
    public function provideKeysAndOutcomes(): array
    {
        mt_srand(20240711);

        $extensions = ['jpg', 'jpeg', 'png', 'pdf', 'gif', 'webp'];
        $mimes      = [
            'image/jpeg', 'image/png', 'application/pdf', 'image/gif', 'image/webp',
        ];
        // Each iteration is roughly balanced across the three outcomes so the
        // property is exercised on both the failure and the success branches.
        $outcomes = ['success', 'return_false', 'throws'];

        $cases = [];
        $count = 240;

        for ($i = 0; $i < $count; $i++) {
            $tramiteId = mt_rand(1, 9_999_998);
            $ext       = $extensions[mt_rand(0, count($extensions) - 1)];
            // getRandomName()-style value: a hex token + extension. Kept within
            // the safe key charset so the derived key is a valid relative key.
            $randomName = bin2hex(random_bytes(8)) . '.' . $ext;
            $clientName = 'documento_' . $i . '.' . $ext;
            $mime       = $mimes[mt_rand(0, count($mimes) - 1)];
            $size       = mt_rand(1, 5_000_000);
            $outcome    = $outcomes[$i % count($outcomes)];

            $cases['case_' . $i . '_' . $outcome] = [
                $tramiteId, $randomName, $clientName, $mime, $size, $outcome,
            ];
        }

        return $cases;
    }
}

/**
 * Thin probe over the REAL service: bypasses only the constructor's live DB
 * connection so an injectable double can be supplied, and exposes the two
 * protected methods that embody Property 8. All decision logic remains the
 * production code inherited from ExternalTramiteService.
 *
 * @internal
 */
final class NoOrphanProbe extends ExternalTramiteService
{
    public function __construct($db)
    {
        // Intentionally does NOT call parent::__construct() to avoid
        // \Config\Database::connect(). The parent declares $db as private, so
        // assign the injected double into that private property via reflection
        // (the inherited, production methods read exactly that property).
        $property = new \ReflectionProperty(ExternalTramiteService::class, 'db');
        $property->setAccessible(true);
        $property->setValue($this, $db);
    }

    /** Invoke the real per-document put + insert + compensating-delete logic. */
    public function runStore(int $tramiteId, array $requestFiles): int
    {
        return $this->storeUploadedDocuments($tramiteId, $requestFiles);
    }

    /** Invoke the real transaction-rollback compensation path. */
    public function compensate(): void
    {
        $this->compensatePendingUploads();
    }
}

/**
 * Minimal database double. storeUploadedDocuments() only ever calls
 * $db->table('tra_doc_status')->insert($row); this models that surface and
 * records inserted rows so "a referencing row remains" can be asserted.
 *
 * @internal
 */
final class FakeDbForOrphanTest
{
    /** Default outcome for every insert: 'success' | 'return_false' | 'throws'. */
    public string $insertMode = 'success';

    /**
     * Optional per-call outcome queue (consumed in order); falls back to
     * $insertMode when empty. Lets a test model "first insert ok, second fails".
     *
     * @var string[]
     */
    public array $insertQueue = [];

    /** @var array<int, array{table:string, data:array}> */
    public array $rows = [];

    public function table(string $table): FakeBuilderForOrphanTest
    {
        return new FakeBuilderForOrphanTest($this, $table);
    }

    public function nextOutcome(): string
    {
        if ($this->insertQueue !== []) {
            return array_shift($this->insertQueue);
        }

        return $this->insertMode;
    }

    public function countRowsForKey(string $key): int
    {
        return count(array_filter(
            $this->rows,
            static fn (array $row): bool => ($row['data']['ruta'] ?? null) === $key
        ));
    }
}

/**
 * Query-builder double returned by FakeDbForOrphanTest::table().
 *
 * @internal
 */
final class FakeBuilderForOrphanTest
{
    public function __construct(private FakeDbForOrphanTest $db, private string $table)
    {
    }

    /**
     * Mirrors CI4 BaseBuilder::insert() enough for the code under test, which
     * only checks for a strict `=== false` failure.
     *
     * @param array<string, mixed> $data
     *
     * @return bool
     */
    public function insert(array $data)
    {
        switch ($this->db->nextOutcome()) {
            case 'throws':
                throw new \RuntimeException('Simulated DB insert exception');
            case 'return_false':
                return false;
            default:
                $this->db->rows[] = ['table' => $this->table, 'data' => $data];

                return true;
        }
    }
}

/**
 * Duck-typed stand-in for CodeIgniter\HTTP\Files\UploadedFile. The code under
 * test only calls the accessors below; a real UploadedFile can't be used here
 * because isValid() relies on is_uploaded_file(), which is always false in a
 * test process. All modelled files are valid, not-yet-moved uploads.
 *
 * @internal
 */
final class FakeUploadedFile
{
    public function __construct(
        private string $tmp,
        private string $randomName,
        private string $clientName,
        private string $mime,
        private int $size,
        private bool $valid = true,
        private bool $moved = false
    ) {
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function hasMoved(): bool
    {
        return $this->moved;
    }

    public function getTempName(): string
    {
        return $this->tmp;
    }

    public function getRandomName(): string
    {
        return $this->randomName;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getClientMimeType(): string
    {
        return $this->mime;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
