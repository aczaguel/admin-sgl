<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\AwsSecretProvider;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use ReflectionClass;

/**
 * Property 8: No secrets in artifacts.
 *
 * Two assertions back this property:
 *
 *   1. The production AwsSecretProvider builds the `SecretsManagerClient`
 *      WITHOUT a literal `credentials` entry, so the AWS SDK default provider
 *      chain resolves temporary credentials from the EC2 IAM Instance Profile
 *      metadata endpoint (Req 2.2, Req 6.2). This is proven structurally by
 *      scanning the AwsSecretProvider source: no `credentials` array key may be
 *      assigned into the client construction args (comment lines are ignored so
 *      the security narration in the docblock does not trip the scan), and
 *      functionally by constructing the provider from region only. `Config\Secrets`
 *      is additionally checked to expose NO access-key/secret-key property.
 *
 *   2. No AWS access-key/secret-key VALUE and no RDS secret value lives in the
 *      committed artifacts (Req 8.4, Req 6.2). This is proven by scanning `.env`,
 *      the CI4 `env` template, `app/Config/Secrets.php`, and the operator docs
 *      (`SECRETS_MANAGER_README.md`) for access-key/secret patterns, and by
 *      asserting that every `SECRETS_*` key that appears is limited to the
 *      allow-listed reference/region/provider-flag/ttl keys — never a key that
 *      would carry a secret VALUE (e.g. SECRETS_PASSWORD).
 *
 *      NOTE: `SECRETS_MANAGER_README.md` is produced by task 11.1, which may not
 *      exist yet. The scan guards a missing docs file (soft pass), and task 11.1
 *      MUST also satisfy this property when it creates the file.
 *
 * **Property 8: No secrets in artifacts**
 * **Validates: Requirements 8.4, 2.2, 6.2**
 *
 * @internal
 */
final class NoSecretsInArtifactsTest extends CIUnitTestCase
{
    /** Production AwsSecretProvider source, relative to the project root. */
    private const AWS_PROVIDER_REL = 'app/Libraries/Secrets/AwsSecretProvider.php';

    /** Operator documentation produced by task 11.1 (guarded — may not exist yet). */
    private const OPERATOR_DOCS_REL = 'SECRETS_MANAGER_README.md';

    /**
     * The ONLY `SECRETS_*` env keys permitted in any artifact. Each is a
     * reference / region / provider-flag / ttl — never a secret value.
     */
    private const ALLOWED_SECRETS_KEYS = [
        'SECRETS_PROVIDER',
        'SECRETS_RDS_REFERENCE',
        'SECRETS_REGION',
        'SECRETS_CACHE_TTL',
    ];

    // ---------------------------------------------------------------------
    // Assertion 1 — SecretsManagerClient built with no literal `credentials` (2.2, 6.2)
    // ---------------------------------------------------------------------

    /**
     * The AwsSecretProvider source must never assign a literal `credentials`
     * array key into the SecretsManagerClient construction args, so credentials
     * come exclusively from the IAM Instance Profile.
     *
     * **Validates: Requirements 2.2, 6.2**
     */
    public function testAwsProviderBuildsClientWithoutLiteralCredentials(): void
    {
        $path = ROOTPATH . self::AWS_PROVIDER_REL;
        $this->assertFileExists($path, 'AwsSecretProvider production source should exist');

        $source = (string) file_get_contents($path);
        $lines  = preg_split('/\r\n|\r|\n/', $source) ?: [];

        // Sanity: the source really does construct a SecretsManagerClient.
        $this->assertMatchesRegularExpression(
            '/new\s+SecretsManagerClient\s*\(/',
            $source,
            'AwsSecretProvider should construct at least one SecretsManagerClient'
        );

        // A `credentials` key would appear either as an array literal key
        // (`'credentials' => ...`) or as an index assignment
        // (`$args['credentials'] = ...`). Neither may exist in real code.
        $credentialsKeyPatterns = [
            "array key"       => '/[\'"]credentials[\'"]\s*=>/',
            "index assignment" => '/\[\s*[\'"]credentials[\'"]\s*\]/',
        ];

        foreach ($lines as $number => $rawLine) {
            $line = trim($rawLine);

            // Skip comment/docblock lines — the security narration legitimately
            // mentions "credentials" in prose.
            if ($line === '' || $this->isCommentLine($line)) {
                continue;
            }

            foreach ($credentialsKeyPatterns as $label => $regex) {
                $this->assertDoesNotMatchRegularExpression(
                    $regex,
                    $rawLine,
                    sprintf(
                        'AwsSecretProvider must NOT hardcode a `credentials` %s '
                        . '(line %d): credentials come from the IAM Instance Profile.',
                        $label,
                        $number + 1
                    )
                );
            }
        }
    }

