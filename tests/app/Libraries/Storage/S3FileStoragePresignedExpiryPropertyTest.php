<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\S3FileStorage;
use Aws\MockHandler;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Property-based test for Property 9: Presigned expiry (against the mocked S3 driver).
 *
 * Validates: Requirements 2.5, 10.5
 *
 * For arbitrary valid ttl in (0, 604800]:
 *   - url(key, ttl) returns a non-empty SigV4 presigned URL whose
 *     X-Amz-Expires query param equals exactly the requested ttl, and whose
 *     X-Amz-Date issuance timestamp is present and recent (Req 2.5).
 * For the default ttl (url(key) with no ttl arg):
 *   - X-Amz-Expires equals 300 (Req 10.5).
 * For arbitrary out-of-range ttl (<= 0 or > 604800):
 *   - url(key, ttl) returns '' (an error indication), not a URL (Req 2.5 boundary).
 *
 * The driver is backed by the AWS SDK MockHandler with dummy static
 * credentials and region us-east-1 so SigV4 presign signing happens entirely
 * offline (no network, no live AWS). Presigning never touches the handler, so
 * an empty MockHandler queue is sufficient.
 *
 * PBT generators are implemented as seeded PHPUnit data providers so any
 * counterexample is reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class S3FileStoragePresignedExpiryPropertyTest extends CIUnitTestCase
{
    /** Bucket used for every test client. */
    private const BUCKET = 'test-bucket';

    /** SSE algorithm the driver is configured with. */
    private const SSE = 'AES256';

    /** Maximum valid presign ttl, in seconds (AWS SigV4 cap of 7 days). */
    private const MAX_TTL = 604800;

    /** The default ttl the url() contract must use when none is supplied. */
    private const DEFAULT_TTL = 300;

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Build an S3FileStorage backed by a MockHandler-driven client with dummy
     * static credentials so presigned-URL signing happens entirely offline.
     */
    private function makeStorage(): S3FileStorage
    {
        $config         = new FileStorageConfig();
        $config->bucket = self::BUCKET;
        $config->region = 'us-east-1';
        $config->sse    = self::SSE;

        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => new MockHandler(),
        ]);

        return new S3FileStorage($config, $client);
    }

    /**
     * Parse the query string of a URL into an associative array.
     *
     * @return array<string, string>
     */
    private function queryParams(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertIsString($query, 'presigned URL must contain a query string: ' . $url);

        $params = [];
        parse_str($query, $params);

        return $params;
    }

    // ---------------------------------------------------------------------
    // Property 9a: valid ttl -> presigned URL encoding exactly that expiry
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideValidTtls
     */
    public function testValidTtlEncodesRequestedExpiry(string $key, int $ttl): void
    {
        // Guard: the generator must only emit ttls inside the valid range.
        $this->assertGreaterThan(0, $ttl, 'Generator produced a non-positive ttl.');
        $this->assertLessThanOrEqual(self::MAX_TTL, $ttl, 'Generator produced a ttl over the max.');

        $storage = $this->makeStorage();

        $before = time();
        $url    = $storage->url($key, $ttl);
        $after  = time();

        $message = sprintf('key=%s ttl=%d', $key, $ttl);

        // Req 2.5: a valid ttl yields a non-empty SigV4 presigned URL.
        $this->assertNotSame('', $url, 'url must produce a presigned URL for a valid ttl. ' . $message);
        $this->assertStringStartsWith('https://', $url, 'presigned URL should be https. ' . $message);

        $params = $this->queryParams($url);

        $this->assertArrayHasKey('X-Amz-Signature', $params, 'presigned URL should be SigV4-signed. ' . $message);
        $this->assertArrayHasKey('X-Amz-Expires', $params, 'presigned URL must encode X-Amz-Expires. ' . $message);

        // The encoded expiry must equal exactly the requested ttl.
        $this->assertSame(
            $ttl,
            (int) $params['X-Amz-Expires'],
            'X-Amz-Expires must equal the requested ttl. ' . $message
        );

        // X-Amz-Date (issuance) must be present and recent (within the call window).
        $this->assertArrayHasKey('X-Amz-Date', $params, 'presigned URL must encode X-Amz-Date. ' . $message);
        $issued = $this->parseAmzDate($params['X-Amz-Date']);
        $this->assertNotFalse($issued, 'X-Amz-Date must be a parseable ISO8601 basic timestamp. ' . $message);
        // Allow a small clock skew margin around the observed call window.
        $this->assertGreaterThanOrEqual($before - 5, $issued, 'X-Amz-Date must not predate issuance. ' . $message);
        $this->assertLessThanOrEqual($after + 5, $issued, 'X-Amz-Date must be recent. ' . $message);
    }

    // ---------------------------------------------------------------------
    // Property 9b: default ttl -> X-Amz-Expires == 300 (Req 10.5)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideKeys
     */
    public function testDefaultTtlIs300(string $key): void
    {
        $storage = $this->makeStorage();

        // Call url() with no ttl argument to exercise the default.
        $url = $storage->url($key);

        $message = 'key=' . $key;

        $this->assertNotSame('', $url, 'url must produce a presigned URL for the default ttl. ' . $message);

        $params = $this->queryParams($url);
        $this->assertArrayHasKey('X-Amz-Expires', $params, 'presigned URL must encode X-Amz-Expires. ' . $message);
        $this->assertSame(
            self::DEFAULT_TTL,
            (int) $params['X-Amz-Expires'],
            'default X-Amz-Expires must be 300 seconds. ' . $message
        );
    }

    // ---------------------------------------------------------------------
    // Property 9c: out-of-range ttl -> error indication, no URL
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideOutOfRangeTtls
     */
    public function testOutOfRangeTtlYieldsErrorIndication(string $key, int $ttl): void
    {
        // Guard: the generator must only emit ttls outside the valid range.
        $this->assertTrue($ttl <= 0 || $ttl > self::MAX_TTL, 'Generator produced an in-range ttl.');

        $storage = $this->makeStorage();

        $url = $storage->url($key, $ttl);

        $this->assertSame(
            '',
            $url,
            sprintf("url must return '' (error indication) for out-of-range ttl. key=%s ttl=%d", $key, $ttl)
        );
    }

    // ---------------------------------------------------------------------
    // Generators
    // ---------------------------------------------------------------------

    /**
     * Seeded pseudo-random generator of (key, valid ttl) pairs across the
     * full open-closed range (0, 604800], including both boundaries (1 and
     * 604800) as explicit edge cases.
     *
     * @return array<string, array{0: string, 1: int}>
     */
    public function provideValidTtls(): array
    {
        mt_srand(20240611);

        $cases = [];
        $count = 200;

        for ($i = 0; $i < $count; $i++) {
            $key = $this->randomKey();
            $ttl = mt_rand(1, self::MAX_TTL);

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$key, $ttl];
        }

        // Explicit boundary and common edge cases.
        $cases['edge_min_ttl_1']       = ['evidencias/min12345.png', 1];
        $cases['edge_max_ttl_604800']  = ['evidencias/max12345.png', self::MAX_TTL];
        $cases['edge_default_value']   = ['documentostatus/def12345.pdf', self::DEFAULT_TTL];
        $cases['edge_one_hour']        = ['pago_gestor/12472/hour1234.jpg', 3600];
        $cases['edge_one_day']         = ['tramites/999/day12345.png', 86400];

        return $cases;
    }

    /**
     * Seeded pseudo-random generator of keys (for the default-ttl property).
     *
     * @return array<string, array{0: string}>
     */
    public function provideKeys(): array
    {
        mt_srand(20240612);

        $cases = [];
        for ($i = 0; $i < 50; $i++) {
            $cases['key_' . $i . '_' . bin2hex(random_bytes(3))] = [$this->randomKey()];
        }

        $cases['edge_single_segment'] = ['avatars_only_file.png'];
        $cases['edge_per_id']         = ['pago_gestor/12472/comprobante_abc123.jpg'];

        return $cases;
    }

    /**
     * Seeded pseudo-random generator of (key, out-of-range ttl) pairs: values
     * <= 0 and values > 604800, including both boundaries (0 and 604801).
     *
     * @return array<string, array{0: string, 1: int}>
     */
    public function provideOutOfRangeTtls(): array
    {
        mt_srand(20240613);

        $cases = [];
        $count = 100;

        for ($i = 0; $i < $count; $i++) {
            $key = $this->randomKey();

            // Alternate between the two out-of-range halves.
            if ($i % 2 === 0) {
                // <= 0
                $ttl = -mt_rand(0, 1000000);
            } else {
                // > 604800
                $ttl = self::MAX_TTL + mt_rand(1, 1000000);
            }

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$key, $ttl];
        }

        // Explicit boundary and common edge cases.
        $cases['edge_zero']          = ['evidencias/zero1234.png', 0];
        $cases['edge_neg_one']       = ['evidencias/neg12345.png', -1];
        $cases['edge_just_over_max'] = ['evidencias/over1234.png', self::MAX_TTL + 1];
        $cases['edge_far_over_max']  = ['evidencias/far12345.png', 10_000_000];

        return $cases;
    }

    // ---------------------------------------------------------------------
    // Small generators / parsing helpers
    // ---------------------------------------------------------------------

    /**
     * Build a valid relative key with 1..4 segments matching
     * `[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*`, never a ".." segment.
     */
    private function randomKey(): string
    {
        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $shape = mt_rand(0, 2);

        if ($shape === 0) {
            return $this->randomSegment();
        }

        if ($shape === 1) {
            $category = $categories[mt_rand(0, count($categories) - 1)];

            return $category . '/' . mt_rand(1, 999999) . '/' . $this->randomSegment();
        }

        $segments   = [];
        $segmentCnt = mt_rand(2, 4);
        for ($s = 0; $s < $segmentCnt; $s++) {
            $segments[] = $this->randomSegment();
        }

        return implode('/', $segments);
    }

    /**
     * A single non-empty path segment from `[A-Za-z0-9._-]`, never "." or "..".
     */
    private function randomSegment(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._-';
        $len      = mt_rand(1, 24);

        do {
            $segment = '';
            for ($i = 0; $i < $len; $i++) {
                $segment .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
        } while ($segment === '.' || $segment === '..');

        return $segment;
    }

    /**
     * Parse an X-Amz-Date value (ISO8601 basic, e.g. 20240611T101112Z) into a
     * Unix timestamp, or false when it cannot be parsed.
     *
     * @return int|false
     */
    private function parseAmzDate(string $value)
    {
        $dt = \DateTimeImmutable::createFromFormat('Ymd\THis\Z', $value, new \DateTimeZone('UTC'));
        if ($dt === false) {
            return false;
        }

        return $dt->getTimestamp();
    }
}
