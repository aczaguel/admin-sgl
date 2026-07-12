<?php

namespace Tests\App\Libraries\Secrets;

use App\Libraries\Secrets\SecretsManagerService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Secrets as SecretsConfig;
use InvalidArgumentException;
use Throwable;

/**
 * Property-based test for Property 7: Invalid provider flag is rejected.
 *
 * **Property 7: Invalid provider flag is rejected**
 * **Validates: Requirements 1.5**
 *
 * For arbitrary NON-EMPTY flag strings that are neither `aws` nor `env`,
 * constructing the {@see SecretsManagerService} must fail closed with a
 * descriptive {@see InvalidArgumentException} whose message contains BOTH:
 *   - the offending flag value (so the operator sees exactly what they set), and
 *   - the two accepted values `aws` and `env`.
 *
 * This exercises {@see SecretsManagerService::makeProvider()}, which does a
 * `match` on `$config->provider` and, in its default arm, throws
 * `InvalidArgumentException("Invalid SECRETS_PROVIDER '{$c->provider}'.
 * Accepted values: aws, env.")`.
 *
 * INJECTION NOTE: `Config\Secrets` lowercases + trims `SECRETS_PROVIDER` in its
 * constructor when it reads the env var, so it could never normally hold a
 * mixed-case or whitespace-padded value. This is a UNIT-LEVEL property test of
 * the service's `match`, so we construct a `Config\Secrets` normally and then
 * set its PUBLIC `provider` property directly to inject arbitrary invalid
 * values. That is why case variants such as `AWS` / `Env` are valid invalid
 * cases here: with a direct assignment they bypass the config's normalization
 * and reach `makeProvider` verbatim, where they are (correctly) not equal to the
 * lowercase `aws` / `env` the `match` accepts.
 *
 * The empty string is intentionally EXCLUDED from the generator: an empty/absent
 * flag defaults to `env` (Req 1.4) and is NOT an error.
 *
 * Cases come from a seeded PHPUnit data-provider generator (>= 100 iterations),
 * covering unicode, whitespace-containing, symbol-only, and case-variant flags,
 * following the tests/ convention used by the sibling Secrets property tests.
 *
 * @internal
 */
final class SecretsManagerServiceInvalidProviderPropertyTest extends CIUnitTestCase
{
    /** The two accepted provider flags the error message must advertise. */
    private const ACCEPTED = ['aws', 'env'];

