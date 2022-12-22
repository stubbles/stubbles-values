<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use Generator;
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
    /** @var  string[] */
    private array $allowedValues = ['foo', 'bar'];

    public function validValues(): Generator
    {
        yield ['foo'];
        yield ['bar'];
        yield [['bar', 'foo']];
    }

    /**
     * @test
     * @dataProvider validValues
     */
    public function validValueEvaluatesToTrue(mixed $value): void
    {
        assertTrue(value($value)->isOneOf($this->allowedValues));
    }

    public function invalidValues(): Generator
    {
        yield ['baz'];
        yield [null];
        yield [['bar', 'foo', 'baz']];
    }

    /**
     * @test
     * @dataProvider invalidValues
     */
    public function invalidValueEvaluatesToFalse(mixed $value): void
    {
        assertFalse(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function evaluatesToFalseForSimilarValueWhenStrictEnabled(): void
    {
        assertFalse(value(1)->isOneOf([true, '1', 'true'], true));
    }

    /**
     * @test
     * @since 8.1.0
     */
    public function evaluatesToTrueForSimilarValueWhenStrictNotEnabled(): void
    {
        assertTrue(value(1)->isOneOf([true, '1', 'true']));
    }
}
