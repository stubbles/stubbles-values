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
use UnexpectedValueException;
/**
 * Class to read and parse properties.
 *
 * Properties are iterable using foreach:
 * <code>
 * foreach (Properties::fromFile($propertyFile) as $sectionName => $section) {
 *     // $section is an array containing all section values as key-value pairs
 * }
 * </code>
 *
 * @implements \Iterator<string,array<string,string|Secret>>
 */
class Properties implements \Iterator
{
    /**
     * @var array<string,array<string,string|Secret>>
     */
    protected array $propertyData;

    /**
     * @api
     * @param array<string,array<string,string|Secret>> $propertyData  the property data
     */
    final public function __construct(array $propertyData = [])
    {
        foreach ($propertyData as $section => $values) {
            foreach (array_keys($values) as $key) {
                if (is_string($key) && substr($key, -8) === 'password') {
                    $propertyData[$section][$key] = Secret::create($values[$key]);
                }
            }
        }

        $this->propertyData = $propertyData;
    }

    /**
     * construct instance from string
     *
     * @api
     * @throws InvalidArgumentException
     * @since  2.0.0
     */
    public static function fromString(string $propertyString): self
    {
        $propertyData = @parse_ini_string($propertyString, true);
        if (false === $propertyData) {
            throw new InvalidArgumentException(
                    'Property string contains errors and can not be parsed: '
                    . lastErrorMessage()->value()
            );
        }

        return new static($propertyData);
    }

    /**
     * construct instance from a file
     *
     * @api
     * @throws InvalidArgumentException if file can not be found or is not readable
     * @throws UnexpectedValueException if file contains errors and can not be parsed
     */
    public static function fromFile(string $propertiesFile): self
    {
        if (!file_exists($propertiesFile) || !is_readable($propertiesFile)) {
            throw new InvalidArgumentException(
                'Property file ' . $propertiesFile . ' not found'
            );
        }

        $propertyData = @parse_ini_file($propertiesFile, true);
        if (false === $propertyData) {
            throw new UnexpectedValueException(
                'Property file at ' . $propertiesFile
                . ' contains errors and can not be parsed: '
                . lastErrorMessage()->value()
            );
        }

        return new static($propertyData);
    }

    /**
     * merges properties from another instance into itself
     *
     * The return value is a new instance with properties from this and the other
     * instance combined. If both instances have sections of the same name the
     * section from the other instance overwrite the section from this instance.
     *
     * @api
     * @since  1.3.0
     */
    public function merge(Properties $otherProperties): self
    {
        return new static(array_merge($this->propertyData, $otherProperties->propertyData));
    }

    /**
     * checks if a certain section exists
     *
     * @api
     * @since 4.0.0
     */
    public function containSection(string $sectionKey): bool
    {
        return isset($this->propertyData[$sectionKey]);
    }

    /**
     * returns a whole section if it exists or the default if the section does not exist
     *
     * @api
     * @param  array<string,string> $default
     * @return array<string,mixed>
     * @since  4.0.0
     */
    public function section(string $sectionKey, array $default = []): array
    {
        if (isset($this->propertyData[$sectionKey])) {
            return $this->propertyData[$sectionKey];
        }

        return $default;
    }

    /**
     * returns a list of all keys of a specific section
     *
     * @api
     * @param  string[] $default
     * @return string[]
     * @since  4.0.0
     */
    public function keysForSection(string $sectionKey, array $default = []): array
    {
        if (isset($this->propertyData[$sectionKey])) {
            return array_keys($this->propertyData[$sectionKey]);
        }

        return $default;
    }

    /**
     * checks if a certain section contains a certain key
     *
     * @api
     * @since 4.0.0
     */
    public function containValue(string $sectionKey, string $key): bool
    {
        return isset($this->propertyData[$sectionKey])
            && isset($this->propertyData[$sectionKey][$key]);
    }

    /**
     * returns a value from a section or a default value if the section or key does not exist
     *
     * @api
     * @since 4.0.0
     */
    public function value(string $sectionKey, string $key, mixed $default = null): mixed
    {
        if (isset($this->propertyData[$sectionKey]) && isset($this->propertyData[$sectionKey][$key])) {
            return $this->propertyData[$sectionKey][$key];
        }

        return $default;
    }

    /**
     * parses value and returns the parsing result
     *
     * @see   \stubbles\values\Parse::toType()
     * @since 4.1.0
     */
    public function parseValue(string $sectionKey, string $key, mixed $default = null): mixed
    {
        if (isset($this->propertyData[$sectionKey]) && isset($this->propertyData[$sectionKey][$key])) {
            if ($this->propertyData[$sectionKey][$key] instanceof Secret) {
                return $this->propertyData[$sectionKey][$key];
            }

            return Parse::toType($this->propertyData[$sectionKey][$key]);
        }

        return $default;
    }

    /**
     * returns a parser instance for the value
     *
     * In case the value was recognized as password and is therefore an instance
     * of \stubbles\values\Secret a LogicException is thrown as
     * this value can not be parsed.
     *
     * @throws LogicException
     * @since  5.0.0
     */
    public function parse(string $sectionKey, string $key): Parse
    {
        if (!isset($this->propertyData[$sectionKey]) || !isset($this->propertyData[$sectionKey][$key])) {
            return new Parse(null);
        }

        if ($this->propertyData[$sectionKey][$key] instanceof Secret) {
            throw new LogicException('Can not parse fields with passwords');
        }

        return new Parse($this->propertyData[$sectionKey][$key]);
    }

    /**
     * returns current section
     *
     * @return  array<string,mixed>|false
     * @see     http://php.net/manual/en/spl.iterators.php
     */
    public function current(): mixed
    {
        return current($this->propertyData);
    }

    /**
     * returns name of current section
     */
    public function key(): string
    {
        return (string) key($this->propertyData);
    }

    /**
     * forwards to next section
     */
    public function next(): void
    {
        next($this->propertyData);
    }

    /**
     * rewind to first section
     */
    public function rewind(): void
    {
        reset($this->propertyData);
    }

    /**
     * checks if there are more valid sections
     */
    public function valid(): bool
    {
        return current($this->propertyData) !== false;
    }
}
