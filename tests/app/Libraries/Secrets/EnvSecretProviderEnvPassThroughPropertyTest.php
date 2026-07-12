<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\EnvSecretProvider;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use ReflectionClass;

/**
 * Property-based test for Property 6: Env provider passes through today's
 * `.env` values unchanged.
 *
 * **Validates: Requirements 7.1, 7.2, 7.3, 10.1**
 *
 * For arbitrary `database.default.hostname/database/username/password` (and an
 * optional `database.default.port`) — including special and unicode
 * characters — this asserts that {@see EnvSecretProvider::getSecret()} returns
 * exactly those values mapped to the canonical keys:
 *
 *   database.default.hostname -> host
 *   database.default.database -> dbname
 *   database.default.username -> username
 *   database.default.password -> password
 *   database.default.port     -> port (int; present only when the env key is)
 *
 * and that no AWS Secrets Manager / AWS SDK contact is ever attempted (Req
 * 7.2): the provider is structurally free of any AWS dependency and resolves
 * purely from local env values.
 *
 * PBT generators are implemented as a seeded PHPUnit data provider (>=100
 * iterations) so any counterexample is deterministically reproducible and no
 * new runtime dependency is introduced.
 *
 * @internal
 */
final class EnvSecretProviderEnvPassThroughPropertyTest extends CIUnitTestCase
{
    /** The env keys the provider reads (and this test drives). */
    private const ENV_KEYS = [
        'database.default.hostname',
        'database.default.database',
        'database.default.username',
        'database.default.password',
        'database.default.port',
    ];

    /**
     * Values env() coerces to a non-string (true/false/''/null) — the generator
     * must never emit these verbatim, since they are a documented env() quirk
     * rather than a property of the provider.
     *
     * @var array<int, string>
     */
    private const ENV_RESERVED = ['true', 'false', 'empty', 'null'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearDbEnv();
    }

    protected function tearDown(): void
    {
        $this->clearDbEnv();
        parent::tearDown();
    }

    /**
     * Property 6: whatever plain-`.env` DB values are configured, getSecret()
     * returns exactly those values mapped to the canonical keys, and nothing
     * else.
     *
     * @dataProvider provideEnvCredentialSets
     */
    public function testEnvValuesPassThroughUnchanged(
        string $hostname,
        string $database,
        string $username,
        string $password,
        ?int $port
    ): void {
        // Guard: the generator must never emit env()-coerced sentinels or empty
        // values, which the provider intentionally skips.
        foreach ([$hostname, $database, $username, $password] as $value) {
            $this->assertNotSame('', $value, 'Generator emitted an empty value');
            $this->assertNotContains(strtolower($value), self::ENV_RESERVED, 'Generator emitted an env() sentinel: ' . $value);
        }

        $this->putEnv('database.default.hostname', $hostname);
        $this->putEnv('database.default.database', $database);
        $this->putEnv('database.default.username', $username);
        $this->putEnv('database.default.password', $password);
        if ($port !== null) {
            $this->putEnv('database.default.port', (string) $port);
        }

        $provider = new EnvSecretProvider(new SecretsConfig());

        // $reference is ignored in env mode (Req 7.1); pass an arbitrary value.
        $result = $provider->getSecret('ignored-reference');

        // Build the exact expected map in the provider's key order so assertSame
        // enforces both values AND the absence of any extra keys.
        $expected = [
            'host'     => $hostname,
            'dbname'   => $database,
            'username' => $username,
            'password' => $password,
        ];
        if ($port !== null) {
            $expected['port'] = $port;
        }

        $message = sprintf(
            'host=%s db=%s user=%s passLen=%d port=%s',
            $hostname,
            $database,
            $username,
            strlen($password),
            $port === null ? '(absent)' : (string) $port
        );

        $this->assertSame($expected, $result, 'Resolved group must equal the configured .env values exactly. ' . $message);

        // Req 7.2: no AWS material is involved — the map holds only the canonical
        // DB keys and never any AWS reference/credential artifact.
        $this->assertSame(
            array_keys($expected),
            array_keys($result),
            'Resolved map must contain only the canonical DB keys. ' . $message
        );
    }

