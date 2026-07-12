<?php

namespace Tests\App\Integration;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretsManagerService;
use Aws\History;
use Aws\Middleware;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database as DatabaseConfig;
use Config\Secrets as SecretsConfig;
use ReflectionClass;

/**
 * Integration test — bootstrap resolution with the provider toggled (task 10.1).
 *
 * Validates: Requirements 3.2, 3.6, 7.3, 10.2, 10.3.
 *
 * Exercises the exact resolution the bootstrap performs — the operation
 * Config\Database::__construct runs (task 7.2) to populate the `default`
 * connection group before the first database connection is opened:
 *
 *     service('secrets')->resolveRdsInto($this->default)
 *
 * ── Why the resolution is invoked directly ──────────────────────────────
 * Config\Database::__construct short-circuits when ENVIRONMENT === 'testing'
 * (it sets defaultGroup='tests' and returns WITHOUT resolving secrets, so the
 * suite never contacts AWS). Under the normal PHPUnit environment the
 * resolution branch is therefore never reached. To integration-test that
 * branch we take a real Config\Database `default` group shape and apply
 * SecretsManagerService::resolveRdsInto($group) to it directly — precisely the
 * call the non-testing constructor makes — under each provider mode:
 *
 *   env mode (Req 7.3, 10.3): database.default.* .env values populate the group.
 *   aws mode (Req 3.2, 10.2): a MockHandler-backed AwsSecretProvider returns a
 *                             valid RDS JSON blob that populates the group,
 *                             with no live AWS call.
 *
 * ── Web front-controller vs `spark` CLI path (Req 3.6, 10.2, 10.3) ──────────
 * Both entry points instantiate the SAME Config\Database class and call the
 * SAME service('secrets')->resolveRdsInto(). The resolution logic reads only
 * Config\Database + Config\Secrets + the active provider — it has NO branch on
 * PHP_SAPI or the entry point. This test documents and demonstrates that
 * path-independence by resolving the identical group twice (labelled web and
 * cli) for each provider and asserting the populated groups are byte-for-byte
 * identical, and by asserting the constructor's testing short-circuit converges
 * on the same class (defaultGroup='tests', resolution skipped) on both paths.
 *
 * @internal
 */
final class SecretsBootstrapIntegrationTest extends CIUnitTestCase
{
    /** A representative RDS secret reference (name/ARN). Never a value. */
    private const REFERENCE = 'sgl/prod/rds';

    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    /** The five connection-group keys resolveRdsInto must fully populate. */
    private const RDS_KEYS = ['hostname', 'username', 'password', 'database', 'port'];

    /**
     * Snapshot of every environment key this test mutates, keyed by name, so it
     * can be restored exactly in tearDown across all three sources CI4 env()
     * reads ($_ENV, $_SERVER, getenv).
     *
     * @var array<string, array{env: string|null, server: string|null, getenv: string|false}>
     */
    private array $envSnapshot = [];

