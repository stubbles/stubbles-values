<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
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
 * @implements \Iterator<string,array<string,mixed>>
 */
class Properties implements \Iterator
{
    /**
     * property data
     *
     * @var  array<string,array<string,mixed>>
     */
    protected $propertyData;

    /**
     * constructor
     *
     * @api
     * @param  array<string,array<string,mixed>>  $propertyData  the property data
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
     * construct class from string
     *
     * @api
     * @param   string  $propertyString
     * @return  \stubbles\values\Properties
     * @throws  \InvalidArgumentException
     * @since   2.0.0
     */
    public static function fromString(string $propertyString): self
    {
        $propertyData = @parse_ini_string($propertyString, true);
        if (false === $propertyData) {
            throw new \InvalidArgumentException(
                    'Property string contains errors and can not be parsed: '
                    . lastErrorMessage()->value()
            );
        }

        return new static($propertyData);
    }

    /**
     * construct class from a file
     *
     * @api
     * @param   string  $propertiesFile  full path to file containing properties
     * @return  \stubbles\values\Properties
     * @throws  \InvalidArgumentException  if file can not be found or is not readable
     * @throws  \UnexpectedValueException  if file contains errors and can not be parsed
     */
    public static function fromFile(string $propertiesFile): self
    {
        if (!file_exists($propertiesFile) || !is_readable($propertiesFile)) {
            throw new \InvalidArgumentException(
                    'Property file ' . $propertiesFile . ' not found'
            );
        }

        $propertyData = @parse_ini_file($propertiesFile, true);
        if (false === $propertyData) {
            throw new \UnexpectedValueException(
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
     * @param   \stubbles\values\Properties  $otherProperties
     * @return  \stubbles\values\Properties
     * @since   1.3.0
     */
    public function merge(Properties $otherProperties): self
    {
        return new static(array_merge($this->propertyData, $otherProperties->propertyData));
    }

    /**
     * checks if a certain section exists
     *
     * @api
     * @param   string  $section  name of the section
     * @return  bool
     * @since   4.0.0
     */
    public function containSection(string $section): bool
    {
        return isset($this->propertyData[$section]);
    }

    /**
     * returns a whole section if it exists or the default if the section does not exist
     *
     * @api
     * @param   string                 $section  name of the section
     * @param   array<string,string>   $default  value to return if section does not exist
     * @return  array<string,string>
     * @since   4.0.0
     */
    public function section(string $section, array $default = []): array
    {
        if (isset($this->propertyData[$section])) {
            return $this->propertyData[$section];
        }

        return $default;
    }

    /**
     * returns a list of all keys of a specific section
     *
     * @api
     * @param   string    $section  name of the section
     * @param   string[]  $default  value to return if section does not exist
     * @return  string[]
     * @since   4.0.0
     */
    public function keysForSection(string $section, array $default = []): array
    {
        if (isset($this->propertyData[$section])) {
            return array_keys($this->propertyData[$section]);
        }

        return $default;
    }

    /**
     * checks if a certain section contains a certain key
     *
     * @api
     * @param   string  $section  name of the section
     * @param   string  $key      name of the key
     * @return  bool
     * @since   4.0.0
     */
    public function containValue(string $section, string $key): bool
    {
        if (isset($this->propertyData[$section]) && isset($this->propertyData[$section][$key])) {
            return true;
        }

        return false;
    }

    /**
     * returns a value from a section or a default value if the section or key does not exist
     *
     * @api
     * @param   string  $section  name of the section
     * @param   string  $key      name of the key
     * @param   mixed   $default  value to return if section or key does not exist
     * @return  scalar
     * @since   4.0.0
     */
    public function value(string $section, string $key, $default = null)
    {
        if (isset($this->propertyData[$section]) && isset($this->propertyData[$section][$key])) {
            return $this->propertyData[$section][$key];
        }

        return $default;
    }

    /**
     * parses value and returns the parsing result
     *
     * @param   string  $section
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     * @see     \stubbles\values\Parse::toType()
     * @since   4.1.0
     */
    public function parseValue(string $section, string $key, $default = null)
    {
        if (isset($this->propertyData[$section]) && isset($this->propertyData[$section][$key])) {
            if ($this->propertyData[$section][$key] instanceof Secret) {
                return $this->propertyData[$section][$key];
            }

            return Parse::toType($this->propertyData[$section][$key]);
        }

        return $default;
    }

    /**
     * returns a parser instance for the value
     *
     * In case the value was recognized as password and is therefore an instance
     * of \stubbles\values\Secret  an IllegalAccessException is thrown as
     * this value can not be parsed.
     *
     * @param   string  $section
     * @param   string  $key
     * @return  \stubbles\values\Parse
     * @throws  \LogicException
     * @since   5.0.0
     */
    public function parse(string $section, string $key): Parse
    {
        if (!isset($this->propertyData[$section]) || !isset($this->propertyData[$section][$key])) {
            return new Parse(null);
        }

        if ($this->propertyData[$section][$key] instanceof Secret) {
            throw new \LogicException('Can not parse fields with passwords');
        }

        return new Parse($this->propertyData[$section][$key]);
    }

    /**
     * returns current section
     *
     * @return  array<string,string>
     * @see     http://php.net/manual/en/spl.iterators.php
     */
    public function current(): array
    {
        return current($this->propertyData);
    }

    /**
     * returns name of current section
     *
     * @return  string
     * @see     http://php.net/manual/en/spl.iterators.php
     */
    public function key(): string
    {
        return (string) key($this->propertyData);
    }

    /**
     * forwards to next section
     *
     * @see  http://php.net/manual/en/spl.iterators.php
     */
    public function next(): void
    {
        next($this->propertyData);
    }

    /**
     * rewind to first section
     *
     * @see  http://php.net/manual/en/spl.iterators.php
     */
    public function rewind(): void
    {
        reset($this->propertyData);
    }

    /**
     * checks if there are more valid sections
     *
     * @return  bool
     * @see     http://php.net/manual/en/spl.iterators.php
     */
    public function valid(): bool
    {
        return current($this->propertyData) !== false;
    }
}
