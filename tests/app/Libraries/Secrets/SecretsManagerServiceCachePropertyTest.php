<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\SecretProvider;
use App\Libraries\Secrets\SecretsManagerService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use ReflectionClass;

/**
 * Property 3: Cache performs at most one fetch per reference per request.
 *
 * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
 *
 * For `k >= 1` resolutions of the SAME reference:
 *   - with no TTL configured (cacheTtl = 0, Req 4.4) or within an unexpired TTL
 *     (Req 4.3), the underlying provider is invoked EXACTLY ONCE and every one
 *     of the `k` results is equal (the cached material is returned without a
 *     new provider call, Req 4.1/4.2);
 *   - when a configured TTL has elapsed, exactly ONE refetch occurs (a single
 *     additional provider call), after which the cache is warm again.
 *
 * Determinism note (TTL expiry):
 * The production {@see SecretsManagerService::getSecret()} stamps each cache
 * entry with `expiresAt = time() + cacheTtl` and compares against `time()`.
 * Rather than sleeping (slow, flaky) or using a very short TTL (racy), this test
 * simulates an ELAPSED TTL deterministically by reaching into the private
 * `$cache` property via reflection and rewinding the stored entry's `expiresAt`
 * to a timestamp strictly in the past. The next `getSecret()` then observes an
 * expired entry and performs exactly one refetch. This exercises the real
 * expiry branch without any wall-clock dependence.
 *
 * The provider under test is a counting fake ({@see CountingSecretProvider})
 * implementing {@see SecretProvider}, injected via reflection on the private
 * `provider` property so no AWS SDK / live call is ever involved. Cases are
 * produced by seeded PHPUnit data-provider generators (>= 100 iterations),
 * following the tests/ convention used by the sibling Secrets property tests.
 *
 * @internal
 */
final class SecretsManagerServiceCachePropertyTest extends CIUnitTestCase
{
    /**
     * Property 3 (single fetch): for `k >= 1` resolutions of the same reference
     * with either no TTL (cacheTtl = 0) or an unexpired TTL, the provider is
     * invoked exactly once and all `k` results are equal.
     *
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
     *
     * @dataProvider singleFetchProvider
     */
    public function testAtMostOneFetchPerReferenceWithinTtl(
        int $k,
        int $ttl,
        string $reference,
        array $secretValue
    ): void {
        $fake    = new CountingSecretProvider($secretValue);
        $service = $this->makeServiceWithProvider($fake, $ttl);

        $results = [];
        for ($i = 0; $i < $k; $i++) {
            $results[] = $service->getSecret($reference);
        }

        $message = sprintf('k=%d ttl=%d ref=%s', $k, $ttl, $reference);

        // Req 4.1/4.2: exactly one underlying fetch regardless of how many times
        // the same reference is resolved within the (unexpired) cache window.
        $this->assertSame(1, $fake->calls, 'Provider must be fetched exactly once per reference. ' . $message);

        // Req 4.2/4.3/4.4: every resolution returns the same cached material.
        $first = $results[0];
        foreach ($results as $idx => $result) {
            $this->assertSame($first, $result, "Resolution #{$idx} must equal the first (cached) result. " . $message);
        }

        // The single returned value reflects the provider's first (and only)
        // fetch — the counting marker proves no hidden refetch occurred.
        $this->assertSame(1, $first['__fetch'], 'Cached value must come from the single fetch. ' . $message);
    }

