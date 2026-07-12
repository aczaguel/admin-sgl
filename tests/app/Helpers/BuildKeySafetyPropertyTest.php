<?php

namespace Tests\App\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * Property-based test for Property 4: Key safety.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5
 *
 * For arbitrary category / originalName (including path-traversal attempts,
 * unicode, spaces and special characters), buildKey() must NEVER yield a key
 * that:
 *   - contains a ".." segment (path traversal),
 *   - has a leading "/",
 *   - contains a backslash,
 * and it must ALWAYS match `^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$` with a
 * length between 1 and 1024 characters. For per-id categories the numeric id
 * must be the second path segment (Req 3.3), and the random suffix must keep
 * keys distinct (Req 3.5).
 *
 * PBT generators are implemented as PHPUnit data providers (pseudo-random,
 * deterministically seeded) so no new runtime dependency is introduced.
 *
 * @internal
 */
final class BuildKeySafetyPropertyTest extends CIUnitTestCase
{
    /** The canonical key pattern required by Requirement 3.1. */
    private const KEY_PATTERN = '#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#';

    /** Categories that must place the numeric id as the second segment (Req 3.3). */
    private const PER_ID_CATEGORIES = ['pago_gestor', 'pago_derechos', 'cobro_cliente'];

    protected function setUp(): void
    {
        parent::setUp();
        helper('filestorage');
    }

    /**
     * Property 4: every key produced by buildKey is traversal-safe and
     * matches the required pattern.
     *
     * @dataProvider provideArbitraryInputs
     */
    public function testProducedKeyIsAlwaysSafe(string $category, ?int $id, string $originalName): void
    {
        try {
            $key = buildKey($category, $id, $originalName);
        } catch (InvalidArgumentException $e) {
            // A documented rejection (empty/invalid category, or a per-id
            // category supplied without a valid positive id per Req 3.6) is a
            // safe outcome: no unsafe key was produced. The property only
            // constrains keys that ARE produced.
            $this->addToAssertionCount(1);

            return;
        }

        $message = sprintf(
            'Unsafe key produced. category=%s id=%s originalName=%s -> key=%s',
            var_export($category, true),
            var_export($id, true),
            var_export($originalName, true),
            var_export($key, true)
        );

        // Req 3.1: matches the canonical pattern and is 1..1024 chars.
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), $message);
        $length = strlen($key);
        $this->assertGreaterThanOrEqual(1, $length, $message);
        $this->assertLessThanOrEqual(1024, $length, $message);

        // Req 3.2 / 3.4: no leading '/', no backslash.
        $this->assertNotSame('/', substr($key, 0, 1), $message);
        $this->assertStringNotContainsString('\\', $key, $message);

        // Req 3.2: no ".." segment anywhere in the key (path-traversal defense).
        $segments = explode('/', $key);
        foreach ($segments as $segment) {
            $this->assertNotSame('..', $segment, $message);
            $this->assertNotSame('', $segment, $message); // no empty segment (no leading/duplicate '/')
        }

        // Req 3.3: for per-id categories the numeric id is the second segment.
        if (in_array($category, self::PER_ID_CATEGORIES, true) && $id !== null && $id > 0) {
            $this->assertArrayHasKey(1, $segments, $message);
            $this->assertSame((string) $id, $segments[1], $message);
        }
    }

    /**
     * Property 4 (Req 3.5): two keys derived for the same category/id are
     * distinct thanks to the random suffix.
     *
     * @dataProvider provideArbitraryInputs
     */
    public function testKeysAreDistinctForSameInputs(string $category, ?int $id, string $originalName): void
    {
        try {
            $first  = buildKey($category, $id, $originalName);
            $second = buildKey($category, $id, $originalName);
        } catch (InvalidArgumentException $e) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertNotSame(
            $first,
            $second,
            sprintf('Keys collided for category=%s id=%s name=%s', $category, var_export($id, true), $originalName)
        );
    }

    /**
     * Pseudo-random generator of arbitrary inputs, implemented as a seeded
     * data provider. Mixes valid categories, path-traversal attempts, unicode,
     * whitespace and special characters, and both valid positive ids and null.
     *
     * @return array<string, array{0: string, 1: int|null, 2: string}>
     */
    public function provideArbitraryInputs(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240517);

        $categories = [
            // Known/valid categories.
            'documentostatus', 'evidencias', 'avatars', 'tramites',
            'pago_gestor', 'pago_derechos', 'cobro_cliente',
            // Path-traversal attempts.
            '..', '../', '../../etc', '../../etc/passwd', 'foo/../bar', '/etc', './hidden',
            '..\\..\\windows', 'a/..', '...', ' .. ',
            // Unicode / whitespace / special chars.
            'categoría', 'файлы', '   ', 'weird cat', 'sp&ci*l!', "tab\tcat", '',
        ];

        $names = [
            // Normal names.
            'photo.jpg', 'comprobante.PDF', 'archivo.PNG', 'documento.jpeg',
            // Traversal attempts embedded in the filename.
            '../../etc/passwd', '..\\..\\secret.txt', '/absolute/path.png', '....//....//x.jpg',
            'a/../b.gif', '.htaccess', '..', '.', '',
            // Unicode / spaces / special chars.
            'niño año.jpg', 'файл.png', 'my file (1).JPG', 'sp@ce&sym#ol!.gif',
            "line\nbreak.txt", 'emoji😀.png', str_repeat('x', 300) . '.jpg',
        ];

        $cases = [];
        $count = 600;

        for ($i = 0; $i < $count; $i++) {
            $category = $categories[mt_rand(0, count($categories) - 1)];
            $name     = $names[mt_rand(0, count($names) - 1)];

            // Per-id categories always get a valid positive id; others get a
            // mix of null and positive ids.
            if (in_array($category, self::PER_ID_CATEGORIES, true)) {
                $id = mt_rand(1, 999999);
            } else {
                $id = (mt_rand(0, 1) === 0) ? null : mt_rand(1, 999999);
            }

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$category, $id, $name];
        }

        return $cases;
    }
}