    protected function tearDown(): void
    {
        foreach ($this->envSnapshot as $key => $prior) {
            if ($prior['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $prior['env'];
            }

            if ($prior['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $prior['server'];
            }

            if ($prior['getenv'] === false) {
                putenv($key);
            } else {
                putenv($key . '=' . $prior['getenv']);
            }
        }
        $this->envSnapshot = [];

        parent::tearDown();
    }

    // =====================================================================
    // env provider leg (Req 7.3, 10.3)
    // =====================================================================

    /**
     * With SECRETS_PROVIDER=env, resolveRdsInto populates a real Config\Database
     * `default` group from the plain database.default.* .env values — exactly
     * what the application uses today (Req 7.3, 10.3).
     */
    public function testEnvProviderPopulatesDefaultGroupFromDotEnv(): void
    {
        $this->setEnv('database.default.hostname', 'db.local.internal');
        $this->setEnv('database.default.username', 'sgl_local');
        $this->setEnv('database.default.password', 'local-p@ss#1');
        $this->setEnv('database.default.database', 'procedures_local');
        $this->setEnv('database.default.port', '3310');

        $config           = new SecretsConfig();
        $config->provider = 'env';

        $group = $this->freshDefaultGroup();
        (new SecretsManagerService($config))->resolveRdsInto($group);

        $this->assertSame('db.local.internal', $group['hostname']);
        $this->assertSame('sgl_local', $group['username']);
        $this->assertSame('local-p@ss#1', $group['password']);
        $this->assertSame('procedures_local', $group['database']);
        $this->assertSame(3310, $group['port']);

        $this->assertGroupFullyPopulated($group);
    }

    // =====================================================================
    // aws provider leg (Req 3.2, 10.2)
    // =====================================================================

    /**
     * With SECRETS_PROVIDER=aws (MockHandler-backed, no live AWS), resolveRdsInto
     * fetches the RDS secret, parses the JSON blob, and fully populates a real
     * Config\Database `default` group before the first connection (Req 3.2, 10.2).
     */
    public function testAwsProviderPopulatesDefaultGroupFromSecretsManager(): void
    {
        $history = new History();
        $mock    = new MockHandler();
        $mock->append(new Result(['SecretString' => $this->validBlob()]));

        $service = $this->makeAwsService($mock, $history);

        $group = $this->freshDefaultGroup();
        $service->resolveRdsInto($group);

        $this->assertSame('sgl-prod.abcdef.us-east-1.rds.amazonaws.com', $group['hostname']);
        $this->assertSame('sgl_app', $group['username']);
        $this->assertSame('s3cr3t-p@ss', $group['password']);
        $this->assertSame('procedures', $group['database']);
        $this->assertSame(3306, $group['port']);

        $this->assertGroupFullyPopulated($group);

        // The credentials were resolved entirely offline via the mock handler.
        $this->assertCount(1, $history, 'the RDS secret must be fetched offline (no live AWS call)');
    }

    /**
     * The aws leg defaults the port to 3306 when the secret omits it (Req 3.2).
     */
    public function testAwsProviderDefaultsPortWhenOmitted(): void
    {
        $mock = new MockHandler();
        $mock->append(new Result(['SecretString' => json_encode([
            'username' => 'sgl_app',
            'password' => 's3cr3t-p@ss',
            'host'     => 'sgl-prod.abcdef.us-east-1.rds.amazonaws.com',
            'dbname'   => 'procedures',
            // no port
        ])]));

        $group = $this->freshDefaultGroup();
        $this->makeAwsService($mock)->resolveRdsInto($group);

        $this->assertSame(3306, $group['port'], 'omitted port must default to the MySQL default 3306');
        $this->assertGroupFullyPopulated($group);
    }

    // =====================================================================
    // Web front-controller vs spark CLI path (Req 3.6, 10.2, 10.3)
    // =====================================================================

    /**
     * The resolution logic is entry-point independent: the web front controller
     * and the `spark` CLI both instantiate the same Config\Database class and
     * call the same resolveRdsInto(), with no PHP_SAPI branch anywhere in the
     * path. Resolving the identical group twice (labelled web and cli) yields
     * byte-for-byte identical fully-populated groups under BOTH providers.
     */
    public function testResolutionIsPathIndependentAcrossWebAndCli(): void
    {
        // --- env provider: web and cli legs must converge identically --------
        $this->setEnv('database.default.hostname', 'db.local.internal');
        $this->setEnv('database.default.username', 'sgl_local');
        $this->setEnv('database.default.password', 'local-p@ss#1');
        $this->setEnv('database.default.database', 'procedures_local');
        $this->setEnv('database.default.port', '3310');

        $envConfig           = new SecretsConfig();
        $envConfig->provider = 'env';

        $envWeb = $this->freshDefaultGroup();
        (new SecretsManagerService($envConfig))->resolveRdsInto($envWeb); // web front controller

        $envCli = $this->freshDefaultGroup();
        (new SecretsManagerService($envConfig))->resolveRdsInto($envCli); // spark CLI

        $this->assertGroupFullyPopulated($envWeb);
        $this->assertGroupFullyPopulated($envCli);
        $this->assertSame(
            $envWeb,
            $envCli,
            'env resolution must be identical on the web and spark CLI paths'
        );

        // --- aws provider: web and cli legs must converge identically --------
        $awsWeb = $this->freshDefaultGroup();
        $webMock = new MockHandler();
        $webMock->append(new Result(['SecretString' => $this->validBlob()]));
        $this->makeAwsService($webMock)->resolveRdsInto($awsWeb); // web front controller

        $awsCli = $this->freshDefaultGroup();
        $cliMock = new MockHandler();
        $cliMock->append(new Result(['SecretString' => $this->validBlob()]));
        $this->makeAwsService($cliMock)->resolveRdsInto($awsCli); // spark CLI

        $this->assertGroupFullyPopulated($awsWeb);
        $this->assertGroupFullyPopulated($awsCli);
        $this->assertSame(
            $awsWeb,
            $awsCli,
            'aws resolution must be identical on the web and spark CLI paths'
        );
    }

    /**
     * Both entry points converge on the SAME Config\Database class, whose
     * constructor short-circuits under the testing environment: defaultGroup is
     * set to 'tests' and secret resolution is skipped entirely, so the pristine
     * hardcoded credentials are left untouched (no AWS contact during tests).
     */
    public function testDatabaseConstructorTestingShortCircuitSkipsResolution(): void
    {
        $this->assertSame('testing', ENVIRONMENT, 'the suite must run in the testing environment');

        $db = new DatabaseConfig();

        // The definitive, observable proof that the constructor's testing
        // short-circuit ran and secret resolution (task 7.2) was skipped: the
        // default group is switched to the in-memory SQLite `tests` group. Had
        // resolution run and failed under `aws` with no configured reference it
        // would have thrown; had it run under `env` it would still not flip the
        // default group to 'tests'. This convergence is identical on the web and
        // spark CLI paths because both instantiate this same class.
        $this->assertSame('tests', $db->defaultGroup, 'testing must select the in-memory tests group');

        // The tests group targets in-memory SQLite, never a resolved RDS host —
        // confirming no secret-managed credentials were applied under testing.
        $this->assertSame(':memory:', $db->tests['database'], 'the testing group must use in-memory SQLite');
        $this->assertSame('SQLite3', $db->tests['DBDriver'], 'the testing group must use the SQLite driver');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * A real Config\Database `default` connection-group shape. Under the testing
     * environment the constructor short-circuits (defaultGroup='tests') WITHOUT
     * resolving secrets, so $default is the pristine connection-group array the
     * bootstrap would hand to resolveRdsInto() on both the web and CLI paths.
     *
     * @return array<string, mixed>
     */
    private function freshDefaultGroup(): array
    {
        return (new DatabaseConfig())->default;
    }

    /**
     * Build a SecretsManagerService in `aws` mode whose active provider is an
     * AwsSecretProvider backed by the given MockHandler-driven client, so the
     * whole resolution runs offline (no live AWS call). Dummy static credentials
     * keep the SDK from reaching the EC2 metadata endpoint in tests.
     */
    private function makeAwsService(MockHandler $mock, ?History $history = null): SecretsManagerService
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = self::REFERENCE;
        $config->region       = self::REGION;

        // Constructs a real AwsSecretProvider; we replace it with a mock-backed
        // one so the aws leg is exercised entirely offline.
        $service = new SecretsManagerService($config);

        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);

        if ($history !== null) {
            $client->getHandlerList()->appendSign(Middleware::history($history));
        }

        $awsProvider = new AwsSecretProvider($config, $client);

        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $awsProvider);

        return $service;
    }

