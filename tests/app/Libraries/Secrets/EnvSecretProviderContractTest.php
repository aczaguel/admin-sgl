<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\EnvSecretProvider;
use App\Libraries\Secrets\SecretProvider;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;

/**
 * Contract tests for EnvSecretProvider (task 3.2).
 *
 * EnvSecretProvider reproduces today's plain-`.env` behavior: it reads the
 * `database.default.*` keys via CI4 env() and maps them to the canonical keys
 * (host, dbname, username, password, and optional port), ignoring the
 * $reference argument, and it never contacts AWS.
 *
 * These tests assert:
 *   - The values returned equal exactly the configured `database.default.*`
 *     values (Req 7.1, Req 7.3).
 *   - The $reference argument is ignored — env mode always resolves the local
 *     keys regardless of the reference (Req 7.1).
 *   - No AWS SDK call is attempted. This is proven structurally: the provider
 *     source references no `Aws\` type and no SecretsManagerClient, so it is
 *     impossible for it to reach AWS, and functionally: resolution completes
 *     fully offline with no network configuration (Req 7.2).
 *
 * @internal
 */
final class EnvSecretProviderContractTest extends CIUnitTestCase
{
    /** Absolute path of the production EnvSecretProvider source. */
    private const ENV_PROVIDER_REL = 'app/Libraries/Secrets/EnvSecretProvider.php';

    /** The `database.default.*` env keys this provider reads. */
    private const DB_ENV_KEYS = [
        'database.default.hostname',
        'database.default.database',
        'database.default.username',
        'database.default.password',
        'database.default.port',
    ];

    /** Snapshot of the env keys so each test starts and ends clean. */
    private array $envSnapshot = [];

    private EnvSecretProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot and fully clear the DB env keys so each test controls them.
        // CI4 env() resolves from $_ENV, then $_SERVER, then getenv(), so all
        // three sources must be cleared/restored for real isolation from the
        // container's loaded `.env`.
        foreach (self::DB_ENV_KEYS as $key) {
            $existing = getenv($key);
            $this->envSnapshot[$key] = [
                'env'    => $_ENV[$key] ?? null,
                'server' => $_SERVER[$key] ?? null,
                'getenv' => $existing === false ? null : $existing,
            ];
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        $this->provider = new EnvSecretProvider(new SecretsConfig());
    }

