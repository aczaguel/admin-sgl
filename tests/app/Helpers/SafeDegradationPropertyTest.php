<?php

namespace Tests\App\Helpers;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property-based test for Property 6: Empty/unresolvable degrade safely.
 *
 * Validates: Requirements 8.1, 8.2, 8.3, 8.4, 12.3, 12.4
 *
 * For any stored value that is empty, whitespace-only, or non-normalizable
 * (a `..` path segment or a byte with character code 0), the render-path
 * helpers must degrade safely:
 *
 *   - `file_url()` resolves the value to the empty string '' (Req 8.1, 12.3).
 *   - No exception propagates out of `file_url`/`file_url_map`/`file_url_list`
 *     for any input (Req 8.3).
 *   - `file_url_map()` excludes empty/whitespace values but RETAINS a non-empty
 *     unresolvable value as a key mapped to '' (Req 8.2 — other rows survive).
 *   - `file_url_list()` excludes empty/whitespace values and carries a non-empty
 *     unresolvable value as an entry whose `url` is '' (Req 8.2).
 *   - A good value mixed with bad values still resolves to a non-empty URL and
 *     is never dropped or corrupted (Req 8.2).
 *   - Resolution passes through the Legacy_Normalizer / storage contract so the
 *     `..`-segment and null-byte defenses are applied (Req 12.4), and the
 *     storage double records NO store/DB mutation — only read-only `url()`
 *     calls, never `put()`/`delete()` (Req 8.4, 12.4).
 *
 * giorgiosironi/eris is not installed, so the "fuzz harness" is a seeded
 * PHPUnit data provider that sweeps the adversarial value classes. The
 * `fileStorage` service is a recording double injected via
 * `Services::injectMock`, matching the sibling s3-file-storage test
 * conventions. No live AWS calls are made.
 *
 * @internal
 */
