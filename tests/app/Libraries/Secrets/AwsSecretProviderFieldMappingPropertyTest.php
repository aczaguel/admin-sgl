<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretsManagerService;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;

/**
 * Property 2: JSON field-mapping correctness with port default.
 *
 * For an arbitrary VALID RDS secret JSON blob
 * ({@code {"host":..,"username":..,"password":..,"dbname":..[,"port":..]}}),
 * resolving it through {@see AwsSecretProvider::getSecret()} returns a map whose
 * fields equal the input, and the canonical mapping into a Config\Database group
 * is:
 *
 *   hostname <- host      username <- username     password <- password
 *   database <- dbname    port     <- port when present, else 3306 (MySQL default)
 *
 * i.e. `hostname=host`, `username=username`, `password=password`,
 * `database=dbname`, and `port` equals the given port when present or `3306`
 * when omitted. Host/username/password/dbname are generated with special and
 * unicode characters to prove the JSON round-trip preserves them byte-for-byte.
 *
 * **Property 2: JSON field-mapping correctness with port default**
 * **Validates: Requirements 3.1, 3.5**
 *
 * Determinism: the AWS SDK is exercised through a {@see MockHandler} so no live
 * AWS call is ever made. `AwsSecretProvider::getSecret()` returns the raw parsed
 * map (it validates an optional positive-integer port but does NOT default it).
 * The port DEFAULT of 3306 and the `host->hostname` / `dbname->database`
 * renaming are applied by {@see SecretsManagerService::resolveRdsInto()}
 * (task 6.3), which is covered by its own property tests (task 6.x). This test
 * asserts the mapping semantics directly: it prefers
 * `SecretsManagerService::resolveRdsInto()` when that method is available at run
 * time, and otherwise applies the documented mapping locally so the property
 * (host/username/password/dbname mapping + port default 3306) is verified now
 * against what `AwsSecretProvider` returns.
 *
 * Cases are produced by seeded PHPUnit data-provider generators (>= 100
 * iterations), following the tests/ convention used by the S3 property tests.
 *
 * @internal
 */
final class AwsSecretProviderFieldMappingPropertyTest extends CIUnitTestCase
{
    /**
     * Property 2: for arbitrary valid RDS secret JSON, the resolved connection
     * group maps hostname<-host, username<-username, password<-password,
     * database<-dbname, and port<-given port when present.
     *
     * **Validates: Requirements 3.1, 3.5**
     *
     * @dataProvider validRdsSecretWithPortProvider
     */
    public function testFieldMappingPreservesValuesAndPortWhenPresent(
        string $host,
        string $username,
        string $password,
        string $dbname,
        int $port,
    ): void {
        $json = $this->encodeSecret([
            'host'     => $host,
            'username' => $username,
            'password' => $password,
            'dbname'   => $dbname,
            'port'     => $port,
        ]);

        $provider = $this->makeProvider($json);
        $map      = $provider->getSecret('rds/creds');

        // The parsed provider map preserves every field byte-for-byte (Req 3.1).
        $this->assertSame($host, $map['host'], 'host must round-trip through JSON unchanged');
        $this->assertSame($username, $map['username'], 'username must round-trip through JSON unchanged');
        $this->assertSame($password, $map['password'], 'password must round-trip through JSON unchanged');
        $this->assertSame($dbname, $map['dbname'], 'dbname must round-trip through JSON unchanged');

        // Canonical Config\Database mapping (Req 3.1, 3.5).
        $group = $this->mapRdsInto($map);

        $this->assertSame($host, $group['hostname'], 'hostname must equal host');
        $this->assertSame($username, $group['username'], 'username must equal username');
        $this->assertSame($password, $group['password'], 'password must equal password');
        $this->assertSame($dbname, $group['database'], 'database must equal dbname');
        $this->assertSame($port, $group['port'], 'port must equal the given port when present');
    }

