<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use RuntimeException;
/**
 * Pattern to ensure a value complies to a given regular expression.
 *
 * The predicate uses preg_match() and checks if the value occurs exactly
 * one time. Please make sure that the supplied regular expression contains
 * correct delimiters, they will not be applied automatically. The matches()
 * method throws a PatternMatchFailed in case the regular expression is invalid.
 *
 * @since 7.1.0
 */
class Pattern
{
    /**
     * map of pcre error codes and according error messages
     */
    private const ERRORS = [
        PREG_NO_ERROR              => 'invalid regular expression',
        PREG_INTERNAL_ERROR        => 'internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR => 'backtrack limit exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'recursion limit exhausted',
        PREG_BAD_UTF8_ERROR        => 'malformed UTF-8 data',
        PREG_BAD_UTF8_OFFSET_ERROR => 'did not end at valid UTF-8 codepoint',
        PREG_JIT_STACKLIMIT_ERROR  => 'failed because of limited JIT stack space'
    ];

    public function __construct(private string $pattern) { }

    /**
     * test that the given value complies with the regular expression
     *
     * @throws PatternMatchFailed in case the used regular expresion is invalid
     */
    public function matches(string $value): bool
    {
        $check = @preg_match($this->pattern, $value);
        if (false === $check) {
            throw new PatternMatchFailed(sprintf(
                'Failure while matching "%s", reason: %s.',
                $this->pattern,
                $this->messageFor(preg_last_error())
            ));
        }

        return (1 != $check) ? false : true;
    }

    /**
     * translates error code into proper error message
     */
    private function messageFor(int $errorCode): string
    {
        return self::ERRORS[$errorCode] ?? preg_last_error_msg();
    }
}
