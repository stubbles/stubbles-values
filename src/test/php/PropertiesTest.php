<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

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
 *
 * @group  values
 * @group  properties
 */
class PropertiesTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\values\Properties
     */
    protected $properties;

    protected function setUp(): void
    {
        $this->properties = new Properties(
                ['scalar' => ['stringValue' => 'This is a string',
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
                 'array'  => ['arrayValue1' => '[foo|bar|baz]',
                              'arrayValue2' => '[]',
                              'hashValue1'  => '[foo:bar|baz]',
                              'hashValue2'  => '[]'
                             ],
                 'range'  => ['rangeValue1' => '1..5',
                              'rangeValue2' => 'a..e',
                              'rangeValue3' => '1..',
                              'rangeValue4' => 'a..',
                              'rangeValue5' => '..5',
                              'rangeValue6' => '..e',
                              'rangeValue7' => '5..1',
                              'rangeValue8' => 'e..a'
                             ],
                 'empty'  => []
                ]
       );
    }

    /**
     * @return  array
     */
    public function sections(): array
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

    /**
     * @test
     * @dataProvider  sections
     */
    public function containSectionReturnsTrueForExistingSections(string $name)
    {
        assertTrue($this->properties->containSection($name));
    }

    /**
     * @test
     */
    public function containSectionReturnsFalseForNonExistingSections()
    {
        assertFalse($this->properties->containSection('doesNotExist'));
    }

    /**
     * @test
     * @dataProvider  sections
     */
    public function sectionWithoutDefaultValueReturnsSectionValues(string $name, $value)
    {
        assertThat(
                $this->properties->section($name),
                equals($value)
        );
    }

    /**
     * @test
     */
    public function sectionWithoutDefaultValueReturnsEmptyArrayIfSectionDoesNotExist()
    {
        assertEmptyArray($this->properties->section('doesNotExist'));
    }

    /**
     * @test
     * @dataProvider  sections
     */
    public function sectionWithDefaultValueReturnsSectionValues(string $name, $value)
    {
        assertThat(
                $this->properties->section($name, ['foo' => 'bar']),
                equals($value)
        );
    }

    /**
     * @test
     */
    public function sectionWithDefaultValueReturnsDefaultValueIfSectionDoesNotExist()
    {
        assertThat(
                $this->properties->section('doesNotExist', ['foo' => 'bar']),
                equals(['foo' => 'bar'])
        );
    }

    /**
     * @test
     * @dataProvider  sections
     */
    public function keysForSectionReturnsListOfKeysForGivenSection(string $name, $value)
    {
        assertThat(
                $this->properties->keysForSection($name, ['foo', 'bar']),
                equals(array_keys($value))
        );
    }

    /**
     * @test
     */
    public function keysForSectionReturnsDefaultListOfSectionDoesNotExist()
    {
        assertThat(
                $this->properties->keysForSection('doesNotExist', ['foo', 'bar']),
                equals(['foo', 'bar'])
        );
    }

    /**
     * @return  array
     */
    public function existingSectionKeys()
    {
        $data = [];
        foreach ($this->sections() as $section) {
            foreach (array_keys($section[1]) as $key) {
                $data[] = [$section[0], $key];
            }
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider  existingSectionKeys
     */
    public function containValueReturnsTrueIfValueExist(string $section, $key)
    {
        assertTrue($this->properties->containValue($section, $key));
    }

    /**
     * @test
     */
    public function containValueReturnsFalseIfValueDoesNotExist()
    {
        assertFalse($this->properties->containValue('empty', 'any'));
    }

    /**
     * @test
     */
    public function containValueReturnsFalseIfSectionDoesNotExist()
    {
        assertFalse($this->properties->containValue('doesNotExist', 'any'));
    }

    /**
     * @return  array
     */
    public function existingSectionValues(): array
    {
        $data = [];
        foreach ($this->sections() as $section) {
            foreach ($section[1] as $key => $value) {
                $data[] = [$section[0], $key, $value];
            }
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider  existingSectionValues
     */
    public function valueWithoutDefaultValueReturnsValueIfExists(string $section, string $key, $expectedValue)
    {
        assertThat($this->properties->value($section, $key), equals($expectedValue));
    }

    /**
     * @test
     */
    public function valueWithoutDefaultValueReturnsNullIfValueDoesNotExist()
    {
        assertNull($this->properties->value('empty', 'any'));
    }

    /**
     * @test
     */
    public function valueWithoutDefaultValueReturnsNullIfSectionDoesNotExist()
    {
        assertNull($this->properties->value('doesNotExist', 'any'));
    }

    /**
     * @test
     * @dataProvider  existingSectionValues
     */
    public function valueWithDefaultValueReturnsValueIfExists(string $section, string $key, $expectedValue)
    {
        assertThat(
                $this->properties->value($section, $key, 'otherValue'),
                equals($expectedValue)
        );
    }

    /**
     * @test
     */
    public function valueWithDefaultValueReturnsDefaultValueIfValueDoesNotExist()
    {
        assertThat(
                $this->properties->value('empty', 'any', 'otherValue'),
                equals('otherValue')
        );
    }

    /**
     * @test
     */
    public function valueWithDefaultValueReturnsDefaultValueIfSectionDoesNotExist()
    {
        assertThat(
                $this->properties->value('doesNotExist', 'any', 'otherValue'),
                equals('otherValue')
        );
    }

    /**
     * @test
     * @group  bug249
     */
    public function iteratingOverInstanceIteratesOverSections()
    {
        foreach ($this->properties as $section => $sectionData) {
            assertTrue($this->properties->containSection($section));
            assertThat($this->properties->section($section), equals($sectionData));
        }
    }

    /**
     * @test
     * @group  bug249
     * @since  1.3.2
     */
    public function iteratingAfterIterationShouldRestartIteration()
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

    /**
     * @test
     */
    public function fromNonExistantFileThrowsInvalidArgumentException()
    {
        expect(function() {
                Properties::fromFile(__DIR__ . '/doesNotExist.ini');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function invalidIniFileThrowsIOException()
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('invalid.ini')
                 ->at($root)
                 ->withContent("[invalid{");
        expect(function() {
                Properties::fromFile(vfsStream::url('config/invalid.ini'));
        })
        ->throws(\UnexpectedValueException::class);
    }

    /**
     * @test
     */
    public function validIniFileReturnsInstance()
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('test.ini')
                 ->at($root)
                 ->withContent("[foo]\nbar=baz");
        $properties = Properties::fromFile(vfsStream::url('config/test.ini'));
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @since  2.0.0
     * @group  bug213
     */
    public function invalidIniStringThrowsException()
    {
        if (defined('HHVM_VERSION')) {
            $expected = "No ending delimiter '^' found: [^([a-z]{3})$]";
        } else {
            $expected = 'syntax error, unexpected $end';
        }

        expect(function() {
                Properties::fromString("[invalid{");
        })
        ->throws(\InvalidArgumentException::class)
        ->message(contains('Property string contains errors and can not be parsed: ' . $expected));
    }

    /**
     * @test
     * @since  2.0.0
     * @group  bug213
     */
    public function validIniStringReturnsInstance()
    {
        $properties = Properties::fromString("[foo]\nbar=baz");
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @since  1.3.0
     */
    public function mergeMergesTwoPropertiesInstancesAndReturnsNewInstance()
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
     * @test
     * @since  1.3.0
     */
    public function mergeMergesProperties()
    {
        $properties1 = new Properties(['foo' => ['bar' => 'baz']]);
        $properties2 = new Properties(['bar' => ['bar' => 'baz']]);
        $resultProperties = $properties1->merge($properties2);
        assertThat($resultProperties->section('foo'), equals(['bar' => 'baz']));
        assertThat($resultProperties->section('bar'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @since  1.3.0
     */
    public function mergeOverwritesSectionsOfMergingInstanceWithThoseFromMergedInstance()
    {
        $properties1 = new Properties(['foo' => ['bar' => 'baz'],
                                   'bar' => ['baz' => 'foo']
                                  ]
                       );
        $properties2 = new Properties(['bar' => ['bar' => 'baz']]);
        $resultProperties = $properties1->merge($properties2);
        assertThat($resultProperties->section('foo'), equals(['bar' => 'baz']));
        assertThat($resultProperties->section('bar'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @group  secure_string
     * @since  4.0.0
     */
    public function propertiesWithKeyPasswordBecomeInstancesOfSecret()
    {
        assertThat(
                (new Properties(['foo' => ['password' => 'baz']]))
                        ->value('foo', 'password'),
                isInstanceOf(Secret::class)
        );
    }

    /**
     * @test
     * @group  secure_string
     * @since  4.1.2
     */
    public function propertiesWhereKeyEndsWithPasswordBecomeInstancesOfSecret()
    {
        assertThat(
                (new Properties(['foo' => ['example.another.password' => 'baz']]))
                        ->value('foo', 'example.another.password'),
                isInstanceOf(Secret::class)
        );
    }

    /**
     * @test
     * @group  secure_string
     * @since  4.1.0
     */
    public function parseSecretValueReturnsSecretInstance()
    {
        assertThat(
                (new Properties(['foo' => ['password' => 'baz']]))
                        ->parseValue('foo', 'password'),
                isInstanceOf(Secret::class)
        );
    }

    /**
     * @test
     * @group  secret
     * @since  5.0.0
     */
    public function parseSecretThrowsIllegalAccessException()
    {
        expect(function() {
                (new Properties(['foo' => ['password' => 'baz']]))
                        ->parse('foo', 'password');
        })
        ->throws(\LogicException::class)
        ->withMessage('Can not parse fields with passwords');
    }

    /**
     * @return  array
     */
    public function parseValueList(): array
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
     * @param  mixed   $expected
     * @param  string  $section
     * @param  string  $key
     * @test
     * @dataProvider  parseValueList
     * @since  4.1.0
     */
    public function parseValueReturnsValueCastedToRecognizedType($expected, string $section, string $key)
    {
        assertTrue($expected === $this->properties->parseValue($section, $key));
    }

    /**
     * @test
     * @since  4.1.0
     */
    public function parseValueWithNonExistingKeyReturnsDefault()
    {
        assertThat(
                $this->properties->parseValue('empty', 'doesNotExist', 6100),
                equals(6100)
        );
    }

    /**
     * @test
     * @since  4.1.0
     */
    public function parseValueFromNonExistingSectionReturnsDefault()
    {
        assertThat(
                $this->properties->parseValue('doesNotExist', 'rangeValue8', 6100),
                equals(6100)
        );
    }

    /**
     * @return  array
     * @since  5.0.0
     */
    public function parseList(): array
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
     * @param  mixed   $expected
     * @param  string  $section
     * @param  string  $key
     * @param  string  $type
     * @test
     * @dataProvider  parseList
     * @since  5.0.0
     */
    public function parseReturnsValueCastedToRecognizedType(
            $expected,
            string $section,
            string $key,
            string $type
    ) {
        assertThat(
                $this->properties->parse($section, $key)->$type(),
                equals($expected)
        );
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function parseNonExistingReturnsNullInstance()
    {
        assertThat(
                $this->properties->parse('empty', 'doesNotExist'),
                equals(new Parse(null))
        );
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function parseFromNonExistingSectionReturnsDefault()
    {
        assertThat(
                $this->properties->parse('doesNotExist', 'rangeValue8'),
                equals(new Parse(null))
        );
    }
}
