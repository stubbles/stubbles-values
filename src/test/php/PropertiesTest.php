<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use InvalidArgumentException;
use LogicException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

use function bovigo\assert\{
    assertThat,
    assertEmptyArray,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\contains,
    predicate\equals,
    predicate\isInstanceOf,
    predicate\isNotSameAs
};
/**
 * Tests for stubbles\values\Properties.
 */
#[Group('values')]
#[Group('properties')]
class PropertiesTest extends TestCase
{
    protected Properties $properties;

    protected function setUp(): void
    {
        $this->properties = new Properties([
            'scalar' => [
                'stringValue' => 'This is a string',
                'intValue1'   => '303',
                'intValue2'   => 303,
                'floatValue1' => '3.13',
                'floatValue2' => 3.13,
                'boolValue1'  => '1',
                'boolValue2'  => 1,
                'boolValue3'  => 'yes',
                'boolValue4'  => 'true',
                'boolValue5'  => 'on',
                'boolValue6'  => '0',
                'boolValue7'  => 0,
                'boolValue8'  => 'no',
                'boolValue9'  => 'false',
                'boolValue10' => 'off',
                'boolValue11' => 'other'
            ],
            'array' => [
                'arrayValue1' => '[foo|bar|baz]',
                'arrayValue2' => '[]',
                'hashValue1'  => '[foo:bar|baz]',
                'hashValue2'  => '[]'
            ],
            'range' => [
                'rangeValue1' => '1..5',
                'rangeValue2' => 'a..e',
                'rangeValue3' => '1..',
                'rangeValue4' => 'a..',
                'rangeValue5' => '..5',
                'rangeValue6' => '..e',
                'rangeValue7' => '5..1',
                'rangeValue8' => 'e..a'
            ],
            'empty' => []
                
        ]);
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function sections(): array
    {
        return [
            ['scalar', [
                    'stringValue' => 'This is a string',
                    'intValue1'   => '303',
                    'intValue2'   => 303,
                    'floatValue1' => '3.13',
                    'floatValue2' => 3.13,
                    'boolValue1'  => '1',
                    'boolValue2'  => 1,
                    'boolValue3'  => 'yes',
                    'boolValue4'  => 'true',
                    'boolValue5'  => 'on',
                    'boolValue6'  => '0',
                    'boolValue7'  => 0,
                    'boolValue8'  => 'no',
                    'boolValue9'  => 'false',
                    'boolValue10' => 'off',
                    'boolValue11' => 'other'
            ]],
            ['array', [
                    'arrayValue1' => '[foo|bar|baz]',
                    'arrayValue2' => '[]',
                    'hashValue1'  => '[foo:bar|baz]',
                    'hashValue2'  => '[]'
            ]],
            ['range', [
                    'rangeValue1' => '1..5',
                    'rangeValue2' => 'a..e',
                    'rangeValue3' => '1..',
                    'rangeValue4' => 'a..',
                    'rangeValue5' => '..5',
                    'rangeValue6' => '..e',
                    'rangeValue7' => '5..1',
                    'rangeValue8' => 'e..a'
            ]],
            ['empty', []]
        ];
    }

    #[Test]
    #[DataProvider('sections')]
    public function containSectionReturnsTrueForExistingSections(string $name): void
    {
        assertTrue($this->properties->containSection($name));
    }

    #[Test]
    public function containSectionReturnsFalseForNonExistingSections(): void
    {
        assertFalse($this->properties->containSection('doesNotExist'));
    }

    #[Test]
    #[DataProvider('sections')]
    public function sectionWithoutDefaultValueReturnsSectionValues(
        string $name,
        mixed $value
    ): void {
        assertThat(
            $this->properties->section($name),
            equals($value)
        );
    }

    #[Test]
    public function sectionWithoutDefaultValueReturnsEmptyArrayIfSectionDoesNotExist(): void
    {
        assertEmptyArray($this->properties->section('doesNotExist'));
    }

    #[Test]
    #[DataProvider('sections')]
    public function sectionWithDefaultValueReturnsSectionValues(string $name, mixed $value): void
    {
        assertThat(
            $this->properties->section($name, ['foo' => 'bar']),
            equals($value)
        );
    }

    #[Test]
    public function sectionWithDefaultValueReturnsDefaultValueIfSectionDoesNotExist(): void
    {
        assertThat(
            $this->properties->section('doesNotExist', ['foo' => 'bar']),
            equals(['foo' => 'bar'])
        );
    }

    #[Test]
    #[DataProvider('sections')]
    public function keysForSectionReturnsListOfKeysForGivenSection(
        string $name,
        mixed $value
    ): void {
        assertThat(
            $this->properties->keysForSection($name, ['foo', 'bar']),
            equals(array_keys($value))
        );
    }

    #[Test]
    public function keysForSectionReturnsDefaultListOfSectionDoesNotExist(): void
    {
        assertThat(
            $this->properties->keysForSection('doesNotExist', ['foo', 'bar']),
            equals(['foo', 'bar'])
        );
    }

    /**
     * @return array<mixed>
     */
    public static function existingSectionKeys(): array
    {
        $data = [];
        foreach (self::sections() as $section) {
            foreach (array_keys($section[1]) as $key) {
                $data[] = [$section[0], $key];
            }
        }

        return $data;
    }

    #[Test]
    #[DataProvider('existingSectionKeys')]
    public function containValueReturnsTrueIfValueExist(string $section, string $key): void
    {
        assertTrue($this->properties->containValue($section, $key));
    }

    #[Test]
    public function containValueReturnsFalseIfValueDoesNotExist(): void
    {
        assertFalse($this->properties->containValue('empty', 'any'));
    }

    #[Test]
    public function containValueReturnsFalseIfSectionDoesNotExist(): void
    {
        assertFalse($this->properties->containValue('doesNotExist', 'any'));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function existingSectionValues(): array
    {
        $data = [];
        foreach (self::sections() as $section) {
            foreach ($section[1] as $key => $value) {
                $data[] = [$section[0], $key, $value];
            }
        }

        return $data;
    }

    #[Test]
    #[DataProvider('existingSectionValues')]
    public function valueWithoutDefaultValueReturnsValueIfExists(
        string $section,
        string $key,
        mixed $expectedValue
    ): void {
        assertThat($this->properties->value($section, $key), equals($expectedValue));
    }

    #[Test]
    public function valueWithoutDefaultValueReturnsNullIfValueDoesNotExist(): void
    {
        assertNull($this->properties->value('empty', 'any'));
    }

    #[Test]
    public function valueWithoutDefaultValueReturnsNullIfSectionDoesNotExist(): void
    {
        assertNull($this->properties->value('doesNotExist', 'any'));
    }

    #[Test]
    #[DataProvider('existingSectionValues')]
    public function valueWithDefaultValueReturnsValueIfExists(
        string $section,
        string $key,
        mixed $expectedValue
    ): void {
        assertThat(
                $this->properties->value($section, $key, 'otherValue'),
                equals($expectedValue)
        );
    }

    #[Test]
    public function valueWithDefaultValueReturnsDefaultValueIfValueDoesNotExist(): void
    {
        assertThat(
            $this->properties->value('empty', 'any', 'otherValue'),
            equals('otherValue')
        );
    }

    #[Test]
    public function valueWithDefaultValueReturnsDefaultValueIfSectionDoesNotExist(): void
    {
        assertThat(
                $this->properties->value('doesNotExist', 'any', 'otherValue'),
                equals('otherValue')
        );
    }

    #[Test]
    #[Group('bug249')]
    public function iteratingOverInstanceIteratesOverSections(): void
    {
        foreach ($this->properties as $section => $sectionData) {
            assertTrue($this->properties->containSection($section));
            assertThat($this->properties->section($section), equals($sectionData));
        }
    }

    /**
     * @since 1.3.2
     */
    #[Test]
    #[Group('bug249')]
    public function iteratingAfterIterationShouldRestartIteration(): void
    {
        $firstIterationEntries = 0;
        foreach ($this->properties as $section => $sectionData) {
            assertThat($this->properties->section($section), equals($sectionData));
            $firstIterationEntries++;
        }

        $secondIterationEntries = 0;
        foreach ($this->properties as $section => $sectionData) {
            assertThat($this->properties->section($section), equals($sectionData));
            $secondIterationEntries++;
        }

        assertThat($secondIterationEntries, equals($firstIterationEntries));
    }

    #[Test]
    public function fromNonExistantFileThrowsInvalidArgumentException(): void
    {
        expect(fn() => Properties::fromFile(__DIR__ . '/doesNotExist.ini'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function invalidIniFileThrowsIOException(): void
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('invalid.ini')
            ->at($root)
            ->withContent("[invalid{");
        expect(fn() => Properties::fromFile(vfsStream::url('config/invalid.ini')))
            ->throws(UnexpectedValueException::class);
    }

    #[Test]
    public function validIniFileReturnsInstance(): void
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('test.ini')
            ->at($root)
            ->withContent("[foo]\nbar=baz");
        $properties = Properties::fromFile(vfsStream::url('config/test.ini'));
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @since 2.0.0
     */
    #[Test]
    #[Group('bug213')]
    #[WithoutErrorHandler]
    public function invalidIniStringThrowsException(): void
    {
        expect(fn() => Properties::fromString("[invalid{"))
            ->throws(InvalidArgumentException::class)
            ->message(contains(
                'Property string contains errors and can not be parsed: syntax error, unexpected end'
            ));
    }

    /**
     * @since 2.0.0
     */
    #[Test]
    #[Group('bug213')]
    public function validIniStringReturnsInstance(): void
    {
        $properties = Properties::fromString("[foo]\nbar=baz");
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @since 1.3.0
     */
    #[Test]
    public function mergeMergesTwoPropertiesInstancesAndReturnsNewInstance(): void
    {
        $properties1 = new Properties(['foo' => ['bar' => 'baz']]);
        $properties2 = new Properties(['bar' => ['bar' => 'baz']]);
        $resultProperties = $properties1->merge($properties2);
        assertThat(
            $resultProperties,
            isNotSameAs($properties1)->and(isNotSameAs($properties2))
        );
    }

    /**
     * @since 1.3.0
     */
    #[Test]
    public function mergeMergesProperties(): void
    {
        $properties1 = new Properties(['foo' => ['bar' => 'baz']]);
        $properties2 = new Properties(['bar' => ['bar' => 'baz']]);
        $resultProperties = $properties1->merge($properties2);
        assertThat($resultProperties->section('foo'), equals(['bar' => 'baz']));
        assertThat($resultProperties->section('bar'), equals(['bar' => 'baz']));
    }

    /**
     * @since 1.3.0
     */
    #[Test]
    public function mergeOverwritesSectionsOfMergingInstanceWithThoseFromMergedInstance(): void
    {
        $properties1 = new Properties([
            'foo' => ['bar' => 'baz'],
            'bar' => ['baz' => 'foo']
        ]);
        $properties2 = new Properties(['bar' => ['bar' => 'baz']]);
        $resultProperties = $properties1->merge($properties2);
        assertThat($resultProperties->section('foo'), equals(['bar' => 'baz']));
        assertThat($resultProperties->section('bar'), equals(['bar' => 'baz']));
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    #[Group('secure_string')]
    public function propertiesWithKeyPasswordBecomeInstancesOfSecret(): void
    {
        assertThat(
            (new Properties(['foo' => ['password' => 'baz']]))->value('foo', 'password'),
            isInstanceOf(Secret::class)
        );
    }

    /**
     * @since 4.1.2
     */
    #[Test]
    #[Group('secure_string')]
    public function propertiesWhereKeyEndsWithPasswordBecomeInstancesOfSecret(): void
    {
        assertThat(
            (new Properties(['foo' => ['example.another.password' => 'baz']]))
                    ->value('foo', 'example.another.password'),
            isInstanceOf(Secret::class)
        );
    }

    /**
     * @since 4.1.0
     */
    #[Test]
    #[Group('secure_string')]
    public function parseSecretValueReturnsSecretInstance(): void
    {
        assertThat(
            (new Properties(['foo' => ['password' => 'baz']]))
                    ->parseValue('foo', 'password'),
            isInstanceOf(Secret::class)
        );
    }

    /**
     * @test
     * @since 5.0.0
     */
    #[Test]
    #[Group('secret')]
    public function parseSecretThrowsIllegalAccessException(): void
    {
        expect(fn() => (new Properties(['foo' => ['password' => 'baz']]))
                        ->parse('foo', 'password')
        )
            ->throws(LogicException::class)
            ->withMessage('Can not parse fields with passwords');
    }

    /**
     * @return array<array<mixed>>
     */
    public static function parseValueList(): array
    {
        return [
            ['This is a string', 'scalar', 'stringValue'],
            [303, 'scalar', 'intValue1'],
            [303, 'scalar', 'intValue2'],
            [3.13, 'scalar', 'floatValue1'],
            [3.13, 'scalar', 'floatValue2'],
            [1, 'scalar', 'boolValue1'],
            [1, 'scalar', 'boolValue2'],
            [true, 'scalar', 'boolValue3'],
            [true, 'scalar', 'boolValue4'],
            [true, 'scalar', 'boolValue5'],
            [0, 'scalar', 'boolValue6'],
            [0, 'scalar', 'boolValue7'],
            [false, 'scalar', 'boolValue8'],
            [false, 'scalar', 'boolValue9'],
            [false, 'scalar', 'boolValue10'],
            [['foo', 'bar', 'baz'], 'array', 'arrayValue1'],
            [[], 'array', 'arrayValue2'],
            [['foo' => 'bar', 'baz'], 'array', 'hashValue1'],
            [[], 'array', 'hashValue2'],
            [[1, 2, 3, 4, 5], 'range', 'rangeValue1'],
            [['a', 'b', 'c', 'd', 'e'], 'range', 'rangeValue2'],
            [[], 'range', 'rangeValue3'],
            [[], 'range', 'rangeValue4'],
            [[], 'range', 'rangeValue5'],
            [[], 'range', 'rangeValue6'],
            [[5, 4, 3, 2, 1], 'range', 'rangeValue7'],
            [['e', 'd', 'c', 'b', 'a'], 'range', 'rangeValue8']
        ];
    }

    /**
     * @since 4.1.0
     */
    #[Test]
    #[DataProvider('parseValueList')]
    public function parseValueReturnsValueCastedToRecognizedType(
        mixed $expected,
        string $section,
        string $key
    ): void {
        assertTrue($expected === $this->properties->parseValue($section, $key));
    }

    /**
     * @since 4.1.0
     */
    #[Test]
    public function parseValueWithNonExistingKeyReturnsDefault(): void
    {
        assertThat(
            $this->properties->parseValue('empty', 'doesNotExist', 6100),
            equals(6100)
        );
    }

    /**
     * @since 4.1.0
     */
    #[Test]
    public function parseValueFromNonExistingSectionReturnsDefault(): void
    {
        assertThat(
            $this->properties->parseValue('doesNotExist', 'rangeValue8', 6100),
            equals(6100)
        );
    }

    /**
     * @return array<array<mixed>>
     * @since  5.0.0
     */
    public static function parseList(): array
    {
        return [
            ['This is a string', 'scalar', 'stringValue', 'asString'],
            [303, 'scalar', 'intValue1', 'asInt'],
            [303, 'scalar', 'intValue2', 'asInt'],
            [3.13, 'scalar', 'floatValue1', 'asFloat'],
            [3.13, 'scalar', 'floatValue2', 'asFloat'],
            [false, 'scalar', 'boolValue1', 'asBool'],
            [false, 'scalar', 'boolValue2', 'asBool'],
            [true, 'scalar', 'boolValue3', 'asBool'],
            [true, 'scalar', 'boolValue4', 'asBool'],
            [true, 'scalar', 'boolValue5', 'asBool'],
            [false, 'scalar', 'boolValue6', 'asBool'],
            [false, 'scalar', 'boolValue7', 'asBool'],
            [false, 'scalar', 'boolValue8', 'asBool'],
            [false, 'scalar', 'boolValue9', 'asBool'],
            [false, 'scalar', 'boolValue10', 'asBool'],
            [['foo', 'bar', 'baz'], 'array', 'arrayValue1', 'asList'],
            [[], 'array', 'arrayValue2', 'asList'],
            [['foo' => 'bar', 'baz'], 'array', 'hashValue1', 'asMap'],
            [[], 'array', 'hashValue2', 'asMap'],
            [[1, 2, 3, 4, 5], 'range', 'rangeValue1', 'asRange'],
            [['a', 'b', 'c', 'd', 'e'], 'range', 'rangeValue2', 'asRange'],
            [[], 'range', 'rangeValue3', 'asRange'],
            [[], 'range', 'rangeValue4', 'asRange'],
            [[], 'range', 'rangeValue5', 'asRange'],
            [[], 'range', 'rangeValue6', 'asRange'],
            [[5, 4, 3, 2, 1], 'range', 'rangeValue7', 'asRange'],
            [['e', 'd', 'c', 'b', 'a'], 'range', 'rangeValue8', 'asRange']
        ];
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    #[DataProvider('parseList')]
    public function parseReturnsValueCastedToRecognizedType(
        mixed $expected,
        string $section,
        string $key,
        string $type
    ): void {
        assertThat(
            $this->properties->parse($section, $key)->$type(),
            equals($expected)
        );
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    public function parseNonExistingReturnsNullInstance(): void
    {
        assertThat(
            $this->properties->parse('empty', 'doesNotExist'),
            equals(new Parse(null))
        );
    }

    /**
     * @since 5.0.0
     */
    #[Test]
    public function parseFromNonExistingSectionReturnsDefault(): void
    {
        assertThat(
            $this->properties->parse('doesNotExist', 'rangeValue8'),
            equals(new Parse(null))
        );
    }
}
