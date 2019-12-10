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
use function bovigo\assert\expect;
/**
 * Tests for stubbles\values\Value::equals().
 *
 * @group  values
 * @group  value_checks
 * @since  7.2.0
 */
class ValueEqualsTest extends TestCase
{
    /**
     * @test
     */
    public function constructionWithObjectThrowsIllegalArgumentException(): void
    {
        $value = value('foo');
        expect(function() use ($value) {
                $value->equals(new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @return  array<array<mixed>>
     */
    public function tuplesEvaluatingToTrue(): array
    {
        return [[true, true],
                [false, false],
                [5, 5],
                [null, null],
                ['foo', 'foo']
        ];
    }

    /**
     * @param  scalar  $expected
     * @param  mixed   $value
     * @test
     * @dataProvider  tuplesEvaluatingToTrue
     */
    public function evaluatesToTrue($expected, $value): void
    {
        assertTrue(value($value)->equals($expected));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function tuplesEvaluatingToFalse(): array
    {
        return [[true, false],
                [false, true],
                [false, new \stdClass()],
                [false, null],
                [5, 'foo'],
                [5, 6],
                [true, 5],
                [false, 0],
                [true, 'foo'],
                ['foo', 'bar'],
                [5, new \stdClass()],
                ['foo', new \stdClass()]
        ];
    }

    /**
     * @param  scalar  $expected
     * @param  mixed   $value
     * @test
     * @dataProvider  tuplesEvaluatingToFalse
     */
    public function evaluatesToFalse($expected, $value): void
    {
        assertFalse(value($value)->equals($expected));
    }
}
