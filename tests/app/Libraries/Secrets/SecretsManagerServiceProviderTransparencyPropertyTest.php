<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretsManagerService;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use ReflectionClass;

/**
 * Property 1: Provider transparency and selection.
 *
 * **Validates: Requirements 1.2, 1.3, 1.4, 1.6, 10.1, 10.2, 10.3**
 *
 * The single caller code path — {@see SecretsManagerService::resolveRdsInto()}
 * invoked through the one {@see SecretsManagerService} interface — must yield a
 * FULLY POPULATED connection group (hostname, username, password, database,
 * port all set) regardless of which backend actually supplies the credentials:
 *
 *   - provider = `env`      → resolves via {@see \App\Libraries\Secrets\EnvSecretProvider}
 *                             from the plain `database.default.*` env values
 *                             (Req 1.3, 10.3).
 *   - provider = `aws`      → resolves via a MockHandler-backed
 *                             {@see AwsSecretProvider} from a valid RDS JSON blob,
 *                             with no live AWS call (Req 1.2, 10.2).
 *   - flag absent / empty   → {@see SecretsConfig} defaults `provider` to `env`,
 *                             so resolution behaves exactly like the current
 *                             plain-`.env` system (Req 1.4, 10.1).
 *
 * The transparency assertion is the crux: for EVERY mode the caller executes
 * the identical `$service->resolveRdsInto($group)` statement and always ends up
 * with the same five, fully-populated group keys — the caller never learns nor
 * cares which provider ran (Req 1.6).
 *
 * Cases come from seeded PHPUnit data-provider generators (>= 100 iterations)
 * that vary the credential values (ASCII, symbols, unicode) and cycle the
 * provider mode, following the tests/ convention used by the sibling Secrets
 * property tests. The AWS provider is exercised entirely offline via the AWS
 * SDK {@see MockHandler} injected through the service's private `provider`
 * (same reflection pattern as {@see SecretsManagerServiceFailClosedPropertyTest}
 * and {@see AwsSecretProviderContractTest}), so no live AWS call ever occurs.
 *
 * @internal
 */
final class SecretsManagerServiceProviderTransparencyPropertyTest extends CIUnitTestCase
{
    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    /** The plain-`.env` DB keys the env provider reads (and this test drives). */
    private const ENV_KEYS = [
        'database.default.hostname',
        'database.default.database',
        'database.default.username',
        'database.default.password',
        'database.default.port',
    ];

    /**
     * Values env() coerces to a non-string (true/false/''/null) — the generator
     * must never emit these verbatim for env-backed modes, since they are a
     * documented env() quirk rather than a property of the service.
     *
     * @var array<int, string>
     */
    private const ENV_RESERVED = ['true', 'false', 'empty', 'null'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearSecretsAndDbEnv();
    }

    protected function tearDown(): void
    {
        $this->clearSecretsAndDbEnv();
        parent::tearDown();
    }

    /**
     * Property 1: whichever provider mode is active, the same caller code path
     * populates the connection group completely with the backend-appropriate
     * credentials.
     *
     * **Validates: Requirements 1.2, 1.3, 1.4, 1.6, 10.1, 10.2, 10.3**
     *
     * @dataProvider provideProviderModesAndCredentials
     *
     * @param 'env'|'aws'|'default' $mode     Which provider mode to exercise.
     * @param string                $host     Generated hostname.
     * @param string                $username Generated username.
     * @param string                $password Generated password.
     * @param string                $dbname   Generated database name.
     * @param int|null              $port     Generated port, or null (defaults 3306).
     */
    public function testResolvesFullyThroughSingleInterfaceRegardlessOfProvider(
        string $mode,
        string $host,
        string $username,
        string $password,
        string $dbname,
        ?int $port
    ): void {
        $expected = [
            'hostname' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $dbname,
            'port'     => $port ?? 3306, // default MySQL port when omitted (Req 3.5)
        ];

        $service = $this->makeServiceForMode($mode, $host, $username, $password, $dbname, $port);

        // THE single caller code path — identical for every backend (Req 1.6).
        // Start from an EMPTY group to prove full population, not partial reuse.
        $group = [];
        $service->resolveRdsInto($group);

        $context = sprintf(
            'mode=%s host=%s user=%s db=%s port=%s',
            $mode,
            $host,
            $username,
            $dbname,
            $port === null ? '(absent)' : (string) $port
        );

        // Transparency: all five keys are present and fully populated.
        foreach (['hostname', 'username', 'password', 'database', 'port'] as $key) {
            $this->assertArrayHasKey(
                $key,
                $group,
                "Group key '{$key}' must be populated through the single interface. " . $context
            );
        }

        $this->assertSame(
            $expected['hostname'],
            $group['hostname'],
            'hostname must equal the backend-supplied host. ' . $context
        );
        $this->assertSame(
            $expected['username'],
            $group['username'],
            'username must equal the backend-supplied username. ' . $context
        );
        $this->assertSame(
            $expected['password'],
            $group['password'],
            'password must equal the backend-supplied password. ' . $context
        );
        $this->assertSame(
            $expected['database'],
            $group['database'],
            'database must equal the backend-supplied dbname. ' . $context
        );
        $this->assertSame(
            $expected['port'],
            $group['port'],
            'port must equal the given port or default to 3306. ' . $context
        );
    }

