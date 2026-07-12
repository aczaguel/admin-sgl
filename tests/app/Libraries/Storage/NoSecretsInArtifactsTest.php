<?php

namespace Tests\App\Libraries\Storage;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Property 10: No secrets in artifacts.
 *
 * Two assertions back this property:
 *
 *   1. The production S3 client is constructed WITHOUT a literal `credentials`
 *      entry, so the AWS SDK resolves temporary credentials from the EC2 IAM
 *      Instance Profile metadata endpoint (Requirement 10.1). This is proven by
 *      scanning the S3FileStorage source: every production `new S3Client([...])`
 *      construction must omit a `credentials` array key. Test files intentionally
 *      inject dummy credentials for offline presign signing, so the scan is
 *      restricted to production source and never inspects tests/.
 *
 *   2. No AWS access-key or secret-key VALUE lives anywhere in the repository's
 *      configuration or environment (Requirement 10.2). This is proven by
 *      scanning `.env`, the CI4 `env` template, and every `app/Config/*.php`
 *      file for access-key/secret patterns (an AKIA-prefixed access key id,
 *      an `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` assignment with a value,
 *      an ini-profile `aws_secret_access_key`, or a literal `credentials`
 *      array carrying a `key`/`secret`). Comment lines are ignored so that
 *      documentation merely mentioning these names does not trip the scan.
 *
 * **Property 10: No secrets in artifacts**
 * **Validates: Requirements 10.1, 10.2**
 *
 * @internal
 */
final class NoSecretsInArtifactsTest extends CIUnitTestCase
{
    /**
     * The production S3 driver source, located from the project root.
     */
    private const S3_DRIVER_REL = 'app/Libraries/Storage/S3FileStorage.php';

    // ---------------------------------------------------------------------
    // Assertion 1 — S3 client built with no literal `credentials` entry (10.1)
    // ---------------------------------------------------------------------

    /**
     * Every `new S3Client([...])` in the production driver must omit a
     * `credentials` array key, so credentials come from the Instance Profile.
     *
     * **Validates: Requirement 10.1**
     */
    public function testProductionS3ClientIsConstructedWithoutLiteralCredentials(): void
    {
        $path = ROOTPATH . self::S3_DRIVER_REL;
        $this->assertFileExists($path, 'S3FileStorage production source should exist');

        $source = (string) file_get_contents($path);

        // Capture every `new S3Client( [ ... ] )` construction-array literal.
        $matched = preg_match_all(
            '/new\s+S3Client\s*\(\s*\[(.*?)\]\s*\)/s',
            $source,
            $matches
        );

        $this->assertNotFalse($matched, 'regex over S3FileStorage source should not error');
        $this->assertGreaterThanOrEqual(
            1,
            $matched,
            'S3FileStorage should build at least one S3Client instance'
        );

        foreach ($matches[1] as $index => $constructionArray) {
            $this->assertDoesNotMatchRegularExpression(
                '/[\'"]credentials[\'"]\s*=>/',
                $constructionArray,
                "Production S3Client construction #{$index} must NOT pass a literal "
                . "'credentials' key (credentials come from the IAM Instance Profile)."
            );
        }
    }

    /**
     * Constructing the driver from config only (no injected client) must not
     * require or embed any literal credentials — it succeeds building a client
     * from region alone, matching the Instance-Profile posture.
     *
     * **Validates: Requirement 10.1**
     */
    public function testS3DriverConstructsFromRegionOnly(): void
    {
        $config         = new \Config\FileStorage();
        $config->bucket = 'unit-test-bucket';
        $config->region = 'us-east-1';

        $driver = new \App\Libraries\Storage\S3FileStorage($config);

        $this->assertInstanceOf(
            \App\Libraries\Storage\S3FileStorage::class,
            $driver,
            'S3FileStorage must build with no literal credentials (region only).'
        );
    }

    // ---------------------------------------------------------------------
    // Assertion 2 — no access-key/secret VALUE in .env or config (10.2)
    // ---------------------------------------------------------------------

    /**
     * Scan `.env`, the `env` template, and every `app/Config/*.php` file for
     * AWS access-key/secret patterns. None must be present.
     *
     * **Validates: Requirement 10.2**
     */
    public function testConfigAndEnvContainNoAwsKeyOrSecret(): void
    {
        $files = $this->artifactsToScan();

        $this->assertNotEmpty($files, 'there should be at least one artifact to scan');

        $violations = [];

        foreach ($files as $file) {
            foreach ($this->scanForSecretPatterns($file) as $violation) {
                $violations[] = $violation;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "No AWS access-key/secret pattern may appear in config or env.\nFound:\n"
            . implode("\n", $violations)
        );
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Absolute paths of the production artifacts to scan for secrets.
     *
     * @return list<string>
     */
    private function artifactsToScan(): array
    {
        $files = [];

        foreach (['.env', 'env'] as $envFile) {
            $path = ROOTPATH . $envFile;
            if (is_file($path)) {
                $files[] = $path;
            }
        }

        $configGlob = glob(ROOTPATH . 'app/Config/*.php') ?: [];
        foreach ($configGlob as $configFile) {
            $files[] = $configFile;
        }

        return $files;
    }

    /**
     * Return a list of human-readable violation messages found in $file.
     *
     * Comment lines (`#`, `//`, `*`, `/*`) are skipped so that documentation
     * mentioning these names by way of explanation is not flagged; only real
     * assignments / literal key material trip the scan.
     *
     * @return list<string>
     */
    private function scanForSecretPatterns(string $file): array
    {
        $violations = [];
        $lines      = preg_split('/\r\n|\r|\n/', (string) file_get_contents($file)) ?: [];

        // label => regex. Each targets an actual value/assignment, not a mention.
        $patterns = [
            'AKIA access key id'              => '/\bAKIA[0-9A-Z]{16}\b/',
            'AWS_ACCESS_KEY_ID assignment'    => '/AWS_ACCESS_KEY_ID\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'AWS_SECRET_ACCESS_KEY assignment' => '/AWS_SECRET_ACCESS_KEY\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'aws_secret_access_key (ini)'     => '/aws_secret_access_key\s*[=:]\s*[\'"]?\S+/i',
            'aws_access_key_id (ini)'         => '/aws_access_key_id\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'literal credentials key'         => '/[\'"]credentials[\'"]\s*=>\s*\[[^\]]*[\'"](?:key|secret)[\'"]\s*=>/is',
        ];

        foreach ($lines as $number => $rawLine) {
            $line    = trim($rawLine);
            $lineNo  = $number + 1;

            if ($line === '' || $this->isCommentLine($line)) {
                continue;
            }

            foreach ($patterns as $label => $regex) {
                if (preg_match($regex, $rawLine) === 1) {
                    $violations[] = sprintf(
                        '%s:%d matched [%s]: %s',
                        $file,
                        $lineNo,
                        $label,
                        $line
                    );
                }
            }
        }

        return $violations;
    }

    /**
     * True when a trimmed line is a comment (env `#`, or PHP `//`, `#`, `*`, `/*`).
     */
    private function isCommentLine(string $trimmed): bool
    {
        return str_starts_with($trimmed, '#')
            || str_starts_with($trimmed, '//')
            || str_starts_with($trimmed, '*')
            || str_starts_with($trimmed, '/*');
    }
}
