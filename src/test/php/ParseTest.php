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
use function bovigo\assert\assertThat;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Tests for stubbles\values\Parse.
 *
 * @group  values
 * @since  4.1.0
 */
class ParseTest extends TestCase
{
    protected function tearDown(): void
    {
        Parse::removeRecognition('binford');
        Parse::__static();
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToIntConversions(): array
    {
        return [
            [0, '0'],
            [1, '1'],
            [-303, '-303'],
            [80, '80foo'],
            [3, '3.14'],
            [0, ''],
            [null, null]
        ];
    }

    /**
     * @param  int     $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToIntConversions
     */
    public function toIntReturnsValueCastedToInteger($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toInt($stringToParse), equals($expectedResult));
    }

    /**
     * @param  int     $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToIntConversions
     * @since  5.0.0
     */
    public function asIntReturnsValueCastedToInteger($expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asInt(), equals($expectedResult));
    }

    /**
     * @param  int     $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToIntConversions
     * @since  5.0.0
     */
    public function asIntWithDefaultReturnsValueCastedToInteger($expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo('foo')->asInt(), equals($expectedResult));
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function toIntOnNullReturnsNull(): void
    {
        assertNull(Parse::toInt(null));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToFloatConversions(): array
    {
        return [
            [0.1, '0.1'],
            [1, '1'],
            [-3.03, '-3.03'],
            [8.0, '8.0foo'],
            [3.14, '3.14'],
            [0, ''],
            [null, null]
        ];
    }

    /**
     * @param  float   $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToFloatConversions
     */
    public function toFloatReturnsValueCastedToFloat($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toFloat($stringToParse), equals($expectedResult));
    }

    /**
     * @param  float   $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToFloatConversions
     * @since  5.0.0
     */
    public function asFloatReturnsValueCastedToFloat($expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asFloat(), equals($expectedResult));
    }

    /**
     * @param  float   $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToFloatConversions
     * @since  5.0.0
     */
    public function asFloatWithDefaultReturnsValueCastedToFloat($expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo('foo')->asFloat(), equals($expectedResult));
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function toFloatOnNullReturnsNull(): void
    {
        assertNull(Parse::toFloat(null));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToBoolConversions(): array
    {
        return [
            [true, 'yes'],
            [true, 'true'],
            [true, 'on'],
            [false, '3.14'],
            [false, 'no'],
            [false, 'false'],
            [false, 'off'],
            [false, 'other'],
            [false, ''],
            [false, null],

        ];
    }

    /**
     * @param  bool    $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToBoolConversions
     */
    public function toBoolReturnsValueCastedToBool($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toBool($stringToParse), equals($expectedResult));
    }

    /**
     * @param  bool    $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToBoolConversions
     * @since  5.0.0
     */
    public function asBoolReturnsValueCastedToBool($expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asBool(), equals($expectedResult));
    }

    /**
     * @param  bool    $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToBoolConversions
     * @since  5.0.0
     */
    public function asBoolWithDefaultReturnsValueCastedToBool($expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asBool(),
                equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToListConversions(): array
    {
        return [
            [['foo', 'bar', 'baz'], 'foo|bar|baz', Parse::SEPARATOR_LIST],
            [['foo|bar|baz'], 'foo|bar|baz', ','],
            [['foo', 'bar', 'baz'], 'foo,bar,baz', ','],
            [['foo', 'bar', 'baz'], '[foo|bar|baz]', Parse::SEPARATOR_LIST],
            [[], '', Parse::SEPARATOR_LIST],
            [[], '', ','],
            [[], '[]', Parse::SEPARATOR_LIST],
            [[], '[]', ','],
            [null, null, Parse::SEPARATOR_LIST],
            [['', ''], '|', Parse::SEPARATOR_LIST],
            [['', ''], ',', ','],
            [['', ''], '[|]', Parse::SEPARATOR_LIST],
            [['', ''], '[,]', ','],
            [['foo'], 'foo', Parse::SEPARATOR_LIST],
            [['foo'], 'foo', ','],
            [['foo'], '[foo]', Parse::SEPARATOR_LIST],
            [['foo'], '[foo]', ',']

        ];
    }

    /**
     * @param  mixed   $expectedResult
     * @param  string  $stringToParse
     * @param  string  $separator
     * @test
     * @dataProvider  stringToListConversions
     */
    public function toListReturnsValueCastedToList(
            $expectedResult,
            ?string $stringToParse,
            string $separator
    ): void {
        assertThat(Parse::toList($stringToParse, $separator), equals($expectedResult));
    }

    /**
     * @param  mixed   $expectedResult
     * @param  string  $stringToParse
     * @param  string  $separator
     * @test
     * @dataProvider  stringToListConversions
     * @since  5.0.0
     */
    public function asListReturnsValueCastedToList(
            $expectedResult,
            ?string $stringToParse,
            string $separator
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asList($separator), equals($expectedResult));
    }

    /**
     * @param  mixed   $expectedResult
     * @param  string  $stringToParse
     * @param  string  $separator
     * @test
     * @dataProvider  stringToListConversions
     * @since  5.0.0
     */
    public function asListWithDefaultReturnsValueCastedToList(
            $expectedResult,
            ?string $stringToParse,
            string $separator
    ): void {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asList($separator),
                equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToMapConversions(): array
    {
        return [
            [['foo', 'bar', 'baz'], 'foo|bar|baz'],
            [['foo', 'bar', 'baz'], '[foo|bar|baz]'],
            [['foo' => 'bar', 'baz' => 'dummy'], 'foo:bar|baz:dummy'],
            [['foo' => 'bar', 'baz' => 'dummy'], '[foo:bar|baz:dummy]'],
            [['foo' => 'bar', 'baz'], 'foo:bar|baz'],
            [['foo' => 'bar', 'baz'], '[foo:bar|baz]'],
            [[], ''],
            [[], '[]'],
            [null, null],
            [['', ''], '|'],
            [['', ''], '[|]'],
            [['foo'], 'foo'],
            [['foo'], '[foo]'],
            [['foo' => 'baz'], 'foo:baz'],
            [['foo' => 'baz'], '[foo:baz]']

        ];
    }

    /**
     * @param  array<mixed>  $expectedResult
     * @param  string        $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     */
    public function toMapReturnsValueCastedToMap($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toMap($stringToParse), equals($expectedResult));
    }

    /**
     * @param  array<mixed>  $expectedResult
     * @param  string        $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     * @since  5.0.0
     */
    public function asMapReturnsValueCastedToMap($expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asMap(), equals($expectedResult));
    }

    /**
     * @param  array<mixed>  $expectedResult
     * @param  string        $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     * @since  5.0.0
     */
    public function asMapWithDefaultReturnsValueCastedToMap($expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asMap(),
                equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToRangeConversions(): array
    {
        return [
            [[1, 2, 3, 4, 5], '1..5'],
            [['a', 'b', 'c', 'd', 'e'], 'a..e'],
            [[], '1..'],
            [[], 'a..'],
            [[], '..5'],
            [[], '..e'],
            [[5, 4, 3, 2, 1], '5..1'],
            [['e', 'd', 'c', 'b', 'a'], 'e..a'],
            [[], ''],
            [null, null],
            [[], 'other']

        ];
    }

    /**
     * @param  mixed[]  $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToRangeConversions
     */
    public function toRangeReturnsValueCastedToRange($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toRange($stringToParse), equals($expectedResult));
    }

    /**
     * @param  mixed[]  $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToRangeConversions
     * @since  5.0.0
     */
    public function asRangeReturnsValueCastedToRange($expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asRange(), equals($expectedResult));
    }

    /**
     * @param  mixed[]  $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToRangeConversions
     * @since  5.0.0
     */
    public function asRangeWithDefaultReturnsValueCastedToRange($expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asRange(),
                equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToClassConversions(): array
    {
        return [
            [new \ReflectionClass(__CLASS__), __CLASS__ . '.class'],
            [new \ReflectionClass(SomeInterface::class), SomeInterface::class . '.class'],
            [null, null],
            [null, ''],
            [null, 'other']

        ];
    }

    /**
     * @param  \ReflectionClass<object>  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     */
    public function toClassReturnsValueCastedToClassInstance(?\ReflectionClass $expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toClass($stringToParse), equals($expectedResult));
    }

    /**
     * @param  \ReflectionClass<object>  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     * @since  5.0.0
     */
    public function asClassReturnsValueCastedToClassInstance(?\ReflectionClass $expectedResult, ?string $stringToParse): void
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asClass(), equals($expectedResult));
    }

    /**
     * @param  \ReflectionClass<object>  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     * @since  5.0.0
     */
    public function asClassWithDefaultReturnsValueCastedToClassInstance(?\ReflectionClass $expectedResult, ?string $stringToParse): void
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo('foo')->asClass(), equals($expectedResult));
    }

    /**
     * @test
     */
    public function toClassWithNonExistingClassThrowsReflectionException(): void
    {
        expect(function() {
            Parse::toClass('does\not\Exist.class');
        })
            ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function asClassWithNonExistingClassThrowsReflectionException(): void
    {
        $parse = new Parse('does\not\Exist.class');
        expect(function() use ($parse) { $parse->asClass(); })
            ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function asClassWithNonExistingClassAndDefaultThrowsReflectionException(): void
    {
        $parse = new Parse('does\not\Exist.class');
        expect(function() use ($parse) {
            $parse->defaultingTo(__CLASS__ . '.class')->asClass();
        })
            ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsNullForNull(): void
    {
        assertNull(Parse::toClassname(null));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsNullForEmptyString(): void
    {
        assertNull(Parse::toClassname(''));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsNullForNonExistingClass(): void
    {
        assertNull(Parse::toClassname('does\not\Exist::class'));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsClassnameOfExistingClass(): void
    {
        assertThat(
                Parse::toClassname(__CLASS__ . '::class'),
                equals(__CLASS__)
        );
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsNullForNull(): void
    {
        $parse = new Parse(null);
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsNullForEmptyString(): void
    {
        $parse = new Parse('');
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsNullForNonExistingClass(): void
    {
        $parse = new Parse('does\not\Exist::class');
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsClassnameOfExistingClass(): void
    {
        $parse = new Parse(__CLASS__ . '::class');
        assertThat($parse->asClassname(), equals(__CLASS__));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function stringToTypeConversions(): array
    {
        return [
            [null, null],
            ['', ''],
            [null, 'null'],
            [1, '1'],
            [true, 'yes'],
            [true, 'true'],
            [true, 'on'],
            [0, '0'],
            [false, 'no'],
            [false, 'false'],
            [false, 'off'],
            [303, '303'],
            [-303, '-303'],
            [3.03, '3.03'],
            [-3.03, '-3.03'],
            [['foo' => 'bar', 'baz'], '[foo:bar|baz]'],
            [['foo', 'bar', 'baz'], '[foo|bar|baz]'],
            [[1, 2, 3, 4, 5], '1..5'],
            [['a', 'b', 'c', 'd', 'e'], 'a..e'],
            [new \ReflectionClass(__CLASS__), __CLASS__ . '.class'],
            [MyClass::TEST_CONSTANT, MyClass::class . '::TEST_CONSTANT'],
            ['just a string', 'just a string']
        ];
    }

    /**
     * @param  mixed   $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToTypeConversions
     */
    public function toTypeReturnsValueCastedToRecognizedType($expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toType($stringToParse), equals($expectedResult));
    }

    /**
     * @test
     */
    public function userDefinedRecognitionWithSuccessReturnsValueFromUserDefinedConversion(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 6100'), equals('More power!'));
    }

    /**
     * @test
     */
    public function userDefinedRecognitionWithoutSuccessReturnsValueAsString(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 610'), equals('Binford 610'));
    }

    /**
     * @test
     */
    public function canReplaceExistingRecognition(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return true; } }, 'booleanTrue');
        assertTrue(Parse::toType('Binford 6100'));
    }

    /**
     * @return  array<array<mixed>>
     */
    public function methods(): array
    {
        return [
            [null, 'asString'],
            [0, 'asInt'],
            [0, 'asFloat'],
            [false, 'asBool'],
            [null, 'asList'],
            [null, 'asMap'],
            [null, 'asRange'],
            [null, 'asClass'],
        ];
    }

    /**
     *
     * @param  mixed   $expected
     * @param  string  $method
     * @test
     * @dataProvider  methods
     * @since  5.0.0
     */
    public function parseNullReturnsNull($expected, string $method): void
    {
        $parse = new Parse(null);
        assertThat($parse->$method(), equals($expected));
    }

    /**
     *
     * @param  mixed   $expected
     * @param  string  $method
     * @test
     * @dataProvider  methods
     * @since  5.0.0
     */
    public function parseNullWithDefaultReturnsDefault($expected, string $method): void
    {
        $parse = new Parse(null);
        assertThat($parse->defaultingTo('foo')->$method(), equals('foo'));
    }
}
