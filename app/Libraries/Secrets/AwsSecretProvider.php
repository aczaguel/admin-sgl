<?php

namespace App\Libraries\Secrets;

use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Config\Secrets as SecretsConfig;
use Throwable;

/**
 * AwsSecretProvider.
 *
 * Fetches secret material from AWS Secrets Manager using IAM Instance Profile
 * credentials, parses the JSON blob, and validates the required RDS fields.
 *
 * Security posture:
 *   - The SecretsManagerClient is built from `version` + `region` ONLY. There
 *     is intentionally NO `credentials` key, so the AWS SDK default provider
 *     chain resolves temporary credentials from the EC2 IAM Instance Profile.
 *     No access keys ever live in the repository or configuration (Req 2.2,
 *     Req 6.1).
 *   - Every failure logs ONLY the reference and a reason code via
 *     log_message('error', ...) and throws SecretResolutionException. The raw
 *     secret value is NEVER logged, echoed, or written to disk (Req 5.2,
 *     Req 5.4, Req 8.2).
 *
 * A client (or a lower-level handler) may be injected for tests so the AWS SDK
 * MockHandler can be exercised without any live AWS call.
 */
final class AwsSecretProvider implements SecretProvider
{
    /** Required RDS fields that must be present and non-empty (Req 3.4). */
    private const REQUIRED_FIELDS = ['host', 'username', 'password', 'dbname'];

    private SecretsManagerClient $client;

    /**
     * @param SecretsConfig              $config  Holds region + reference only, never a value.
     * @param SecretsManagerClient|null  $client  Optional pre-built client (tests).
     * @param callable|null              $handler Optional low-level handler (e.g. AWS SDK
     *                                            MockHandler) used to build the client when no
     *                                            explicit $client is supplied (tests).
     */
    public function __construct(
        private SecretsConfig $config,
        ?SecretsManagerClient $client = null,
        ?callable $handler = null,
    ) {
        $this->client = $client ?? $this->makeClient($config, $handler);
    }

    /**
     * Build the SecretsManagerClient from region only.
     *
     * No `credentials` entry is provided, so the SDK default credential provider
     * chain reads temporary credentials from the EC2 IAM Instance Profile metadata
     * endpoint (Req 2.2, Req 6.1). An optional $handler lets tests inject a
     * MockHandler with no live AWS contact.
     */
    private function makeClient(SecretsConfig $config, ?callable $handler): SecretsManagerClient
    {
        $args = [
            'version' => 'latest',
            'region'  => $config->region,
        ];

        if ($handler !== null) {
            $args['handler'] = $handler;
        }

        return new SecretsManagerClient($args);
    }

    /**
     * Resolve the secret identified by $reference into a validated flat map.
     *
     * @param string $reference Secret name or ARN. Never a value.
     *
     * @return array<string,string|int> Parsed secret material (in memory only).
     *
     * @throws SecretResolutionException on unreachable, missing, malformed, or
     *         incomplete secret. The message names the reference and reason and
     *         NEVER contains the secret value.
     */
    public function getSecret(string $reference): array
    {
        try {
            $result = $this->client->getSecretValue(['SecretId' => $reference]);
        } catch (AwsException $e) {
            $reason = $this->awsReason($e);
            $this->logRedacted($reference, $reason);

            throw new SecretResolutionException($reference, $reason);
        } catch (Throwable $e) {
            // Any non-AWS failure (network, SDK config) still fails closed without
            // leaking secret material.
            $this->logRedacted($reference, 'aws-error');

            throw new SecretResolutionException($reference, 'aws-error');
        }

        $raw = $result['SecretString'] ?? null;
        if ($raw === null || $raw === '') {
            $this->logRedacted($reference, 'empty-secret-string');

            throw new SecretResolutionException($reference, 'empty-secret-string');
        }

        $parsed = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            // NEVER log the raw value — only the reason (Req 3.3, Req 5.2).
            $this->logRedacted($reference, 'invalid-json');

            throw new SecretResolutionException($reference, 'invalid-json');
        }

        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $parsed) || $this->isEmptyScalar($parsed[$field])) {
                $reason = 'missing-field:' . $field;
                $this->logRedacted($reference, $reason);

                throw new SecretResolutionException($reference, $reason);
            }
        }

        if (array_key_exists('port', $parsed) && ! $this->isPositiveInt($parsed['port'])) {
            $this->logRedacted($reference, 'invalid-port');

            throw new SecretResolutionException($reference, 'invalid-port');
        }

        return $parsed;
    }

    /**
     * A required field is acceptable only when it is a non-empty scalar.
     * Empty strings, null, arrays, and objects are rejected.
     */
    private function isEmptyScalar(mixed $value): bool
    {
        if (! is_scalar($value)) {
            return true;
        }

        return trim((string) $value) === '';
    }

    /**
     * The optional `port` field, when present, must be a positive integer.
     * Accepts integers and integer-valued numeric strings (e.g. "3306").
     */
    private function isPositiveInt(mixed $value): bool
    {
        if (is_bool($value)) {
            return false;
        }

        if (is_int($value)) {
            return $value > 0;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value > 0;
        }

        return false;
    }

    /**
     * Derive a secret-free reason code from an AwsException. Prefers the AWS
     * error code (e.g. ResourceNotFoundException, AccessDeniedException); falls
     * back to a generic reason. Never includes the secret value.
     */
    private function awsReason(AwsException $e): string
    {
        $code = $e->getAwsErrorCode();

        return ($code !== null && $code !== '') ? $code : 'aws-unreachable';
    }

    /**
     * Secret-free logging: emit ONLY the reference and a reason code. The secret
     * value is never passed into this method (Req 5.2, Req 5.4, Req 8.2).
     */
    private function logRedacted(string $reference, string $reason): void
    {
        log_message('error', 'Secrets: reference=' . $reference . ' reason=' . $reason);
    }
}
