<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
/**
 * Tests for stubbles\*().
 *
 * @since  3.1.0
 */
#[Group('lang')]
class FunctionsTest extends TestCase
{
    /**
     * @since  3.4.2
     */
    #[Test]
    public function lastErrorMessageShouldBeNullByDefault(): void
    {
        assertThat(lastErrorMessage(), equals(Result::of(null)));
    }

    /**
     * @since  3.4.2
     */
    #[Test]
    #[WithoutErrorHandler]
    public function lastErrorMessageShouldContainLastError(): void
    {
        @file_get_contents(__DIR__ . '/doesNotExist.txt');
        assertThat(
            lastErrorMessage()->value(),
            equals(
                'file_get_contents('
                . __DIR__
                . '/doesNotExist.txt): Failed to open stream: No such file or directory'
            )
        );
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    public function typeOfOjectReturnsNameOfClass(): void
    {
        assertThat(typeOf($this), equals(__CLASS__));
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    public function typeOfResourceReturnsResourceWithResourceType(): void
    {
        $fp = fopen(__FILE__, 'r');
        assertThat(typeOf($fp), equals('resource[stream]'));
        fclose($fp);
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function valueTypes(): array
    {
        return [
                [303, 'integer'],
                [3.03, 'double'],
                ['foo', 'string'],
                [[], 'array'],
                [true, 'boolean'],
                [null, 'NULL']
        ];
    }

    /**
     * @param  mixed  $value
     * @since  7.0.0
     */
    #[Test]
    #[DataProvider('valueTypes')]
    public function typeOfOtherValuesReturnsNativeType($value, string $expectedType): void
    {
        assertThat(typeOf($value), equals($expectedType));
    }
}
