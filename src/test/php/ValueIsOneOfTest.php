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
     * @type  string[]
     */
    private $allowedValues;

    protected function setUp(): void
    {
        $this->allowedValues = ['foo', 'bar'];
    }

    public function validValues(): array
    {
        return [['foo'],
                ['bar'],
                [['bar', 'foo']]
        ];
    }

    /**
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue($value)
    {
        assertTrue(value($value)->isOneOf($this->allowedValues));
    }

    public function invalidValues(): array
    {
        return [['baz'],
                [null],
                [['bar', 'foo', 'baz']]
        ];
    }

    /**
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($value)
    {
        assertFalse(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function evaluatesToFalseForSimilarValueWhenStrictEnabled()
    {
        assertFalse(value(1)->isOneOf([true, '1', 'true'], true));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function evaluatesToTrueForSimilarValueWhenStrictNotEnabled()
    {
        assertTrue(value(1)->isOneOf([true, '1', 'true']));
    }
}
