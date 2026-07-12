<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\EnvSecretProvider;
use App\Libraries\Secrets\SecretProvider;
use App\Libraries\Secrets\SecretResolutionException;
use App\Libraries\Secrets\SecretsManagerService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Unit tests for SecretsManagerService (task 6.4).
 *
 * Covers provider selection, the RDS field-mapping (port default 3306), the
 * aws-only configuration assertions, and the fail-closed contract of
 * resolveRdsInto():
 *
 *   - `env` / `aws` select the right provider (Req 1.2, 1.3, indirectly).
 *   - An absent provider flag defaults to `env` (Req 1.4, 10.1).
 *   - An invalid flag throws a descriptive error naming the offending value
 *     and the accepted values `aws`, `env` (Req 1.5).
 *   - A missing reference or region under `aws` throws naming the setting
 *     (Req 2.3, 2.4).
 *   - resolveRdsInto() populates all five connection-group keys and defaults
 *     the port to 3306 when the secret omits it (Req 3.2, 3.5).
 *   - A failing resolve (assertion error OR provider throw) leaves the target
 *     `$group` completely unmodified — fail closed (Req 5.3).
 *
 * The service's active provider is a private property; it is inspected and, for
 * the mapping/fail-closed cases, swapped for an in-memory fake SecretProvider
 * via reflection so no AWS SDK / network call is ever made.
 *
 * @internal
 */
final class SecretsManagerServiceTest extends CIUnitTestCase
{
    // ---------------------------------------------------------------------
    // Reflection helpers
    // ---------------------------------------------------------------------

    /** Read the service's private `provider` property. */
    private function readProvider(SecretsManagerService $service): SecretProvider
    {
        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);

