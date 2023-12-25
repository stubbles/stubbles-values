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
use function bovigo\assert\expect;
/**
 * Tests for stubbles\values\Pattern.
 *
 * @group values
 * @since 7.1.0
 */
class PatternTest extends TestCase
{
    /**
     * @return  array<array<string>>
     */
    public static function validValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'foo'];
        yield ['/^([a-z]{3})$/i', 'foo'];
        yield ['/^([a-z]{3})$/i', 'Bar'];
    }

    /**
     * @test
     * @dataProvider validValues
     */
    public function validValueEvaluatesToTrue(string $pattern, string $value): void
    {
        assertTrue(pattern($pattern)->matches($value));
    }

    public static function invalidValues(): Generator
    {
        yield ['/^([a-z]{3})$/', 'Bar'];
        yield ['/^([a-z]{3})$/', 'baz0123'];
        yield ['/^([a-z]{3})$/i', 'baz0123'];
    }

    /**
     * @test
     * @dataProvider invalidValues
     */
    public function invalidValueEvaluatesToFalse(string $pattern, string $value): void
    {
        assertFalse(pattern($pattern)->matches($value));
    }

    /**
     * @test
     */
    public function invalidRegexThrowsRuntimeExceptionOnEvaluation(): void
    {
        expect(fn() => pattern('^([a-z]{3})$')->matches('foo'))
            ->throws(\RuntimeException::class)
            ->withMessage('Failure while matching "^([a-z]{3})$", reason: internal PCRE error.');
    }
}