    /**
     * Constructing the provider from config only (region, no injected client)
     * must succeed without any literal credentials, matching the Instance-Profile
     * posture. The SDK resolves credentials lazily, so no network call occurs at
     * construction.
     *
     * **Validates: Requirements 2.2, 6.2**
     */
    public function testAwsProviderConstructsFromRegionOnly(): void
    {
        $config         = new SecretsConfig();
        $config->region = 'us-east-1';

        $provider = new AwsSecretProvider($config);

        $this->assertInstanceOf(
            AwsSecretProvider::class,
            $provider,
            'AwsSecretProvider must build with no literal credentials (region only).'
        );
    }

    /**
     * `Config\Secrets` must expose NO property that could hold an AWS access key
     * or secret key — only the reference, region, provider flag, and cache TTL.
     *
     * **Validates: Requirements 8.4, 6.2**
     */
    public function testSecretsConfigHasNoAccessKeyOrSecretKeyProperty(): void
    {
        $reflection = new ReflectionClass(SecretsConfig::class);

        $forbidden = '/(access[_-]?key|secret[_-]?key|aws[_-]?key|password|credential)/i';

        foreach ($reflection->getProperties() as $property) {
            $this->assertDoesNotMatchRegularExpression(
                $forbidden,
                $property->getName(),
                sprintf(
                    'Config\\Secrets must NOT declare a credential/secret-bearing '
                    . 'property; found "%s".',
                    $property->getName()
                )
            );
        }
    }

    // ---------------------------------------------------------------------
    // Assertion 2 — no access-key/secret VALUE or stray SECRETS_* key (8.4, 6.2)
    // ---------------------------------------------------------------------

    /**
     * Scan the committed artifacts for AWS access-key/secret patterns. None may
     * be present in `.env`, the `env` template, `app/Config/Secrets.php`, or the
     * operator docs.
     *
     * **Validates: Requirements 8.4, 6.2**
     */
    public function testArtifactsContainNoAwsKeyOrSecretValue(): void
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
            "No AWS access-key/secret pattern may appear in the artifacts.\nFound:\n"
            . implode("\n", $violations)
        );
    }

    /**
     * Every `SECRETS_*` key found across the artifacts must be one of the
     * allow-listed reference/region/provider-flag/ttl keys. A key such as
     * `SECRETS_PASSWORD` or `SECRETS_RDS_PASSWORD` would mean a secret VALUE
     * had leaked into configuration.
     *
     * **Validates: Requirements 8.4, 2.2**
     */
    public function testOnlyReferenceRegionAndProviderFlagSecretsKeysAppear(): void
    {
        $files = $this->artifactsToScan();

        $violations = [];

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);

            if (preg_match_all('/\bSECRETS_[A-Z0-9_]+\b/', $contents, $matches) > 0) {
                foreach ($matches[0] as $key) {
                    if (! in_array($key, self::ALLOWED_SECRETS_KEYS, true)) {
                        $violations[] = sprintf('%s references disallowed key %s', $file, $key);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Only reference/region/provider-flag/ttl SECRETS_* keys may appear.\nFound:\n"
            . implode("\n", $violations)
        );
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Absolute paths of the committed artifacts to scan. The operator docs file
     * is included only when it exists (task 11.1 may not have created it yet).
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

        $secretsConfig = ROOTPATH . 'app/Config/Secrets.php';
        if (is_file($secretsConfig)) {
            $files[] = $secretsConfig;
        }

        // Guard: operator docs are produced by task 11.1 and may not exist yet.
        $docs = ROOTPATH . self::OPERATOR_DOCS_REL;
        if (is_file($docs)) {
            $files[] = $docs;
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
            'AKIA access key id'               => '/\bAKIA[0-9A-Z]{16}\b/',
            'AWS_ACCESS_KEY_ID assignment'     => '/AWS_ACCESS_KEY_ID\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'AWS_SECRET_ACCESS_KEY assignment' => '/AWS_SECRET_ACCESS_KEY\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'aws_secret_access_key (ini)'      => '/aws_secret_access_key\s*[=:]\s*[\'"]?\S+/i',
            'aws_access_key_id (ini)'          => '/aws_access_key_id\s*[=:]\s*[\'"]?[A-Za-z0-9\/+]{8,}/i',
            'literal credentials key'          => '/[\'"]credentials[\'"]\s*=>\s*\[[^\]]*[\'"](?:key|secret)[\'"]\s*=>/is',
        ];

        foreach ($lines as $number => $rawLine) {
            $line   = trim($rawLine);
            $lineNo = $number + 1;

            if ($line === '' || $this->isCommentLine($line)) {
                continue;
            }

            foreach ($patterns as $label => $regex) {
                if (preg_match($regex, $rawLine) === 1) {
                    $violations[] = sprintf('%s:%d matched [%s]: %s', $file, $lineNo, $label, $line);
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
