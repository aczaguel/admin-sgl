<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretResolutionException;
use App\Libraries\Secrets\SecretsManagerService;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\History;
use Aws\Middleware;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use InvalidArgumentException;
use ReflectionClass;
use Throwable;

/**
 * Property 4: Fail closed on invalid config or unresolvable secret.
 *
 * **Validates: Requirements 2.3, 2.4, 3.3, 3.4, 3.6, 5.1, 5.3**
 *
 * For EVERY failure mode of {@see SecretsManagerService::resolveRdsInto()}, two
 * things must hold:
 *
 *   1. Resolution raises an error that NAMES the failing input — the offending
 *      setting for configuration errors (`SECRETS_RDS_REFERENCE` /
 *      `SECRETS_REGION`, Req 2.3/2.4), or the Secret_Reference (and the missing
 *      field where applicable, Req 3.4/5.1) for resolution errors.
 *   2. The target connection group passed BY REFERENCE is left completely
 *      UNMODIFIED — the application never opens a connection with partial or
 *      unknown credentials (fail closed, Req 3.3/3.6/5.3).
 *
 * Failure modes exercised:
 *   - missing / empty / whitespace `rdsReference` under provider=aws
 *       → InvalidArgumentException naming SECRETS_RDS_REFERENCE (Req 2.3)
 *   - missing / empty / whitespace `region` under provider=aws
 *       → InvalidArgumentException naming SECRETS_REGION (Req 2.4)
 *   - unreachable Secrets Manager / non-existent reference / access denied
 *       → SecretResolutionException naming the reference (Req 5.1)
 *   - empty SecretString, invalid JSON
 *       → SecretResolutionException naming the reference (Req 3.3)
 *   - each missing required field (host/username/password/dbname)
 *       → SecretResolutionException naming the reference + the missing field
 *         (Req 3.4)
 *
 * AWS failures are simulated entirely offline with the AWS SDK
 * {@see MockHandler} (same pattern as {@see AwsSecretProviderContractTest}); a
 * mock-backed {@see AwsSecretProvider} is injected into the service's private
 * `provider` via reflection so NO live AWS call ever occurs. Configuration
 * failures construct the service with a valid aws reference/region (so the SDK
 * client builds without contacting the network) and then blank the setting on
 * the shared {@see SecretsConfig} the service holds, so `assertConfigured()`
 * rejects it at resolve time — mirroring {@see SecretsManagerServiceTest}.
 *
 * Cases come from seeded PHPUnit data-provider generators (>= 100 iterations
 * per generator) with varied references, field names, and sentinel groups,
 * following the tests/ convention used by the sibling Secrets property tests.
 *
 * @internal
 */
final class SecretsManagerServiceFailClosedPropertyTest extends CIUnitTestCase
{
    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    /** Required RDS fields whose absence must fail closed (Req 3.4). */
    private const REQUIRED_FIELDS = ['host', 'username', 'password', 'dbname'];

    // =====================================================================
    // Configuration failures (Req 2.3, 2.4) — fail closed, group unchanged
    // =====================================================================

