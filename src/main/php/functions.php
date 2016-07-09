<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\values
 */
namespace stubbles\values {

    /**
     * returns error message from last error that occurred
     *
     * @return  \stubbles\values\Result
     * @since   3.4.2
     */
    function lastErrorMessage(): Result
    {
        return Result::of(error_get_last())
                ->map(function(array $error) { return $error['message']; });
    }

    /**
     * determines the correct type of a value
     *
     * @param   mixed   &$value
     * @return  string
     * @since   3.1.0
     */
    function typeOf(&$value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_resource($value)) {
            return 'resource[' . get_resource_type($value) . ']';
        }

        return \gettype($value);
    }

    /**
     * creates a pattern from given regular expression which can be used to match other string values
     *
     * @api
     * @param   string  $regex  regular expression
     * @return  \stubbles\values\Pattern
     * @since   7.1.0
     */
    function pattern(string $regex): Pattern
    {
        return new Pattern($regex);
    }

    /**
     * creates a value instance
     *
     * @api
     * @param   mixed  $value
     * @return  \stubbles\values\Value
     * @since   7.1.0
     */
    function value($value): Value
    {
        return Value::of($value);
    }
}
