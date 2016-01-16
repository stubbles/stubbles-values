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
 * Tests for stubbles\values\Pattern.
 *
 * @group  values
 * @since  7.1.0
 */
class PatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return  array
     */
    public function validValues()
    {
        return [['/^([a-z]{3})$/', 'foo'],
                ['/^([a-z]{3})$/i', 'foo'],
                ['/^([a-z]{3})$/i', 'Bar']
        ];
    }

    /**
     * @param  string  $pattern
     * @param  string  $value
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue($pattern, $value)
    {
        assertTrue(pattern($pattern)->matches($value));
    }

    /**
     * @return  array
     */
    public function invalidValues()
    {
        return [['/^([a-z]{3})$/', 'Bar'],
                ['/^([a-z]{3})$/', 'baz0123'],
                ['/^([a-z]{3})$/i', 'baz0123']
        ];
    }

    /**
     * @param  string  $pattern
     * @param  string  $value
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($pattern, $value)
    {
        assertFalse(pattern($pattern)->matches($value));
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  Given value of type "integer" can not be matched against a regular expression
     */
    public function nonStringsThrowInvalidArgumentException()
    {
        pattern('/^([a-z]{3})$/')->matches(303);
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  Given value of type "NULL" can not be matched against a regular expression
     */
    public function nullThrowInvalidArgumentException()
    {
        pattern('/^([a-z]{3})$/')->matches(null);
    }

    /**
     * @test
     * @expectedException  RuntimeException
     * @expectedExceptionMessage  Failure while matching "^([a-z]{3})$", reason: invalid regular expression.
     */
    public function invalidRegexThrowsRuntimeExceptionOnEvaluation()
    {
        pattern('^([a-z]{3})$')->matches('foo');
    }
}