    protected function tearDown(): void
    {
        // Restore the original values across all three sources.
        foreach (self::DB_ENV_KEYS as $key) {
            $snap = $this->envSnapshot[$key];

            if ($snap['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $snap['env'];
            }

            if ($snap['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $snap['server'];
            }

            if ($snap['getenv'] === null) {
                putenv($key);
            } else {
                putenv($key . '=' . $snap['getenv']);
            }
        }
        $this->envSnapshot = [];

        parent::tearDown();
    }

    /** Set a `database.default.*` env value visible to CI4 env(). */
    private function setDbEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
    }

    // ---------------------------------------------------------------------
    // Values returned equal the configured database.default.* values
    // ---------------------------------------------------------------------

    public function testReturnsExactlyTheConfiguredDatabaseDefaultValues(): void
    {
        $this->setDbEnv('database.default.hostname', 'rds.example.internal');
        $this->setDbEnv('database.default.database', 'procedures');
        $this->setDbEnv('database.default.username', 'sgl_app');
        $this->setDbEnv('database.default.password', 'p@ss w0rd-#1!');
        $this->setDbEnv('database.default.port', '3307');

        $map = $this->provider->getSecret('ignored-reference');

        $this->assertSame([
            'host'     => 'rds.example.internal',
            'dbname'   => 'procedures',
            'username' => 'sgl_app',
            'password' => 'p@ss w0rd-#1!',
            'port'     => 3307,
        ], $map, 'Returned map must equal the configured database.default.* values with canonical keys');
    }

    public function testPortIsCastToIntWhenPresent(): void
    {
        $this->setDbEnv('database.default.hostname', 'localhost');
        $this->setDbEnv('database.default.database', 'sgl');
        $this->setDbEnv('database.default.username', 'root');
        $this->setDbEnv('database.default.password', 'secret');
        $this->setDbEnv('database.default.port', '3306');

        $map = $this->provider->getSecret('');

        $this->assertArrayHasKey('port', $map);
        $this->assertSame(3306, $map['port'], 'port must be cast to int');
    }

    public function testOmitsPortWhenNotConfigured(): void
    {
        $this->setDbEnv('database.default.hostname', 'localhost');
        $this->setDbEnv('database.default.database', 'sgl');
        $this->setDbEnv('database.default.username', 'root');
        $this->setDbEnv('database.default.password', 'secret');
        // No port configured.

        $map = $this->provider->getSecret('');

        $this->assertArrayNotHasKey('port', $map, 'port must be absent when database.default.port is not set');
        $this->assertSame([
            'host'     => 'localhost',
            'dbname'   => 'sgl',
            'username' => 'root',
            'password' => 'secret',
        ], $map);
    }

    public function testAbsentKeysAreOmittedSoConfigDatabaseDefaultsApply(): void
    {
        // Only hostname is provided; every other key is unset.
        $this->setDbEnv('database.default.hostname', 'only-host');

        $map = $this->provider->getSecret('');

        // Absent keys are simply not returned (the caller then keeps
        // Config\Database's own defaults) — Req 7.3 / 10.1.
        $this->assertSame(['host' => 'only-host'], $map);
    }

    public function testEmptyValuesAreTreatedAsAbsent(): void
    {
        $this->setDbEnv('database.default.hostname', 'host');
        $this->setDbEnv('database.default.database', 'db');
        $this->setDbEnv('database.default.username', 'user');
        $this->setDbEnv('database.default.password', 'pw');
        // 'empty' is CI4's sentinel that env() resolves to an empty string.
        $this->setDbEnv('database.default.port', 'empty');

        $map = $this->provider->getSecret('');

        $this->assertArrayNotHasKey('port', $map, 'An empty port must be omitted, not returned as 0');
    }

    // ---------------------------------------------------------------------
    // The $reference argument is ignored (Req 7.1)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideReferences
     */
    public function testReferenceArgumentIsIgnored(string $reference): void
    {
        $this->setDbEnv('database.default.hostname', 'host');
        $this->setDbEnv('database.default.database', 'db');
        $this->setDbEnv('database.default.username', 'user');
        $this->setDbEnv('database.default.password', 'pw');

        $map = $this->provider->getSecret($reference);

        $this->assertSame([
            'host'     => 'host',
            'dbname'   => 'db',
            'username' => 'user',
            'password' => 'pw',
        ], $map, "Result must be identical regardless of the reference: '{$reference}'");
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideReferences(): array
    {
        return [
            'empty reference'      => [''],
            'plain name'           => ['prod/rds/credentials'],
            'full arn'             => ['arn:aws:secretsmanager:us-east-1:123456789012:secret:prod/rds-AbCdEf'],
            'nonexistent name'     => ['does-not-exist'],
        ];
    }

    // ---------------------------------------------------------------------
    // No AWS SDK call is attempted (Req 7.2)
    // ---------------------------------------------------------------------

    public function testEnvSecretProviderIsASecretProvider(): void
    {
        $this->assertInstanceOf(
            SecretProvider::class,
            $this->provider,
            'EnvSecretProvider must satisfy the SecretProvider contract'
        );
    }

    /**
     * The provider source must reference no AWS SDK type whatsoever, so an AWS
     * call is structurally impossible from env mode (Req 7.2).
     */
    public function testEnvProviderSourceReferencesNoAwsSdk(): void
    {
        $path = ROOTPATH . self::ENV_PROVIDER_REL;
        $this->assertFileExists($path, 'EnvSecretProvider production source should exist');

        $source = (string) file_get_contents($path);

        // Only real AWS SDK usage is forbidden. A `use Aws\...` import, a
        // SecretsManagerClient reference, or a getSecretValue() call would each
        // mean the provider can reach AWS. (A prose mention of a sibling class
        // in a comment is not an AWS call and is intentionally not scanned.)
        $forbidden = [
            'Aws\\ namespace use'  => '/\buse\s+Aws\\\\/',
            'SecretsManagerClient' => '/SecretsManagerClient/',
            'getSecretValue call'  => '/->\s*getSecretValue\s*\(/',
        ];

        foreach ($forbidden as $label => $regex) {
            $this->assertDoesNotMatchRegularExpression(
                $regex,
                $source,
                "EnvSecretProvider must not reference [{$label}] — env mode never contacts AWS (Req 7.2)."
            );
        }
    }

    /**
     * Resolution completes fully offline: no region, reference, or AWS client
     * is needed, and the call returns the local values without error — proving
     * no AWS SDK call is attempted (Req 7.2).
     */
    public function testResolutionCompletesOfflineWithoutAwsConfiguration(): void
    {
        // A Config\Secrets with no AWS reference/region at all (env-mode defaults).
        $config = new SecretsConfig();
        $config->rdsReference = '';
        $config->region       = '';

        $provider = new EnvSecretProvider($config);

        $this->setDbEnv('database.default.hostname', 'offline-host');
        $this->setDbEnv('database.default.database', 'offline-db');
        $this->setDbEnv('database.default.username', 'offline-user');
        $this->setDbEnv('database.default.password', 'offline-pw');

        $map = $provider->getSecret('any');

        $this->assertSame([
            'host'     => 'offline-host',
            'dbname'   => 'offline-db',
            'username' => 'offline-user',
            'password' => 'offline-pw',
        ], $map, 'env resolution must succeed offline with no AWS configuration');
    }
}
