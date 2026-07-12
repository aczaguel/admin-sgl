<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use App\Libraries\Secrets\SecretResolutionException;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\History;
use Aws\Middleware;
use Aws\MockHandler;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;

/**
 * Contract tests for AwsSecretProvider, backed by the AWS SDK MockHandler
 * (no live AWS calls are ever made).
 *
 * Validates:
 *   - Req 3.1  valid JSON blob is parsed and the mapped fields are returned
 *   - Req 5.1  AwsException (unreachable / ResourceNotFound / AccessDenied)
 *              throws SecretResolutionException naming the reference + reason
 *   - Req 3.3  empty SecretString and malformed JSON throw the matching reason
 *   - Req 3.4  each missing required field throws reason `missing-field:<name>`
 *   - Req 6.1  the client is exercised entirely offline via the MockHandler,
 *              so no live AWS call ever occurs
 *
 * @internal
 */
final class AwsSecretProviderContractTest extends CIUnitTestCase
{
    /** A representative RDS secret reference (name/ARN). Never a value. */
    private const REFERENCE = 'sgl/prod/rds';

    /** AWS region used for every offline test client. */
    private const REGION = 'us-east-1';

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Build an AwsSecretProvider backed by a MockHandler-driven client.
     *
     * Dummy static credentials are supplied so no IAM Instance-Profile /
     * EC2 metadata lookup happens in tests. The MockHandler replaces the
     * network handler so no request ever leaves the process.
     *
     * @param History|null $history Optional history middleware to assert that
     *                              every command was handled by the mock (i.e.
     *                              no live AWS call).
     */
    private function makeProvider(MockHandler $mock, ?History $history = null): AwsSecretProvider
    {
        $config         = new SecretsConfig();
        $config->region = self::REGION;

        $client = new SecretsManagerClient([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);

        if ($history !== null) {
            $client->getHandlerList()->appendSign(Middleware::history($history));
        }

        return new AwsSecretProvider($config, $client);
    }

    /** A GetSecretValue Result whose SecretString is the given raw string. */
    private function secretString(string $raw): Result
    {
        return new Result(['SecretString' => $raw]);
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

    // ---------------------------------------------------------------------
    // valid JSON blob -> mapped fields
    // ---------------------------------------------------------------------

    public function testValidJsonBlobReturnsMappedFields(): void
    {
        $history = new History();
        $mock    = new MockHandler();
        $mock->append($this->secretString($this->validBlob()));

        $provider = $this->makeProvider($mock, $history);
        $map      = $provider->getSecret(self::REFERENCE);

        $this->assertSame('sgl-prod.abcdef.us-east-1.rds.amazonaws.com', $map['host']);
        $this->assertSame('sgl_app', $map['username']);
        $this->assertSame('s3cr3t-p@ss', $map['password']);
        $this->assertSame('procedures', $map['dbname']);
        $this->assertSame(3306, $map['port']);

        // The SecretId sent must be the reference, never a value.
        $command = $mock->getLastCommand();
        $this->assertNotNull($command);
        $this->assertSame('GetSecretValue', $command->getName());
        $this->assertSame(self::REFERENCE, $command['SecretId']);

        // Every command was served by the mock => no live AWS call.
        $this->assertCount(1, $history, 'exactly one offline GetSecretValue should occur');
    }

    public function testValidBlobWithoutPortIsAcceptedAndPortLeftToCaller(): void
    {
        $mock = new MockHandler();
        $mock->append($this->secretString(json_encode([
            'username' => 'u',
            'password' => 'p',
            'host'     => 'h',
            'dbname'   => 'd',
        ])));

        $map = $this->makeProvider($mock)->getSecret(self::REFERENCE);

        $this->assertSame('h', $map['host']);
        $this->assertSame('u', $map['username']);
        $this->assertSame('p', $map['password']);
        $this->assertSame('d', $map['dbname']);
        $this->assertArrayNotHasKey('port', $map, 'omitted port is not injected by the provider');
    }

    // ---------------------------------------------------------------------
    // AwsException -> throws with reference + reason (Req 5.1, 6.1)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideAwsErrorCodes
     */
    public function testAwsExceptionThrowsWithReferenceAndReason(?string $awsCode, string $expectedReason): void
    {
        $history = new History();
        $mock    = new MockHandler();
        $mock->append(static function (CommandInterface $cmd) use ($awsCode): AwsException {
            $context = $awsCode === null ? [] : ['code' => $awsCode];

            return new AwsException('AWS failure', $cmd, $context);
        });

        $provider = $this->makeProvider($mock, $history);

        try {
            $provider->getSecret(self::REFERENCE);
            $this->fail('expected SecretResolutionException was not thrown');
        } catch (SecretResolutionException $e) {
            $this->assertSame(self::REFERENCE, $e->reference);
            $this->assertSame($expectedReason, $e->reason);
            $this->assertStringContainsString(self::REFERENCE, $e->getMessage());
            $this->assertStringContainsString($expectedReason, $e->getMessage());
        }

        // The failure was produced by the mock, not by a live AWS call.
        $this->assertCount(1, $history, 'the AWS error must originate from the offline mock');
    }

    /**
     * @return array<string, array{0: string|null, 1: string}>
     */
    public function provideAwsErrorCodes(): array
    {
        return [
            'resource not found' => ['ResourceNotFoundException', 'ResourceNotFoundException'],
            'access denied'      => ['AccessDeniedException', 'AccessDeniedException'],
            'unreachable/no code' => [null, 'aws-unreachable'],
        ];
    }

    // ---------------------------------------------------------------------
    // empty SecretString / malformed JSON / missing field (Req 3.3, 3.4)
    // ---------------------------------------------------------------------

    public function testEmptySecretStringThrowsEmptySecretString(): void
    {
        $mock = new MockHandler();
        $mock->append($this->secretString(''));

        $this->assertReasonForMock($mock, 'empty-secret-string');
    }

    public function testMissingSecretStringThrowsEmptySecretString(): void
    {
        $mock = new MockHandler();
        $mock->append(new Result([])); // no SecretString key at all

        $this->assertReasonForMock($mock, 'empty-secret-string');
    }

    /**
     * @dataProvider provideMalformedJson
     */
    public function testMalformedJsonThrowsInvalidJson(string $raw): void
    {
        $mock = new MockHandler();
        $mock->append($this->secretString($raw));

        $this->assertReasonForMock($mock, 'invalid-json');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideMalformedJson(): array
    {
        return [
            'truncated object' => ['{"host":"h",'],
            'not json'         => ['this-is-not-json'],
            'json scalar'      => ['"just-a-string"'],
            'json number'      => ['42'],
            'json null'        => ['null'],
        ];
    }

    /**
     * @dataProvider provideMissingFields
     */
    public function testMissingRequiredFieldThrowsMatchingReason(string $missing): void
    {
        $fields = [
            'username' => 'u',
            'password' => 'p',
            'host'     => 'h',
            'dbname'   => 'd',
        ];
        unset($fields[$missing]);

        $mock = new MockHandler();
        $mock->append($this->secretString(json_encode($fields)));

        $this->assertReasonForMock($mock, 'missing-field:' . $missing);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideMissingFields(): array
    {
        return [
            'missing host'     => ['host'],
            'missing username' => ['username'],
            'missing password' => ['password'],
            'missing dbname'   => ['dbname'],
        ];
    }

    public function testEmptyRequiredFieldIsTreatedAsMissing(): void
    {
        $mock = new MockHandler();
        $mock->append($this->secretString(json_encode([
            'username' => 'u',
            'password' => '   ', // blank -> treated as missing
            'host'     => 'h',
            'dbname'   => 'd',
        ])));

        $this->assertReasonForMock($mock, 'missing-field:password');
    }

    // ---------------------------------------------------------------------
    // Shared assertion helper
    // ---------------------------------------------------------------------

    /**
     * Assert that resolving through a MockHandler-backed provider throws a
     * SecretResolutionException carrying the reference and the expected reason,
     * with no live AWS call (every command handled by the offline mock).
     */
    private function assertReasonForMock(MockHandler $mock, string $expectedReason): void
    {
        $history  = new History();
        $provider = $this->makeProvider($mock, $history);

        try {
            $provider->getSecret(self::REFERENCE);
            $this->fail("expected SecretResolutionException with reason {$expectedReason}");
        } catch (SecretResolutionException $e) {
            $this->assertSame(self::REFERENCE, $e->reference);
            $this->assertSame($expectedReason, $e->reason);
        }

        $this->assertCount(1, $history, 'resolution must be served entirely by the offline mock');
    }
}
