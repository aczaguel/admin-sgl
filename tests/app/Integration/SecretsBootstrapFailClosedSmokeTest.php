<?php

namespace Tests\App\Integration;

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
use Config\Database as DatabaseConfig;
use Config\Secrets as SecretsConfig;
use ReflectionClass;
use Throwable;

/**
 * Fail-closed bootstrap smoke test (task 10.2).
 *
 * **Validates: Requirements 5.1, 5.3, 3.6**
 *
 * A handful of focused assertions covering the two guarantees the bootstrap
 * (task 7.2) must uphold when Config\Database is constructed:
 *
 *   1. Testing short-circuit (Req 3.6): under ENVIRONMENT === 'testing' the
 *      Config\Database constructor sets defaultGroup='tests' and RETURNS
 *      WITHOUT resolving secrets — it never calls resolveRdsInto(), never
 *      contacts AWS, and never throws. The pristine in-memory SQLite `tests`
 *      group is left in place.
 *
 *   2. Fail closed on an unresolvable secret (Req 5.1, 5.3): the exact uncaught
 *      call the non-testing constructor makes —
 *      `service('secrets')->resolveRdsInto($this->default)` — must propagate the
 *      SecretResolutionException out of resolveRdsInto so NO connection is ever
 *      opened, and it must leave the target `default` group BYTE-FOR-BYTE
 *      UNMODIFIED (no partial credentials).
 *
 * The constructor skips resolution under testing, so the fail-closed leg
 * simulates the non-testing bootstrap path by invoking resolveRdsInto() directly
 * on a real Config\Database `default` group with a MockHandler-backed
 * AwsSecretProvider that fails — mirroring the sibling
 * {@see SecretsBootstrapIntegrationTest} and
 * {@see \Tests\App\Libraries\Secrets\SecretsManagerServiceFailClosedPropertyTest}.
 * No live AWS call is ever made.
 *
 * @internal
 */
final class SecretsBootstrapFailClosedSmokeTest extends CIUnitTestCase
{
    /** A representative RDS secret reference (name/ARN). Never a value. */
    private const REFERENCE = 'sgl/prod/rds';

    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    // =====================================================================
    // Testing short-circuit — resolution skipped entirely (Req 3.6)
    // =====================================================================

    /**
     * Under the testing environment, constructing Config\Database short-circuits
     * to the in-memory SQLite `tests` group and never resolves secrets: it does
     * not throw and never contacts AWS. This proves the constructor's guard
     * (task 7.2) skips resolution entirely during the test suite.
     */
    public function testTestingEnvironmentSkipsSecretResolution(): void
    {
        $this->assertSame('testing', ENVIRONMENT, 'the suite must run in the testing environment');

        // Construction must not throw — no resolveRdsInto(), no AWS contact.
        $db = new DatabaseConfig();

        // Observable proof the short-circuit ran: default group switched to
        // 'tests' targeting in-memory SQLite (never a resolved RDS host).
        $this->assertSame('tests', $db->defaultGroup, 'testing must select the in-memory tests group');
        $this->assertSame(':memory:', $db->tests['database'], 'the testing group must use in-memory SQLite');
        $this->assertSame('SQLite3', $db->tests['DBDriver'], 'the testing group must use the SQLite driver');
    }

    // =====================================================================
    // Fail closed — unresolvable secret propagates, group untouched (Req 5.1, 5.3)
    // =====================================================================

    /**
     * An unreachable Secrets Manager (AwsException) makes resolveRdsInto()
     * propagate a SecretResolutionException that names the reference, and leaves
     * the real Config\Database `default` group byte-for-byte unmodified — so the
     * bootstrap never opens a connection with partial credentials (Req 5.1, 5.3).
     */
    public function testUnreachableSecretPropagatesAndLeavesDefaultGroupUnmodified(): void
    {
        $history = new History();
        $mock    = new MockHandler();
        $mock->append(static function (CommandInterface $cmd): AwsException {
            // No error code => an "unreachable"-style AWS failure.
            return new AwsException('AWS endpoint unreachable', $cmd);
        });

        $service = $this->makeAwsServiceWithMock($mock, $history);

        // The exact connection-group shape the bootstrap hands to resolveRdsInto.
        $pristine = (new DatabaseConfig())->default;
        $group    = $pristine;

        $thrown = null;

        try {
            $service->resolveRdsInto($group);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(
            SecretResolutionException::class,
            $thrown,
            'an unreachable secret must propagate a SecretResolutionException (fail closed)'
        );
        $this->assertSame(self::REFERENCE, $thrown->reference, 'the exception must carry the Secret_Reference');
        $this->assertStringContainsString(self::REFERENCE, $thrown->getMessage(), 'the error must name the reference');

        // FAIL CLOSED: no partial credentials — the group is completely unchanged.
        $this->assertSame($pristine, $group, 'the default group must be left byte-for-byte unmodified');

        // The failure originated from the offline mock — no live AWS call.
        $this->assertGreaterThanOrEqual(1, count($history), 'the failure must come from the offline mock (no live AWS call)');
    }

    /**
     * A missing secret (ResourceNotFoundException) likewise fails closed: the
     * exception propagates out of resolveRdsInto and the target `default` group
     * is left completely unmodified, so no connection is opened (Req 5.1, 5.3).
     */
    public function testMissingSecretPropagatesAndLeavesDefaultGroupUnmodified(): void
    {
        $history = new History();
        $mock    = new MockHandler();
        $mock->append(static function (CommandInterface $cmd): AwsException {
            return new AwsException('Secret not found', $cmd, ['code' => 'ResourceNotFoundException']);
        });

        $service = $this->makeAwsServiceWithMock($mock, $history);

        $pristine = (new DatabaseConfig())->default;
        $group    = $pristine;

        $thrown = null;

        try {
            $service->resolveRdsInto($group);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(
            SecretResolutionException::class,
            $thrown,
            'a non-existent reference must propagate a SecretResolutionException (fail closed)'
        );
        $this->assertSame('ResourceNotFoundException', $thrown->reason, 'the reason must reflect the missing secret');
        $this->assertStringContainsString(self::REFERENCE, $thrown->getMessage(), 'the error must name the reference');

        // FAIL CLOSED: the group must be identical to the pristine default.
        $this->assertSame($pristine, $group, 'the default group must be left byte-for-byte unmodified');

        $this->assertGreaterThanOrEqual(1, count($history), 'the failure must come from the offline mock (no live AWS call)');
    }

    // =====================================================================
    // Helper
    // =====================================================================

    /**
     * Build a SecretsManagerService in `aws` mode whose active provider is a
     * MockHandler-backed AwsSecretProvider injected via reflection, so the whole
     * resolution runs offline. Dummy static credentials keep the SDK from
     * reaching the EC2 metadata endpoint.
     */
    private function makeAwsServiceWithMock(MockHandler $mock, History $history): SecretsManagerService
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = self::REFERENCE;
        $config->region       = self::REGION;

        $service = new SecretsManagerService($config);

        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);
        $client->getHandlerList()->appendSign(Middleware::history($history));

        $awsProvider = new AwsSecretProvider($config, $client);

        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $awsProvider);

        return $service;
    }
}
