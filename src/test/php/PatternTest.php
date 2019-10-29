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
 * Tests for stubbles\values\Pattern.
 *
 * @group  values
 * @since  7.1.0
 */
class PatternTest extends TestCase
{
    /**
     * @return  array
     */
    public function validValues(): array
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
    public function invalidValues(): array
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
     */
    public function invalidRegexThrowsRuntimeExceptionOnEvaluation()
    {
        expect(function() {
                pattern('^([a-z]{3})$')->matches('foo');
        })
        ->throws(\RuntimeException::class)
        ->withMessage('Failure while matching "^([a-z]{3})$", reason: internal PCRE error.');
    }
}