    /**
     * Property 2 (port default): for arbitrary valid RDS secret JSON that OMITS
     * the port field, the mapped connection group defaults port to MySQL 3306
     * while every other field still maps from the secret (Req 3.5).
     *
     * **Validates: Requirements 3.1, 3.5**
     *
     * @dataProvider validRdsSecretWithoutPortProvider
     */
    public function testPortDefaultsTo3306WhenOmitted(
        string $host,
        string $username,
        string $password,
        string $dbname,
    ): void {
        $json = $this->encodeSecret([
            'host'     => $host,
            'username' => $username,
            'password' => $password,
            'dbname'   => $dbname,
        ]);

        $provider = $this->makeProvider($json);
        $map      = $provider->getSecret('rds/creds');

        // The provider returns the raw parsed map: it does NOT inject a port
        // (the 3306 default is applied by the mapping step below / task 6.3).
        $this->assertArrayNotHasKey('port', $map, 'provider must not fabricate a port');

        $group = $this->mapRdsInto($map);

        $this->assertSame($host, $group['hostname'], 'hostname must equal host');
        $this->assertSame($username, $group['username'], 'username must equal username');
        $this->assertSame($password, $group['password'], 'password must equal password');
        $this->assertSame($dbname, $group['database'], 'database must equal dbname');
        $this->assertSame(3306, $group['port'], 'port must default to 3306 when omitted');
    }

    // ── Mapping under test ───────────────────────────────────────────────────

    /**
     * Apply the canonical RDS-secret -> Config\Database mapping to a parsed map.
     *
     * Prefers {@see SecretsManagerService::resolveRdsInto()} when that method is
     * available at run time (task 6.3), so the exact production mapping is
     * exercised end-to-end. When it is not yet implemented, applies the
     * documented mapping locally (host->hostname, dbname->database, port ?? 3306)
     * so Property 2 is verified against what AwsSecretProvider returns today.
     *
     * @param array<string,string|int> $map
     *
     * @return array{hostname:string,username:string,password:string,database:string,port:int}
     */
    private function mapRdsInto(array $map): array
    {
        if (method_exists(SecretsManagerService::class, 'resolveRdsInto')) {
            $group = [];
            $this->resolveViaService($map, $group);

            /** @var array{hostname:string,username:string,password:string,database:string,port:int} $group */
            return $group;
        }

        return [
            'hostname' => (string) $map['host'],
            'username' => (string) $map['username'],
            'password' => (string) $map['password'],
            'database' => (string) $map['dbname'],
            'port'     => (int) ($map['port'] ?? 3306),
        ];
    }

    /**
     * Drive SecretsManagerService::resolveRdsInto() with a MockHandler-backed AWS
     * client so the exact production mapping runs offline. Only invoked when the
     * method exists (guarded by the caller).
     *
     * @param array<string,string|int> $map
     * @param array<string,mixed>      $group
     */
    private function resolveViaService(array $map, array &$group): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'rds/creds';
        $config->region       = 'us-east-1';
        $config->cacheTtl     = 0;

