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
namespace stubbles\values;
/**
 * Provides functionality to work with single values.
 *
 * @api
 * @since  7.2.0
 */
class Value
{
    /**
     * map of additional checks added at runtime
     *
     * @type  array
     */
    private static $checks = [];
    /**
     * @type  \stubbles\values\Value
     */
    private static $null;
    /**
     * @type  mixed
     */
    private $value;

    /**
     * static initializer
     */
    public static function __static()
    {
        self::$null = new self(null);
    }

    /**
     * constructor
     *
     * @param  mixed  $value  actual result value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * static constructor
     *
     * @param   mixed   $value  actual result value
     * @return  \stubbles\values\Value
     */
    public static function of($value): self
    {
        if (null === $value) {
            return self::$null;
        }

        return new static($value);
    }

    /**
     * checks if parameter is null
     *
     * @return  bool
     * @since   8.1.0
     */
    public function isNull(): bool
    {
        return null === $this->value;
    }

    /**
     * checks if value is empty
     *
     * Value is empty if its value is null, an empty string or an empty array.
     *
     * @return  bool
     * @since   8.1.0
     */
    public function isEmpty(): bool
    {
        return $this->isNull()
            || (is_array($this->value) && count($this->value) === 0)
            || (is_string($this->value) && strlen($this->value) === 0);
    }


    /**
     * returns actual value
     *
     * @return  mixed
     */
    public function value()
    {
        return $this->value;
    }


    /**
     * checks that $needle is contained in value
     *
     * @param   mixed  $needle
     * @return  bool
     */
    public function contains($needle): bool
    {
        if (null === $this->value) {
            return is_null($needle);
        }

        if (is_string($this->value)) {
            return false !== strpos($this->value, (string) $needle);
        }

        if (is_array($this->value) || $this->value instanceof \Traversable) {
            foreach ($this->value as $element) {
                if ($element === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * checks that value contains any of the given elements
     *
     * @param   array  $elements
     * @return  bool
     */
    public function containsAnyOf(array $elements): bool
    {
        if (!is_scalar($this->value) || null === $this->value) {
            return false;
        }

        foreach ($elements as $needle) {
            if (is_bool($needle) && $this->value === $needle) {
                return true;
            } elseif (!is_bool($needle) && ($this->value === $needle || (is_string($this->value) && false !== strpos($this->value, (string) $needle)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * checks that $expected is equal to value
     *
     * @param   scalar|null  $expected
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function equals($expected): bool
    {
        if (!is_scalar($expected) && null != $expected) {
            throw new \InvalidArgumentException(
                    'Can only compare scalar values and null.'
            );
        }

        return $expected === $this->value;
    }

    /**
     * checks that value is one of the allowed values
     *
     * @param   array  $allowedValues
     * @param   bool   $strict         optional  whether type must match as well
     * @return  bool
     */
    public function isOneOf(array $allowedValues, bool $strict = false): bool
    {
        if (!is_array($this->value)) {
            return in_array($this->value, $allowedValues, $strict);
        }

        foreach ($this->value as $value) {
            if (!in_array($value, $allowedValues, $strict)) {
                return false;
            }
        }

        return true;
    }

    /**
     * checks whether value is matched by given regular expression
     *
     * @param   string  $regex  regular expression to apply
     * @return  bool
     */
    public function isMatchedBy(string $regex): bool
    {
        return pattern($regex)->matches($this->value);
    }

    /**
     * checks whether value satisfies given callable
     *
     * The given callable function must accept the value to check as first
     * parameter. It must either return true or false.
     *
     * @param   callable  $check
     * @return  bool
     */
    public function satisfies(callable $check): bool
    {
        return $check($this->value);
    }

    /**
     * adds a check method
     *
     * The $method must be an allowed method name which can be called on an
     * instance of stubbles\values\Value. The given callable function must
     * accept the value to check as first parameter and any additional
     * parameters after that. It must either return true or false.
     *
     * <code>
     * Value::addCheck('isHttpUri', [stubbles\peer\http\HttpUri::class, 'isValid']);
     *
     * if (value('http://example.net')->isHttpUri()) {
     *     // yes, do something with it
     * }
     * </code>
     *
     * Self-defined checks can be overwritten by passing another callable with
     * the same method name. PHP internal functions like is_int() must not be
     * defined as check, they are available by default. Please note that it is
     * not allowed to overwrite existing functions, e.g. you can not redefine
     * is_int().
     *
     * @param   string    $method
     * @param   callable  $function
     * @throws  \InvalidArgumentException
     */
    public static function defineCheck(string $method, callable $function)
    {
        if (function_exists($method)) {
            throw new \InvalidArgumentException('Can not overwrite internal PHP function ' . $method . '().');
        }

        self::$checks[$method] = $function;
    }

    /**
     * intercept calls to dynamicly added methods
     *
     * @param   string  $method     name of method
     * @param   array   $arguments  list of additional arguments for method
     * @return  bool
     * @throws  \BadMethodCallException  in case called method does not exist
     */
    public function __call(string $method, array $arguments): bool
    {
        if (!isset(self::$checks[$method]) && !function_exists($method)) {
            throw new \BadMethodCallException('Method ' . __CLASS__ . '::' . $method . '() does not exist.');
        } elseif (!isset(self::$checks[$method]) && function_exists($method)) {
            return $method($this->value, ...$arguments);
        }


        $function = self::$checks[$method];
        return $function($this->value, ...$arguments);
    }
}
Value::__static();
