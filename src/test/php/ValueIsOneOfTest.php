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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\values\Value::isOneOf().
 *
 * @since  7.2.0
 */
#[Group('values')]
#[Group('value_checks')]
class ValueIsOneOfTest extends TestCase
{
    /** @var  string[] */
    private array $allowedValues = ['foo', 'bar'];

    public static function validValues(): Generator
    {
        yield ['foo'];
        yield ['bar'];
        yield [['bar', 'foo']];
    }

    #[Test]
    #[DataProvider('validValues')]
    public function validValueEvaluatesToTrue(mixed $value): void
    {
        assertTrue(value($value)->isOneOf($this->allowedValues));
    }

    public static function invalidValues(): Generator
    {
        yield ['baz'];
        yield [null];
        yield [['bar', 'foo', 'baz']];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function invalidValueEvaluatesToFalse(mixed $value): void
    {
        assertFalse(value($value)->isOneOf($this->allowedValues));
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function evaluatesToFalseForSimilarValueWhenStrictEnabled(): void
    {
        assertFalse(value(1)->isOneOf([true, '1', 'true'], true));
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function evaluatesToTrueForSimilarValueWhenStrictNotEnabled(): void
    {
        assertTrue(value(1)->isOneOf([true, '1', 'true']));
    }
}