    /** A well-formed RDS secret JSON blob. */
    private function validBlob(): string
    {
        return json_encode([
            'username' => 'sgl_app',
            'password' => 's3cr3t-p@ss',
            'host'     => 'sgl-prod.abcdef.us-east-1.rds.amazonaws.com',
            'port'     => 3306,
            'dbname'   => 'procedures',
        ]);
    }

    /**
     * Assert the group holds all five RDS connection-group keys, each populated
     * with a non-empty value — i.e. the group is ready for the first query.
     *
     * @param array<string, mixed> $group
     */
    private function assertGroupFullyPopulated(array $group): void
    {
        foreach (self::RDS_KEYS as $key) {
            $this->assertArrayHasKey($key, $group, "the {$key} key must be populated before the first query");
            $this->assertNotSame('', (string) $group[$key], "the {$key} key must be non-empty");
        }

        $this->assertIsInt($group['port'], 'the resolved port must be an integer');
        $this->assertGreaterThan(0, $group['port'], 'the resolved port must be a positive integer');
    }

    /**
     * Set an environment key across all three sources CI4 env() reads, recording
     * its prior state once so tearDown can restore it exactly.
     */
    private function setEnv(string $key, string $value): void
    {
        if (! array_key_exists($key, $this->envSnapshot)) {
            $this->envSnapshot[$key] = [
                'env'    => $_ENV[$key] ?? null,
                'server' => $_SERVER[$key] ?? null,
                'getenv' => getenv($key),
            ];
        }

        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}
