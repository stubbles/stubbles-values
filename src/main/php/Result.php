<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
/**
 * Enables to wrap a return value.
 *
 * In other languages or libraries this is known as an Optional, but I think
 * this is a very bad name.
 *
 * @since  6.0.0
 */
class Result
{
    /**
     * @var  \stubbles\values\Result
     */
    private static $null;
    /**
     * @var  mixed
     */
    private $value;

    /**
     * static initializer
     */
    public static function __static(): void
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
     * @return  \stubbles\values\Result
     */
    public static function of($value): self
    {
        if (null === $value) {
            return self::$null;
        }

        return new self($value);
    }

    /**
     * checks if a value is present
     *
     * Present means the value is not null.
     *
     * @return  bool
     */
    public function isPresent(): bool
    {
        return null !== $this->value;
    }

    /**
     * checks if value is empty
     *
     * @return  bool
     * @since   6.2.0
     */
    public function isEmpty(): bool
    {
        if (is_int($this->value)) {
            return false;
        }

        return empty($this->value);
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
     * returns result when value is present and fulfills the predicate
     *
     * In case the value is null or or doesn't fulfill the predicate the return
     * value is a null result.
     *
     * @param   callable  $predicate
     * @return  \stubbles\values\Result
     */
    public function filter(callable $predicate): self
    {
        if ($this->isPresent() && $predicate($this->value)) {
            return $this;
        }

        return self::$null;
    }

    /**
     * maps the value using mapper into a different result
     *
     * In case the value is null the return value still is a null result.
     *
     * @param   callable  $mapper
     * @return  \stubbles\values\Result
     */
    public function map(callable $mapper): self
    {
        if ($this->isPresent()) {
            return new self($mapper($this->value));
        }

        return self::$null;
    }

    /**
     * returns the result if value is present, or result of other
     *
     * @param   mixed  $other
     * @return  \stubbles\values\Result
     */
    public function whenNull($other): self
    {
        if ($this->isPresent()) {
            return $this;
        }

        return self::of($other);
    }

    /**
     * returns the result if value is present, or the result of applied other
     *
     * @param   callable  $other
     * @return  \stubbles\values\Result
     */
    public function applyWhenNull(callable $other): self
    {
        if ($this->isPresent()) {
            return $this;
        }

        return self::of($other());
    }

    /**
     * returns the result if value is not empty, or result of other
     *
     * @param   mixed  $other
     * @return  \stubbles\values\Result
     * @since   6.2.0
     */
    public function whenEmpty($other): self
    {
        if (!$this->isEmpty()) {
            return $this;
        }

        return self::of($other);
    }

    /**
     * returns the result if value is not empty, or the result of applied other
     *
     * @param   callable  $other
     * @return  \stubbles\values\Result
     * @since   6.2.0
     */
    public function applyWhenEmpty(callable $other): self
    {
        if (!$this->isEmpty()) {
            return $this;
        }

        return self::of($other());
    }
}
Result::__static();
