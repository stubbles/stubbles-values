<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\values
 */
namespace stubbles\values;
/**
 * define this constant for PHP < 7.0
 */
if (!defined('PREG_JIT_STACKLIMIT_ERROR')) {
    define('PREG_JIT_STACKLIMIT_ERROR', 6);
}
/**
 * Pattern to ensure a value complies to a given regular expression.
 *
 * The predicate uses preg_match() and checks if the value occurs exactly
 * one time. Please make sure that the supplied regular expression contains
 * correct delimiters, they will not be applied automatically. The matches()
 * method throws a \RuntimeException in case the regular expression is invalid.
 *
 * @since  7.1.0
 */
class Pattern
{
    /**
     * the regular expression to use for validation
     *
     * @type  string
     */
    private $pattern;
    /**
     * map of pcre error codes and according error messages
     *
     * @array
     */
    private static $errors = [
            PREG_NO_ERROR              => 'invalid regular expression',
            PREG_INTERNAL_ERROR        => 'internal PCRE error',
            PREG_BACKTRACK_LIMIT_ERROR => 'backtrack limit exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'recursion limit exhausted',
            PREG_BAD_UTF8_ERROR        => 'malformed UTF-8 data',
            PREG_BAD_UTF8_OFFSET_ERROR => 'did not end at valid UTF-8 codepoint',
            PREG_JIT_STACKLIMIT_ERROR  => 'failed because of limited JIT stack space'
    ];

    /**
     * constructor
     *
     * @param  string  $regex  regular expression to use for validation
     */
    public function __construct($regex)
    {
        $this->pattern = $regex;
    }

    /**
     * test that the given value complies with the regular expression
     *
     * @param   string  $value
     * @return  bool
     * @throws  \InvalidArgumentException  in case given value is not a string
     * @throws  \RuntimeException  in case the used regular expresion is invalid
     */
    public function matches($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                    'Given value of type "' . typeOf($value)
                . '" can not be matched against a regular expression.'
            );
        }

        $check = @preg_match($this->pattern, $value);
        if (false === $check) {
            throw new \RuntimeException(sprintf(
                    'Failure while matching "%s", reason: %s.',
                    $this->pattern,
                    $this->messageFor(preg_last_error())
            ));
        }

        return ((1 != $check) ? (false) : (true));
    }

    /**
     * translates error code into proper error message
     *
     * @param   int  $errorCode
     * @return  string
     */
    private function messageFor($errorCode)
    {
        if (isset(self::$errors[$errorCode])) {
            return self::$errors[$errorCode];
        }

        return 'Unknown error with error code ' . $errorCode;
    }
}
