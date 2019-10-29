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
    public function resultOfNullIsAlwaysSame()
    {
        assertThat(Result::of(null), isSameAs(Result::of(null)));
    }

    /**
     * @test
     */
    public function resultOfNullMeansResultNotPresent()
    {
        assertFalse(Result::of(null)->isPresent());
    }

    /**
     * @test
     */
    public function resultOfNonNullMeansResultPresent()
    {
        assertTrue(Result::of(303)->isPresent());
    }

    /**
     * @test
     */
    public function valueReturnsResultValue()
    {
        assertThat(Result::of(303)->value(), equals(303));
    }

    /**
     * @test
     */
    public function filterOnResultOfNullReturnsResultOfNull()
    {
        assertThat(
                Result::of(null)->filter(function($value) { return true; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultOfNullWhenPredicateDenies()
    {
        assertThat(
                Result::of(303)->filter(function($value) { return false; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultWhenPredicateApproves()
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
    public function mapResultOfNullReturnsResultOfNull()
    {
        assertThat(
                Result::of(null)->map(function($value) { return 909; }),
                isSameAs(Result::of(null))
        );
    }

    /**
     * @test
     */
    public function mapResultOfNonNullReturnsMappedResult()
    {
        assertThat(
                Result::of(303)->map(function($value) { return 909; }),
                equals(Result::of(909))
        );
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNullReturnsOther()
    {
        assertThat(Result::of(null)->whenNull(909)->value(), equals(909));
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNonNullReturnsValue()
    {
        assertThat(Result::of(303)->whenNull(909)->value(), equals(303));
    }

    /**
     * @test
     */
    public function applyhenNullOnResultOfNullReturnsOther()
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
    public function applyWhenNullOnResultOfNonNullReturnsValue()
    {
        assertThat(
                Result::of(303)
                        ->applyWhenNull(function() { return 909; })
                        ->value(),
                equals(303)
        );
    }

    /**
     * @return  array
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
    public function isEmptyForEmptyValues($value)
    {
        assertTrue(Result::of($value)->isEmpty());
    }

    /**
     * @return  array
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
    public function isNotEmptyForNomEmptyValues($value)
    {
        assertFalse(Result::of($value)->isEmpty());
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfEmptyReturnsOther($value)
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals(909));
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfNonEmptyReturnsValue($value)
    {
        assertThat(Result::of($value)->whenEmpty(909)->value(), equals($value));
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function applyhenEmptyOnResultOfEmptyReturnsOther($value)
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
    public function applyWhenEmptyOnResultOfNonEmptyReturnsValue($value)
    {
        assertThat(
                Result::of($value)
                        ->applyWhenEmpty(function() { return 909; })
                        ->value(),
                equals($value)
        );
    }
}