    /**
     * Property 3 (TTL expiry): once a configured TTL has elapsed, resolving the
     * same reference again triggers EXACTLY ONE refetch, and subsequent
     * resolutions within the fresh TTL window are served from cache again.
     *
     * **Validates: Requirements 4.1, 4.3**
     *
     * @dataProvider ttlExpiryProvider
     */
    public function testExactlyOneRefetchAfterTtlElapsed(
        int $warmReadsBefore,
        int $warmReadsAfter,
        int $ttl,
        string $reference,
        array $secretValue
    ): void {
        $fake    = new CountingSecretProvider($secretValue);
        $service = $this->makeServiceWithProvider($fake, $ttl);

        $message = sprintf(
            'before=%d after=%d ttl=%d ref=%s',
            $warmReadsBefore,
            $warmReadsAfter,
            $ttl,
            $reference
        );

        // Warm the cache and read it several times: exactly one fetch so far.
        $service->getSecret($reference);
        for ($i = 0; $i < $warmReadsBefore; $i++) {
            $service->getSecret($reference);
        }
        $this->assertSame(1, $fake->calls, 'Only one fetch before the TTL elapses. ' . $message);

        $valueBefore = $service->getSecret($reference);
        $this->assertSame(1, $valueBefore['__fetch'], 'Pre-expiry value comes from fetch #1. ' . $message);

        // Deterministically simulate an elapsed TTL by rewinding the cached
        // entry's expiresAt into the past (see class docblock).
        $this->expireCacheEntry($service, $reference);

        // The next resolution observes the expired entry and refetches ONCE.
        $valueAfter = $service->getSecret($reference);
        $this->assertSame(2, $fake->calls, 'Exactly one refetch must occur after the TTL elapses. ' . $message);
        $this->assertSame(2, $valueAfter['__fetch'], 'Post-expiry value must come from the refetch (#2). ' . $message);

        // The cache is warm again: further reads within the new TTL window do
        // not fetch a third time.
        for ($i = 0; $i < $warmReadsAfter; $i++) {
            $again = $service->getSecret($reference);
            $this->assertSame($valueAfter, $again, 'Post-refetch reads must be served from cache. ' . $message);
        }
        $this->assertSame(2, $fake->calls, 'No extra fetch after the cache is warm again. ' . $message);
    }

    /**
     * Property 3 (independent references): distinct references are cached
     * independently — each is fetched exactly once and never shares another's
     * cached material.
     *
     * **Validates: Requirements 4.1, 4.2**
     *
     * @dataProvider independentReferencesProvider
     */
    public function testEachReferenceFetchedExactlyOnce(int $k, array $references): void
    {
        $fake    = new CountingSecretProvider(['host' => 'h', 'username' => 'u', 'password' => 'p', 'dbname' => 'd']);
        $service = $this->makeServiceWithProvider($fake, 0);

        // Resolve every reference k times in interleaved order.
        for ($round = 0; $round < $k; $round++) {
            foreach ($references as $reference) {
                $service->getSecret($reference);
            }
        }

        $unique = array_values(array_unique($references));

        $message = sprintf('k=%d refs=%s', $k, implode(',', $unique));
        $this->assertSame(count($unique), $fake->calls, 'One fetch per DISTINCT reference. ' . $message);
    }

    // ── Service factory + reflection helpers ─────────────────────────────────

    /**
     * Build a SecretsManagerService whose active provider is replaced by the
     * given counting fake (via reflection on the private `provider` property),
     * with the supplied cache TTL. The service is constructed with provider=env
     * so no AWS client is created during construction.
     */
    private function makeServiceWithProvider(SecretProvider $provider, int $ttl): SecretsManagerService
    {
        $config           = new SecretsConfig();
        $config->provider = 'env';   // valid flag → EnvSecretProvider built, then swapped out
        $config->cacheTtl = $ttl;

        $service = new SecretsManagerService($config);

        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $provider);