    /**
     * Property 4 (config errors): a missing/empty/whitespace reference or region
     * under provider=aws raises an InvalidArgumentException that names the
     * offending setting, and leaves the target group untouched.
     *
     * **Validates: Requirements 2.3, 2.4, 3.6, 5.3**
     *
     * @dataProvider provideConfigFailures
     *
     * @param 'reference'|'region' $which        Which setting to blank.
     * @param string               $blankValue   The empty/whitespace value injected.
     * @param array<string,mixed>  $sentinelGroup Known group asserted unchanged.
     */
    public function testConfigFailureFailsClosedNamingSetting(
        string $which,
        string $blankValue,
        array $sentinelGroup
    ): void {
        $expectedSetting = $which === 'reference' ? 'SECRETS_RDS_REFERENCE' : 'SECRETS_REGION';

        // Construct with a VALID aws config so the service (and its SDK client)
        // build cleanly, then blank the setting on the config the service holds.
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'sgl/prod/rds';
        $config->region       = self::REGION;

        $service = new SecretsManagerService($config);

        if ($which === 'reference') {
            $config->rdsReference = $blankValue;
        } else {
            $config->region = $blankValue;
        }

        $group  = $sentinelGroup;
        $thrown = null;

        try {
            $service->resolveRdsInto($group);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $context = sprintf('which=%s blank=%s', $which, $this->describe($blankValue));

        $this->assertInstanceOf(
            InvalidArgumentException::class,
            $thrown,
            'A missing/empty ' . $expectedSetting . ' under aws must throw InvalidArgumentException. ' . $context
        );
        $this->assertStringContainsString(
            $expectedSetting,
            $thrown->getMessage(),
            'The error must name the missing setting. ' . $context
        );

        // FAIL CLOSED: the group must be byte-for-byte identical to the sentinel.
        $this->assertSame(
            $sentinelGroup,
            $group,
            'The target group must be left completely unmodified on a config error. ' . $context
        );
    }

    /**
     * Seeded generator of configuration-failure cases: blanks either the
     * reference or the region with an empty or whitespace-only value, across a
     * variety of sentinel groups. >= 100 iterations plus explicit edge cases.
     *
     * @return iterable<string, array{0:string,1:string,2:array<string,mixed>}>
     */
    public static function provideConfigFailures(): iterable
    {
        $blanks = ['', ' ', '   ', "\t", "\n", " \t \n "];

        // Explicit boundary cases for both settings and empty/populated groups.
        yield 'reference-empty-emptygroup'   => ['reference', '', []];
        yield 'reference-spaces-fullgroup'   => ['reference', '   ', self::fullSentinel(1)];
        yield 'region-empty-emptygroup'      => ['region', '', []];
        yield 'region-tab-fullgroup'         => ['region', "\t", self::fullSentinel(2)];

        for ($seed = 1; $seed <= 110; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $which = mt_rand(0, 1) === 0 ? 'reference' : 'region';
            $blank = $blanks[mt_rand(0, count($blanks) - 1)];
            $group = self::randomSentinel($seed);

            yield "config-seed-{$seed}" => [$which, $blank, $group];
        }
    }

    // =====================================================================
    // Resolution failures (Req 3.3, 3.4, 5.1) — fail closed, group unchanged
    // =====================================================================

    /**
     * Property 4 (resolution errors): every unresolvable-secret failure mode
     * raises a SecretResolutionException that names the Secret_Reference (and the
     * missing field where applicable) and leaves the target group untouched.
     *
     * **Validates: Requirements 3.3, 3.4, 3.6, 5.1, 5.3**
     *
     * @dataProvider provideResolutionFailures
     *
     * @param string              $mode          Failure mode discriminator.
     * @param string|null         $param         Mode-specific parameter.
     * @param string              $reference     Secret name/ARN under test.
     * @param array<string,mixed> $sentinelGroup Known group asserted unchanged.
     */
    public function testResolutionFailureFailsClosedNamingReference(
        string $mode,
        ?string $param,
        string $reference,
        array $sentinelGroup
    ): void {
        $history = new History();
        $mock    = $this->buildMockForMode($mode, $param);

        $service = $this->makeAwsServiceWithMock($reference, $mock, $history);

        $group  = $sentinelGroup;
        $thrown = null;

        try {
            $service->resolveRdsInto($group);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $context = sprintf('mode=%s param=%s ref=%s', $mode, $this->describe((string) $param), $reference);

        // Every resolution failure is a fail-closed SecretResolutionException.
        $this->assertInstanceOf(
            SecretResolutionException::class,
            $thrown,
            'A resolution failure must throw SecretResolutionException. ' . $context
        );

        // It must carry and name the reference (Req 5.1/5.4).
        $this->assertSame(
            $reference,
            $thrown->reference,
            'The exception must carry the Secret_Reference. ' . $context
        );
        $this->assertStringContainsString(
            $reference,
            $thrown->getMessage(),
            'The error message must name the Secret_Reference. ' . $context
        );

        // Missing-field failures must additionally name the missing field (Req 3.4).
        if ($mode === 'missing-field') {
            $this->assertStringContainsString(
                (string) $param,
                $thrown->reason,
                'A missing-field error must name the missing field. ' . $context
            );
            $this->assertStringContainsString(
                (string) $param,
                $thrown->getMessage(),
                'The message must name the missing field. ' . $context
            );
        }

        // FAIL CLOSED: the group must be byte-for-byte identical to the sentinel.
        $this->assertSame(
            $sentinelGroup,
            $group,
            'The target group must be left completely unmodified on a resolution error. ' . $context
        );

        // The failure was produced entirely by the offline mock — no live call.
        // The SDK may retry transient errors (e.g. ThrottlingException) so the
        // mock can be exercised more than once; any count >= 1 against the
        // mock-only handler proves nothing ever reached a live AWS endpoint.
        $this->assertGreaterThanOrEqual(
            1,
            count($history),
            'The failure must originate from the offline mock (no live AWS call). ' . $context
        );
    }

    /**
     * Seeded generator across every resolution-failure mode, with varied
     * references and sentinel groups. >= 100 iterations plus explicit edge
     * cases spanning: AWS exceptions (unreachable / ResourceNotFound /
     * AccessDenied), empty SecretString, invalid JSON, and each missing field.
     *
     * @return iterable<string, array{0:string,1:string|null,2:string,3:array<string,mixed>}>
     */
    public static function provideResolutionFailures(): iterable
    {
        // Explicit representative cases (one per mode).
        yield 'aws-unreachable'      => ['aws-exception', null, 'rds/creds', self::fullSentinel(1)];
        yield 'aws-not-found'        => ['aws-exception', 'ResourceNotFoundException', 'rds/creds', []];
        yield 'aws-access-denied'    => ['aws-exception', 'AccessDeniedException', 'arn:aws:secretsmanager:us-east-1:1:secret:x', self::fullSentinel(2)];
        yield 'empty-secret-string'  => ['empty-secret', '', 'rds/creds', self::fullSentinel(3)];
        yield 'missing-secret-key'   => ['empty-secret', null, 'rds/creds', []];
        yield 'invalid-json-partial' => ['invalid-json', '{"host":"h",', 'rds/creds', self::fullSentinel(4)];
        yield 'invalid-json-scalar'  => ['invalid-json', '"just-a-string"', 'rds/creds', []];
        yield 'missing-host'         => ['missing-field', 'host', 'rds/creds', self::fullSentinel(5)];
        yield 'missing-username'     => ['missing-field', 'username', 'rds/creds', self::fullSentinel(6)];
        yield 'missing-password'     => ['missing-field', 'password', 'rds/creds', []];
        yield 'missing-dbname'       => ['missing-field', 'dbname', 'rds/creds', self::fullSentinel(7)];

        $awsCodes     = [null, 'ResourceNotFoundException', 'AccessDeniedException', 'InternalServiceError', 'ThrottlingException'];
        $malformed    = ['{"host":"h",', 'not-json', '"scalar"', '42', 'null', 'true', '[1,2,3]', '{bad}'];
        $modeWheel    = ['aws-exception', 'empty-secret', 'invalid-json', 'missing-field'];

        for ($seed = 1; $seed <= 116; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $mode      = $modeWheel[$seed % count($modeWheel)];
            $reference = self::randomReference($seed);
            $group     = self::randomSentinel($seed);

            $param = match ($mode) {
                'aws-exception' => $awsCodes[mt_rand(0, count($awsCodes) - 1)],
                'empty-secret'  => mt_rand(0, 1) === 0 ? '' : null,
                'invalid-json'  => $malformed[mt_rand(0, count($malformed) - 1)],
                'missing-field' => self::REQUIRED_FIELDS[mt_rand(0, count(self::REQUIRED_FIELDS) - 1)],
            };

            yield "resolve-seed-{$seed}" => [$mode, $param, $reference, $group];
        }
    }

    // =====================================================================
    // Mock + service builders
    // =====================================================================

    /**
     * Build the AWS SDK MockHandler that reproduces the given failure mode:
     *   - aws-exception: the command yields an AwsException (optional error code)
     *   - empty-secret : a Result with empty or absent SecretString
     *   - invalid-json : a Result whose SecretString is not a valid JSON object
     *   - missing-field: a Result whose JSON blob omits one required field
     */
    private function buildMockForMode(string $mode, ?string $param): MockHandler
    {
        $mock = new MockHandler();

        switch ($mode) {
            case 'aws-exception':
                $mock->append(static function (CommandInterface $cmd) use ($param): AwsException {
                    $context = $param === null ? [] : ['code' => $param];

                    return new AwsException('AWS failure', $cmd, $context);
                });
                break;

            case 'empty-secret':
                // '' => empty SecretString; null => Result with no SecretString key.
                $mock->append($param === null ? new Result([]) : new Result(['SecretString' => $param]));
                break;

            case 'invalid-json':
                $mock->append(new Result(['SecretString' => (string) $param]));
                break;

            case 'missing-field':
                $fields = ['host' => 'h', 'username' => 'u', 'password' => 'p', 'dbname' => 'd'];
                unset($fields[(string) $param]);
                $mock->append(new Result(['SecretString' => json_encode($fields)]));
                break;

            default:
                $this->fail("unknown failure mode {$mode}");
        }

        return $mock;
    }

    /**
     * Build a SecretsManagerService configured for the aws provider whose active
     * provider is a MockHandler-backed AwsSecretProvider injected via reflection
     * (so construction stays offline and resolution never hits the network).
     */
    private function makeAwsServiceWithMock(string $reference, MockHandler $mock, History $history): SecretsManagerService
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = $reference;
        $config->region       = self::REGION;

        // Constructs a real AwsSecretProvider (no network at build time).
        $service = new SecretsManagerService($config);

        // Dummy static credentials so no IAM Instance-Profile / metadata lookup
        // happens; the MockHandler replaces the network handler entirely.
        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);
        $client->getHandlerList()->appendSign(Middleware::history($history));

        $mockProvider = new AwsSecretProvider($config, $client);

        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $mockProvider);

