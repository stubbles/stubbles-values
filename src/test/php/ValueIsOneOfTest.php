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
 * Tests for stubbles\values\Value::isOneOf().
 *
 * @group  values
 * @group  value_checks
 * @since  7.2.0
 */
class ValueIsOneOfTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  string[]
     */
    private $allowedValues;

    protected function setUp(): void
    {
        $this->allowedValues = ['foo', 'bar'];
    }

    /**
     * @return  array<array<mixed>>
     */
    public function validValues(): array
    {
        return [['foo'],
                ['bar'],
                [['bar', 'foo']]
        ];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue($value): void
    {
        assertTrue(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function invalidValues(): array
    {
        return [['baz'],
                [null],
                [['bar', 'foo', 'baz']]
        ];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($value): void
    {
        assertFalse(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function evaluatesToFalseForSimilarValueWhenStrictEnabled(): void
    {
        assertFalse(value(1)->isOneOf([true, '1', 'true'], true));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function evaluatesToTrueForSimilarValueWhenStrictNotEnabled(): void
    {
        assertTrue(value(1)->isOneOf([true, '1', 'true']));
    }
}
