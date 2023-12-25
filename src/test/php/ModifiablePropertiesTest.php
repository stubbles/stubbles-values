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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
 */
#[Group('values')]
#[Group('properties')]
class ModifiablePropertiesTest extends TestCase
{
    protected ModifiableProperties $modifiableProperties;

    protected function setUp(): void
    {
        $this->modifiableProperties = new ModifiableProperties([
                'scalar' => [
                        'stringValue' => 'This is a string',
                        'intValue'    => '303',
                        'floatValue'  => '3.13',
                        'boolValue'   => 'true'
                ],
                'array'  => [
                        'arrayValue'  => 'foo|bar|baz',
                        'hashValue'   => 'foo:bar|baz',
                ],
                'range'  => [
                        'rangeValue1' => '1..5',
                        'rangeValue2' => 'a..e'
                ],
                'empty'  => []
        ]);
    }

    #[Test]
    public function setNonExistingSectionEnsuresSectionIsContained(): void
    {
        assertTrue(
                $this->modifiableProperties->setSection('doesNotExist', ['foo' => 'bar'])
                        ->containSection('doesNotExist')
        );
    }

    #[Test]
    public function setNonExistingSectionAddsSection(): void
    {
        assertThat(
                $this->modifiableProperties->setSection('doesNotExist', ['foo' => 'bar'])
                        ->section('doesNotExist'),
                equals(['foo' => 'bar'])
        );
    }

    #[Test]
    public function setExistingSectionReplacesSection(): void
    {
        assertThat(
                $this->modifiableProperties->setSection('empty', ['foo' => 'bar'])
                        ->section('empty'),
                equals(['foo' => 'bar'])
        );
    }

    #[Test]
    public function setNonExistingValueForNonExistingSectionEnsuresSectionIsContained(): void
    {
        assertTrue(
                $this->modifiableProperties->setValue('doesNotExist', 'foo', 'bar')
                        ->containSection('doesNotExist')
        );
    }

    #[Test]
    public function setNonExistingValueForNonExistingSectionAddsSectionAndValue(): void
    {
        assertThat(
                $this->modifiableProperties->setValue('doesNotExist', 'foo', 'bar')
                        ->section('doesNotExist'),
                equals(['foo' => 'bar'])
        );
    }

    #[Test]
    public function setNonExistingValueForExistingSectionAddsValueToSection(): void
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

    #[Test]
    public function setExistingValueForExistingSectionReplacesValueInSection(): void
    {
        assertThat(
                $this->modifiableProperties->setValue('empty', 'foo', 'bar')
                        ->section('empty'),
                equals(['foo' => 'bar'])
        );
    }

    #[Test]
    public function setBooleanTrueTransformsToPropertyStorage(): void
    {
        assertThat(
                $this->modifiableProperties->setBooleanValue('empty', 'foo', true)
                        ->section('empty'),
                equals(['foo' => 'true'])
        );
    }

    #[Test]
    public function setBooleanFalseTransformsToPropertyStorage(): void
    {
        assertThat(
                $this->modifiableProperties->setBooleanValue('empty', 'foo', false)
                        ->section('empty'),
                equals(['foo' => 'false'])
        );
    }

    #[Test]
    public function setArrayValueTransformsToPropertyStorage(): void
    {
        assertThat(
                $this->modifiableProperties->setArrayValue('empty', 'foo', [1, 2, 3])
                        ->section('empty'),
                equals(['foo' => '1|2|3'])
        );
    }

    #[Test]
    public function setHashValueTransformsToPropertyStorage(): void
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

    #[Test]
    public function setIntegerRangeValueTransformsToPropertyStorage(): void
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

    #[Test]
    public function setReverseIntegerRangeValueTransformsToPropertyStorage(): void
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

    #[Test]
    public function setCharacterRangeValueTransformsToPropertyStorage(): void
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

    #[Test]
    public function setReverseCharacterRangeValueTransformsToPropertyStorage(): void
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

    #[Test]
    public function fromNonExistantFileThrowsInvalidArgumentException(): void
    {
        expect(function() {
            ModifiableProperties::fromFile(__DIR__ . '/doesNotExist.ini');
        })
            ->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function invalidIniFileThrowsException(): void
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

    #[Test]
    public function validIniFileReturnsInstance(): void
    {
        $root = vfsStream::setup('config');
        vfsStream::newFile('test.ini')
                 ->at($root)
                 ->withContent("[foo]\nbar=baz");
        $properties = ModifiableProperties::fromFile(vfsStream::url('config/test.ini'));
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    #[Group('bug213')]
    public function invalidIniStringThrowsException(): void
    {
        expect(function() {
                ModifiableProperties::fromString("[invalid{");
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    #[Group('bug213')]
    public function validIniStringReturnsInstance(): void
    {
        $properties = ModifiableProperties::fromString("[foo]\nbar=baz");
        assertThat($properties->section('foo'), equals(['bar' => 'baz']));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function mergeReturnsModifiableProperties(): void
    {
        assertThat(
                $this->modifiableProperties->merge(new Properties([])),
                isInstanceOf(ModifiableProperties::class)
        );
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function unmodifiableTurnsModifiableIntoNonModifiableProperties(): void
    {
        assertThat(
                $this->modifiableProperties->unmodifiable(),
                isInstanceOf(Properties::class)
        );
    }
}
