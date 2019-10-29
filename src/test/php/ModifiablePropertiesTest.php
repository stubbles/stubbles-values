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
    assertTrue,
    expect,
    predicate\equals,
    predicate\isInstanceOf
};
/**
 * Tests for stubbles\values\ModifiableProperties.
 *
 * @since  1.7.0
 * @group  values
 * @group  properties
 */
class ModifiablePropertiesTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\values\ModifiableProperties
     */
    protected $modifiableProperties;

    protected function setUp(): void
    {
        $this->modifiableProperties = new ModifiableProperties(
                ['scalar' => ['stringValue' => 'This is a string',
                              'intValue'    => '303',
                              'floatValue'  => '3.13',
                              'boolValue'   => 'true'
                             ],
                 'array'  => ['arrayValue'  => 'foo|bar|baz',
                              'hashValue'   => 'foo:bar|baz',
                             ],
                 'range'  => ['rangeValue1' => '1..5',
                              'rangeValue2' => 'a..e'
                             ],
                 'empty'  => []
                ]
        );
    }

    /**
     * @test
     */
    public function setNonExistingSectionEnsuresSectionIsContained()
    {
        assertTrue(
                $this->modifiableProperties->setSection('doesNotExist', ['foo' => 'bar'])
                        ->containSection('doesNotExist')
        );
    }

    /**
     * @test
     */
    public function setNonExistingSectionAddsSection()
    {
        assertThat(
                $this->modifiableProperties->setSection('doesNotExist', ['foo' => 'bar'])
                        ->section('doesNotExist'),
                equals(['foo' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function setExistingSectionReplacesSection()
    {
        assertThat(
                $this->modifiableProperties->setSection('empty', ['foo' => 'bar'])
                        ->section('empty'),
                equals(['foo' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function setNonExistingValueForNonExistingSectionEnsuresSectionIsContained()
    {
        assertTrue(
                $this->modifiableProperties->setValue('doesNotExist', 'foo', 'bar')
                        ->containSection('doesNotExist')
        );
    }

    /**
     * @test
     */
    public function setNonExistingValueForNonExistingSectionAddsSectionAndValue()
    {
        assertThat(
                $this->modifiableProperties->setValue('doesNotExist', 'foo', 'bar')
                        ->section('doesNotExist'),
                equals(['foo' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function setNonExistingValueForExistingSectionAddsValueToSection()
    {
        assertThat(
                $this->modifiableProperties->setValue('scalar', 'stringValue', 'bar')
                        ->section('scalar'),
                equals([
                        'stringValue' => 'bar',
                        'intValue'    => '303',
                        'floatValue'  => '3.13',
                        'boolValue'   => 'true'
                ])
        );
    }

    /**
     * @test
     */
    public function setExistingValueForExistingSectionReplacesValueInSection()
    {
        assertThat(
                $this->modifiableProperties->setValue('empty', 'foo', 'bar')
                        ->section('empty'),
                equals(['foo' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function setBooleanTrueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setBooleanValue('empty', 'foo', true)
                        ->section('empty'),
                equals(['foo' => 'true'])
        );
    }

    /**
     * @test
     */
    public function setBooleanFalseTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setBooleanValue('empty', 'foo', false)
                        ->section('empty'),
                equals(['foo' => 'false'])
        );
    }

    /**
     * @test
     */
    public function setArrayValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setArrayValue('empty', 'foo', [1, 2, 3])
                        ->section('empty'),
                equals(['foo' => '1|2|3'])
        );
    }

    /**
     * @test
     */
    public function setHashValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setHashValue(
                        'empty',
                        'foo',
                        [1 => 10, 2 => 20, 3 => 30]
                )->section('empty'),
                equals(['foo' => '1:10|2:20|3:30'])
        );
    }

    /**
     * @test
     */
    public function setIntegerRangeValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setRangeValue(
                        'empty',
                        'foo',
                        [1, 2, 3, 4, 5]
                )->section('empty'),
                equals(['foo' => '1..5'])
        );
    }

    /**
     * @test
     */
    public function setReverseIntegerRangeValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setRangeValue(
                        'empty',
                        'foo',
                        [5, 4, 3, 2, 1]
                )->section('empty'),
                equals(['foo' => '5..1'])
        );
    }

    /**
     * @test
     */
    public function setCharacterRangeValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setRangeValue(
                        'empty',
                        'foo',
                        ['a', 'b', 'c', 'd', 'e']
                )->section('empty'),
                equals(['foo' => 'a..e'])
        );
    }

    /**
     * @test
     */
    public function setReverseCharacterRangeValueTransformsToPropertyStorage()
    {
        assertThat(
                $this->modifiableProperties->setRangeValue(
                        'empty',
                        'foo',
                        ['e', 'd', 'c', 'b', 'a']
                )->section('empty'),
                equals(['foo' => 'e..a'])
        );
    }

    /**
     * @test
     */
    public function fromNonExistantFileThrowsInvalidArgumentException()
    {
        expect(function() {
                ModifiableProperties::fromFile(__DIR__ . '/doesNotExist.ini');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function invalidIniFileThrowsException()
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('invalid.ini')
                 ->at($root)
                 ->withContent("[invalid{");
        expect(function() {
                ModifiableProperties::fromFile(vfsStream::url('config/invalid.ini'));
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
        $properties = ModifiableProperties::fromFile(vfsStream::url('config/test.ini'));
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @since  2.0.0
     * @group  bug213
     */
    public function invalidIniStringThrowsException()
    {
        expect(function() {
                ModifiableProperties::fromString("[invalid{");
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @since  2.0.0
     * @group  bug213
     */
    public function validIniStringReturnsInstance()
    {
        $properties = ModifiableProperties::fromString("[foo]\nbar=baz");
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function mergeReturnsModifiableProperties()
    {
        assertThat(
                $this->modifiableProperties->merge(new Properties([])),
                isInstanceOf(ModifiableProperties::class)
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function unmodifiableTurnsModifiableIntoNonModifiableProperties()
    {
        assertThat(
                $this->modifiableProperties->unmodifiable(),
                isInstanceOf(Properties::class)
        );
    }
}
