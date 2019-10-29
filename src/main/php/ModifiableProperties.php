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
 * Properties instance which allows modification of properties.
 *
 * @since       1.7.0
 */
class ModifiableProperties extends Properties
{
    /**
     * sets a section
     *
     * If a section with this name already exists it will be replaced.
     *
     * @api
     * @param   string  $section
     * @param   array   $data
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setSection(string $section, array $data): self
    {
        $this->propertyData[$section] = $data;
        return $this;
    }

    /**
     * sets value of property in given section
     *
     * If a property with this name in the given section already exists it will
     * be replaced.
     *
     * @api
     * @param   string  $section
     * @param   string  $name
     * @param   mixed   $value
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setValue(string $section, string $name, $value): self
    {
        if (!isset($this->propertyData[$section])) {
            $this->propertyData[$section] = [];
        }

        $this->propertyData[$section][$name] = (string) $value;
        return $this;
    }

    /**
     * sets a boolean property value in given section
     *
     * If a property with this name in the given section already exists it will
     * be replaced.
     *
     * @api
     * @param   string  $section
     * @param   string  $name
     * @param   bool    $value
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setBooleanValue(string $section, string $name, $value): self
    {
        return $this->setValue($section, $name, ((true === $value) ? ('true') : ('false')));
    }

    /**
     * sets an array as property value in given section
     *
     * If a property with this name in the given section already exists it will
     * be replaced.
     *
     * @api
     * @param   string  $section
     * @param   string  $name
     * @param   array   $value
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setArrayValue(string $section, string $name, array $value): self
    {
        return $this->setValue($section, $name, join('|', $value));
    }

    /**
     * sets a hash map as property value in given section
     *
     * If a property with this name in the given section already exists it will
     * be replaced.
     *
     * @api
     * @param   string  $section
     * @param   string  $name
     * @param   array   $hash
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setHashValue(string $section, string $name, array $hash): self
    {
        $values = [];
        foreach($hash as $key => $val) {
            $values[] = $key . ':' . $val;
        }

        return $this->setArrayValue($section, $name, $values);
    }

    /**
     * sets a range as property value in given section
     *
     * If a property with this name in the given section already exists it will
     * be replaced.
     *
     * @api
     * @param   string  $section
     * @param   string  $name
     * @param   array   $range
     * @return  \stubbles\values\ModifiableProperties
     */
    public function setRangeValue(string $section, string $name, array $range): self
    {
        return $this->setValue($section, $name, array_shift($range) . '..' . array_pop($range));
    }

    /**
     * returns unmodifiable version of properties
     *
     * @return  \stubbles\values\Properties
     * @since   4.0.0
     */
    public function unmodifiable(): Properties
    {
        return new Properties($this->propertyData);
    }
}
