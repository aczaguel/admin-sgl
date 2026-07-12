<?php

namespace App\Libraries\Secrets;

use Config\Secrets as SecretsConfig;
use InvalidArgumentException;

/**
 * SecretsManagerService.
 *
 * Facade / factory for secret resolution. It resolves the active provider from
 * Config\Secrets (a single `provider` switch flips between AWS Secrets Manager
 * and plain local .env), owns the per-request in-memory Secret_Cache, and
 * exposes the single operation that maps a resolved RDS secret into a
 * Config\Database connection group.
 *
 * Registered as a shared service (`service('secrets')`) so the provider and the
 * AWS SDK client are built at most once per request. Mirrors FileStorageService.
 *
 * NOTE (task sequencing): this class is being built incrementally.
 *   - Task 6.1 (this change): constructor + provider selection (makeProvider).
 *   - Task 6.2: per-request cached getSecret() with optional TTL.
 *   - Task 6.3: resolveRdsInto()/assertConfigured().
 * The in-memory cache property is declared now so tasks 6.2/6.3 extend cleanly.
 *
 * @see SecretProvider
 */
final class SecretsManagerService implements SecretProvider
{
    private SecretProvider $provider;

    private SecretsConfig $config;

    /**
     * Per-request in-memory Secret_Cache, keyed by Secret_Reference.
     *
     * Populated by the cached getSecret() implemented in task 6.2; declared here
     * so later tasks extend this class cleanly. Never serialized to disk.
     *
     * @var array<string, array{value: array<string,string|int>, expiresAt: int|null}>
     */
    private array $cache = [];

    public function __construct(SecretsConfig $config)
    {
        $this->config   = $config;
        $this->provider = $this->makeProvider($config); // throws on invalid provider flag
    }

    /**
     * Select the active provider by the configured flag, failing closed on an
     * invalid value (Req 1.2, 1.3, 1.4, 1.5).
     *
     * The `env` default is already applied by Config\Secrets when the flag is
     * absent/empty (Req 1.4, 10.1), so an empty flag never reaches here as an
     * error. Any value other than `aws` or `env` is rejected with a descriptive
     * error that names the offending value and the accepted values (Req 1.5).
     *
     * @throws InvalidArgumentException when the provider flag is not aws or env.
     */
    private function makeProvider(SecretsConfig $c): SecretProvider
    {
        return match ($c->provider) {
            'aws'   => new AwsSecretProvider($c),
            'env'   => new EnvSecretProvider($c),
            default => throw new InvalidArgumentException(
                "Invalid SECRETS_PROVIDER '{$c->provider}'. Accepted values: aws, env."
            ),
        };
    }

    /**
     * Resolve the secret identified by $reference, cached for the request.
     *
     * Task 6.2 — per-request caching with optional TTL (Req 4.1, 4.2, 4.3, 4.4,
     * 8.1). At most one underlying provider fetch runs per reference per request
     * while the cached entry is unexpired:
     *   - Cache hit: when an entry exists and its `expiresAt` is null (valid for
     *     the whole request, Req 4.4) or still in the future (within the
     *     configured TTL, Req 4.3), the cached value is returned WITHOUT calling
     *     the provider (Req 4.2).
     *   - Cache miss (or expired): the active provider is queried (may throw —
     *     fail closed), and the result is stored keyed by reference with
     *     `expiresAt = ttl > 0 ? now + ttl : null` (Req 4.1) before being
     *     returned.
     *
     * The resolved value lives only in this in-memory cache for the lifetime of
     * the request and is never serialized to disk (Req 8.1).
     *
     * @param string $reference Secret name or ARN. Never a value.
     *
     * @return array<string,string|int> Parsed secret material (in memory only).
     *
     * @throws SecretResolutionException on unreachable, missing, malformed, or
     *         incomplete secret (fail closed).
     */
    public function getSecret(string $reference): array
    {
        $now = time();

        if (isset($this->cache[$reference])) {
            $entry = $this->cache[$reference];
            if ($entry['expiresAt'] === null || $entry['expiresAt'] > $now) {
                return $entry['value']; // cache hit — no provider call (Req 4.2)
            }
        }

        $value   = $this->provider->getSecret($reference); // may throw (fail closed)
        $expires = $this->config->cacheTtl > 0 ? $now + $this->config->cacheTtl : null;

        $this->cache[$reference] = ['value' => $value, 'expiresAt' => $expires];

        return $value;
    }

    /**
     * Resolve the RDS secret and populate a Config\Database connection group in
     * place (Req 3.2, 3.5, 3.6, 7.3).
     *
     * Task 6.3. The method:
     *   1. Calls assertConfigured() to require a non-empty reference and region
     *      when the provider is `aws` (Req 2.3, 2.4).
     *   2. Resolves the RDS secret through the cached getSecret() (may throw —
     *      fail closed, Req 5.3).
     *   3. Maps the returned fields to the CI4 connection-group keys, defaulting
     *      the port to 3306 when absent (Req 3.5).
     *
     * FAIL CLOSED (Req 3.6, 5.3): every value is computed into local variables
     * BEFORE any assignment to $group. Because assertConfigured() and
     * getSecret() are the only operations that can throw, and they both run
     * before the first write to $group, a failure at any point leaves $group
     * completely unmodified — the application never opens a connection with
     * partial credentials.
     *
     * Idempotent: calling twice within a request re-uses the cached secret and
     * yields the same $group.
     *
     * @param array<string,mixed> $group Reference to a Config\Database
     *                                    connection group (typically `default`).
     *
     * @throws \InvalidArgumentException when provider=aws and the reference or
     *         region is missing (Req 2.3, 2.4).
     * @throws SecretResolutionException on unreachable, missing, malformed, or
     *         incomplete secret (fail closed, Req 5.3).
     */
    public function resolveRdsInto(array &$group): void
    {
        $this->assertConfigured(); // Req 2.3, 2.4, 1.5

        // Resolve + map into locals FIRST; a throw here leaves $group untouched.
        $map = $this->getSecret($this->config->rdsReference); // may throw (fail closed)

        $hostname = (string) $map['host'];
        $username = (string) $map['username'];
        $password = (string) $map['password'];
        $database = (string) $map['dbname'];
        $port     = (int) ($map['port'] ?? 3306); // default MySQL port (Req 3.5)

        // All values obtained successfully — commit them to the group in one go.
        $group['hostname'] = $hostname;
        $group['username'] = $username;
        $group['password'] = $password;
        $group['database'] = $database;
        $group['port']     = $port;
    }

    /**
     * Require the settings the AWS provider needs before any resolution runs
     * (Req 2.3, 2.4).
     *
     * When the provider is `aws`, both the Secret_Reference and the AWS region
     * must be non-empty; otherwise resolution is rejected with a descriptive
     * error that names the missing setting. For the `env` provider there is
     * nothing to assert (no remote reference or region is required).
     *
     * @throws \InvalidArgumentException when provider=aws and rdsReference or
     *         region is empty.
     */
    private function assertConfigured(): void
    {
        if ($this->config->provider === 'aws') {
            if (trim($this->config->rdsReference) === '') {
                throw new InvalidArgumentException(
                    'SECRETS_RDS_REFERENCE is required when SECRETS_PROVIDER=aws.'
                );
            }

            if (trim($this->config->region) === '') {
                throw new InvalidArgumentException(
                    'SECRETS_REGION is required when SECRETS_PROVIDER=aws.'
                );
            }
        }
    }
}
