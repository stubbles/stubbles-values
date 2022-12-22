<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;

use ReflectionClass;
/**
 * Provides functions for parsing strings to a target type.
 *
 * @since 4.1.0
 */
class Parse
{
    public const SEPARATOR_LIST = '|';
    /**
     * list of values which are treated as boolean true
     *
     * @var  string[]
     */
    private static array $booleanTrue  = ['yes', 'true', 'on'];
    /**
     * list of known type recognitions
     *
     * @var  callable[]
     */
    private static array $recognitions = [];

    /**
     * static initializer
     */
    public static function __static(): void
    {
        self::addRecognition(function(?string $string) { if (self::toBool($string)) { return true; } }, 'booleanTrue');
        self::addRecognition(function(?string $string) { if (null !== $string && in_array(strtolower($string), ['no', 'false', 'off'])) { return false; } }, 'booleanFalse');
        self::addRecognition(function(?string $string) { if (null !== $string && preg_match('/^[+-]?[0-9]+$/', $string) != false) { return self::toInt($string);} }, 'int');
        self::addRecognition(function(?string $string) { if (null !== $string && preg_match('/^[+-]?[0-9]+\.[0-9]+$/', $string) != false) { return self::toFloat($string); } }, 'float');
        self::addRecognition(
                function(?string $string)
                {
                    if (null !== $string && substr($string, 0, 1) === '[' && substr($string, -1) === ']') {
                        return (strstr($string, ':') !== false) ? self::toMap($string) : self::toList($string);
                    }
                },
                'array'
        );
        self::addRecognition(function(?string $string) { if (null !== $string && strstr($string, '..') !== false) { return self::toRange($string); } }, 'range');
        self::addRecognition(function(?string $string) { $classname = self::toClassname($string); if (null !== $classname) { return $classname; } }, 'string');
        self::addRecognition(function(?string $string) { $class = self::toClass($string); if (null !== $class) { return $class; } }, 'ReflectionClass');
        self::addRecognition(function(?string $string) { if (null !== $string && defined($string)) { return constant($string); } }, 'constant');
    }

    /**
     * adds given callable for type recognition
     *
     * The callable must accept a string value and return a type. If the return
     * value is null the recognition will be treated as failed and the next
     * recognition will be tried.
     * <code>
     * Parse::addRecognition(
     *      function($string)
     *      {
     *          if ('Binford 6100' === $string) {
     *              return new Binford('More power!');
     *          }
     *      },
     *      'binford'
     * );
     * Parse::toType('Binford 6100'); // yields instance of Binford
     * </code>
     *
     * @param  callable  $recognition
     * @param  string    $name         name under which recognition should be stored
     */
    public static function addRecognition(callable $recognition, string $name): void
    {
        self::$recognitions[$name] = $recognition;
    }

    /**
     * removes recognition with given name
     *
     * @param   string  $name  name under which recognition is stored
     * @return  bool  true if recognition was present and removed, false otherwise
     */
    public static function removeRecognition(string $name): bool
    {
        if (isset(self::$recognitions[$name])) {
            unset(self::$recognitions[$name]);
            return true;
        }

        return false;
    }

    /**
     * parses string to a type depending on the value of the string
     *
     * These are the conversions being tried in their order:
     * String value                                         => result
     * null, ''                                             => string value as it is
     * 'null'                                               => null
     * 'yes', 'true', 'on'                                  => true
     * 'no', 'false', 'off'                                 => false
     * string containing of numbers only                    => integer
     * string containing of numbers and a dot               => float
     * string starting with [, ending with ]
     *      and containing at least one :                   => map   (i.e. array, see toMap())
     * string starting with [, ending with ]                => list  (i.e. array, see toList())
     * string containing ..                                 => range (i.e. array, see toRange())
     * <fully\qualified\Classname::class>                   => string representing an existing class name
     * <fully\qualified\Classname.class>                    => ReflectionClass
     * string containing name of a constant                 => value of the constant
     * recognition added via Parse::addRecognition()        => return type of the callable
     * all other                                            => string value as is
     */
    public static function toType(mixed $string): mixed
    {
        if (null == $string) {
            return $string;
        }

        if (is_string($string) && 'null' === strtolower($string)) {
            return null;
        }

        foreach (self::$recognitions as $recognition) {
            $value = $recognition($string);
            if (null !== $value) {
                return $value;
            }
        }

        return $string;
    }

    /**
     * parses string to an integer
     */
    public static function toInt(?string $string): ?int
    {
        if (null === $string) {
            return null;
        }

        return intval($string);
    }

    /**
     * parses string to a float
     */
    public static function toFloat(?string $string): ?float
    {
        if (null === $string) {
            return null;
        }

        return floatval($string);
    }

    /**
     * parses string to a boolean value
     *
     * The return value is true if the string value is either "1", "yes", "true"
     * or "on". In any other case the return value will be false.
     */
    public static function toBool(?string $string): bool
    {
        if (null === $string) {
            return false;
        }

        return in_array(strtolower($string), self::$booleanTrue);
    }

    /**
     * parses string to a list of strings
     *
     * If the value is empty the return value will be an empty array. If the
     * value is not empty it will be splitted at "|".
     * Example:
     * <code>
     * key = "foo|bar|baz"
     * </code>
     * The resulting array would be ['foo', 'bar', 'baz']
     *
     * @param   string  $separator  optional  character which separates list values
     * @return  string[]
     */
    public static function toList(
        ?string $string,
        string $separator = self::SEPARATOR_LIST
    ): ?array {
        if (null === $string) {
            return null;
        }

        $withoutParenthesis = self::removeParenthesis($string);
        if ('' === $withoutParenthesis) {
            return [];
        }

        if (strlen($separator) > 0 && strstr($withoutParenthesis, $separator) !== false) {
            return explode($separator, $withoutParenthesis);
        }

        return [$withoutParenthesis];
    }

