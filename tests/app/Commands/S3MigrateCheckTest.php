<?php

namespace Tests\App\Commands;

use App\Commands\S3MigrateCheck;
use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\FileStorage as FileStorageConfig;
use Config\Services;
use GuzzleHttp\Psr7\Response;

/**
 * Unit tests for the s3:migrate-check command (task 11.2).
 *
 * The command is `final`, so tests drive it through its real injection seams —
 * NOT a subclass — while still avoiding live AWS and any dependence on the real
 * uploads tree:
 *   - setConfig() points localRoot at a per-test temp directory whose file
 *     count is fully controlled here, so countLocalFiles() returns a known
 *     value with no *.tmp files involved.
 *   - setS3Client() injects a MockHandler-backed S3Client so countS3Objects()
 *     (ListObjectsV2 paginator) returns a fixed KeyCount, or throws to simulate
 *     an unreachable bucket. Dummy static credentials keep signing fully
 *     offline (no IAM metadata lookup).
 * CLI output is captured with CITestStreamFilter (project convention, see
 * DispatchExternalWebhooksCommandTest).
 *
 * Coverage (Requirements 8.3, 8.4, 8.5, 8.8, 8.9):
 *   - drift <= 0 (local <= s3)     => EXIT_SUCCESS, "counterpart" message  (8.4)
 *   - local > s3                   => EXIT_SUCCESS, warning with drift value (8.5)
 *   - zero local files             => EXIT_SUCCESS, drift 0                 (8.9)
 *   - bucket unreachable (throws)  => EXIT_ERROR, NO drift reported         (8.8)
 * The "Drift  : N" line printed on the non-error paths validates that drift is
 * reported at all (8.3).
 */
final class S3MigrateCheckTest extends CIUnitTestCase
{
    private const BUCKET = 'test-bucket';

    /** @var resource */
    private $streamFilterStdout;

    /** @var resource */
    private $streamFilterStderr;

    /** Absolute path to the per-test temp uploads root. */
    private string $localRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->streamFilterStdout   = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilterStderr   = stream_filter_append(STDERR, 'CITestStreamFilter');

        $this->localRoot = sys_get_temp_dir() . '/s3migratecheck_' . uniqid('', true);
        mkdir($this->localRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilterStdout);
        stream_filter_remove($this->streamFilterStderr);

        if ($this->localRoot !== '' && is_dir($this->localRoot)) {
            foreach (glob($this->localRoot . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->localRoot);
        }

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /** Populate the temp uploads root with $count regular (non-.tmp) files. */
    private function seedLocalFiles(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            file_put_contents($this->localRoot . "/file{$i}.jpg", 'x');
        }
    }

    /** An S3Client whose ListObjectsV2 handler is driven by $mock (offline). */
    private function makeClient(MockHandler $mock): S3Client
    {
        return new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $mock,
        ]);
    }

    /** Build the command wired to the temp root and the given S3 client. */
    private function makeCommand(MockHandler $mock): S3MigrateCheck
    {
        $config            = new FileStorageConfig();
        $config->bucket    = self::BUCKET;
        $config->region    = 'us-east-1';
        $config->localRoot = $this->localRoot;

        return (new S3MigrateCheck(Services::logger(), service('commands')))
            ->setConfig($config)
            ->setS3Client($this->makeClient($mock));
    }

    /** A single-page ListObjectsV2 result reporting $keyCount objects. */
    private function listResult(int $keyCount): Result
    {
        return new Result(['KeyCount' => $keyCount, 'IsTruncated' => false]);
    }

    // ---------------------------------------------------------------------
    // Req 8.4 — local <= s3 (drift <= 0) => success
    // ---------------------------------------------------------------------

    public function testDriftBelowZeroReportsSuccessWithCounterpartMessage(): void
    {
        $this->seedLocalFiles(5);
        $mock = new MockHandler();
        $mock->append($this->listResult(8));

        $result = $this->makeCommand($mock)->run([]);
        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(EXIT_SUCCESS, $result);
        $this->assertStringContainsString('Drift  : -3', $buffer);
        $this->assertStringContainsString('cada archivo local tiene su contraparte en S3', $buffer);
        $this->assertStringNotContainsString('ADVERTENCIA', $buffer);
    }

    public function testDriftEqualToZeroReportsSuccess(): void
    {
        $this->seedLocalFiles(4);
        $mock = new MockHandler();
        $mock->append($this->listResult(4));

        $result = $this->makeCommand($mock)->run([]);
        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(EXIT_SUCCESS, $result);
        $this->assertStringContainsString('Drift  : 0', $buffer);
        $this->assertStringContainsString('cada archivo local tiene su contraparte en S3', $buffer);
    }

    // ---------------------------------------------------------------------
    // Req 8.5 — local > s3 => warning that includes the drift value
    // ---------------------------------------------------------------------

    public function testLocalGreaterThanS3ReportsWarningWithDriftValue(): void
    {
        $this->seedLocalFiles(10);
        $mock = new MockHandler();
        $mock->append($this->listResult(3));

        $result = $this->makeCommand($mock)->run([]);
        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(EXIT_SUCCESS, $result);
        $this->assertStringContainsString('Drift  : 7', $buffer);
        $this->assertStringContainsString('ADVERTENCIA', $buffer);
        // The warning must include the numeric drift value (7).
        $this->assertStringContainsString('faltan 7 objeto(s) en S3', $buffer);
    }

    // ---------------------------------------------------------------------
    // Req 8.9 — zero local files => success with drift 0
    // ---------------------------------------------------------------------

    public function testZeroLocalFilesReportsSuccessWithDriftZero(): void
    {
        // No local files seeded.
        $mock = new MockHandler();
        $mock->append($this->listResult(12));

        $result = $this->makeCommand($mock)->run([]);
        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(EXIT_SUCCESS, $result);
        $this->assertStringContainsString('Local  : 0', $buffer);
        $this->assertStringContainsString('no hay archivos locales que migrar', $buffer);
        $this->assertStringContainsString('drift 0', $buffer);
        $this->assertStringNotContainsString('ADVERTENCIA', $buffer);
    }

    // ---------------------------------------------------------------------
    // Req 8.8 — bucket unreachable => error, NO drift reported
    // ---------------------------------------------------------------------

    public function testBucketUnreachableReportsErrorWithoutDrift(): void
    {
        $this->seedLocalFiles(5);
        $mock = new MockHandler();
        // ListObjectsV2 fails => countS3Objects() throws => run() reports ERROR.
        $mock->append(static function (CommandInterface $cmd): S3Exception {
            return new S3Exception(
                'bucket inalcanzable',
                $cmd,
                ['code' => 'NoSuchBucket', 'response' => new Response(404)]
            );
        });

        $result = $this->makeCommand($mock)->run([]);
        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(EXIT_ERROR, $result);
        $this->assertStringContainsString('bucket inalcanzable', $buffer);
        $this->assertStringContainsString('No se reporta drift', $buffer);
        // No drift line must be printed when the S3 count is unavailable.
        $this->assertStringNotContainsString('Drift  :', $buffer);
    }
}
