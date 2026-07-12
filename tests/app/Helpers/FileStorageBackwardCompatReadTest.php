<?php

namespace Tests\App\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property test for Property 5: Backward-compatible read.
 *
 * Validates: Requirements 4.2, 4.3, 4.4, 4.5, 4.6, 5.1, 5.3
 *
 * For arbitrary category/id/filename, this asserts:
 *   1. keyFromStored(bareFilename, category, id) equals the canonical key
 *      (category[+ '/' + id] + '/' + filename).
 *   2. Every legacy shape of the same stored reference (bare filename,
 *      relative key, absolute URL with the /assets/uploads/ prefix, and
 *      absolute URL without it) normalizes to that same canonical key.
 *   3. keyFromStored is a fixed point when re-applied to its own output:
 *      keyFromStored(keyFromStored(v)) == keyFromStored(v).
 *
 * Property tests use PHPUnit data-provider generators (no external runtime
 * dependency), combining deterministic edge cases with a seeded pseudo-random
 * sweep across the arbitrary input space (category/id/filename).
 */
class FileStorageBackwardCompatReadTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('filestorage');
    }

    /**
     * Generator: arbitrary (category, id, filename) tuples.
     *
     * Yields deterministic edge cases first, then a seeded pseudo-random
     * sweep so the property is exercised across a wide sample of the input
     * space while remaining reproducible.
     *
     * @return array<string, array{0:string,1:?int,2:string}>
     */
    public function arbitraryStoredValueProvider(): array
    {
        $perIdCategories = ['pago_gestor', 'pago_derechos', 'cobro_cliente'];
        $flatCategories  = ['documentostatus', 'evidencias', 'avatars', 'tramites'];

        $filenames = [
            'abc.jpg',
            'comprobante_12472_ab12cd34ef56.pdf',
            'documento.docx',
            'a.b.c.txt',
            'file123.png',
            'IMG-0001.jpeg',
            'contrato_final.PDF',
            'x.zip',
        ];

        $cases = [];

        // --- Deterministic edge cases -------------------------------------
        $cases['flat/no-id/simple']        = ['documentostatus', null, 'abc.jpg'];
        $cases['flat/with-id']             = ['evidencias', 42, 'file123.png'];
        $cases['perid/pago_gestor']        = ['pago_gestor', 12472, 'comprobante_12472_ab12cd34ef56.pdf'];
        $cases['perid/pago_derechos']      = ['pago_derechos', 1, 'documento.docx'];
        $cases['perid/cobro_cliente']      = ['cobro_cliente', 999999, 'a.b.c.txt'];
        $cases['flat/dotted-name']         = ['tramites', 7, 'a.b.c.txt'];
        $cases['flat/uppercase-ext']       = ['avatars', null, 'contrato_final.PDF'];

        // --- Seeded pseudo-random sweep -----------------------------------
        // Deterministic seed => reproducible "arbitrary" inputs.
        mt_srand(20240705);
        $allCategories = array_merge($perIdCategories, $flatCategories);

        for ($i = 0; $i < 150; $i++) {
            $category = $allCategories[mt_rand(0, count($allCategories) - 1)];
            $isPerId  = in_array($category, $perIdCategories, true);

            // Per-id categories always get a positive integer id; flat ones
            // randomly get an id or null (both are valid legacy shapes).
            if ($isPerId) {
                $id = mt_rand(1, 5000000);
            } else {
                $id = (mt_rand(0, 1) === 1) ? mt_rand(1, 5000000) : null;
            }

            $filename = $filenames[mt_rand(0, count($filenames) - 1)];

            $cases["rand/{$i}"] = [$category, $id, $filename];
        }

        return $cases;
    }

    /**
     * Compute the canonical relative key the way the design specifies:
     * category [+ '/' + id] + '/' + filename.
     */
    private function canonicalKey(string $category, ?int $id, string $filename): string
    {
        $cat = trim($category, '/');

        if ($id !== null) {
            return $cat . '/' . $id . '/' . $filename;
        }

        return $cat . '/' . $filename;
    }

    /**
     * Property (Req 4.3, 5.1): a bare filename is rebuilt into the canonical
     * relative key using its category and optional id.
     *
     * @dataProvider arbitraryStoredValueProvider
     */
    public function testBareFilenameYieldsCanonicalKey(string $category, ?int $id, string $filename): void
    {
        $expected = $this->canonicalKey($category, $id, $filename);
        $actual   = keyFromStored($filename, $category, $id);

        $this->assertSame(
            $expected,
            $actual,
            "Bare filename '{$filename}' with category '{$category}' and id " . var_export($id, true)
                . " should normalize to the canonical key."
        );
    }

    /**
     * Property (Req 4.2, 4.4, 4.5, 4.6, 5.3): all legacy shapes of the same
     * stored reference normalize to the identical canonical key.
     *
     * Shapes covered:
     *   - bare filename
     *   - relative key (already canonical, and with a spurious leading '/')
     *   - absolute URL containing the /assets/uploads/ prefix
     *   - absolute URL without that prefix (origin-only strip)
     *
     * @dataProvider arbitraryStoredValueProvider
     */
    public function testAllLegacyShapesNormalizeToSameCanonicalKey(string $category, ?int $id, string $filename): void
    {
        $canonical = $this->canonicalKey($category, $id, $filename);

        // Bare filename (the common legacy case).
        $this->assertSame(
            $canonical,
            keyFromStored($filename, $category, $id),
            'bare filename'
        );

        // Relative key already stored as source of truth.
        $this->assertSame(
            $canonical,
            keyFromStored($canonical, $category, $id),
            'relative key'
        );

        // Relative key with a spurious leading slash (Req 4.4).
        $this->assertSame(
            $canonical,
            keyFromStored('/' . $canonical, $category, $id),
            'relative key with leading slash'
        );

        // Absolute URL with the /assets/uploads/ prefix (Req 4.5).
        $absoluteWithPrefix = 'https://app.example.com/assets/uploads/' . $canonical;
        $this->assertSame(
            $canonical,
            keyFromStored($absoluteWithPrefix, $category, $id),
            'absolute URL with /assets/uploads/ prefix'
        );

        // Absolute URL without the prefix: origin-only strip (Req 4.9), which
        // still recovers the canonical key when the path is the key itself.
        $absoluteNoPrefix = 'https://cdn.example.com/' . $canonical;
        $this->assertSame(
            $canonical,
            keyFromStored($absoluteNoPrefix, $category, $id),
            'absolute URL without /assets/uploads/ prefix'
        );
    }

    /**
     * Property (Req 4.6): keyFromStored is a fixed point when re-applied to
     * its own output for every legacy shape.
     * keyFromStored(keyFromStored(v)) == keyFromStored(v).
     *
     * @dataProvider arbitraryStoredValueProvider
     */
    public function testKeyFromStoredIsFixedPoint(string $category, ?int $id, string $filename): void
    {
        $canonical = $this->canonicalKey($category, $id, $filename);

        $inputs = [
            'bare'               => $filename,
            'relative'           => $canonical,
            'relative-slash'     => '/' . $canonical,
            'absolute-prefixed'  => 'https://app.example.com/assets/uploads/' . $canonical,
            'absolute-no-prefix' => 'https://cdn.example.com/' . $canonical,
        ];

        foreach ($inputs as $label => $v) {
            $once  = keyFromStored($v, $category, $id);
            $twice = keyFromStored($once, $category, $id);

            $this->assertSame(
                $once,
                $twice,
                "keyFromStored should be a fixed point for shape '{$label}' (input: '{$v}')."
            );
        }
    }
}
