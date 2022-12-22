<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values {

    /**
     * returns error message from last error that occurred
     *
     * 
     * @phpstan-return Result<string>
     * @since 3.4.2
     */
    function lastErrorMessage(): Result
    {
        // @phpstan-ignore-next-line
        return Result::of(error_get_last())
            ->map(fn(array $error) => $error['message']);
    }

    /**
     * determines the correct type of a value
     *
     * @since 3.1.0
     */
    function typeOf(mixed &$value): string
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
     * @since 7.1.0
     */
    function pattern(string $regex): Pattern
    {
        return new Pattern($regex);
    }

    /**
     * creates a value instance
     *
     * @api
     * @since 7.1.0
     * @template T
     * @phpstan-param T $value
     * @phpstan-return Value<T>
     */
    function value(mixed $value): Value
    {
        // @phpstan-ignore-next-line
        return Value::of($value);
    }
}