    /**
     * The absent/empty provider flag must resolve to the env provider so that a
     * deployment with no SECRETS_PROVIDER behaves exactly like today's system
     * (Req 1.4, 10.1). This asserts the defaulting explicitly, independent of a
     * successful resolution.
     */
    public function testAbsentFlagDefaultsToEnvProvider(): void
    {
        // No SECRETS_PROVIDER set (cleared in setUp).
        $config = new SecretsConfig();
        $this->assertSame('env', $config->provider, 'absent SECRETS_PROVIDER must default to env');

        $this->putEnv('database.default.hostname', 'legacy-host');
        $this->putEnv('database.default.database', 'legacy_db');
        $this->putEnv('database.default.username', 'legacy_user');
        $this->putEnv('database.default.password', 'legacy-pass');

        $service = new SecretsManagerService($config);

        $group = [];
        $service->resolveRdsInto($group);

        $this->assertSame('legacy-host', $group['hostname']);
        $this->assertSame('legacy_user', $group['username']);
        $this->assertSame('legacy-pass', $group['password']);
        $this->assertSame('legacy_db', $group['database']);
        $this->assertSame(3306, $group['port']);
    }

    // =====================================================================
    // Service builders per mode
    // =====================================================================

    /**
     * Build a SecretsManagerService configured for the given mode, seeded with
     * the supplied credentials so that resolving yields exactly them.
     *
     * For env/default modes the credentials are written to the plain-`.env` DB
     * keys the EnvSecretProvider reads. For aws mode a MockHandler-backed
     * AwsSecretProvider returning the equivalent JSON blob is injected via
     * reflection so no live AWS call occurs.
     */
    private function makeServiceForMode(
        string $mode,
        string $host,
        string $username,
        string $password,
        string $dbname,
        ?int $port
    ): SecretsManagerService {
        switch ($mode) {
            case 'aws':
                $config               = new SecretsConfig();
                $config->provider     = 'aws';
                $config->rdsReference = 'sgl/prod/rds';
                $config->region       = self::REGION;

                $service = new SecretsManagerService($config);
                $this->injectMockAwsProvider($service, $config, $host, $username, $password, $dbname, $port);

                return $service;

            case 'default':
                // Leave SECRETS_PROVIDER unset so the config defaults to env
                // (Req 1.4, 10.1); write the plain-.env DB values.
                $this->seedDbEnv($host, $username, $password, $dbname, $port);
                $config = new SecretsConfig();
                $this->assertSame('env', $config->provider, 'absent flag must default to env');

                return new SecretsManagerService($config);

            case 'env':
            default:
                $this->seedDbEnv($host, $username, $password, $dbname, $port);
                $config           = new SecretsConfig();
                $config->provider = 'env';

                return new SecretsManagerService($config);
        }
    }

