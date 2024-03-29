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
 * Tests for stubbles\values\Value::containsAnyOf().
 *
 * @since 7.2.0
 */
#[Group('values')]
#[Group('value_checks')]
class ValueContainsAnyOfTest extends TestCase
{
    public static function tuplesEvaluatingToTrue(): Generator
    {
        yield [[true], true];
        yield [[false], false];
        yield [[5], 5];
        yield [[5], '55'];
        yield [[5], '25'];
        yield [[5], 'foo5'];
        yield [[5], 'fo5o'];
        yield [['foo', 'bar'], 'foobar'];
        yield [['foo', 'bar'], 'foo'];
    }

    #[Test]
    #[DataProvider('tuplesEvaluatingToTrue')]
    public function evaluatesToTrue(array $contained, mixed $value): void
    {
        assertTrue(value($value)->containsAnyOf($contained));
    }

    public static function tuplesEvaluatingToFalse(): Generator
    {
        yield [[true], false];
        yield [[false], true];
        yield [[false], new \stdClass()];
        yield [[false], null];
        yield [[5], 'foo'];
        yield [[5], 6];
        yield [[5], 55];
        yield [[5], 25];
        yield [[true], 5];
        yield [[false], 0];
        yield [[true], 'foo'];
        yield [['foo', 'baz'], 'bar'];
    }

    #[Test]
    #[DataProvider('tuplesEvaluatingToFalse')]
    public function evaluatesToFalse(array $contained, mixed $value): void
    {
        assertFalse(value($value)->containsAnyOf($contained));
    }
}
