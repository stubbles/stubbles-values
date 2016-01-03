stubbles/values
=================

Useful classes to ease the work with values.


Build status
------------

[![Build Status](https://secure.travis-ci.org/stubbles/stubbles-values.png)](http://travis-ci.org/stubbles/stubbles-values) [![Coverage Status](https://coveralls.io/repos/stubbles/stubbles-values/badge.png?branch=master)](https://coveralls.io/r/stubbles/stubbles-values?branch=master)

[![Latest Stable Version](https://poser.pugx.org/stubbles/values/version.png)](https://packagist.org/packages/stubbles/values) [![Latest Unstable Version](https://poser.pugx.org/stubbles/values/v/unstable.png)](//packagist.org/packages/stubbles/values)


Installation
------------

_stubbles/values_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/values": "^7.0"


Requirements
------------

_stubbles/values_ requires at least PHP 5.6.


Available classes
-----------------

### `stubbles\values\Result`

Enables to wrap a return value which might be `null`. In other languages or
libraries this is known as an Optional. To create an instance call
`Result::of($value)`.


#### `isPresent()`

Returns ´true` when result contains a value which not `null`.


#### `isEmpty()`

Returns `true` when result contains an empty value. Please note that this method
will always return `false` in case the value is an integer, even if it is 0.


#### `value()`

Returns the actual value.


#### `filter(callable $predicate)`

Allows to filter the value. In case the value fulfills the given predicate the
`Result` instance is returned. If the value does not fulfill the predicate the
equivalent of `Result::of(null)` is returned.

```php
$filter = function($value) { return 303 === $value; };
echo Result::of(303)->filter($filter)->value(); // displays 303
echo Result::of(313)->filter($filter)->value(); // displays nothing
echo Result::of(null)->filter($filter)->value(); // displays nothing
```


#### `map(callable $mapper)`

Maps the value using the given mapper into a different result. In case the value
is `null` the return value still is equivalent to `Result::of(null)`.

```php
$mapper = function($value) { return 'Roland TB 303'; };
echo Result::of(303)->map($mapper)->value(); // displays "Roland TB 303"
echo Result::of(null)->map($mapper)->value(); // displays nothing
```


#### `whenNull($other)`

Returns the result if value is present, or result of other.

```php
$default = 909;
echo Result::of(303)->whenNull($default)->value(); // displays 303
echo Result::of(null)->whenNull($default)->value(); // displays 909
```


#### `applyWhenNull(callable $other)`

Returns the result if value is present, or the result of applied other.

```php
$default = function() { return 909; };
echo Result::of(303)->applyWhenNull($default)->value(); // displays 303
echo Result::of(null)->applyWhenNull($default)->value(); // displays 909
```


#### `whenEmpty($other)`

Returns the result if value is not empty, or result of other.

```php
$default = 'Roland TB 303';
echo Result::of('Roland 909')->whenEmpty($default)->value(); // displays Roland 909
echo Result::of('')->whenEmpty($default)->value(); // displays Roland TB 303
```


#### `applyWhenEmpty($other)`

Returns the result if value is not empty, or result of applied other.

```php
$default = function() { return 'Roland TB 303'; };
echo Result::of('Roland 909')->applyWhenEmpty($default)->value(); // displays Roland 909
echo Result::of('')->applyWhenEmpty($default)->value(); // displays Roland TB 303
```


### `stubbles\values\Secret`

Secret provides a reasonable secure storage for security-sensitive lists of
characters, such as passwords.

It prevents accidentially revealing them in output, by var_dump()ing, echo()ing,
or casting the object to array. All these cases will not show the password, nor
the crypt of it.

However, it is not safe to consider this implementation secure in a crypto-
graphically sense, because it does not care for a very strong encryption, and it
does share the encryption key with all instances of it in a single PHP instance.

When using this class, you must make sure not to extract the secured string and
pass it to a place where an exception might occur, as it might be exposed as
method argument.

Instances of this class can not be serialized.


#### Create a secret

To create a secret call `Secret::create('my secret value')`.

Please note that you can not use this function to create a secret with value
`null`. If you explicitly want to do that use `Secret::forNull()`.


#### `isNull()`

Checks if actual secret is `null`.


#### `unveil()`

Unveils the secret and returns it as a string. This should be called at the
latest possible moment to avoid unneccessary revealing of the value to be
intended stored secure.


#### `substring($start, $length = null)`

Creates a substring of the secret value as new `Secret` instance.


#### `length()`

Returns length of the secret. Allows to check if it would fit a certain place
without the need to unveil it first.


### `stubbles\values\Parse`

Provides methods for parsing strings to a target type.


#### `Parse::toInt($value)`

Parses integer value from given string.


#### `Parse::toFloat($value)`

Parses float value from given string.


#### `Parse::toBool($string)`

Parses boolean value from given string. The return value is true if the string
value is either "1", "yes", "true" or "on". In any other case the return value
will be false.


#### `Parse::toList($string)`

Parses string to a list of strings.

If the value is empty the return value will be an empty array. If the value is
not empty it will be splitted at "|".

```php
Parse::toList("foo|bar|baz"); // results in ['foo', 'bar', 'baz']
```


#### `Parse::toMap($string)`

Parses string to a map.

If the value is empty the return value will be an empty map. If the value is not
empty it will be splitted at "|". The resulting list will be splitted at the
first ":", the first part becoming the key and the remainer becoming the value
in the map. If no ":" is present, the whole value will be appended to the map
using a numeric value for the key.

```php
Parse::toMap("foo:bar|baz"); // results in ['foo' => 'bar', 'baz']
```


#### `Parse::toRange($string)`

Parses string to a range.

Ranges can be written as 1..5 which will return an array: [1, 2, 3, 4, 5]. Works
also with letters and reverse order a..e, e..a and 5..1.


#### `Parse::toClass($string)`

Parses string to a reflection class.

String must have the format _fully\qualified\Classname.class_. In case the
string can not be parsed the return value is `null`.


#### `Parse::toClassname($string)`

Parses string as existing class name.

String must have the format _fully\qualified\Classname::class_. In case the
string can not be parsed or the class does not exist the return value is `null`.


### `stubbles\values\Properties`

Provides a convenient way to read property files and make their values
accessible. Properties can both be read from a string you might have in memory
or from a file:

```php
$properties = Properties::fromString($propertyString);
$properties = Properties::fromFile('path/to/properties.ini');
```

Properties consist of sections, which each has a list of key-value pairs.
Suppose the following properties file:

```php
[config]
cool.stuff = "Roland TB 303"
interesting = "Pink fluffy unicorns dancing on rainbows"

[other]
the.answer = 42
github = true
```

Here, the properties consists of the sections _config_ and _other_, where both
have different key-value pairs, _cool.stuff_ and _interesting_ belong to _config_
and the other two to _other_.

You can get the raw value of a property:

```php
$answer = $properties->getValue('other', 'the.answer'); // $answer now has the value "42" of type string
```

However, that might not be what you wanted to achieve. Rather, you would like to
have a correct type. That's where the parse*() methods come into play:

```php
$answer = $properties->parseInt('other', 'the.answer'); // $answer now has the value 42 of type int
```


#### `parseInt($section, $key, $default = 0)`

Tries to read the value and convert it to int. If the section or key is not set,
return value is `$default`.


#### `parseFloat($section, $key, $default = 0.0)`

Tries to read the value and convert it to float. If the section or key is not
set, return value is `$default`.


#### `parseBool($section, $key, $default = false)`

Tries to read the value and convert it to boolean. If the section or key is not
set, return value is `$default`.

The following value contents will be converted to `true`:

 * yes
 * true
 * on

All other values will evaluate to `false`.


#### `parseArray($section, $key, array $default = null)`

Tries to read the value and convert it to an array. If the section or key is not
set, return value is `$default`.

If the value is empty the return value will be an empty array. If the value is
not empty it will be splitted at _|_. So if the value would be _key = foo|bar|baz_
it gets converted to `['foo', 'bar', 'baz']`.


#### `parseHash($section, $key, array $default = null)`

Tries to read the value and convert it to a hashmap. If the section or key is
not set, return value is `$default`.

If the value is empty the return value will be an empty hash. If the value is
not empty it will be splitted at _|_. The resulting array will be splitted at
the first _:_, the first part becoming the key and the remaining part becoming
the value in the hash. If no _:_ is present, the whole value will be appended to
the hash using a numeric value. So _key = foo:bar|baz_ results in
`['foo' => 'bar', 'baz']`.


#### `parseRange($section, $key, array $default = [])`

Tries to read the value and convert it to a range. If the section or key is not
set, return value is `$default`.

Ranges in properties should be written as _key = 1..5_ the resulting value is
`[1, 2, 3, 4, 5]`.

This works also with letters and reverse order:

    letters = a..e
    letter_reverse = e..a
    numbers_reverse = 5..1


#### Passwords

_Available since release 4.0.0_

Since release 4.0.0, properties with key `password` will be stored as an
instance of `stubbles|values\Secret`. This will also be the return value when
requesting the value of such a property.


### Modifiable properties

_Available since release 1.7.0_

By default properties are read only. In case you need modifiable properties use
the `stubbles\values\ModifiableProperties` class. It provides means to set
property values:


#### `setSection($section, array $data)`

Sets a complete section with given section name. In case this section already
exists it will be replaced.


#### `setValue($section, $name, $value)`

Sets a single property value.


#### `setBooleanValue($section, $name, $value)`

Sets a single property to a boolean value in a way that it can be read properly
by `parseBool()`.


#### `setArrayValue($section, $name, array $value)`

Sets an array property to a value in a way that it can be read properly by
`parseArray()`.


#### `setHashValue($section, $name, array $hash)`

Sets a map property to a value in a way that it can be read properly by
`parseHash()`.


#### `setRangeValue($section, $name, array $range)`

Sets a range property to a value in a way that it can be read properly by
`parseRange()`.


#### `unmodifiable()`

_Available since release 4.0.0_

Returns an unmodifiable instance from the modifiable properties.


Available functions
-------------------

### `stubbles\values\lastErrorMessage()`

Returns the message of the native PHP function `error_get_last()['message']` as
instance of `stubbles\values\Result`.


### `typeOf(&$value)`

Returns the correct type of given value.

For objects the actual class name will be returned. In case of resources the
return value will be _resource[type_of_resource], e.g. _resource[stream]_. For
all other types the result is call to PHP's native `gettype()` function.
