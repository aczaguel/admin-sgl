<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\S3FileStorage;
use Aws\CommandInterface;
use Aws\History;
use Aws\Middleware;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;
use GuzzleHttp\Psr7\Response;

/**
 * Contract tests for the S3 storage driver, backed by the AWS SDK MockHandler
 * (no live AWS calls).
 *
 * Validates:
 *   - Req 2.5  presigned GET URL is produced for a valid ttl
 *   - Req 2.6  put/delete/exists reject traversal keys before any S3 action
 *   - Req 2.7  url() with ttl <= 0 returns an error indication ('') and no URL
 *   - Req 2.8  url() with ttl > 604800 returns an error indication ('') and no URL
 *   - Req 10.3 PutObject requests server-side encryption
 *   - Req 10.7 (round-trip put -> exists -> url -> delete behaves as contracted)
 *
 * @internal
 */
final class S3FileStorageContractTest extends CIUnitTestCase
{
    /** Bucket used for every test client. */
    private const BUCKET = 'test-bucket';

    /** SSE algorithm expected on every PutObject (Req 10.3). */
    private const SSE = 'AES256';

    /** A real temp file so PutObject's SourceFile can be serialized offline. */
    private string $tmpFile = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpFile = tempnam(sys_get_temp_dir(), 's3contract_');
        file_put_contents($this->tmpFile, 'contract-test-bytes');
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && is_file($this->tmpFile)) {
            @unlink($this->tmpFile);
        }

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Build an S3FileStorage backed by a MockHandler-driven client.
     *
     * Dummy static credentials are supplied so presigned-URL signing happens
     * entirely offline (no Instance-Profile / metadata lookup in tests). The
     * MockHandler replaces the network handler so no request ever leaves.
     */
    private function makeStorage(MockHandler $mock, ?History $history = null): S3FileStorage
    {
        $config         = new FileStorageConfig();
        $config->bucket = self::BUCKET;
        $config->region = 'us-east-1';
        $config->sse    = self::SSE;

        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);

        if ($history !== null) {
            $client->getHandlerList()->appendSign(Middleware::history($history));
        }

        return new S3FileStorage($config, $client);
    }

    /** A 404 S3Exception for the given command, so doesObjectExist() -> false. */
    private function notFound(CommandInterface $command): S3Exception
    {
        return new S3Exception(
            'Not Found',
            $command,
            ['code' => 'NotFound', 'response' => new Response(404)]
        );
    }

    // ---------------------------------------------------------------------
    // put -> exists -> url -> delete round-trip
    // ---------------------------------------------------------------------

    public function testPutExistsUrlDeleteRoundTrip(): void
    {
        $key  = 'pago_gestor/12472/abc12345.jpg';
        $mock = new MockHandler();

        // 1) put -> PutObject succeeds.
        $mock->append(new Result([]));
        // 2) exists (before delete) -> HeadObject succeeds => true.
        $mock->append(new Result([]));
        // 3) delete -> DeleteObject succeeds.
        $mock->append(new Result([]));
        // 4) exists (after delete) -> HeadObject 404 => false.
        $mock->append(fn (CommandInterface $cmd) => $this->notFound($cmd));

        $storage = $this->makeStorage($mock);

        $this->assertTrue($storage->put($key, $this->tmpFile), 'put should succeed');
        $this->assertTrue($storage->exists($key), 'exists should be true after put');

        $url = $storage->url($key, 300);
        $this->assertNotSame('', $url, 'url should produce a presigned URL for a valid ttl');
        $this->assertStringStartsWith('https://', $url, 'presigned URL should be an https URL');
        $this->assertStringContainsString('X-Amz-Signature', $url, 'presigned URL should be SigV4-signed');

        $this->assertTrue($storage->delete($key), 'delete should succeed');
        $this->assertFalse($storage->exists($key), 'exists should be false after delete');
    }

    // ---------------------------------------------------------------------
    // delete idempotence on an absent key
    // ---------------------------------------------------------------------

    public function testDeleteIsIdempotentOnAbsentKey(): void
    {
        $key  = 'evidencias/never-written.png';
        $mock = new MockHandler();

        // S3 DeleteObject returns success even when the key is absent.
        $mock->append(new Result([]));
        $mock->append(new Result([]));

        $storage = $this->makeStorage($mock);

        $this->assertTrue($storage->delete($key), 'first delete of absent key returns true');
        $this->assertTrue($storage->delete($key), 'repeated delete of absent key returns true');
    }

    // ---------------------------------------------------------------------
    // traversal rejection: no S3 call issued
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideTraversalKeys
     */
    public function testTraversalKeysAreRejectedWithoutAnyS3Call(string $badKey): void
    {
        $history = new History();
        // Empty queue: if any operation reached the handler it would throw,
        // but the driver rejects the key first. History confirms 0 calls.
        $storage = $this->makeStorage(new MockHandler(), $history);

        $this->assertFalse($storage->put($badKey, $this->tmpFile), "put must reject: {$badKey}");
        $this->assertFalse($storage->delete($badKey), "delete must reject: {$badKey}");
        $this->assertFalse($storage->exists($badKey), "exists must reject: {$badKey}");
        $this->assertSame('', $storage->url($badKey, 300), "url must return '' for: {$badKey}");

        $this->assertCount(0, $history, "no S3 request should be issued for traversal key: {$badKey}");
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideTraversalKeys(): array
    {
        return [
            'dotdot segment'          => ['pago_gestor/../secret.jpg'],
            'dotdot only'             => ['..'],
            'leading dotdot'          => ['../etc/passwd'],
            'leading slash'           => ['/absolute/key.png'],
            'backslash'               => ['evidencias\\win\\file.png'],
            'trailing dotdot segment' => ['a/b/..'],
            'empty key'               => [''],
        ];
    }

    // ---------------------------------------------------------------------
    // SSE header present on PutObject (Req 10.3)
    // ---------------------------------------------------------------------

    public function testPutObjectRequestsServerSideEncryption(): void
    {
        $key  = 'documentostatus/enc12345.pdf';
        $mock = new MockHandler();
        $mock->append(new Result([]));

        $storage = $this->makeStorage($mock);

        $this->assertTrue($storage->put($key, $this->tmpFile));

        $command = $mock->getLastCommand();
        $this->assertNotNull($command, 'a PutObject command should have been sent');
        $this->assertSame('PutObject', $command->getName());
        $this->assertArrayHasKey('ServerSideEncryption', $command->toArray(), 'SSE param must be present on PutObject');
        $this->assertSame(self::SSE, $command['ServerSideEncryption'], 'SSE must equal the configured algorithm');
        $this->assertSame(self::BUCKET, $command['Bucket']);
        $this->assertSame($key, $command['Key']);
    }

    // ---------------------------------------------------------------------
    // ttl out of range -> error indication, no presign (Req 2.7, 2.8)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideOutOfRangeTtls
     */
    public function testUrlRejectsOutOfRangeTtlWithoutPresigning(int $ttl): void
    {
        $history = new History();
        // Empty queue is fine: presigning never touches the handler, and an
        // out-of-range ttl must short-circuit before producing any URL.
        $storage = $this->makeStorage(new MockHandler(), $history);

        $url = $storage->url('evidencias/some12345.png', $ttl);

        $this->assertSame('', $url, "url must return '' (error indication) for ttl={$ttl}");
        $this->assertCount(0, $history, 'no S3 request should be issued for an out-of-range ttl');
    }

    /**
     * @return array<string, array{0: int}>
     */
    public function provideOutOfRangeTtls(): array
    {
        return [
            'zero ttl'          => [0],
            'negative ttl'      => [-1],
            'large negative'    => [-604800],
            'just over max'     => [604801],
            'far over max'      => [1_000_000],
        ];
    }
}
