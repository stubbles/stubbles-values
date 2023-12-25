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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
 */
#[Group('types')]
#[Group('values')]
#[Group('value_checks')]
class ValueTest extends TestCase
{
    #[Test]
    public function valueOfNullIsAlwaysSame(): void
    {
        assertThat(value(null), isSameAs(value(null)));
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfNullIsNull(): void
    {
        assertTrue(value(null)->isNull());
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfNullIsEmpty(): void
    {
        assertTrue(value(null)->isEmpty());
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfEmptyArrayIsEmpty(): void
    {
        assertTrue(value([])->isEmpty());
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfEmptyStringIsEmpty(): void
    {
        assertTrue(value('')->isEmpty());
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfNonNullIsNotNull(): void
    {
        assertFalse(value(303)->isNull());
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function valueOfNonNullIsNotEmpty(): void
    {
        assertFalse(value(303)->isEmpty());
    }

    #[Test]
    public function valueReturnsValue(): void
    {
        assertThat(value(303)->value(), equals(303));
    }

    public static function validValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'foo'];
        yield ['/^([a-z]{3})$/i', 'foo'];
        yield ['/^([a-z]{3})$/i', 'Bar'];
    }

    #[Test]
    #[DataProvider('validValues')]
    public function validValueEvaluatesToTrue(string $pattern, string $value): void
    {
        assertTrue(value($value)->isMatchedBy($pattern));
    }

    public static function invalidValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'Bar'];
        yield ['/^([a-z]{3})$/', 'baz0123'];
        yield ['/^([a-z]{3})$/i', 'baz0123'];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function invalidValueEvaluatesToFalse(string $pattern, string $value): void
    {
        assertFalse(value($value)->isMatchedBy($pattern));
    }

    #[Test]
    public function valueSatisfiesCallableWhenCallableReturnsTrue(): void
    {
        assertTrue(value(303)->satisfies(fn($value) => $value === 303));
    }

    #[Test]
    public function valueDoesNotSatisfyCallableWhenCallableReturnsFalse(): void
    {
        assertFalse(value(303)->satisfies(fn($value) => $value !== 303));
    }

    #[Test]
    public function useUndefinedCheckMethodThrowsBadMethodCallException(): void
    {
        expect(fn() => value(303)->isAwesome())
            ->throws(BadMethodCallException::class)
            ->withMessage('Method ' . Value::class . '::isAwesome() does not exist.');
    }

    #[Test]
    public function useDefinedCheckReturnsResultOfDefinedCheck(): void
    {
        Value::defineCheck(
            'isReallyAwesome',
            fn($value) => $value === 303
        );
        assertTrue(value(303)->isReallyAwesome());
    }

    #[Test]
    public function internalPhpFunctionsAreAlreadyDefinedAsChecks(): void
    {
        assertTrue(value(303)->is_int());
    }

    #[Test]
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