final class SafeDegradationPropertyTest extends CIUnitTestCase
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
     * The double faithfully models the storage contract's key-safety guarantee
     * (mirroring LocalFileStorage::assertKey, plus null-byte rejection): an
     * unsafe key resolves to '' rather than a servable URL. It records every
     * method invocation so the test can assert that only the read-only `url()`
     * is ever called and that no store mutation (`put`/`delete`) occurs.
     *
     * @return object exposing ->calls, ->mutations and the FileStorage methods.
     */
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            /** @var array<int,array<int,mixed>> Every method invocation, in order. */
            public array $calls = [];

            /** @var array<int,array<int,mixed>> Store-mutating calls (put/delete). */
            public array $mutations = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = ['url', $key, $ttl];

                // Model the storage key-safety contract: an unsafe key never
                // resolves to a servable URL (Req 12.3 / 12.4).
                if (!$this->isSafeKey($key)) {
                    return '';
                }

                return 'https://cdn.example/' . $key . '?ttl=' . $ttl;
            }

            public function put(string $key, string $localTmpPath): bool
            {
                $this->calls[]     = ['put', $key];
                $this->mutations[] = ['put', $key];

                return true;
            }

            public function delete(string $key): bool
            {
                $this->calls[]     = ['delete', $key];
                $this->mutations[] = ['delete', $key];

                return true;
            }

            public function exists(string $key): bool
            {
                $this->calls[] = ['exists', $key];

                return false;
            }

            /**
             * Reject traversal-unsafe / null-byte keys, mirroring the real
             * driver's assertKey plus a null-byte guard.
             */
            private function isSafeKey(string $key): bool
            {
                if ($key === '') {
                    return false;
                }
                if (strpos($key, "\0") !== false) {
                    return false;
                }
                if (strpos($key, '\\') !== false) {
                    return false;
                }
                if ($key[0] === '/') {
                    return false;
                }
                foreach (explode('/', $key) as $segment) {
                    if ($segment === '..') {
                        return false;
                    }
                }

                return true;
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    /**
     * Property 6 (Req 8.1, 8.3, 12.3, 12.4): every empty, whitespace-only, or
     * unresolvable stored value resolves to '' through `file_url()` without
     * raising, and never triggers a store mutation.
     *
     * @dataProvider provideUnresolvableValues
     */
    public function testFileUrlDegradesToEmptyString(string $value, string $category, ?int $id): void
    {
        $fake = $this->injectRecordingStorage();

        $url = file_url($value, $category, $id);

        $message = sprintf(
            'file_url must degrade to "" for unresolvable value=%s (category=%s, id=%s), got %s',
            var_export($value, true),
            var_export($category, true),
            var_export($id, true),
            var_export($url, true)
        );

        $this->assertSame('', $url, $message);
        $this->assertSame([], $fake->mutations, 'file_url must never mutate the store: ' . $message);
    }

    /**
     * Property 6 (Req 8.2, 12.3): `file_url_map()` either excludes an
     * empty/whitespace value or retains a non-empty unresolvable value mapped
     * to '' — it never throws and never mutates the store.
     *
     * @dataProvider provideUnresolvableValues
     */
    public function testFileUrlMapExcludesOrRetainsEmpty(string $value, string $category, ?int $id): void
    {
        $fake = $this->injectRecordingStorage();

        $map = file_url_map([$value], $category, $id);

        $trimmed = trim($value);
        if ($trimmed === '') {
            $this->assertSame([], $map, 'Empty/whitespace values must be excluded from the map.');
        } else {
            $this->assertArrayHasKey($trimmed, $map, 'A non-empty value must be retained as a map key.');
            $this->assertSame('', $map[$trimmed], 'An unresolvable non-empty value must map to "".');
        }

        $this->assertSame([], $fake->mutations, 'file_url_map must never mutate the store.');
    }

    /**
     * Property 6 (Req 8.2, 12.3): `file_url_list()` excludes empty/whitespace
     * values and carries a non-empty unresolvable value as an entry whose
     * `url` is '' — never throwing and never mutating the store.
     *
     * @dataProvider provideUnresolvableValues
     */
    public function testFileUrlListExcludesOrCarriesEmpty(string $value, string $category, ?int $id): void
    {
        $fake = $this->injectRecordingStorage();

        $list = file_url_list([$value], $category, $id);

        $trimmed = trim($value);
        if ($trimmed === '') {
            $this->assertSame([], $list, 'Empty/whitespace values must be excluded from the list.');
        } else {
            $this->assertCount(1, $list, 'A non-empty value must yield exactly one list entry.');
            $this->assertSame($trimmed, $list[0]['name']);
            $this->assertSame('', $list[0]['url'], 'An unresolvable non-empty value must carry url "".');
        }

        $this->assertSame([], $fake->mutations, 'file_url_list must never mutate the store.');
    }

    /**
     * Property 6 (Req 8.2, 8.3): a good value mixed with adversarial values
     * still resolves to a non-empty URL and is neither dropped nor corrupted,
     * while the bad values degrade to '' — the whole gallery renders without an
     * exception.
     *
     * @dataProvider provideMixedLists
     *
     * @param string[] $storedValues
     */
    public function testMixedListRendersGoodItemsAndDegradesBadOnes(array $storedValues, string $category, ?int $id): void
    {
        $fake = $this->injectRecordingStorage();

        $map  = file_url_map($storedValues, $category, $id);
        $list = file_url_list($storedValues, $category, $id);

        foreach ($storedValues as $value) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue; // excluded from both structures
            }

            $this->assertArrayHasKey($trimmed, $map, 'Non-empty value must appear in the map.');

            // A genuinely resolvable value keeps a non-empty URL; an adversarial
            // one degrades to '' — but is never dropped or corrupted.
            if ($this->isResolvable($value, $category, $id)) {
                $this->assertNotSame('', $map[$trimmed], 'A resolvable value must keep its non-empty URL.');
                $this->assertStringStartsWith('https://cdn.example/', $map[$trimmed]);
            } else {
                $this->assertSame('', $map[$trimmed], 'An unresolvable value must degrade to "".');
            }
        }

        // The list carries one entry per non-empty occurrence (order/dup checks
        // belong to Property 9); here we only assert nothing throws and the
        // resolvable items survive with non-empty URLs.
        foreach ($list as $entry) {
            if ($this->isResolvable($entry['name'], $category, $id)) {
                $this->assertNotSame('', $entry['url']);
            }
        }

        $this->assertSame([], $fake->mutations, 'A mixed render must never mutate the store.');
    }

    /**
     * Property 6 (Req 8.4, 12.4): sweeping every adversarial value through all
     * three helpers performs read-only resolution — the storage double records
     * zero store mutations across the entire sweep.
     */
    public function testEntireSweepPerformsNoStoreMutation(): void
    {
        $fake = $this->injectRecordingStorage();

        foreach ($this->provideUnresolvableValues() as $case) {
            [$value, $category, $id] = $case;
            file_url($value, $category, $id);
            file_url_map([$value], $category, $id);
            file_url_list([$value], $category, $id);
        }

        $this->assertSame([], $fake->mutations, 'No put()/delete() may occur on the render path.');
    }

    /**
     * Whether a value is expected to resolve to a non-empty URL under the
     * recording double's key-safety contract (used only by the mixed-list
     * assertions; empty/whitespace/`..`/null-byte are the unresolvable set).
     */
    private function isResolvable(string $value, string $category, ?int $id): bool
    {
        $key = keyFromStored($value, $category, $id);
        if ($key === '') {
            return false;
        }
        if (strpos($key, "\0") !== false || strpos($key, '\\') !== false || $key[0] === '/') {
            return false;
        }
        foreach (explode('/', $key) as $segment) {
            if ($segment === '..') {
                return false;
            }
        }

        return true;
    }

    /**
     * Seeded fuzz harness over the adversarial value classes: empty,
     * whitespace-only, `..` path segments, and null-byte values, spread across
     * documentostatus (no id) and the per-id categories.
     *
     * @return array<string, array{0:string,1:string,2:int|null}>
     */
    public function provideUnresolvableValues(): array
    {
        $values = [
            // Empty / whitespace-only (Req 8.1).
            'empty'        => '',
            'space'        => ' ',
            'spaces'       => '   ',
            'tab'          => "\t",
            'newline'      => "\n",
            'mixed_ws'     => "  \t\r\n ",
            // Null-byte values (Req 12.3). Leading/trailing null bytes are
            // stripped by trim() and short-circuit; embedded null bytes survive
            // normalization and are rejected by the storage contract.
            'null_only'    => "\0",
            'null_padded'  => "  \0  ",
            'null_embed'   => "a\0b.jpg",
            'null_doc'     => "doc\0.pdf",
            'null_double'  => "\0\0",
            // `..` path segments (Req 12.3 / 12.4).
            'dotdot_bare'  => '..',
            'dotdot_slash' => '../',
            'dotdot_path'  => '../../etc/passwd',
            'dotdot_mid'   => 'foo/../bar.jpg',
            'dotdot_rel'   => 'documentostatus/..',
            'dotdot_back'  => '..\\..\\windows\\x.jpg',
            'dotdot_trail' => 'sub/dir/..',
        ];

        $categories = [
            ['documentostatus', null],
            ['pago_gestor', 12472],
            ['pago_derechos', 900],
            ['cobro_cliente', 1],
        ];

        $cases = [];
        foreach ($values as $label => $value) {
            foreach ($categories as [$category, $id]) {
                $key           = $label . '__' . $category;
                $cases[$key]   = [$value, $category, $id];
            }
        }

        return $cases;
    }

    /**
     * Mixed lists that interleave genuinely resolvable values with the
     * adversarial classes, so the "other items still render" guarantee
     * (Req 8.2) is exercised.
     *
     * @return array<string, array{0:array<int,string>,1:string,2:int|null}>
     */
    public function provideMixedLists(): array
    {
        return [
            'good_bad_good_documentostatus' => [
                ['a.jpg', '', 'b.pdf', '..', 'c.png', "x\0y.gif", '   '],
                'documentostatus',
                null,
            ],
            'good_bad_perid' => [
                ['comprobante.jpg', '../../secret', 'recibo.pdf', "\0", 'foto.png'],
                'pago_gestor',
                12472,
            ],
            'all_bad' => [
                ['', '   ', '..', "a\0b", 'x/../y'],
                'cobro_cliente',
                7,
            ],
            'all_good' => [
                ['first.jpg', 'second.pdf', 'third.png'],
                'documentostatus',
                null,
            ],
        ];
    }
}
