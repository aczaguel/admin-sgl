<?php

namespace App\Libraries\Secrets;

/**
 * SecretProvider contract.
 *
 * The single, stable secret-resolution contract that the service and all
 * callers depend on. Every backend (AWS Secrets Manager, plain local .env, ...)
 * implements this interface, keeping the application decoupled from the AWS SDK
 * and from where credential material actually lives.
 *
 * A single provider switch (Config\Secrets->provider = aws|env) selects the
 * active implementation, mirroring the FileStorage abstraction.
 */
interface SecretProvider
{
    /**
     * Resolve the secret identified by $reference into a flat key/value map.
     *
     * For the RDS secret the returned map contains at least:
     *   host, username, password, dbname  (port optional; the caller applies
     *   the default MySQL port 3306 when absent).
     *
     * @param string $reference Secret name or ARN (Secret_Reference). This is
     *                          ALWAYS an identifier, NEVER the secret value.
     *
     * @return array<string,string|int> Parsed secret material, held in memory
     *                                   only (never logged, echoed, or written
     *                                   to disk).
     *
     * @throws SecretResolutionException on an unreachable, missing, malformed,
     *         or incomplete secret. The exception message names the reference
     *         and reason and NEVER contains the secret value.
     */
    public function getSecret(string $reference): array;
}
