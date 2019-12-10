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
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\values\Value::contains().
 *
 * @group  values
 * @group  value_checks
 * @since  7.2.0
 */
class ValueContainsTest extends TestCase
{
/**
     * returns tuples which evaluate to true
     *
     * @return  array<array<mixed>>
     */
    public function tuplesEvaluatingToTrue(): array
    {
        return [
                [null, null],
                [5, 'foo5'],
                [5, 'fo5o'],
                ['foo', 'foobar'],
                ['foo', 'foo'],
                ['foo', ['foo', 'bar', 'baz']],
                [null, ['foo', null, 'baz']]
        ];
    }

    /**
     * @param  mixed                      $needle
     * @param  string|array<mixed>|\Traversable<mixed>  $haystack
     * @test
     * @dataProvider  tuplesEvaluatingToTrue
     */
    public function evaluatesToTrue($needle, $haystack): void
    {
        assertTrue(value($haystack)->contains($needle));
    }

    /**
     * returns tuples which evaluate to false
     *
     * @return  array<array<mixed>>
     */
    public function tuplesEvaluatingToFalse(): array
    {
        return [
                [5, 'foo'],
                [true, 'blub'],
                ['dummy', 'bar'],
                ['nope', ['foo', 'bar', 'baz']]
        ];
    }

    /**
     * @param  mixed                      $needle
     * @param  string|array<mixed>|\Traversable<mixed>  $haystack
     * @test
     * @dataProvider  tuplesEvaluatingToFalse
     */
    public function evaluatesToFalse($needle, $haystack): void
    {
        assertFalse(value($haystack)->contains($needle));
    }
}
