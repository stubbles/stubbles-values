<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use BadMethodCallException;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isSameAs
};
/**
 * Tests for stubbles\values\Value.
 *
 * @since 7.2.0
 * @group types
 * @group values
 * @group value_checks
 */
class ValueTest extends TestCase
{
    /**
     * @test
     */
    public function valueOfNullIsAlwaysSame(): void
    {
        assertThat(value(null), isSameAs(value(null)));
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfNullIsNull(): void
    {
        assertTrue(value(null)->isNull());
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfNullIsEmpty(): void
    {
        assertTrue(value(null)->isEmpty());
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfEmptyArrayIsEmpty(): void
    {
        assertTrue(value([])->isEmpty());
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfEmptyStringIsEmpty(): void
    {
        assertTrue(value('')->isEmpty());
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfNonNullIsNotNull(): void
    {
        assertFalse(value(303)->isNull());
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function valueOfNonNullIsNotEmpty(): void
    {
        assertFalse(value(303)->isEmpty());
    }

    /**
     * @test
     */
    public function valueReturnsValue(): void
    {
        assertThat(value(303)->value(), equals(303));
    }

    public function validValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'foo'];
        yield ['/^([a-z]{3})$/i', 'foo'];
        yield ['/^([a-z]{3})$/i', 'Bar'];
    }

    /**
     * @test
     * @dataProvider validValues
     */
    public function validValueEvaluatesToTrue(string $pattern, string $value): void
    {
        assertTrue(value($value)->isMatchedBy($pattern));
    }

    public function invalidValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'Bar'];
        yield ['/^([a-z]{3})$/', 'baz0123'];
        yield ['/^([a-z]{3})$/i', 'baz0123'];
    }

    /**
     * @test
     * @dataProvider invalidValues
     */
    public function invalidValueEvaluatesToFalse(string $pattern, string $value): void
    {
        assertFalse(value($value)->isMatchedBy($pattern));
    }

    /**
     * @test
     */
    public function valueSatisfiesCallableWhenCallableReturnsTrue(): void
    {
        assertTrue(value(303)->satisfies(fn($value) => $value === 303));
    }

    /**
     * @test
     */
    public function valueDoesNotSatisfyCallableWhenCallableReturnsFalse(): void
    {
        assertFalse(value(303)->satisfies(fn($value) => $value !== 303));
    }

    /**
     * @test
     */
    public function useUndefinedCheckMethodThrowsBadMethodCallException(): void
    {
        expect(fn() => value(303)->isAwesome())
            ->throws(BadMethodCallException::class)
            ->withMessage('Method ' . Value::class . '::isAwesome() does not exist.');
    }

    /**
     * @test
     */
    public function useDefinedCheckReturnsResultOfDefinedCheck(): void
    {
        Value::defineCheck(
            'isReallyAwesome',
            fn($value) => $value === 303
        );
        assertTrue(value(303)->isReallyAwesome());
    }

    /**
     * @test
     */
    public function internalPhpFunctionsAreAlreadyDefinedAsChecks(): void
    {
        assertTrue(value(303)->is_int());
    }

    /**
     * @test
     */
    public function internalPhpFunctionChecksCanNotBeOverwritten(): void
    {
        expect(fn() => Value::defineCheck(
            'is_integer',
            fn($value) => $value === 303
        ))
            ->throws(InvalidArgumentException::class)
            ->withMessage('Can not overwrite internal PHP function is_integer().');
    }
}