        return $prop->getValue($service);
    }

    /** Swap the service's private `provider` for an in-memory fake. */
    private function swapProvider(SecretsManagerService $service, SecretProvider $fake): void
    {
        $prop = (new ReflectionClass($service))->getProperty('provider');
        $prop->setAccessible(true);
        $prop->setValue($service, $fake);
    }

    /** A fake provider that returns a fixed map (no AWS, no network). */
    private function fakeReturning(array $map): SecretProvider
    {
        return new class ($map) implements SecretProvider {
            public function __construct(private array $map)
            {
            }

            public function getSecret(string $reference): array
            {
                return $this->map;
            }
        };
    }

    /** A fake provider that always fails closed with the given reason. */
    private function fakeThrowing(string $reference, string $reason): SecretProvider
    {
        return new class ($reference, $reason) implements SecretProvider {
            public function __construct(private string $reference, private string $reason)
            {
            }

            public function getSecret(string $reference): array
            {
                throw new SecretResolutionException($this->reference, $this->reason);
            }
        };
    }

    // ---------------------------------------------------------------------
    // Provider selection (Req 1.2, 1.3)
    // ---------------------------------------------------------------------

    public function testEnvFlagSelectsEnvSecretProvider(): void
    {
        $config           = new SecretsConfig();
        $config->provider = 'env';

        $service = new SecretsManagerService($config);

        $this->assertInstanceOf(
            EnvSecretProvider::class,
            $this->readProvider($service),
            "provider 'env' must select the EnvSecretProvider"
        );
    }

    public function testAwsFlagSelectsAwsSecretProvider(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'sgl/prod/rds';
        $config->region       = 'us-east-1';

        $service = new SecretsManagerService($config);

        $this->assertInstanceOf(
            AwsSecretProvider::class,
            $this->readProvider($service),
            "provider 'aws' must select the AwsSecretProvider"
        );
    }

    // ---------------------------------------------------------------------
    // Absent flag defaults to env (Req 1.4, 10.1)
    // ---------------------------------------------------------------------

    public function testAbsentProviderFlagDefaultsToEnv(): void
    {
        // Snapshot + clear SECRETS_PROVIDER across every source CI4 env() reads.
        $snap = [
            'env'    => $_ENV['SECRETS_PROVIDER'] ?? null,
            'server' => $_SERVER['SECRETS_PROVIDER'] ?? null,
            'getenv' => getenv('SECRETS_PROVIDER'),
        ];
        unset($_ENV['SECRETS_PROVIDER'], $_SERVER['SECRETS_PROVIDER']);
        putenv('SECRETS_PROVIDER');

        try {
            $config = new SecretsConfig();

            // Config layer applies the 'env' default when the flag is absent.
            $this->assertSame('env', $config->provider, 'absent SECRETS_PROVIDER must default to env');

            $service = new SecretsManagerService($config);

            $this->assertInstanceOf(
                EnvSecretProvider::class,
                $this->readProvider($service),
                'an absent provider flag must select the EnvSecretProvider (current behavior)'
            );
        } finally {
            if ($snap['env'] === null) {
                unset($_ENV['SECRETS_PROVIDER']);
            } else {
                $_ENV['SECRETS_PROVIDER'] = $snap['env'];
            }
            if ($snap['server'] === null) {
                unset($_SERVER['SECRETS_PROVIDER']);
            } else {
                $_SERVER['SECRETS_PROVIDER'] = $snap['server'];
            }
            if ($snap['getenv'] === false) {
                putenv('SECRETS_PROVIDER');
            } else {
                putenv('SECRETS_PROVIDER=' . $snap['getenv']);
            }
        }
    }

    // ---------------------------------------------------------------------
    // Invalid flag rejected naming value + accepted values (Req 1.5)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideInvalidФlags
     */
    public function testInvalidProviderFlagThrowsNamingValueAndAcceptedValues(string $flag): void
    {
        $config           = new SecretsConfig();
        $config->provider = $flag;

        try {
            new SecretsManagerService($config);
            $this->fail("an invalid provider flag '{$flag}' must be rejected");
        } catch (InvalidArgumentException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString($flag, $message, 'error must name the offending value');
            $this->assertStringContainsString('aws', $message, 'error must list accepted value aws');
            $this->assertStringContainsString('env', $message, 'error must list accepted value env');
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideInvalidФlags(): array
    {
        return [
            'vault'       => ['vault'],
            'AWS-upper'   => ['AWS'],
            'gcp'         => ['gcp'],
            'random word' => ['secretsmanager'],
        ];
    }

    // ---------------------------------------------------------------------
    // Missing reference / region under aws throws (Req 2.3, 2.4)
    // ---------------------------------------------------------------------

    public function testMissingReferenceUnderAwsThrowsNamingReference(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'placeholder'; // valid so the service constructs
        $config->region       = 'us-east-1';

        $service = new SecretsManagerService($config);

        // Now blank the reference so assertConfigured() rejects it at resolve time.
        $config->rdsReference = '';

        $group = ['hostname' => 'unchanged'];

        try {
            $service->resolveRdsInto($group);
            $this->fail('a missing SECRETS_RDS_REFERENCE under aws must throw');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('SECRETS_RDS_REFERENCE', $e->getMessage());
        }

        $this->assertSame(['hostname' => 'unchanged'], $group, 'group must be untouched on config error');
    }

    public function testMissingRegionUnderAwsThrowsNamingRegion(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'sgl/prod/rds';
        $config->region       = 'us-east-1'; // valid so the service constructs

        $service = new SecretsManagerService($config);

        // Now blank the region so assertConfigured() rejects it at resolve time.
        $config->region = '';

        $group = ['hostname' => 'unchanged'];

        try {
            $service->resolveRdsInto($group);
            $this->fail('a missing SECRETS_REGION under aws must throw');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('SECRETS_REGION', $e->getMessage());
        }

        $this->assertSame(['hostname' => 'unchanged'], $group, 'group must be untouched on config error');
    }

    public function testWhitespaceOnlyReferenceUnderAwsIsRejected(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'aws';
        $config->rdsReference = 'placeholder';
        $config->region       = 'us-east-1';

        $service = new SecretsManagerService($config);

        $config->rdsReference = '   '; // whitespace only -> treated as missing

        $group = [];
        $this->expectException(InvalidArgumentException::class);
        $service->resolveRdsInto($group);
    }

    // ---------------------------------------------------------------------
    // resolveRdsInto maps all five keys and defaults port to 3306 (Req 3.2, 3.5)
    // ---------------------------------------------------------------------

    public function testResolveRdsIntoPopulatesAllFiveKeysWithGivenPort(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'env'; // no assertConfigured requirements
        $config->rdsReference = 'rds/creds';

        $service = new SecretsManagerService($config);
        $this->swapProvider($service, $this->fakeReturning([
            'host'     => 'db.internal',
            'username' => 'sgl_app',
            'password' => 's3cr3t-p@ss',
            'dbname'   => 'procedures',
            'port'     => 3307,
        ]));

        $group = [];
        $service->resolveRdsInto($group);

        $this->assertSame('db.internal', $group['hostname']);
        $this->assertSame('sgl_app', $group['username']);
        $this->assertSame('s3cr3t-p@ss', $group['password']);
        $this->assertSame('procedures', $group['database']);
        $this->assertSame(3307, $group['port']);
        $this->assertSame(
            ['hostname', 'username', 'password', 'database', 'port'],
            array_keys($group),
            'exactly the five connection-group keys must be populated'
        );
    }

    public function testResolveRdsIntoDefaultsPortTo3306WhenOmitted(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'env';
        $config->rdsReference = 'rds/creds';

        $service = new SecretsManagerService($config);
        $this->swapProvider($service, $this->fakeReturning([
            'host'     => 'h',
            'username' => 'u',
            'password' => 'p',
            'dbname'   => 'd',
            // no port
        ]));

        $group = [];
        $service->resolveRdsInto($group);

        $this->assertSame('h', $group['hostname']);
        $this->assertSame('u', $group['username']);
        $this->assertSame('p', $group['password']);
        $this->assertSame('d', $group['database']);
        $this->assertSame(3306, $group['port'], 'port must default to the MySQL default 3306 when omitted');
    }

    public function testResolveRdsIntoCastsPortToInt(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'env';
        $config->rdsReference = 'rds/creds';

        $service = new SecretsManagerService($config);
        $this->swapProvider($service, $this->fakeReturning([
            'host'     => 'h',
            'username' => 'u',
            'password' => 'p',
            'dbname'   => 'd',
            'port'     => '5432', // numeric string
        ]));

        $group = [];
        $service->resolveRdsInto($group);

        $this->assertSame(5432, $group['port'], 'port must be cast to int');
    }

    // ---------------------------------------------------------------------
    // Fail closed: a failing resolve leaves the group unchanged (Req 5.3)
    // ---------------------------------------------------------------------

    public function testFailingResolveLeavesGroupUnchanged(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'env';
        $config->rdsReference = 'rds/creds';

        $service = new SecretsManagerService($config);
        $this->swapProvider($service, $this->fakeThrowing('rds/creds', 'invalid-json'));

        $original = [
            'hostname' => 'existing-host',
            'username' => 'existing-user',
            'password' => 'existing-pass',
            'database' => 'existing-db',
            'port'     => 3306,
        ];
        $group = $original;

        try {
            $service->resolveRdsInto($group);
            $this->fail('a failing provider resolve must propagate (fail closed)');
        } catch (SecretResolutionException $e) {
            $this->assertSame('rds/creds', $e->reference);
            $this->assertSame('invalid-json', $e->reason);
        }

        $this->assertSame($original, $group, 'group must be left completely unmodified on a failed resolve');
    }

    public function testFailingResolveDoesNotPartiallyPopulateEmptyGroup(): void
    {
        $config               = new SecretsConfig();
        $config->provider     = 'env';
        $config->rdsReference = 'rds/creds';

        $service = new SecretsManagerService($config);
        $this->swapProvider($service, $this->fakeThrowing('rds/creds', 'aws-unreachable'));

        $group = [];

        try {
            $service->resolveRdsInto($group);
            $this->fail('a failing provider resolve must propagate (fail closed)');
        } catch (SecretResolutionException $e) {
            // expected
        }

        $this->assertSame([], $group, 'no partial credentials may be written on failure');
    }
}
