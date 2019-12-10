<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\values\Result.
 *
 * @since  6.0.0
 * @group  types
 */
class ResultTest extends TestCase
{
    /**
     * @test
     */
    public function resultOfNullIsAlwaysSame(): void
    {
        assertThat(Result::of(null), isSameAs(Result::of(null)));
    }

    /**
     * @test
     */
    public function resultOfNullMeansResultNotPresent(): void
    {
        assertFalse(Result::of(null)->isPresent());
    }

    /**
     * @test
     */
    public function resultOfNonNullMeansResultPresent(): void
    {
        assertTrue(Result::of(303)->isPresent());
    }

    /**
     * @test
     */
    public function valueReturnsResultValue(): void
    {
        assertThat(Result::of(303)->value(), equals(303));
    }

    /**
     * @test
     */
    public function filterOnResultOfNullReturnsResultOfNull(): void
    {
        assertThat(
                Result::of(null)->filter(function($value) { return true; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultOfNullWhenPredicateDenies(): void
    {
        assertThat(
                Result::of(303)->filter(function($value) { return false; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultWhenPredicateApproves(): void
    {
        $result = Result::of(303);
        assertThat(
                $result->filter(function($value) { return true; }),
                isSameAs($result)
        );
    }

    /**
     * @test
     */
    public function mapResultOfNullReturnsResultOfNull(): void
    {
        assertThat(
                Result::of(null)->map(function($value) { return 909; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function mapResultOfNonNullReturnsMappedResult(): void
    {
        assertThat(
                Result::of(303)->map(function($value) { return 909; }),
                equals(Result::of(909))
        );
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNullReturnsOther(): void
    {
        assertThat(Result::of(null)->whenNull(909)->value(), equals(909));
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNonNullReturnsValue(): void
    {
        assertThat(Result::of(303)->whenNull(909)->value(), equals(303));
    }

    /**
     * @test
     */
    public function applyhenNullOnResultOfNullReturnsOther(): void
    {
        assertThat(
                Result::of(null)
                        ->applyWhenNull(function() { return 909; })
                        ->value(),
                equals(909)
        );
    }

    /**
     * @test
     */
    public function applyWhenNullOnResultOfNonNullReturnsValue(): void
    {
        assertThat(
                Result::of(303)
                        ->applyWhenNull(function() { return 909; })
                        ->value(),
                equals(303)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public function emptyValues(): array
    {
        return [[null], [''], [[]]];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function isEmptyForEmptyValues($value): void
    {
        assertTrue(Result::of($value)->isEmpty());
    }

    /**
     * @return  array<array<mixed>>
     */
    public function nonEmptyValues(): array
    {
        return [[0], [303], ['foo'], [['foo']]];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function isNotEmptyForNomEmptyValues($value): void
    {
        assertFalse(Result::of($value)->isEmpty());
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfEmptyReturnsOther($value): void
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals(909));
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfNonEmptyReturnsValue($value): void
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals($value));
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function applyhenEmptyOnResultOfEmptyReturnsOther($value): void
    {
        assertThat(
                Result::of($value)
                        ->applyWhenEmpty(function() { return 909; })
                        ->value(),
                equals(909)
        );
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function applyWhenEmptyOnResultOfNonEmptyReturnsValue($value): void
    {
        assertThat(
                Result::of($value)
                        ->applyWhenEmpty(function() { return 909; })
                        ->value(),
                equals($value)
        );
    }
}