    /**
     * Property 7: any non-empty flag that is neither `aws` nor `env` is rejected
     * with a descriptive error naming the offending value and both accepted
     * values.
     *
     * **Validates: Requirements 1.5**
     *
     * @dataProvider provideInvalidProviderFlags
     */
    public function testInvalidProviderFlagIsRejected(string $flag): void
    {
        // Sanity-check the generator honours the property's precondition.
        $this->assertNotSame('', $flag, 'Generator emitted the empty flag (defaults to env, not an error)');
        $this->assertNotContains($flag, self::ACCEPTED, 'Generator emitted a valid flag: ' . $flag);

        // Build a normal config, then inject the arbitrary invalid flag directly
        // into the public property (bypassing Config\Secrets normalization).
        $config           = new SecretsConfig();
        $config->provider = $flag;

        $thrown = null;

        try {
            new SecretsManagerService($config);
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(
            InvalidArgumentException::class,
            $thrown,
            sprintf('Constructing the service with provider=%s must throw InvalidArgumentException', $this->describe($flag))
        );

        $message = $thrown->getMessage();

        // The message must name the offending value verbatim (Req 1.5).
        $this->assertStringContainsString(
            $flag,
            $message,
            sprintf('Error must contain the offending flag %s. Got: %s', $this->describe($flag), $message)
        );

        // The message must advertise BOTH accepted values (Req 1.5).
        $this->assertStringContainsString(
            'aws',
            $message,
            sprintf('Error must mention accepted value "aws". Got: %s', $message)
        );
        $this->assertStringContainsString(
            'env',
            $message,
            sprintf('Error must mention accepted value "env". Got: %s', $message)
        );
    }

    // --- generators -------------------------------------------------------

    /**
     * Seeded generator of arbitrary NON-EMPTY invalid provider flags. Every
     * emitted value is guaranteed to differ from both `aws` and `env` and to be
     * non-empty. Mixes ASCII words, case variants, symbol strings,
     * whitespace-containing values, and unicode/emoji. >= 100 iterations plus
     * explicit edge cases.
     *
     * @return array<string, array{0:string}>
     */
    public static function provideInvalidProviderFlags(): array
    {
        $cases = [];

        // Explicit representative / boundary cases.
        $explicit = [
            'case-AWS'          => 'AWS',
            'case-Env'          => 'Env',
            'case-Aws'          => 'Aws',
            'case-ENV'          => 'ENV',
            'trailing-space'    => 'aws ',
            'leading-space'     => ' env',
            'inner-space'       => 'a ws',
            'plausible-s3'      => 's3',
            'plausible-local'   => 'local',
            'plausible-vault'   => 'vault',
            'plausible-gcp'     => 'gcp',
            'plausible-azure'   => 'azure',
            'substring-aw'      => 'aw',
            'substring-en'      => 'en',
            'superstring-awss'  => 'awss',
            'superstring-envs'  => 'envs',
            'symbols-only'      => '@#$%^&*',
            'digits-only'       => '12345',
            'single-char'      => 'x',
            'unicode-word'      => 'áws',
            'unicode-emoji'     => '🔒aws',
            'json-ish'          => '{"provider":"aws"}',
            'path-ish'          => '/aws/env',
            'quoted-aws'        => '"aws"',
            'newline-aws'       => "aws\n",
            'tab-env'           => "\tenv",
        ];
        foreach ($explicit as $name => $flag) {
            $cases[$name] = [$flag];
        }

        // Seeded random generator (deterministic; reproducible counterexamples).
        for ($seed = 1; $seed <= 130; $seed++) {
            mt_srand($seed * 2654435761 & 0x7FFFFFFF);

            $flag = self::randomInvalidFlag($seed);

            $cases["seed-{$seed}"] = [$flag];
        }

        return $cases;
    }

    /**
     * Deterministically build a non-empty flag that is guaranteed to be neither
     * `aws` nor `env`. Draws from ASCII, symbols and unicode/emoji glyphs and
     * may contain internal whitespace.
     */
    private static function randomInvalidFlag(int $seed): string
    {
        $palette = [
            'a', 'W', 'S', 'e', 'N', 'V', 'z', 'Q', '9', '0',
            '@', '#', '$', '%', '&', '*', '(', ')', '{', '}', '[', ']',
            ':', ';', '|', '/', '\\', '?', '!', '+', '=', '<', '>', '-', '_', '.',
            ' ', "\t",
            'ñ', 'é', 'ü', 'Ð', 'æ', '€', '±', '§', 'ß', 'λ', 'Ж', '日',
            '🔒', '🛡️', '🚀',
        ];

        $len   = mt_rand(1, 16);
        $value = '';
        for ($i = 0; $i < $len; $i++) {
            $value .= $palette[mt_rand(0, count($palette) - 1)];
        }

        // Guarantee the property's precondition: never emit '', 'aws' or 'env'.
        // Prepend a stable token that is not a substring collision risk and keeps
        // the value non-empty and distinct from both accepted flags.
        $value = 'X' . $seed . $value;

        if ($value === '' || in_array($value, self::ACCEPTED, true)) {
            $value = 'invalid-' . $seed;
        }

        return $value;
    }

    /** Render a flag for human-readable assertion messages. */
    private function describe(string $flag): string
    {
        return "'" . str_replace(["\n", "\t"], ['\\n', '\\t'], $flag) . "'";
    }
}
