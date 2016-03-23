<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\values
 */
namespace stubbles\values;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\values\Value::isOneOf().
 *
 * @group  values
 * @group  value_checks
 * @since  7.2.0
 */
class ValueIsOneOfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  string[]
     */
    private $allowedValues;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->allowedValues = ['foo', 'bar'];
    }

    /**
     * @return  array
     */
    public function validValues()
    {
        return [['foo'],
                ['bar'],
                [['bar', 'foo']]
        ];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue($value)
    {
        assertTrue(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @return  array
     */
    public function invalidValues()
    {
        return [['baz'],
                [null],
                [['bar', 'foo', 'baz']]
        ];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($value)
    {
        assertFalse(value($value)->isOneOf($this->allowedValues));
    }
}
