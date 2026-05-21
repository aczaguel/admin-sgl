<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ExternalApi extends BaseConfig
{
    public bool $enabled = false;
    public array $keys = [];
    public string $apiKeyHeader = 'X-API-Key';
    public string $authorizationScheme = 'Bearer';
    public int $integrationUserId = 0;
    public string $defaultSourceSystem = 'default';
    public array $webhookUrls = [];
    public int $webhookTimeoutSeconds = 10;
    public int $webhookMaxAttempts = 5;

    public function __construct()
    {
        parent::__construct();

        $this->enabled = filter_var((string) env('externalApi.enabled', false), FILTER_VALIDATE_BOOL);
        $keys = array_map('trim', explode(',', (string) env('externalApi.keys', '')));
        $this->keys = array_values(array_filter($keys, static function ($key) {
            return $key !== '';
        }));
        $this->integrationUserId = (int) env('externalApi.integrationUserId', 0);
        $this->defaultSourceSystem = trim((string) env('externalApi.defaultSourceSystem', 'default')) ?: 'default';
        $this->webhookTimeoutSeconds = max(1, (int) env('externalApi.webhookTimeoutSeconds', 10));
        $this->webhookMaxAttempts = max(1, (int) env('externalApi.webhookMaxAttempts', 5));

        $webhookUrls = array_map('trim', explode(',', (string) env('externalApi.webhookUrls', '')));
        $this->webhookUrls = array_values(array_filter($webhookUrls, static function ($url) {
            return $url !== '';
        }));
    }
}