        return $service;
    }

    /**
     * Deterministically simulate an elapsed TTL: rewind the cached entry's
     * `expiresAt` to a timestamp strictly in the past so the next getSecret()
     * treats it as expired and refetches.
     */
    private function expireCacheEntry(SecretsManagerService $service, string $reference): void
    {
        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('cache');
        $prop->setAccessible(true);

        /** @var array<string, array{value: array<string,string|int>, expiresAt: int|null}> $cache */
        $cache = $prop->getValue($service);

        $this->assertArrayHasKey($reference, $cache, 'Cache entry must exist before simulating expiry');

        // Force the entry to have already expired (well before "now").
        $cache[$reference]['expiresAt'] = time() - 3600;
        $prop->setValue($service, $cache);
    }

    // ── Data providers (seeded, >= 100 iterations) ───────────────────────────

    /**
     * Cases varying k (number of resolutions) and TTL (0 = no TTL, or a large
     * unexpired TTL). Every case must yield exactly one fetch.
     *
     * @return iterable<string, array{0:int,1:int,2:string,3:array<string,string|int>}>
     */
    public static function singleFetchProvider(): iterable
    {
        // Explicit boundary / representative cases.
        yield 'k1-no-ttl'        => [1, 0, 'rds/creds', self::secret(1)];
        yield 'k2-no-ttl'        => [2, 0, 'rds/creds', self::secret(2)];
        yield 'k10-no-ttl'       => [10, 0, 'rds/creds', self::secret(3)];
        yield 'k1-large-ttl'     => [1, 3600, 'rds/creds', self::secret(4)];
        yield 'k5-large-ttl'     => [5, 3600, 'rds/creds', self::secret(5)];
        yield 'k50-large-ttl'    => [50, 86400, 'arn:aws:secretsmanager:us-east-1:1:secret:x', self::secret(6)];
        yield 'k100-no-ttl'      => [100, 0, 'rds/creds', self::secret(7)];

        for ($seed = 1; $seed <= 110; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $k   = mt_rand(1, 40);
            $ttl = mt_rand(0, 1) === 1 ? 0 : mt_rand(60, 1_000_000);
            $ref = 'ref-' . $seed . '-' . mt_rand(1000, 9999);

            yield "single-seed-{$seed}" => [$k, $ttl, $ref, self::secret($seed)];
        }
    }

    /**
     * Cases for TTL expiry: warm reads before/after expiry and a positive TTL.
     *
     * @return iterable<string, array{0:int,1:int,2:int,3:string,4:array<string,string|int>}>
     */
    public static function ttlExpiryProvider(): iterable
    {
        yield 'ttl-basic'        => [0, 0, 60, 'rds/creds', self::secret(11)];
        yield 'ttl-warm-before'  => [3, 0, 300, 'rds/creds', self::secret(12)];
        yield 'ttl-warm-after'   => [0, 4, 300, 'rds/creds', self::secret(13)];
        yield 'ttl-warm-both'    => [5, 5, 3600, 'rds/creds', self::secret(14)];
        yield 'ttl-min'          => [1, 1, 1, 'rds/creds', self::secret(15)];

        for ($seed = 1; $seed <= 105; $seed++) {
            mt_srand($seed * 40503 & 0x7FFFFFFF);

            $before = mt_rand(0, 8);
            $after  = mt_rand(0, 8);
            $ttl    = mt_rand(1, 1_000_000);
            $ref    = 'ttl-ref-' . $seed;

            yield "ttl-seed-{$seed}" => [$before, $after, $ttl, $ref, self::secret($seed + 500)];
        }
    }

    /**
     * Cases with multiple distinct (and sometimes repeated) references.
     *
     * @return iterable<string, array{0:int,1:array<int,string>}>
     */
    public static function independentReferencesProvider(): iterable
    {
        yield 'two-refs'         => [3, ['a', 'b']];
        yield 'three-refs'       => [2, ['a', 'b', 'c']];
        yield 'repeated-refs'    => [4, ['a', 'a', 'b']]; // duplicates collapse to distinct
        yield 'single-ref'       => [5, ['only']];

        for ($seed = 1; $seed <= 105; $seed++) {
            mt_srand($seed * 2246822519 & 0x7FFFFFFF);

            $distinct = mt_rand(1, 6);
            $refs     = [];
            for ($i = 0; $i < $distinct; $i++) {
                $refs[] = 'grp' . $seed . '-ref' . $i;
            }
            // Sprinkle a duplicate to prove dedup by reference.
            if ($distinct > 1) {
                $refs[] = $refs[0];
            }
            shuffle($refs);

            $k = mt_rand(1, 20);
            yield "indep-seed-{$seed}" => [$k, $refs];
        }
    }

    /**
     * Deterministic secret material for a case seed. The value is arbitrary; the
     * `__fetch` marker is added by the counting fake, not here.
     *
     * @return array<string,string|int>
     */
    private static function secret(int $seed): array
    {
        return [
            'host'     => 'host-' . $seed . '.example',
            'username' => 'user_' . $seed,
            'password' => 'p@ss-' . $seed . '-ñ€',
            'dbname'   => 'db_' . $seed,
            'port'     => 3306 + ($seed % 100),
        ];
    }
}

/**
 * Counting fake SecretProvider: records how many times getSecret() is actually
 * invoked and tags each returned map with a monotonically increasing `__fetch`
 * marker so tests can distinguish a fresh fetch from a cache hit.
 *
 * @internal
 */
final class CountingSecretProvider implements SecretProvider
{
    /** Number of times getSecret() has actually been executed. */
    public int $calls = 0;

    /** @var array<string,string|int> */
    private array $value;

    /** @param array<string,string|int> $value */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function getSecret(string $reference): array
    {
        $this->calls++;

        $value            = $this->value;
        $value['__fetch'] = $this->calls; // marker proving which fetch produced this map

        return $value;
    }
}
