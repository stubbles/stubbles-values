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
 * Helper class for the test.
 */
class MyClass
{
    const TEST_CONSTANT = 'baz';
}
/**
 * Helper interface for the test.
 */
interface SomeInterface
{
    // intentionally empty
}
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
     * @return  array
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
    public function toIntReturnsValueCastedToInteger($expectedResult, $stringToParse)
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
    public function asIntReturnsValueCastedToInteger($expectedResult, $stringToParse)
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
    public function asIntWithDefaultReturnsValueCastedToInteger($expectedResult, $stringToParse)
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
    public function toIntOnNullReturnsNull()
    {
        assertNull(Parse::toInt(null));
    }

    /**
     * @return  array
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
    public function toFloatReturnsValueCastedToFloat($expectedResult, $stringToParse)
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
    public function asFloatReturnsValueCastedToFloat($expectedResult, $stringToParse)
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
    public function asFloatWithDefaultReturnsValueCastedToFloat($expectedResult, $stringToParse)
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
    public function toFloatOnNullReturnsNull()
    {
        assertNull(Parse::toFloat(null));
    }

    /**
     * @return  array
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
            [null, null],

        ];
    }

    /**
     * @param  bool    $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToBoolConversions
     */
    public function toBoolReturnsValueCastedToBool($expectedResult, $stringToParse)
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
    public function asBoolReturnsValueCastedToBool($expectedResult, $stringToParse)
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asBool($stringToParse), equals($expectedResult));
    }

    /**
     * @param  bool    $expectedResult
     * @param  string  $stringToParse
     * @test
     * @dataProvider  stringToBoolConversions
     * @since  5.0.0
     */
    public function asBoolWithDefaultReturnsValueCastedToBool($expectedResult, $stringToParse)
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asBool($stringToParse),
                equals($expectedResult)
        );
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function toBoolOnNullReturnsNull()
    {
        assertNull(Parse::toBool(null));
    }

    /**
     * @return  array
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
     * @test
     * @dataProvider  stringToListConversions
     */
    public function toListReturnsValueCastedToList(
            $expectedResult,
            $stringToParse,
            string $separator
    ) {
        assertThat(Parse::toList($stringToParse, $separator), equals($expectedResult));
    }

    /**
     * @test
     * @dataProvider  stringToListConversions
     * @since  5.0.0
     */
    public function asListReturnsValueCastedToList(
            $expectedResult,
            $stringToParse,
            string $separator
    ) {
        $parse = new Parse($stringToParse);
        assertThat($parse->asList($separator), equals($expectedResult));
    }

    /**
     * @test
     * @dataProvider  stringToListConversions
     * @since  5.0.0
     */
    public function asListWithDefaultReturnsValueCastedToList(
            $expectedResult,
            $stringToParse,
            string $separator
    ) {
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
     * @return  array
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
     * @param  array   $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     */
    public function toMapReturnsValueCastedToMap($expectedResult, $stringToParse)
    {
        assertThat(Parse::toMap($stringToParse), equals($expectedResult));
    }

    /**
     * @param  array   $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     * @since  5.0.0
     */
    public function asMapReturnsValueCastedToMap($expectedResult, $stringToParse)
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asMap($stringToParse), equals($expectedResult));
    }

    /**
     * @param  array   $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToMapConversions
     * @since  5.0.0
     */
    public function asMapWithDefaultReturnsValueCastedToMap($expectedResult, $stringToParse)
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asMap($stringToParse),
                equals($expectedResult)
        );
    }

    /**
     * @return  array
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
    public function toRangeReturnsValueCastedToRange($expectedResult, $stringToParse)
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
    public function asRangeReturnsValueCastedToRange($expectedResult, $stringToParse)
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asRange($stringToParse), equals($expectedResult));
    }

    /**
     * @param  mixed[]  $expectedResult
     * @param  string   $stringToParse
     * @test
     * @dataProvider  stringToRangeConversions
     * @since  5.0.0
     */
    public function asRangeWithDefaultReturnsValueCastedToRange($expectedResult, $stringToParse)
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat(
                $parse->defaultingTo('foo')->asRange($stringToParse),
                equals($expectedResult)
        );
    }

    /**
     * @return  array
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
     * @param  \ReflectionClass  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     */
    public function toClassReturnsValueCastedToClassInstance($expectedResult, $stringToParse)
    {
        assertThat(Parse::toClass($stringToParse), equals($expectedResult));
    }

    /**
     * @param  \ReflectionClass  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     * @since  5.0.0
     */
    public function asClassReturnsValueCastedToClassInstance($expectedResult, $stringToParse)
    {
        $parse = new Parse($stringToParse);
        assertThat($parse->asClass($stringToParse), equals($expectedResult));
    }

    /**
     * @param  \ReflectionClass  $expectedResult
     * @param  string            $stringToParse
     * @test
     * @dataProvider  stringToClassConversions
     * @since  5.0.0
     */
    public function asClassWithDefaultReturnsValueCastedToClassInstance($expectedResult, $stringToParse)
    {
        if (null === $stringToParse) {
            $expectedResult = 'foo';
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo('foo')->asClass($stringToParse), equals($expectedResult));
    }

    /**
     * @test
     */
    public function toClassWithNonExistingClassThrowsReflectionException()
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
    public function asClassWithNonExistingClassThrowsReflectionException()
    {
        $parse = new Parse('does\not\Exist.class');
        expect(function() use ($parse) {
                $parse->asClass();
        })
        ->throws(\ReflectionException::class);
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function asClassWithNonExistingClassAndDefaultThrowsReflectionException()
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
    public function toClassnameReturnsNullForNull()
    {
        assertNull(Parse::toClassname(null));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsNullForEmptyString()
    {
        assertNull(Parse::toClassname(''));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsNullForNonExistingClass()
    {
        assertNull(Parse::toClassname('does\not\Exist::class'));
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function toClassnameReturnsClassnameOfExistingClass()
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
    public function asClassnameReturnsNullForNull()
    {
        $parse = new Parse(null);
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsNullForEmptyString()
    {
        $parse = new Parse('');
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsNullForNonExistingClass()
    {
        $parse = new Parse('does\not\Exist::class');
        assertNull($parse->asClassname());
    }

    /**
     * @test
     * @since  5.3.0
     */
    public function asClassnameReturnsClassnameOfExistingClass()
    {
        $parse = new Parse(__CLASS__ . '::class');
        assertThat($parse->asClassname(), equals(__CLASS__));
    }

    /**
     * @return  array
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
     * @param type $expectedResult
     * @param type $stringToParse
     * @test
     * @dataProvider  stringToTypeConversions
     */
    public function toTypeReturnsValueCastedToRecognizedType($expectedResult, $stringToParse)
    {
        assertThat(Parse::toType($stringToParse), equals($expectedResult));
    }

    /**
     * @test
     */
    public function userDefinedRecognitionWithSuccessReturnsValueFromUserDefinedConversion()
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 6100'), equals('More power!'));
    }

    /**
     * @test
     */
    public function userDefinedRecognitionWithoutSuccessReturnsValueAsString()
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 610'), equals('Binford 610'));
    }

    /**
     * @test
     */
    public function canReplaceExistingRecognition()
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return true; } }, 'booleanTrue');
        assertTrue(Parse::toType('Binford 6100'));
    }

    /**
     * @return  array
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
    public function parseNullReturnsNull($expected, string $method)
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
    public function parseNullWithDefaultReturnsDefault($expected, string $method)
    {
        $parse = new Parse(null);
        assertThat($parse->defaultingTo('foo')->$method(), equals('foo'));
    }
}
