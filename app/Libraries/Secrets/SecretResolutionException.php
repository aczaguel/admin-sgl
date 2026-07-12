<?php

namespace App\Libraries\Secrets;

use RuntimeException;

/**
 * SecretResolutionException.
 *
 * A dedicated exception carrying the Secret_Reference and a reason, with a
 * message contract that guarantees the Secret_Value is never embedded.
 * Constructed only from reference + reason strings, never from raw secret
 * material.
 */
final class SecretResolutionException extends RuntimeException
{
    public function __construct(
        public readonly string $reference,
        public readonly string $reason,
    ) {
        // Message contains reference + reason only — never the secret value.
        parent::__construct(sprintf('Secret resolution failed [reference=%s]: %s', $reference, $reason));
    }
}
