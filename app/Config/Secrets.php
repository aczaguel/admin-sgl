<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Secrets configuration.
 *
 * A single provider switch (`provider`) flips credential resolution between
 * AWS Secrets Manager and plain local .env, mirroring Config\FileStorage.
 *
 * Bound environment variables:
 *   - provider     <- SECRETS_PROVIDER        (aws | env)   default 'env'
 *   - rdsReference <- SECRETS_RDS_REFERENCE   (secret name or ARN)
 *   - region       <- SECRETS_REGION          (AWS region)
 *   - cacheTtl     <- SECRETS_CACHE_TTL        (seconds; 0 = whole request)
 *
 * IMPORTANT (security): there are intentionally NO secret VALUE settings here
 * and NO AWS access-key/secret-key settings. On the aws provider the SDK
 * resolves temporary credentials from the EC2 IAM Instance Profile. Only the
 * secret REFERENCE, region, and provider flag live in config/.env.
 */
class Secrets extends BaseConfig
{
    /** Active provider: 'aws' or 'env'. Defaults to 'env' (current behavior). */
    public string $provider = 'env';

    /** Secret name or ARN for the RDS credentials secret. Never a value. */
    public string $rdsReference = '';

    /** AWS region for the SecretsManagerClient (aws provider only). */
    public string $region = 'us-east-1';

    /** Per-request cache TTL in seconds. 0 = valid for the whole request. */
    public int $cacheTtl = 0;

    public function __construct()
    {
        parent::__construct();

        $provider = strtolower(trim((string) env('SECRETS_PROVIDER', '')));
        if ($provider !== '') {
            $this->provider = $provider;      // 'env' default preserved when absent (Req 1.4, 10.1)
        }

        $reference = trim((string) env('SECRETS_RDS_REFERENCE', ''));
        if ($reference !== '') {
            $this->rdsReference = $reference;
        }

        $region = trim((string) env('SECRETS_REGION', ''));
        if ($region !== '') {
            $this->region = $region;
        }

        $ttl = env('SECRETS_CACHE_TTL', null);
        if ($ttl !== null && $ttl !== '' && is_numeric($ttl)) {
            $this->cacheTtl = max(0, (int) $ttl);
        }
    }
}