    /**
     * Inject a MockHandler-backed AwsSecretProvider into the service's private
     * `provider` so resolution returns the given credentials as a valid RDS JSON
     * blob, entirely offline (no live AWS call).
     */
    private function injectMockAwsProvider(
        SecretsManagerService $service,
        SecretsConfig $config,
        string $host,
        string $username,
        string $password,
        string $dbname,
        ?int $port
    ): void {
        $blob = [
            'host'     => $host,
            'username' => $username,
            'password' => $password,
            'dbname'   => $dbname,
        ];
        if ($port !== null) {
            $blob['port'] = $port;
        }

        $mock = new MockHandler();
        $mock->append(new Result([
            'SecretString' => json_encode($blob, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '@metadata'    => ['statusCode' => 200],
        ]));

        // Dummy static credentials so SigV4 signing stays offline (no IAM
        // Instance-Profile / metadata lookup); the MockHandler replaces the
        // network handler entirely.
        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);

        $mockProvider = new AwsSecretProvider($config, $client);

        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $mockProvider);
    }

    // =====================================================================
    // Data provider (seeded, >= 100 iterations)
    // =====================================================================

    /**
     * Seeded generator cycling the provider mode across varied credential sets
     * (ASCII, symbols, unicode) with the port present about half the time.
     * >= 100 iterations plus explicit per-mode edge cases.
     *
     * @return iterable<string, array{0:string,1:string,2:string,3:string,4:string,5:int|null}>
     */
    public static function provideProviderModesAndCredentials(): iterable
    {
        // Explicit representative cases per mode.
        yield 'env-basic'      => ['env', 'db.internal', 'sgl_app', 'p@ssw0rd', 'procedures', 3306];
        yield 'env-no-port'    => ['env', 'db.internal', 'sgl_app', 'p@ssw0rd', 'procedures', null];
        yield 'aws-basic'      => ['aws', 'sgl-prod.rds.amazonaws.com', 'sgl_app', 's3cr3t-p@ss', 'procedures', 3306];
        yield 'aws-no-port'    => ['aws', 'sgl-prod.rds.amazonaws.com', 'sgl_app', 's3cr3t-p@ss', 'procedures', null];
        yield 'default-basic'  => ['default', 'legacy-host', 'legacy_user', 'legacy-pass', 'legacy_db', 3306];
        yield 'default-noport' => ['default', 'legacy-host', 'legacy_user', 'legacy-pass', 'legacy_db', null];

        // AWS mode also exercises unicode/symbol values that round-trip via JSON.
        yield 'aws-unicode'    => ['aws', 'máquina-ñ.local', 'usuário', 'contraseña€±§', 'baseÐæ', 5432];
        yield 'aws-emoji-pass' => ['aws', 'host.example', 'admin', 'pw🔒🛡️pass', 'db', 65535];

        $modeWheel = ['env', 'aws', 'default'];

        for ($seed = 1; $seed <= 120; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $mode = $modeWheel[$seed % count($modeWheel)];

            // env-backed modes must avoid env() sentinel/empty coercion; aws
            // mode round-trips through JSON so it can use the richer alphabet.
            $allowUnicode = $mode === 'aws';

            $host     = self::randomValue($seed, 'host', $allowUnicode);
            $username = self::randomValue($seed, 'user', $allowUnicode);
            $password = self::randomValue($seed, 'pass', $allowUnicode);
            $dbname   = self::randomValue($seed, 'db', $allowUnicode);
            $port     = mt_rand(0, 1) === 1 ? mt_rand(1, 65535) : null;

            yield "mode-{$mode}-seed-{$seed}" => [$mode, $host, $username, $password, $dbname, $port];
        }
    }

    /**
     * Deterministically build a non-empty, non-sentinel field value. When
     * $allowUnicode is true it may include unicode/emoji glyphs; otherwise it
     * stays within a safe ASCII alphabet for env-backed modes. A stable ASCII
     * token prefix guarantees the value is never empty, never trims away, and
     * never collides with an env() sentinel.
     */
    private static function randomValue(int $seed, string $kind, bool $allowUnicode): string
    {
        $palette = ['a', 'Z', '9', '_', '-', '.', 'x', '1', '0'];

        if ($allowUnicode) {
            $palette = array_merge($palette, [
                '@', '#', '$', '%', '&', '*', '(', ')', '{', '}', '[', ']',
                ':', ';', '|', '/', '\\', '?', '!', '+', '=', '<', '>', '"', "'", ' ',
                'ñ', 'é', 'ü', 'Ð', 'æ', '€', '±', '§', 'ß', 'λ', 'Ж', '🔒', '🚀',
            ]);
        }

        $len   = mt_rand(3, 14);
        $value = '';
        for ($i = 0; $i < $len; $i++) {
            $value .= $palette[mt_rand(0, count($palette) - 1)];
        }

        // Prefix a stable ASCII token so the value is a meaningful, distinct,
        // non-empty field that never reduces to an env() sentinel.
        $result = $kind . $seed . '_' . $value . 'Z';

        // Defensive guard (should be impossible given the prefix).
        return in_array(strtolower($result), self::ENV_RESERVED, true) ? $result . '_x' : $result;
    }

    // =====================================================================
    // Env helpers
    // =====================================================================

    /** Write a full set of plain-`.env` DB credentials for env-backed modes. */
    private function seedDbEnv(
        string $host,
        string $username,
        string $password,
        string $dbname,
        ?int $port
    ): void {
        $this->putEnv('database.default.hostname', $host);
        $this->putEnv('database.default.database', $dbname);
        $this->putEnv('database.default.username', $username);
        $this->putEnv('database.default.password', $password);
        if ($port !== null) {
            $this->putEnv('database.default.port', (string) $port);
        }
    }

    /**
     * Set an env value across every source env() consults ($_ENV, $_SERVER and
     * the process env) so the provider reads exactly this value.
     */
    private function putEnv(string $key, string $value): void
    {
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        if (! str_contains($value, "\0")) {
            putenv($key . '=' . $value);
        }
    }

    /** Remove every SECRETS_* and DB env key from all sources between iterations. */
    private function clearSecretsAndDbEnv(): void
    {
        $keys = array_merge(self::ENV_KEYS, [
            'SECRETS_PROVIDER',
            'SECRETS_RDS_REFERENCE',
            'SECRETS_REGION',
            'SECRETS_CACHE_TTL',
        ]);

        foreach ($keys as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }
    }
}