    /**
     * Req 7.2 (structural): the Env provider can never attempt an AWS call
     * because it carries no AWS SDK dependency whatsoever. Inspect the class
     * source and imports to prove the absence of any `Aws\` reference.
     */
    public function testEnvProviderHasNoAwsDependency(): void
    {
        $reflection = new ReflectionClass(EnvSecretProvider::class);
        $file       = $reflection->getFileName();
        $this->assertNotFalse($file, 'Could not locate EnvSecretProvider source');

        $source = file_get_contents($file);
        $this->assertNotFalse($source, 'Could not read EnvSecretProvider source');

        $this->assertStringNotContainsStringIgnoringCase('Aws\\', $source, 'Env provider must not reference the AWS SDK namespace');
        $this->assertStringNotContainsString('SecretsManagerClient', $source, 'Env provider must not touch the Secrets Manager client');
        $this->assertStringNotContainsString('getSecretValue', $source, 'Env provider must not call getSecretValue');
    }

    // --- generators -------------------------------------------------------

    /**
     * Seeded generator of arbitrary DB credential sets. Produces host / db /
     * user / password values drawn from ASCII, special and unicode alphabets,
     * with the port present about half the time. >=100 iterations plus a set of
     * explicit edge cases.
     *
     * @return array<string, array{0:string,1:string,2:string,3:string,4:int|null}>
     */
    public function provideEnvCredentialSets(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240611);

        $cases = [];
        $count = 200;

        for ($i = 0; $i < $count; $i++) {
            $hostname = $this->randomValue();
            $database = $this->randomValue();
            $username = $this->randomValue();
            $password = $this->randomValue();
            $port     = mt_rand(0, 1) === 1 ? mt_rand(1, 65535) : null;

            $cases['case_' . $i] = [$hostname, $database, $username, $password, $port];
        }

        // Explicit edge cases: unicode, spaces, symbols, and a value that
        // contains an '=' (which must still round-trip through $_ENV/$_SERVER).
        $cases['edge_unicode']       = ['bd.クラウド.example', 'basé_de_données', 'usüario', 'contra$eña¡', 3306];
        $cases['edge_emoji']         = ['host-🚀.local', 'db-✅', 'user-💾', 'p@ss-🔒word', null];
        $cases['edge_symbols']       = ['10.0.0.1', 'my-db_01', 'root', 'p@$$w0rd!#%&*(){}[]', 5432];
        $cases['edge_spaces']        = ['host with space', 'data base', 'user name', 'pass phrase 123', null];
        $cases['edge_equals_quotes'] = ['h="x"', 'd=b', "u'q'", 'k=v;a=b&c=d', 33060];
        $cases['edge_numeric_like']  = ['127001', '00123', '42', '3306', 3306];

        return $cases;
    }

    /**
     * A random non-empty value that is not an env() sentinel. Mixes ASCII,
     * symbols, and unicode/multibyte characters. Excludes NUL bytes.
     */
    private function randomValue(): string
    {
        $alphabets = [
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'abcdefghijklmnopqrstuvwxyz0123456789.-_',
            '!@#$%^&*()-_=+[]{};:,.<>?/|~`',
            'áéíóúñüàèìòùçâêîôûäëïößαβγδΩ日本語测试🚀✅💾',
        ];

        do {
            $alphabet = $alphabets[mt_rand(0, count($alphabets) - 1)];
            // Operate on characters (multibyte-aware) so unicode alphabets emit
            // whole glyphs, never broken byte sequences.
            $chars = $this->splitChars($alphabet);
            $len   = mt_rand(1, 24);

            $value = '';
            for ($i = 0; $i < $len; $i++) {
                $value .= $chars[mt_rand(0, count($chars) - 1)];
            }
        } while ($value === '' || in_array(strtolower($value), self::ENV_RESERVED, true));

        return $value;
    }

    /**
     * Split a UTF-8 string into an array of whole characters.
     *
     * @return array<int, string>
     */
    private function splitChars(string $str): array
    {
        $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);

        return $chars === false ? [] : $chars;
    }

    // --- env helpers ------------------------------------------------------

    /**
     * Set an env value across every source env() consults ($_ENV, $_SERVER and
     * the process env) so the provider reads exactly this value.
     */
    private function putEnv(string $key, string $value): void
    {
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        // putenv cannot carry NUL bytes; our generator never emits them, but
        // guard anyway. $_ENV takes precedence in env() regardless.
        if (! str_contains($value, "\0")) {
            putenv($key . '=' . $value);
        }
    }

    /** Remove every DB env key from all sources so iterations never leak. */
    private function clearDbEnv(): void
    {
        foreach (self::ENV_KEYS as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }
    }
}
