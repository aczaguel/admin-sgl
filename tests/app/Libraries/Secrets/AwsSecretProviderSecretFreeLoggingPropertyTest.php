<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretResolutionException;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use Config\Secrets as SecretsConfig;
use ReflectionClass;
use Throwable;

/**
 * Property 5: Secret value never appears in logs or error messages.
 *
 * **Validates: Requirements 5.2, 5.4, 8.2, 8.3**
 *
 * For arbitrary secret material driven through EVERY {@see AwsSecretProvider}
 * error path, neither the captured log output NOR the thrown exception (message
 * + reason) may ever contain the secret value — only the Secret_Reference and a
 * reason code are allowed to appear.
 *
 * Error paths where a secret VALUE could leak (each exercised via the AWS SDK
 * {@see MockHandler}, so no live AWS call is ever made):
 *   - `invalid-json`         — the raw SecretString is a malformed, secret-bearing blob
 *   - `missing-field:<name>` — the blob carries real values for the OTHER fields
 *   - `invalid-port`         — the blob carries real field values plus a bad port
 *   - `empty-secret-string`  — no value present (leak-free by construction)
 *
 * Each generated secret embeds one or more DISTINCTIVE canary tokens (mixing
 * ASCII, punctuation/special and unicode/emoji glyphs) so that any leak into a
 * log line or exception string is immediately detectable. After running
 * `getSecret()` the test:
 *   1. captures actual log output via CI4's {@see TestLogger} (which
 *      `log_message()` routes to under `ENVIRONMENT === 'testing'`), and asserts
 *      NO canary token appears in any recorded log message (Req 5.2, 8.2, 8.3);
 *   2. asserts the exception `getMessage()` and `reason` contain NO canary
 *      token, and that the message names only the reference + reason code
 *      (Req 5.4);
 *   3. asserts the log line for the reference matches the redacted shape
 *      `Secrets: reference=<ref> reason=<reason>` exactly (only reference +
 *      reason, never a value).
 *
 * Cases come from seeded PHPUnit data-provider generators (>= 100 iterations)
 * following the tests/ convention used by the sibling Secrets property tests.
 *
 * @internal
 */
final class AwsSecretProviderSecretFreeLoggingPropertyTest extends CIUnitTestCase
{
    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    /** Required RDS fields (Req 3.4). */
    private const REQUIRED_FIELDS = ['host', 'username', 'password', 'dbname'];

