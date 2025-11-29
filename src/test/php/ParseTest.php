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
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Tests for stubbles\values\Parse.
 *
 * @since  4.1.0
 */
#[Group('values')]
class ParseTest extends TestCase
{
    protected function tearDown(): void
    {
        Parse::removeRecognition('binford');
        Parse::__static();
    }

    public static function stringToIntConversions(): iterable
    {
        yield [0, '0'];
        yield [1, '1'];
        yield [-303, '-303'];
        yield [80, '80foo'];
        yield [3, '3.14'];
        yield [0, ''];
        yield [null, null];
    }

    #[Test]
    #[DataProvider('stringToIntConversions')]
    public function toIntReturnsValueCastedToInteger(
        ?int $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toInt($stringToParse), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToIntConversions')]
    public function asIntReturnsValueCastedToInteger(
        ?int $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asInt(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToIntConversions')]
    public function asIntWithDefaultReturnsValueCastedToInteger(
        ?int $expectedResult,
        ?string $stringToParse
    ): void {
        if (null === $stringToParse) {
            $expectedResult = 303;
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo(303)->asInt(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    public function toIntOnNullReturnsNull(): void
    {
        assertNull(Parse::toInt(null));
    }

    public static function stringToFloatConversions(): iterable
    {
        yield [0.1, '0.1'];
        yield [1, '1'];
        yield [-3.03, '-3.03'];
        yield [8.0, '8.0foo'];
        yield [3.14, '3.14'];
        yield [0, ''];
        yield [null, null];
    }

    #[Test]
    #[DataProvider('stringToFloatConversions')]
    public function toFloatReturnsValueCastedToFloat(
        ?float $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toFloat($stringToParse), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToFloatConversions')]
    public function asFloatReturnsValueCastedToFloat(
        ?float $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asFloat(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToFloatConversions')]
    public function asFloatWithDefaultReturnsValueCastedToFloat(
        ?float $expectedResult,
        ?string $stringToParse
    ): void {
        if (null === $stringToParse) {
            $expectedResult = 3.03;
        }

        $parse = new Parse($stringToParse);
        assertThat($parse->defaultingTo(3.03)->asFloat(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    public function toFloatOnNullReturnsNull(): void
    {
        assertNull(Parse::toFloat(null));
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function stringToBoolConversions(): iterable
    {
        yield [true, 'yes'];
        yield [true, 'true'];
        yield [true, 'on'];
        yield [false, '3.14'];
        yield [false, 'no'];
        yield [false, 'false'];
        yield [false, 'off'];
        yield [false, 'other'];
        yield [false, ''];
        yield [false, null];
    }

    #[Test]
    #[DataProvider('stringToBoolConversions')]
    public function toBoolReturnsValueCastedToBool(
        bool $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toBool($stringToParse), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToBoolConversions')]
    public function asBoolReturnsValueCastedToBool(
        bool $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asBool(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToBoolConversions')]
    public function asBoolWithDefaultReturnsValueCastedToBool(
        bool $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat(
            $parse->defaultingTo(false)->asBool(),
            equals($expectedResult)
        );
    }

    public static function stringToListConversions(): iterable
    {
        yield [['foo', 'bar', 'baz'], 'foo|bar|baz', Parse::SEPARATOR_LIST];
        yield [['foo|bar|baz'], 'foo|bar|baz', ','];
        yield [['foo', 'bar', 'baz'], 'foo,bar,baz', ','];
        yield [['foo', 'bar', 'baz'], '[foo|bar|baz]', Parse::SEPARATOR_LIST];
        yield [[], '', Parse::SEPARATOR_LIST];
        yield [[], '', ','];
        yield [[], '[]', Parse::SEPARATOR_LIST];
        yield [[], '[]', ','];
        yield [null, null, Parse::SEPARATOR_LIST];
        yield [['', ''], '|', Parse::SEPARATOR_LIST];
        yield [['', ''], ',', ','];
        yield [['', ''], '[|]', Parse::SEPARATOR_LIST];
        yield [['', ''], '[,]', ','];
        yield [['foo'], 'foo', Parse::SEPARATOR_LIST];
        yield [['foo'], 'foo', ','];
        yield [['foo'], '[foo]', Parse::SEPARATOR_LIST];
        yield [['foo'], '[foo]', ','];
    }

    #[Test]
    #[DataProvider('stringToListConversions')]
    public function toListReturnsValueCastedToList(
        ?array $expectedResult,
        ?string $stringToParse,
        string $separator
    ): void {
        assertThat(Parse::toList($stringToParse, $separator), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToListConversions')]
    public function asListReturnsValueCastedToList(
        ?array $expectedResult,
        ?string $stringToParse,
        string $separator
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asList($separator), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToListConversions')]
    public function asListWithDefaultReturnsValueCastedToList(
        ?array $expectedResult,
        ?string $stringToParse,
        string $separator
    ): void {
        if (null === $stringToParse) {
            $expectedResult = ['default'];
        }

        $parse = new Parse($stringToParse);
        assertThat(
            $parse->defaultingTo(['default'])->asList($separator),
            equals($expectedResult)
        );
    }

    public static function stringToMapConversions(): iterable
    {
        yield [['foo', 'bar', 'baz'], 'foo|bar|baz'];
        yield [['foo', 'bar', 'baz'], '[foo|bar|baz]'];
        yield [['foo' => 'bar', 'baz' => 'dummy'], 'foo:bar|baz:dummy'];
        yield [['foo' => 'bar', 'baz' => 'dummy'], '[foo:bar|baz:dummy]'];
        yield [['foo' => 'bar', 'baz'], 'foo:bar|baz'];
        yield [['foo' => 'bar', 'baz'], '[foo:bar|baz]'];
        yield [[], ''];
        yield [[], '[]'];
        yield [null, null];
        yield [['', ''], '|'];
        yield [['', ''], '[|]'];
        yield [['foo'], 'foo'];
        yield [['foo'], '[foo]'];
        yield [['foo' => 'baz'], 'foo:baz'];
        yield [['foo' => 'baz'], '[foo:baz]'];
    }

    #[Test]
    #[DataProvider('stringToMapConversions')]
    public function toMapReturnsValueCastedToMap(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toMap($stringToParse), equals($expectedResult));
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('stringToMapConversions')]
    public function asMapReturnsValueCastedToMap(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asMap(), equals($expectedResult));
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('stringToMapConversions')]
    public function asMapWithDefaultReturnsValueCastedToMap(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        if (null === $stringToParse) {
            $expectedResult = ['foo' => 'default'];
        }

        $parse = new Parse($stringToParse);
        assertThat(
            $parse->defaultingTo(['foo' => 'default'])->asMap(),
            equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function stringToRangeConversions(): iterable
    {
        yield [[1, 2, 3, 4, 5], '1..5'];
        yield [['a', 'b', 'c', 'd', 'e'], 'a..e'];
        yield [[], '1..'];
        yield [[], 'a..'];
        yield [[], '..5'];
        yield [[], '..e'];
        yield [[5, 4, 3, 2, 1], '5..1'];
        yield [['e', 'd', 'c', 'b', 'a'], 'e..a'];
        yield [[], ''];
        yield [null, null];
        yield [[], 'other'];
    }

    #[Test]
    #[DataProvider('stringToRangeConversions')]
    public function toRangeReturnsValueCastedToRange(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toRange($stringToParse), equals($expectedResult));
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('stringToRangeConversions')]
    public function asRangeReturnsValueCastedToRange(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        $parse = new Parse($stringToParse);
        assertThat($parse->asRange(), equals($expectedResult));
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('stringToRangeConversions')]
    public function asRangeWithDefaultReturnsValueCastedToRange(
        ?array $expectedResult,
        ?string $stringToParse
    ): void {
        if (null === $stringToParse) {
            $expectedResult = ['a', 'b', 'c'];
        }

        $parse = new Parse($stringToParse);
        assertThat(
            $parse->defaultingTo(['a', 'b', 'c'])->asRange(),
            equals($expectedResult)
        );
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function stringToClassConversions(): iterable
    {
        yield [new ReflectionClass(__CLASS__), __CLASS__ . '.class'];
        yield [new ReflectionClass(SomeInterface::class), SomeInterface::class . '.class'];
        yield [null, null];
        yield [null, ''];
        yield [null, 'other'];
    }

    #[Test]
    #[DataProvider('stringToClassConversions')]
    public function toClassReturnsValueCastedToClassInstance(
        ?ReflectionClass $expectedResult,
        ?string $stringToParse
    ): void {
        assertThat(Parse::toClass($stringToParse), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToClassConversions')]
    public function asClassReturnsValueCastedToClassInstance(
        ?ReflectionClass $expectedResult,
        ?string $stringToParse
    ): void {        
        $parse = new Parse($stringToParse);
        assertThat($parse->asClass(), equals($expectedResult));
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('stringToClassConversions')]
    public function asClassWithDefaultReturnsValueCastedToClassInstance(
        ?ReflectionClass $expectedResult,
        ?string $stringToParse
    ): void {
        if (null === $stringToParse) {
            $expectedResult = new ReflectionClass($this);
        }

        $parse = new Parse($stringToParse);
        assertThat(
            $parse->defaultingTo(new ReflectionClass($this))->asClass(),
            equals($expectedResult)
        );
    }

    #[Test]
    public function toClassWithNonExistingClassThrowsReflectionException(): void
    {
        expect(function() {
            Parse::toClass('does\not\Exist.class');
        })
            ->throws(\ReflectionException::class);
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function asClassWithNonExistingClassThrowsReflectionException(): void
    {
        $parse = new Parse('does\not\Exist.class');
        expect(function() use ($parse) { $parse->asClass(); })
            ->throws(\ReflectionException::class);
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function asClassWithNonExistingClassAndDefaultThrowsReflectionException(): void
    {
        $parse = new Parse('does\not\Exist.class');
        expect(function() use ($parse) {
            $parse->defaultingTo(__CLASS__ . '.class')->asClass();
        })
            ->throws(\ReflectionException::class);
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function toClassnameReturnsNullForNull(): void
    {
        assertNull(Parse::toClassname(null));
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function toClassnameReturnsNullForEmptyString(): void
    {
        assertNull(Parse::toClassname(''));
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function toClassnameReturnsNullForNonExistingClass(): void
    {
        assertNull(Parse::toClassname('does\not\Exist::class'));
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function toClassnameReturnsClassnameOfExistingClass(): void
    {
        assertThat(
            Parse::toClassname(__CLASS__ . '::class'),
            equals(__CLASS__)
        );
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function asClassnameReturnsNullForNull(): void
    {
        $parse = new Parse(null);
        assertNull($parse->asClassname());
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function asClassnameReturnsNullForEmptyString(): void
    {
        $parse = new Parse('');
        assertNull($parse->asClassname());
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function asClassnameReturnsNullForNonExistingClass(): void
    {
        $parse = new Parse('does\not\Exist::class');
        assertNull($parse->asClassname());
    }

    /**
     * @since  5.3.0
     */
    #[Test]
    public function asClassnameReturnsClassnameOfExistingClass(): void
    {
        $parse = new Parse(__CLASS__ . '::class');
        assertThat($parse->asClassname(), equals(__CLASS__));
    }

    public static function stringToTypeConversions(): iterable
    {
        yield [null, null];
        yield ['', ''];
        yield [null, 'null'];
        yield [1, '1'];
        yield [true, 'yes'];
        yield [true, 'true'];
        yield [true, 'on'];
        yield [0, '0'];
        yield [false, 'no'];
        yield [false, 'false'];
        yield [false, 'off'];
        yield [303, '303'];
        yield [-303, '-303'];
        yield [3.03, '3.03'];
        yield [-3.03, '-3.03'];
        yield [['foo' => 'bar', 'baz'], '[foo:bar|baz]'];
        yield [['foo', 'bar', 'baz'], '[foo|bar|baz]'];
        yield [[1, 2, 3, 4, 5], '1..5'];
        yield [['a', 'b', 'c', 'd', 'e'], 'a..e'];
        yield [new ReflectionClass(__CLASS__), __CLASS__ . '.class'];
        yield [MyClass::TEST_CONSTANT, MyClass::class . '::TEST_CONSTANT'];
        yield ['just a string', 'just a string'];
    }

    #[Test]
    #[DataProvider('stringToTypeConversions')]
    public function toTypeReturnsValueCastedToRecognizedType(mixed $expectedResult, ?string $stringToParse): void
    {
        assertThat(Parse::toType($stringToParse), equals($expectedResult));
    }

    #[Test]
    public function userDefinedRecognitionWithSuccessReturnsValueFromUserDefinedConversion(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 6100'), equals('More power!'));
    }

    #[Test]
    public function userDefinedRecognitionWithoutSuccessReturnsValueAsString(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return 'More power!'; } }, 'binford');
        assertThat(Parse::toType('Binford 610'), equals('Binford 610'));
    }

    #[Test]
    public function canReplaceExistingRecognition(): void
    {
        Parse::addRecognition(function($string) { if ('Binford 6100' === $string) { return true; } }, 'booleanTrue');
        assertTrue(Parse::toType('Binford 6100'));
    }

    public static function methods(): iterable
    {
        yield [null, 'asString'];
        yield [0, 'asInt'];
        yield [0, 'asFloat'];
        yield [false, 'asBool'];
        yield [null, 'asList'];
        yield [null, 'asMap'];
        yield [null, 'asRange'];
        yield [null, 'asClass'];
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    #[DataProvider('methods')]
    public function parseNullReturnsNull(mixed $expected, string $method): void
    {
        $parse = new Parse(null);
        assertThat($parse->$method(), equals($expected));
    }

    public static function typeEnforcingMethods(): iterable
    {
        yield ['asInt'];
        yield ['asFloat'];
        yield ['asList'];
        yield ['asMap'];
        yield ['asRange'];
        yield ['asClass'];
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('typeEnforcingMethods')]
    public function parseNullWithDefaultThrowsTypeErrorWhenTypeDoesNotMatch(string $method): void
    {
        $parse = new Parse(null);
        expect( fn() => $parse->defaultingTo('foo')->$method())
            ->throws(TypeError::class);
    }
}