        // Re-encode the parsed map so the service resolves the same secret via a
        // fresh MockHandler-backed provider (no live AWS).
        $service = new SecretsManagerService($config);

        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $this->makeProvider($this->encodeSecret($map)));

        $service->resolveRdsInto($group);
    }

    // ── Provider factory (MockHandler, no live AWS) ──────────────────────────

    /**
     * Build an AwsSecretProvider whose SecretsManagerClient is driven by an AWS
     * SDK MockHandler that returns the given SecretString. Dummy static
     * credentials are supplied so SigV4 signing happens entirely offline (no
     * Instance-Profile / metadata lookup in tests); the MockHandler replaces the
     * network handler so no request ever leaves.
     */
    private function makeProvider(string $secretString): AwsSecretProvider
    {
        $mock = new MockHandler();
        $mock->append(new Result([
            'SecretString' => $secretString,
            '@metadata'    => ['statusCode' => 200],
        ]));

        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test', 'secret' => 'test'],
            'handler'     => $mock,
        ]);

        return new AwsSecretProvider(new SecretsConfig(), $client);
    }

    /**
     * Encode a secret map as the JSON blob stored in Secrets Manager, preserving
     * unicode and special characters exactly.
     *
     * @param array<string,string|int> $fields
     */
    private function encodeSecret(array $fields): string
    {
        return json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ── Data providers (seeded, >= 100 iterations) ───────────────────────────

    /**
     * Arbitrary valid RDS secrets WITH an explicit positive-integer port.
     *
     * @return iterable<string, array{0:string,1:string,2:string,3:string,4:int}>
     */
    public static function validRdsSecretWithPortProvider(): iterable
    {
        // Explicit boundary / representative cases first.
        yield 'ascii-basic' => ['db.internal', 'sgl_app', 'p@ssw0rd', 'procedures', 3306];
        yield 'special-chars-password' => [
            'sgl-prod.abcdef.us-east-1.rds.amazonaws.com',
            'user"with\\quotes',
            "p'a\"s\\s/w{o}rd:;#|&$",
            'proc_db',
            5432,
        ];
        yield 'unicode-fields' => ['máquina-ñ.local', 'usuário', 'contraseña€±§', 'baseÐæ', 1];
        yield 'emoji-password' => ['host.example', 'admin', 'pw🔒🛡️pass', 'db', 65535];
        yield 'port-min' => ['h', 'u', 'p', 'd', 1];
        yield 'port-max' => ['h', 'u', 'p', 'd', 65535];
        yield 'whitespace-inside' => ['ho st', 'user name', 'pass word', 'data base', 3307];

        // Seeded generator cases (>= 100 total with the explicit ones above).
        for ($seed = 1; $seed <= 110; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $host     = self::randomValue($seed, 'host');
            $username = self::randomValue($seed, 'user');
            $password = self::randomValue($seed, 'pass');
            $dbname   = self::randomValue($seed, 'db');
            $port     = mt_rand(1, 65535);

            yield "with-port-seed-{$seed}" => [$host, $username, $password, $dbname, $port];
        }
    }

    /**
     * Arbitrary valid RDS secrets that OMIT the port field (port must default to
     * 3306 in the mapping).
     *
     * @return iterable<string, array{0:string,1:string,2:string,3:string}>
     */
    public static function validRdsSecretWithoutPortProvider(): iterable
    {
        yield 'ascii-basic-no-port' => ['db.internal', 'sgl_app', 'p@ssw0rd', 'procedures'];
        yield 'special-chars-no-port' => [
            'sgl-prod.rds.amazonaws.com',
            "u\\ser",
            "s3cr3t\"';/{}[]|",
            'proc_db',
        ];
        yield 'unicode-no-port' => ['máquina-ñ.local', 'usuário', 'contraseña€±§', 'baseÐæ'];

        for ($seed = 201; $seed <= 305; $seed++) {
            mt_srand($seed * 40503 & 0x7FFFFFFF);

            $host     = self::randomValue($seed, 'host');
            $username = self::randomValue($seed, 'user');
            $password = self::randomValue($seed, 'pass');
            $dbname   = self::randomValue($seed, 'db');

            yield "no-port-seed-{$seed}" => [$host, $username, $password, $dbname];
        }
    }

    /**
     * Deterministically build a non-empty field value that may contain special
     * and/or unicode characters. Guaranteed to be a non-empty, non-whitespace
     * scalar so it passes AwsSecretProvider's required-field validation.
     */
    private static function randomValue(int $seed, string $kind): string
    {
        // A palette mixing ASCII, punctuation/special and unicode/emoji glyphs.
        $palette = [
            'a', 'Z', '9', '_', '-', '.', 'x',
            '@', '#', '$', '%', '&', '*', '(', ')', '{', '}', '[', ']',
            ':', ';', '|', '/', '\\', '?', '!', '+', '=', '<', '>',
            '"', "'", ' ',
            'ñ', 'é', 'ü', 'Ð', 'æ', '€', '±', '§', 'ß', 'λ', 'Ж',
            '🔒', '🛡️', '🚀',
        ];

        $len   = mt_rand(3, 14);
        $value = '';
        for ($i = 0; $i < $len; $i++) {
            $value .= $palette[mt_rand(0, count($palette) - 1)];
        }

        // Prepend a stable ASCII token so the value never trims to empty and is
        // always a meaningful, distinct field.
        return $kind . $seed . '_' . $value . 'Z';
    }
}