    /**
     * Property 5: across every error path, no secret value ever reaches logs or
     * the exception message; only the reference and a reason code appear.
     *
     * **Validates: Requirements 5.2, 5.4, 8.2, 8.3**
     *
     * @dataProvider provideLeakyErrorPaths
     *
     * @param string        $mode          Error-path discriminator.
     * @param string|null   $secretString  Raw SecretString the mock returns (null => absent key).
     * @param string        $reference     Secret name/ARN under test (never a value).
     * @param list<string>  $canaries      Distinctive secret tokens that must NEVER leak.
     * @param string        $expectedReason The redacted reason code expected in the log/exception.
     */
    public function testSecretValueNeverLeaksIntoLogsOrErrors(
        string $mode,
        ?string $secretString,
        string $reference,
        array $canaries,
        string $expectedReason
    ): void {
        $this->clearCapturedLogs();

        $provider = $this->makeProvider($secretString);

        $thrown = null;

        try {
            $provider->getSecret($reference);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $context = sprintf('mode=%s ref=%s reason=%s', $mode, $reference, $expectedReason);

        // Every leaky error path must fail closed with a SecretResolutionException.
        $this->assertInstanceOf(
            SecretResolutionException::class,
            $thrown,
            'Error path must throw SecretResolutionException. ' . $context
        );

        /** @var SecretResolutionException $thrown */
        $message = $thrown->getMessage();
        $reason  = $thrown->reason;
        $logs    = $this->capturedLogMessages();

        // ── (1) No canary token may appear in ANY captured log message ───────
        foreach ($canaries as $canary) {
            foreach ($logs as $line) {
                $this->assertStringNotContainsString(
                    $canary,
                    $line,
                    'A secret value token leaked into a log line. ' . $context
                );
            }

            // ── (2) No canary token may appear in the exception message/reason ─
            $this->assertStringNotContainsString(
                $canary,
                $message,
                'A secret value token leaked into the exception message. ' . $context
            );
            $this->assertStringNotContainsString(
                $canary,
                $reason,
                'A secret value token leaked into the exception reason. ' . $context
            );
        }

        // ── The reason + message name only the reference and reason code ─────
        $this->assertSame($expectedReason, $reason, 'reason code mismatch. ' . $context);
        $this->assertSame($reference, $thrown->reference, 'reference must be carried verbatim. ' . $context);
        $this->assertSame(
            sprintf('Secret resolution failed [reference=%s]: %s', $reference, $expectedReason),
            $message,
            'exception message must contain ONLY the reference and reason code. ' . $context
        );

        // ── (3) A redacted log line for this reference must have been written ─
        $expectedLogLine = 'Secrets: reference=' . $reference . ' reason=' . $expectedReason;
        $this->assertContains(
            $expectedLogLine,
            $logs,
            'the redacted (reference + reason only) log line must be present. ' . $context
        );

        // And that redacted line itself must not carry any canary.
        foreach ($canaries as $canary) {
            $this->assertStringNotContainsString($canary, $expectedLogLine, $context);
        }
    }

    // =====================================================================
    // Data provider (seeded, >= 100 iterations)
    // =====================================================================

    /**
     * Generate secret material for every leaky error path, embedding distinctive
     * canary tokens so any leak is detectable.
     *
     * @return iterable<string, array{0:string,1:string|null,2:string,3:list<string>,4:string}>
     */
    public static function provideLeakyErrorPaths(): iterable
    {
        // ── Explicit representative edge cases ───────────────────────────────

        // invalid-json: whole raw blob is secret-bearing but malformed.
        yield 'invalid-json-truncated' => [
            'invalid-json',
            '{"username":"CANARY_admin_u","password":"CANARY_p@$$w0rd_🔒",',
            'sgl/prod/rds',
            ['CANARY_admin_u', 'CANARY_p@$$w0rd_🔒'],
            'invalid-json',
        ];
        yield 'invalid-json-not-json' => [
            'invalid-json',
            'raw-secret-blob CANARY_ñ_secret_€ not-json',
            'sgl/prod/rds',
            ['CANARY_ñ_secret_€'],
            'invalid-json',
        ];

        // missing-field: other fields carry real (sensitive) values.
        yield 'missing-host' => [
            'missing-field',
            self::blobMissing('host', 1),
            'sgl/prod/rds',
            self::canariesFor(1, 'host'),
            'missing-field:host',
        ];
        yield 'missing-password' => [
            'missing-field',
            self::blobMissing('password', 2),
            'arn:aws:secretsmanager:us-east-1:000000000002:secret:sgl/rds-AbCdEf',
            self::canariesFor(2, 'password'),
            'missing-field:password',
        ];

        // invalid-port: all fields present + a bad port.
        yield 'invalid-port-negative' => [
            'invalid-port',
            self::blobWithPort(3, -5),
            'sgl/prod/rds',
            self::canariesFor(3, null),
            'invalid-port',
        ];
        yield 'invalid-port-nonnumeric' => [
            'invalid-port',
            self::blobWithPort(4, 'not-a-port'),
            'sgl/prod/rds',
            self::canariesFor(4, null),
            'invalid-port',
        ];

        // empty-secret-string: nothing to leak, but still must stay redacted.
        yield 'empty-secret-string' => ['empty-secret', '', 'sgl/prod/rds', [], 'empty-secret-string'];
        yield 'absent-secret-string' => ['empty-secret', null, 'sgl/prod/rds', [], 'empty-secret-string'];

        // ── Seeded generator (>= 100 total with the explicit cases above) ────
        $modeWheel      = ['invalid-json', 'missing-field', 'invalid-port', 'empty-secret'];
        $badPortWheel   = [0, -1, -999, 'abc', '3306x', '0', ' ', '99999999999999999999'];

        for ($seed = 1; $seed <= 120; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $mode      = $modeWheel[$seed % count($modeWheel)];
            $reference = self::randomReference($seed);

            switch ($mode) {
                case 'invalid-json':
                    $canary = self::canary($seed, 'blob');
                    // A malformed JSON blob that embeds the canary secret token.
                    $raw = '{"password":"' . $canary . '", "host":' ; // deliberately truncated
                    yield "leak-seed-{$seed}" => ['invalid-json', $raw, $reference, [$canary], 'invalid-json'];
                    break;

                case 'missing-field':
                    $missing = self::REQUIRED_FIELDS[$seed % count(self::REQUIRED_FIELDS)];
                    yield "leak-seed-{$seed}" => [
                        'missing-field',
                        self::blobMissing($missing, $seed),
                        $reference,
                        self::canariesFor($seed, $missing),
                        'missing-field:' . $missing,
                    ];
                    break;

                case 'invalid-port':
                    $badPort = $badPortWheel[$seed % count($badPortWheel)];
                    yield "leak-seed-{$seed}" => [
                        'invalid-port',
                        self::blobWithPort($seed, $badPort),
                        $reference,
                        self::canariesFor($seed, null),
                        'invalid-port',
                    ];
                    break;

                default: // empty-secret
                    yield "leak-seed-{$seed}" => [
                        'empty-secret',
                        $seed % 2 === 0 ? '' : null,
                        $reference,
                        [],
                        'empty-secret-string',
                    ];
                    break;
            }
        }
    }

    // =====================================================================
    // Secret-material builders (all fields carry canary tokens)
    // =====================================================================

    /**
     * A valid RDS blob with one required field removed. The remaining required
     * fields carry distinctive canary secret tokens.
     */
    private static function blobMissing(string $missing, int $seed): string
    {
        $fields = self::canaryFields($seed);
        unset($fields[$missing]);

        return json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * A blob with every required field present (canary-bearing) plus a port.
     *
     * @param int|string $port The (typically invalid) port value.
     */
    private static function blobWithPort(int $seed, int|string $port): string
    {
        $fields         = self::canaryFields($seed);
        $fields['port'] = $port;

        return json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * The four required fields, each populated with a distinctive canary token.
     *
     * @return array<string,string>
     */
    private static function canaryFields(int $seed): array
    {
        return [
            'host'     => self::canary($seed, 'host'),
            'username' => self::canary($seed, 'user'),
            'password' => self::canary($seed, 'pass'),
            'dbname'   => self::canary($seed, 'db'),
        ];
    }

    /**
     * The canary tokens that are actually PRESENT in a blob for a given case.
     * For a missing-field case the removed field's canary is excluded (it is not
     * in the blob), so we only assert on tokens the provider actually saw.
     *
     * @return list<string>
     */
    private static function canariesFor(int $seed, ?string $missing): array
    {
        $map = [
            'host'     => self::canary($seed, 'host'),
            'username' => self::canary($seed, 'user'),
            'password' => self::canary($seed, 'pass'),
            'dbname'   => self::canary($seed, 'db'),
        ];

        if ($missing !== null) {
            unset($map[$missing]);
        }

        return array_values($map);
    }

    /**
     * A distinctive, non-empty, non-whitespace secret token mixing ASCII,
     * punctuation/special and unicode/emoji glyphs. The stable `CANARY` prefix
     * plus the seed/kind guarantees uniqueness and easy leak detection, while
     * never colliding with a reference or a reason code.
     */
    private static function canary(int $seed, string $kind): string
    {
        $palette = [
            '@', '#', '$', '%', '&', '*', '{', '}', '|', '/', '\\', ':', ';',
            '"', "'", '<', '>', '=', '+',
            'ñ', 'é', 'ü', 'Ð', 'æ', '€', '±', '§', 'ß', 'λ', 'Ж',
            '🔒', '🛡', '🚀',
        ];

        $suffix = '';
        for ($i = 0; $i < 6; $i++) {
            $suffix .= $palette[mt_rand(0, count($palette) - 1)];
        }

        return 'CANARY_' . $kind . $seed . '_' . $suffix . '_END';
    }

    /** Deterministic, varied Secret_Reference (name or ARN). Never a value. */
    private static function randomReference(int $seed): string
    {
        if ($seed % 3 === 0) {
            return sprintf('arn:aws:secretsmanager:us-east-1:%012d:secret:sgl/ref-%d-AbCdEf', $seed, $seed);
        }

        return 'sgl/ref-' . $seed . '/' . ($seed % 2 === 0 ? 'rds' : 'db-creds');
    }

    // =====================================================================
    // Provider factory (MockHandler, no live AWS)
    // =====================================================================

    /**
     * Build an AwsSecretProvider whose SecretsManagerClient is driven by an AWS
     * SDK MockHandler returning the given SecretString (or a Result with no
     * SecretString key when $secretString is null). Dummy static credentials are
     * supplied so signing happens offline; the MockHandler replaces the network
     * handler so no request ever leaves the process.
     */
    private function makeProvider(?string $secretString): AwsSecretProvider
    {
        $mock = new MockHandler();
        $mock->append($secretString === null
            ? new Result([])
            : new Result(['SecretString' => $secretString, '@metadata' => ['statusCode' => 200]]));

        $config         = new SecretsConfig();
        $config->region = self::REGION;

        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);

        return new AwsSecretProvider($config, $client);
    }

    // =====================================================================
    // Log capture via CI4 TestLogger (log_message routes here in testing)
    // =====================================================================

    /** Reset the TestLogger's in-memory log store before an iteration. */
    private function clearCapturedLogs(): void
    {
        $prop = (new ReflectionClass(TestLogger::class))->getProperty('op_logs');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    /**
     * Return every log MESSAGE captured by the TestLogger so far.
     *
     * @return list<string>
     */
    private function capturedLogMessages(): array
    {
        $prop = (new ReflectionClass(TestLogger::class))->getProperty('op_logs');
        $prop->setAccessible(true);

        /** @var list<array{level:string,message:string,file:?string}> $logs */
        $logs = $prop->getValue();

        return array_map(static fn (array $row): string => (string) $row['message'], $logs);
    }
}
