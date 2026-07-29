<?php

namespace Tests\App\Helpers;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 9: Order preservation for lists.
 *
 * Validates: Requirements 6.1, 6.4, 6.5, 6.6
 *
 * For an arbitrary list of stored values (mixing bare filenames, relative
 * keys, absolute URLs, duplicates and empty/whitespace-only blanks),
 * `file_url_list(vals, category, id, ttl)` must:
 *
 *  - emit exactly one entry per NON-EMPTY (trimmed) input value, preserving
 *    input order — for all input positions i < j whose trimmed values are
 *    non-empty, the entry for i precedes the entry for j (Req 6.1, 6.4);
 *  - exclude empty / whitespace-only values entirely (Req 6.1, 6.4);
 *  - RETAIN duplicates, one entry per occurrence, in input order (Req 6.5);
 *  - set every entry `name` to the trimmed stored value and every entry `url`
 *    to exactly `file_url(name, category, id, ttl)` (Req 6.1, 6.6);
 *  - never produce a hand-built /assets/uploads/ path when the resolver does
 *    not (the recording double returns presigned-style URLs) (Req 6.6).
 *
 * The `fileStorage` service is a recording double injected via
 * Services::injectMock (matching the sibling s3-file-storage helper tests); no
 * live AWS calls are made. giorgiosironi/eris is not installed, so the fuzz
 * generator is a deterministically-seeded PHPUnit data provider.
 *
 * @internal
 */
final class FileUrlListPropertyTest extends CIUnitTestCase
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
     * The double returns a deterministic presigned-style URL for any key and
     * records every (key, ttl) it was asked to resolve, so the test can assert
     * both the produced URL and that resolution flowed through the service.
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

                // Presigned-style URL; deliberately never an /assets/uploads/ path.
                return 'https://bucket.example/' . $key . '?ttl=' . $ttl . '&sig=abc';
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Property 9: file_url_list preserves input order of non-empty values,
     * excludes blanks, retains duplicates one-per-occurrence, and each entry's
     * url equals file_url(name, category, id, ttl).
     *
     * @dataProvider provideStoredValueLists
     *
     * @param string[] $storedValues
     */
    public function testListPreservesOrderExcludesBlanksAndResolvesThroughFileUrl(
        array $storedValues,
        string $category,
        ?int $id,
        int $ttl
    ): void {
        $this->injectRecordingStorage();

        $list = file_url_list($storedValues, $category, $id, $ttl);

        // Expected sequence of non-empty (trimmed) names, in input order,
        // with duplicates retained one entry per occurrence.
        $expectedNames = [];
        foreach ($storedValues as $value) {
            $name = trim((string) $value);
            if ($name !== '') {
                $expectedNames[] = $name;
            }
        }

        $message = 'Input: ' . var_export($storedValues, true);

        // Req 6.1 / 6.4 / 6.5: one entry per non-empty occurrence, order preserved.
        $this->assertCount(count($expectedNames), $list, $message);
        $this->assertSame($expectedNames, array_column($list, 'name'), $message);

        // Req 6.4 restated as the pairwise ordering property: for every i < j
        // among the emitted entries, the position in the output list is
        // strictly increasing (a direct consequence of the sequence equality
        // above, asserted explicitly to encode the property).
        for ($i = 0; $i < count($list); $i++) {
            for ($j = $i + 1; $j < count($list); $j++) {
                $this->assertLessThan(
                    $j,
                    $i,
                    'Order violated between output positions ' . $i . ' and ' . $j . '. ' . $message
                );
            }
        }

        // Req 6.1 / 6.6: each url equals file_url(name, category, id, ttl) and
        // is never a hand-built /assets/uploads/ path.
        foreach ($list as $index => $entry) {
            $this->assertArrayHasKey('name', $entry, $message);
            $this->assertArrayHasKey('url', $entry, $message);

            $expectedUrl = file_url($entry['name'], $category, $id, $ttl);
            $this->assertSame(
                $expectedUrl,
                $entry['url'],
                'Entry ' . $index . ' url must equal file_url(name,...). ' . $message
            );
            $this->assertStringNotContainsString('/assets/uploads/', $entry['url'], $message);
        }
    }

    /**
     * Deterministically-seeded fuzz generator of stored-value lists.
     *
     * Draws from bare filenames, per-id/flat relative keys, absolute URLs
     * (with and without the /assets/uploads/ prefix), duplicates, and a rich
     * set of empty/whitespace-only blanks — so both the exclusion and
     * order-preservation clauses are exercised across many shapes.
     *
     * @return array<string, array{0: string[], 1: string, 2: int|null, 3: int}>
     */
    public function provideStoredValueLists(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240711);

        $categories = [
            ['documentostatus', null],
            ['evidencias', null],
            ['pago_gestor', 12472],
            ['pago_derechos', 88],
            ['cobro_cliente', 5],
        ];

        $ttls = [300, 900, 60];

        $nonEmptyPool = [
            'comprobante.jpg',
            'recibo.PDF',
            'foto_9.png',
            'documento_5001.pdf',
            'acta.jpeg',
            'evidencia.webp',
            'pago_gestor/12472/file.jpg',
            '/pago_derechos/88/recibo.pdf',
            'https://old.host/assets/uploads/cobro_cliente/5/cobro.png',
            'http://old.host/documentostatus/doc.pdf',
            'niño año.JPG',
            'file (1).gif',
        ];

        $blankPool = [
            '',
            '   ',
            "\t",
            "\n",
            "\r\n ",
            "  \t \n ",
        ];

        $cases = [];
        $count = 400;

        for ($k = 0; $k < $count; $k++) {
            [$category, $id] = $categories[mt_rand(0, count($categories) - 1)];
            $ttl             = $ttls[mt_rand(0, count($ttls) - 1)];

            $len    = mt_rand(0, 8);
            $values = [];
            for ($n = 0; $n < $len; $n++) {
                $roll = mt_rand(0, 9);
                if ($roll < 3) {
                    // ~30% blanks to exercise exclusion.
                    $values[] = $blankPool[mt_rand(0, count($blankPool) - 1)];
                } else {
                    $values[] = $nonEmptyPool[mt_rand(0, count($nonEmptyPool) - 1)];
                }
            }

            $cases['case_' . $k . '_' . bin2hex(random_bytes(3))] = [$values, $category, $id, $ttl];
        }

        // A few hand-picked deterministic edge cases guaranteeing coverage.
        $cases['edge_empty_list']          = [[], 'documentostatus', null, 300];
        $cases['edge_all_blanks']          = [['', '  ', "\t\n"], 'pago_gestor', 12472, 300];
        $cases['edge_duplicates_retained'] = [
            ['a.jpg', 'a.jpg', 'b.pdf', 'a.jpg'], 'evidencias', null, 300,
        ];
        $cases['edge_order_with_blanks']   = [
            ['first.jpg', '   ', 'second.png', '', 'third.pdf'], 'cobro_cliente', 5, 900,
        ];
        $cases['edge_whitespace_trimmed']  = [
            ['  trimmed.jpg  ', "\tspaced.png\t"], 'documentostatus', null, 300,
        ];

        return $cases;
    }
}
