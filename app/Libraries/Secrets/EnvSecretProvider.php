<?php

namespace App\Libraries\Secrets;

use Config\Secrets as SecretsConfig;

/**
 * EnvSecretProvider.
 *
 * Reproduces today's exact behavior so `env` mode is byte-for-byte identical
 * to the current system: it reads the plain local `.env` database keys and
 * contacts nothing external.
 *
 * The $reference argument is ignored — env mode always resolves the RDS
 * credentials straight from the existing `database.default.*` keys via CI4
 * env(). It never talks to AWS and never throws for connectivity, returning
 * only whatever `.env` provides so that any absent key falls back to the
 * hardcoded Config\Database defaults (the caller only overwrites the group
 * keys for values actually returned here).
 *
 * @see SecretProvider
 */
final class EnvSecretProvider implements SecretProvider
{
    public function __construct(private SecretsConfig $config)
    {
        // Config is accepted for parity with AwsSecretProvider and to keep the
        // provider construction uniform in SecretsManagerService::makeProvider.
        // env mode does not need any of its settings.
    }

    /**
     * Resolve RDS credentials from the plain local `.env` values.
     *
     * Reads `database.default.hostname/database/username/password` (and the
     * optional `database.default.port`) via CI4 env() and maps them to the
     * canonical keys the caller expects. The $reference is ignored.
     *
     * @param string $reference Ignored in env mode.
     *
     * @return array<string,string|int> Map with any of host, dbname, username,
     *                                   password, and (optionally) port that
     *                                   `.env` currently provides.
     */
    public function getSecret(string $reference): array
    {
        $map = [];

        $host = env('database.default.hostname');
        if ($host !== null && $host !== '') {
            $map['host'] = (string) $host;
        }

        $dbname = env('database.default.database');
        if ($dbname !== null && $dbname !== '') {
            $map['dbname'] = (string) $dbname;
        }

        $username = env('database.default.username');
        if ($username !== null && $username !== '') {
            $map['username'] = (string) $username;
        }

        $password = env('database.default.password');
        if ($password !== null && $password !== '') {
            $map['password'] = (string) $password;
        }

        $port = env('database.default.port');
        if ($port !== null && $port !== '') {
            $map['port'] = (int) $port;
        }

        return $map;
    }
}