        return $service;
    }

    // =====================================================================
    // Sentinel + reference generators
    // =====================================================================

    /**
     * A fully-populated sentinel connection group (the kind of "existing"
     * credentials that must survive a failed resolve untouched).
     *
     * @return array<string,mixed>
     */
    private static function fullSentinel(int $seed): array
    {
        return [
            'hostname' => 'existing-host-' . $seed,
            'username' => 'existing-user-' . $seed,
            'password' => 'existing-pass-' . $seed,
            'database' => 'existing-db-' . $seed,
            'port'     => 3306,
            'DBDriver' => 'MySQLi',
        ];
    }

    /**
     * A deterministic, varied sentinel group: sometimes empty, sometimes
     * partially/fully populated, sometimes carrying unrelated keys. Whatever it
     * is, it must be returned identical after a failed resolve.
     *
     * @return array<string,mixed>
     */
    private static function randomSentinel(int $seed): array
    {
        switch ($seed % 4) {
            case 0:
                return [];
            case 1:
                return ['hostname' => 'keep-' . $seed];
            case 2:
                return self::fullSentinel($seed);
            default:
                return [
                    'hostname' => 'h' . $seed,
                    'database' => 'd' . $seed,
                    'charset'  => 'utf8mb4',
                    'port'     => 3306 + ($seed % 50),
                ];
        }
    }

    /** Deterministic, varied Secret_Reference (name or ARN). Never a value. */
    private static function randomReference(int $seed): string
    {
        if ($seed % 3 === 0) {
            return sprintf('arn:aws:secretsmanager:us-east-1:%012d:secret:sgl/ref-%d-AbCdEf', $seed, $seed);
        }

        return 'sgl/ref-' . $seed . '/' . ($seed % 2 === 0 ? 'rds' : 'db-creds');
    }

    /** Render a raw value for human-readable assertion messages. */
    private function describe(string $value): string
    {
        return "'" . str_replace(["\n", "\t"], ['\\n', '\\t'], $value) . "'";
    }
}
