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
use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\values\Value.
 *
 * @since  7.2.0
 * @group  types
 * @group  values
 * @group  value_checks
 */
class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function valueOfNullIsAlwaysSame()
    {
        assert(value(null), isSameAs(value(null)));
    }

    /**
     * @test
     */
    public function valueReturnsValue()
    {
        assert(value(303)->value(), equals(303));
    }
    
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
        assertTrue(value($value)->isMatchedBy($pattern));
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
        assertFalse(value($value)->isMatchedBy($pattern));
    }

    /**
     * @test
     */
    public function valueSatisfiesCallableWhenCallableReturnsTrue()
    {
        assertTrue(value(303)->satisfies(
                function($value) { return $value === 303; }
        ));
    }

    /**
     * @test
     */
    public function valueDoesNotSatisfyCallableWhenCallableReturnsFalse()
    {
        assertFalse(value(303)->satisfies(
                function($value) { return $value !== 303; }
        ));
    }

    /**
     * @test
     */
    public function useUndefinedCheckMethodThrowsBadMethodCallException()
    {
        expect(function() {
                value(303)->isAwesome();
        })
        ->throws(\BadMethodCallException::class)
        ->withMessage('Method ' . Value::class . '::isAwesome() does not exist.');
    }

    /**
     * @test
     */
    public function useDefinedCheckReturnsResultOfDefinedCheck()
    {
        Value::defineCheck(
                'isReallyAwesome',
                function($value) { return $value === 303; }
        );
        assertTrue(value(303)->isReallyAwesome());
    }

    /**
     * @test
     */
    public function internalPhpFunctionsAreAlreadyDefinedAsChecks()
    {
        assertTrue(value(303)->is_int());
    }

    /**
     * @test
     */
    public function internalPhpFunctionChecksCanNotBeOverwritten()
    {
        expect(function() {
            Value::defineCheck(
                    'is_integer',
                    function($value) { return $value === 303; }
            );
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Can not overwrite internal PHP function is_integer().');
    }
}
