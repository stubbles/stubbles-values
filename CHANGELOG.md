# Changelog

## 11.0.0

### BC breaks

* raised minimum required PHP version to 8.2
* removed `stubbles\values\Secret::substring()`, deprecated since 10.0.0

## 10.0.0 (2022-12-26)

### BC breaks

* raised minimum required PHP version to 8.0
* removed decprecated second parameter of `stubbles\values\ResourceLoader::load()`, use `stubbles\values\ResourceLoader::loadWith()` instead
* `stubbles\values\ResourceLoader::load()` now throws a `stubbles\values\ResourceLoadingFailure` when loading the resource fails
* The following methods of `stubbles\values\Parse` will now throw a `TypeError` when they fall back to the default value in case the type of the specified default value doesn't match the expected return type:
   * `asInt()`
   * `asFloat()`
   * `asList()`
   * `asMap()`
   * `asRange()`
   * `asClass()`
* fixed bug with `stubbles\values\Parse::asBool()` not returning `false` when both value and default are null
* deprecated `stubbles\values\Secret::substring()`, will be removed with 11.0.0

### Other changes

* `stubbles\values\Secret::create()` has been amended with the #[\SensitiveParameter] attribute for its parameter to further prevent possibilities for leaks of secrets
* New default backing for `stubbles\values\Secret` is ext_sodium

## 9.2.0 (2020-03-06)

* added `stubbles\values\ResourceLoader::loadWith()`, deprecated second parameter of `stubbles\values\ResourceLoader::load()`, will be removed with 10.0.0

## 9.1.2 (2020-02-28)

* fixed path issues under Windows with `stubbles\values\Rootpath` and `stubbles\values\ResourceLoader`

## 9.1.1 (2019-12-16)

* fixed too wide return type hint of `stubbles\values\Properties::section()`, returns `array<string,string>` only

## 9.1.0 (2019-12-10)

* added more phpstan related type hints

## 9.0.1 (2019-10-29)

* fixed `stubbles\values\Parse::toBool()`  now returns `false` when given value is `null` according to description

## 9.0.0 (2019-10-29)

* removed `stubbles\values\Secret::BACKING_MCRYPT`, deprecated since 8.0.0
* raised minimum required PHP version to 7.3
* fixed various possible bugs due to incorrect type usage

## 8.1.0 (2016-07-30)

* added `stubbles\values\Value::isNull()`
* added `stubbles\values\Value::isEmpty()`
* added possibility to enforce strict type check in `stubbles\values\Value::isOneOf()`
* added possibility to use a different character as separator for list values in `stubbles\values\Parse::toList()` and `stubbles\values\Parse::asList()`
* added `stubbles\values\Rootpath::default()`

## 8.0.0 (2016-07-11)

### BC breaks

* raised minimum required PHP version to 7.0.0
* introduced scalar type hints and strict type checking
* `stubbles\values\Value::containsAnyOf()` now evaluates to `false` if it is checked that a number is contained in another number, except the value is a string
* deprecated `stubbles\values\Secret::BACKING_MCRYPT`, will be removed with 9.0.0
  * use `stubbles\values\Secret::BACKING_OPENSSL`
  * default backing changed to `stubbles\values\Secret::BACKING_OPENSSL`

### Other changes

* `stubbles\values\Secret::create()` now swallows `\Error` as well

## 7.2.1 (2016-07-06)

* explicity require ext-iconv now in composer.json

## 7.2.0 (2016-06-08)

* added `stubbles\values\values()`
* added `stubbles\values\Value`

## 7.1.0 (2016-02-21)

* added `stubbles\values\pattern()`
* added `stubbles\values\Rootpath`
* added `stubbles\values\ResourceLoader`

## 7.0.0 (2016-01-11)

* split off from [stubbles/core](https://github.com/stubbles/stubbles-core)
