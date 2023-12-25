<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\values\Result.
 *
 * @since 6.0.0
 */
#[Group('types')]
class ResultTest extends TestCase
{
    #[Test]
    public function resultOfNullIsAlwaysSame(): void
    {
        assertThat(Result::of(null), isSameAs(Result::of(null)));
    }

    #[Test]
    public function resultOfNullMeansResultNotPresent(): void
    {
        assertFalse(Result::of(null)->isPresent());
    }

    #[Test]
    public function resultOfNonNullMeansResultPresent(): void
    {
        assertTrue(Result::of(303)->isPresent());
    }

    #[Test]
    public function valueReturnsResultValue(): void
    {
        assertThat(Result::of(303)->value(), equals(303));
    }

    #[Test]
    public function filterOnResultOfNullReturnsResultOfNull(): void
    {
        assertThat(
            Result::of(null)->filter(fn() => true),
            isSameAs(Result::of(null))
        );
    }

    #[Test]
    public function filterOnResultOfNonNullReturnsResultOfNullWhenPredicateDenies(): void
    {
        assertThat(
            Result::of(303)->filter(fn() => false),
            isSameAs(Result::of(null))
        );
    }

    #[Test]
    public function filterOnResultOfNonNullReturnsResultWhenPredicateApproves(): void
    {
        $result = Result::of(303);
        assertThat(
            $result->filter(fn() => true),
            isSameAs($result)
        );
    }

    #[Test]
    public function mapResultOfNullReturnsResultOfNull(): void
    {
        assertThat(
            Result::of(null)->map(fn() => 909),
            isSameAs(Result::of(null))
        );
    }

    #[Test]
    public function mapResultOfNonNullReturnsMappedResult(): void
    {
        assertThat(
            Result::of(303)->map(fn() => 909),
            equals(Result::of(909))
        );
    }

    #[Test]
    public function whenNullOnResultOfNullReturnsOther(): void
    {
        assertThat(Result::of(null)->whenNull(909)->value(), equals(909));
    }

    #[Test]
    public function whenNullOnResultOfNonNullReturnsValue(): void
    {
        assertThat(Result::of(303)->whenNull(909)->value(), equals(303));
    }

    #[Test]
    public function applyhenNullOnResultOfNullReturnsOther(): void
    {
        assertThat(
            Result::of(null)
                ->applyWhenNull(fn() => 909)
                ->value(),
            equals(909)
        );
    }

    #[Test]
    public function applyWhenNullOnResultOfNonNullReturnsValue(): void
    {
        assertThat(
            Result::of(303)
                ->applyWhenNull(fn() => 909)
                ->value(),
            equals(303)
        );
    }

    public static function emptyValues(): Generator
    {
        yield [null];
        yield [''];
        yield [[]];
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('emptyValues')]
    public function isEmptyForEmptyValues(mixed $value): void
    {
        assertTrue(Result::of($value)->isEmpty());
    }

    public static function nonEmptyValues(): Generator
    {
        yield [0];
        yield [303];
        yield ['foo'];
        yield [['foo']];
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('nonEmptyValues')]
    public function isNotEmptyForNomEmptyValues(mixed $value): void
    {
        assertFalse(Result::of($value)->isEmpty());
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('emptyValues')]
    public function whenEmptyOnResultOfEmptyReturnsOther(mixed $value): void
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals(909));
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('nonEmptyValues')]
    public function whenEmptyOnResultOfNonEmptyReturnsValue(mixed $value): void
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals($value));
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('emptyValues')]
    public function applyhenEmptyOnResultOfEmptyReturnsOther(mixed $value): void
    {
        assertThat(
            Result::of($value)
                ->applyWhenEmpty(fn() => 909)
                ->value(),
            equals(909)
        );
    }

    /**
     * @since 6.2.0
     */
    #[Test]
    #[DataProvider('nonEmptyValues')]
    public function applyWhenEmptyOnResultOfNonEmptyReturnsValue(mixed $value): void
    {
        assertThat(
            Result::of($value)
                ->applyWhenEmpty(fn() => 909)
                ->value(),
            equals($value)
        );
    }
}
