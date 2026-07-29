<?php

namespace Tests\App\Helpers;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 5: Map cardinality and fidelity.
 *
 * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6
 *
 * For file_url_map(vals, category, id, ttl):
 *   - every distinct non-empty (trimmed) value appears as a key mapped to
 *     file_url(v, category, id, ttl)                              (Req 5.1, 5.4)
 *   - empty / whitespace-only values are excluded                 (Req 5.2)
 *   - duplicate values collapse to exactly one entry              (Req 5.3)
 *   - map cardinality == number of distinct non-empty values      (Req 5.1, 5.3)
 *   - an empty input list yields an empty map                     (Req 5.5)
 *   - a non-empty value whose resolver output is '' is RETAINED as
 *     a key mapped to '' rather than excluded                     (Req 5.6)
 *
 * PBT generators are implemented as a seeded PHPUnit data provider (no new
 * runtime dependency; giorgiosironi/eris is not installed). The fileStorage
 * service is a RECORDING double injected via Services::injectMock, matching
 * the convention of the sibling s3-file-storage helper tests. No live AWS
 * calls are made.
 *
 * @internal
 */
final class FileUrlMapPropertyTest extends CIUnitTestCase
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
     * The double records every url(key, ttl) invocation and returns a
     * deterministic URL. Keys containing the sentinel substring "__empty__"
     * resolve to '' so we can exercise Req 5.6 (retain a key mapped to '').
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

                // Simulate an unresolvable-but-non-empty legacy value.
                if (strpos($key, '__empty__') !== false) {
                    return '';
                }

                return 'https://cdn.example/' . $key . '?ttl=' . $ttl;
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Property 5: file_url_map is faithful and has correct cardinality.
     *
     * @dataProvider provideStoredValueLists
     *
     * @param string[] $storedValues
     */
    public function testMapCardinalityAndFidelity(
        array $storedValues,
        string $category,
        ?int $id,
        int $ttl
    ): void {
        $this->injectRecordingStorage();

        $map = file_url_map($storedValues, $category, $id, $ttl);

        // --- Compute the expected distinct set (trim, drop empties, collapse). ---
        $expectedKeys = [];
        foreach ($storedValues as $value) {
            $name = trim((string) $value);
            if ($name === '') {
                continue; // Req 5.2: empty / whitespace-only excluded.
            }
            // Preserve first-seen order; collapse duplicates (Req 5.3).
            if (!in_array($name, $expectedKeys, true)) {
                $expectedKeys[] = $name;
            }
        }

        $message = 'storedValues=' . var_export($storedValues, true)
            . " category={$category} id=" . var_export($id, true) . " ttl={$ttl}";

        // Req 5.1 / 5.3: cardinality equals the distinct non-empty count.
        $this->assertCount(count($expectedKeys), $map, $message);

        // Req 5.5: empty input list yields an empty map (covered by count==0 case).
        if ($expectedKeys === []) {
            $this->assertSame([], $map, $message);
        }

        // Every distinct non-empty value is present exactly once as a key.
        $this->assertSame($expectedKeys, array_keys($map), $message);

        foreach ($expectedKeys as $name) {
            // Req 5.1 / 5.4: mapped value equals file_url(name, category, id, ttl).
            $this->assertArrayHasKey($name, $map, $message);
            $this->assertSame(file_url($name, $category, $id, $ttl), $map[$name], $message);

            // Req 5.4: no mapped value is a hand-built local upload path.
            $this->assertStringNotContainsString('/assets/uploads/', $map[$name], $message);

            // Req 5.6: a non-empty value that resolves to '' is retained as a key.
            if (strpos($name, '__empty__') !== false) {
                $this->assertSame('', $map[$name], $message);
            }
        }
    }

    /**
     * Req 5.2: every empty / whitespace-only input is excluded from the map.
     *
     * @dataProvider provideStoredValueLists
     *
     * @param string[] $storedValues
     */
    public function testEmptyAndWhitespaceValuesExcluded(
        array $storedValues,
        string $category,
        ?int $id,
        int $ttl
    ): void {
        $this->injectRecordingStorage();

        $map = file_url_map($storedValues, $category, $id, $ttl);

        // Guaranteed assertion so an all-blank/empty input list is not "risky".
        $this->assertIsArray($map);

        foreach (array_keys($map) as $key) {
            $this->assertNotSame('', trim($key), 'blank key leaked into map: ' . var_export($storedValues, true));
        }
    }

    /**
     * Seeded generator of arbitrary stored-value lists. Mixes value classes:
     * bare filenames, category/id/file relative keys, absolute base_url URLs,
     * empty/whitespace, adversarial (`..`, null-byte), a resolves-to-empty
     * sentinel, plus injected duplicates and blanks.
     *
     * @return array<string, array{0: string[], 1: string, 2: int|null, 3: int}>
     */
    public function provideStoredValueLists(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240611);

        $valuePool = [
            // Bare filenames.
            'a.jpg', 'b.pdf', 'comprobante.PNG', 'documento_5001.pdf', 'foto.jpeg',
            // Relative keys.
            'pago_gestor/12472/file.jpg', 'documentostatus/doc.pdf', '/cobro_cliente/5/x.png',
            // Absolute URLs (with and without the uploads prefix).
            'https://old.host/assets/uploads/pago_gestor/12472/file.jpg',
            'http://old.host/documentostatus/doc.pdf',
            // Empty / whitespace-only (must be excluded).
            '', '   ', "\t\n ", '  ',
            // Adversarial.
            '../../etc/passwd', "null\0byte.png", '..', '.',
            // Non-empty but resolves-to-empty sentinel (Req 5.6).
            '__empty__legacy.jpg', '__empty__/broken.pdf',
        ];

        $categories = ['documentostatus', 'pago_gestor', 'pago_derechos', 'cobro_cliente', 'evidencias', ''];

        $cases = [];

        // Always include a couple of fixed edge cases.
        $cases['empty_list'] = [[], 'documentostatus', null, 300];
        $cases['all_blank']  = [['', '   ', "\t", ' '], 'pago_gestor', 12472, 300];
        $cases['duplicates_collapse'] = [
            ['a.jpg', 'a.jpg', ' a.jpg ', 'b.pdf', 'b.pdf'],
            'cobro_cliente',
            5,
            300,
        ];
        $cases['resolves_to_empty_retained'] = [
            ['__empty__legacy.jpg', 'a.jpg', '__empty__legacy.jpg'],
            'documentostatus',
            null,
            300,
        ];

        $count = 300;
        for ($i = 0; $i < $count; $i++) {
            $len  = mt_rand(0, 8);
            $list = [];
            for ($j = 0; $j < $len; $j++) {
                $list[] = $valuePool[mt_rand(0, count($valuePool) - 1)];
            }

            // Randomly inject a duplicate to exercise collapsing.
            if ($list !== [] && mt_rand(0, 1) === 0) {
                $list[] = $list[mt_rand(0, count($list) - 1)];
            }

            $category = $categories[mt_rand(0, count($categories) - 1)];
            $id       = (mt_rand(0, 1) === 0) ? null : mt_rand(1, 999999);
            $ttl      = [300, 60, 900, 3600][mt_rand(0, 3)];

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$list, $category, $id, $ttl];
        }

        return $cases;
    }
}