    /**
     * removes leading and trailing parenthesis from list and map strings
     */
    private static function removeParenthesis(string $string): string
    {
        if (substr($string, 0, 1) === '[' && substr($string, -1) === ']') {
            return substr($string, 1, strlen($string) - 2);
        }

        return $string;
    }

    /**
     * parses string to a map
     *
     * If the value is empty the return value will be an empty map. If the
     * value is not empty it will be splitted at "|". The resulting list will
     * be splitted at the first ":", the first part becoming the key and the rest
     * becoming the value in the map. If no ":" is present, the whole value will
     * be appended to the map using a numeric value for the key.
     * Example:
     * <code>
     * key = "foo:bar|baz"
     * </code>
     * The resulting map would be ['foo' => 'bar', 'baz']
     *
     * @return array<string>
     */
    public static function toMap(?string $string): ?array
    {
        if (null === $string) {
            return null;
        } elseif ('' === $string) {
            return [];
        }

        $map = [];
        $list = self::toList($string);
        if (null !== $list) {
            foreach ($list as $keyValue) {
                if (strstr($keyValue, ':') !== false) {
                    list($key, $value) = explode(':', $keyValue, 2);
                    $map[$key]         = $value;
                } else {
                    $map[] = $keyValue;
                }
            }
        }

        return $map;
    }

    /**
     * parses string to a range
     *
     * Ranges can be written as 1..5 which will return an array: [1, 2, 3, 4, 5].
     * Works also with letters and reverse order a..e, e..a and 5..1.
     *
     * @return int[]|float[]|string[]|null
     */
    public static function toRange(?string $string): ?array
    {
        if (null === $string) {
            return null;
        } elseif ('' === $string) {
            return [];
        }

        if (!strstr($string, '..')) {
            return [];
        }

        list($min, $max) = explode('..', $string, 2);
        if (null == $min || null == $max) {
            return [];
        }

        return range($min, $max);
    }

    /**
     * parses string to a reflection class
     *
     * String must have the format <fully\qualified\Classname.class>. In case
     * the string can not be parsed the return value is null.
     *
     * @return ReflectionClass<object>
     */
    public static function toClass(?string $string): ?ReflectionClass
    {
        if (empty($string)) {
            return null;
        }

        $classnameMatches = [];
        $classnameMatcher = '/^([a-zA-Z_]{1}[a-zA-Z0-9_\\\\]*)\.class/';
        if (preg_match($classnameMatcher, $string, $classnameMatches) != false) {
            /** @var class-string $class */
            $class = $classnameMatches[1];
            return new ReflectionClass($class);
        }

        return null;
    }

    /**
     * parses string as class name
     *
     * String must have the format <fully\qualified\Classname::class>. In case
     * the string can not be parsed or the class does not exist the return value
     * is null.
     *
     * @return class-string|null
     * @since  5.3.0
     */
    public static function toClassname(?string $string): ?string
    {
        if (empty($string)) {
            return null;
        }

        $classnameMatches = [];
        $classnameMatcher = '/^([a-zA-Z_]{1}[a-zA-Z0-9_\\\\]*)\::class$/';
        if (preg_match($classnameMatcher, $string, $classnameMatches) != false) {
            if (class_exists($classnameMatches[1])) {
                return $classnameMatches[1];
            }
        }

        return null;
    }

    /**
     * default to be returned in case value is null
     */
    private mixed $default = null;

    /**
     * constructor
     *
     * @param  ?scalar  $value
     * @since  5.0.0
     */
    public function __construct(private mixed $value) { }

    /**
     * set a default to be returned in case value is <null>
     */
    public function defaultingTo(mixed $default): self
    {
        $this->default = $default;
        return $this;
    }

    private function parse(string $method, mixed ...$arguments): mixed
    {
        if (null === $this->value) {
            return $this->default;
        }

        return self::$method($this->value, ...$arguments);
    }

    /**
     * returns value as string (i.e., a pass through)
     *
     * @since 5.0.0
     */
    public function asString(): string
    {
        if (null === $this->value) {
            // @phpstan-ignore-next-line
            return (string) $this->default;
        }

        return (string) $this->value;
    }

    /**
     * parses initial value as integer
     *
     * @since 5.0.0
     */
    public function asInt(): ?int
    {
        return $this->parse('toInt');
    }

    /**
     * parses initial value as float
     *
     * @since 5.0.0
     */
    public function asFloat(): ?float
    {
        return $this->parse('toFloat');
    }

    /**
     * parses initial value as bool
     *
     * @since 5.0.0
     */
    public function asBool(): bool
    {
        return (bool) $this->parse('toBool');
    }

    /**
     * parses initial value as list
     *
     * @return string[]
     * @since  5.0.0
     */
    public function asList(string $separator = self::SEPARATOR_LIST): ?array
    {
        return $this->parse('toList', $separator);
    }

    /**
     * parses initial value as list
     *
     * @return array<string,mixed>
     * @since  5.0.0
     */
    public function asMap(): ?array
    {
        return $this->parse('toMap');
    }

    /**
     * parses initial value as range
     *
     * @return int[]|float[]|string[]|null
     * @since  5.0.0
     */
    public function asRange(): ?array
    {
        return $this->parse('toRange');
    }

    /**
     * parses initial value as reflection class
     *
     * @return ReflectionClass<object>
     * @since  5.0.0
     */
    public function asClass(): ?ReflectionClass
    {
        return $this->parse('toClass');
    }

    /**
     * parses initial value as class name
     *
     * @return class-string|null
     * @since  5.3.0
     */
    public function asClassname(): ?string
    {
        return $this->parse('toClassname');
    }
}
Parse::__static();
