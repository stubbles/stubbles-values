8.1.0 (2016-07-??)
------------------

  * added `stubbles\values\Value::isNull()`
  * added `stubbles\values\Value::isEmpty()`
  * added possibility to enforce strict type check in `stubbles\values\Value::isOneOf()`
  * added possibility to use a different character as separator for list values in `stubbles\values\Parse::toList()` and `stubbles\values\Parse::asList()`


8.0.0 (2016-07-11)
------------------

### BC breaks

  * raised minimum required PHP version to 7.0.0
  * introduced scalar type hints and strict type checking
  * `stubbles\values\Value::containsAnyOf()` now evaluates to `false` if it is checked that a number is contained in another number, except the value is a string
  * deprecated `stubbles\values\Secret::BACKING_MCRYPT`, will be removed with 9.0.0
    * use `stubbles\values\Secret::BACKING_OPENSSL`
    * default backing changed to `stubbles\values\Secret::BACKING_OPENSSL`

  ### Other changes

    * `stubbles\values\Secret::create()` now swallows `\Error` as well


7.2.1 (2016-07-06)
------------------

  * explicity require ext-iconv now in composer.json


7.2.0 (2016-06-08)
-----------------

  * added `stubbles\values\values()`
  * added `stubbles\values\Value`


7.1.0 (2016-02-21)
------------------

  * added `stubbles\values\pattern()`
  * added `stubbles\values\Rootpath`
  * added `stubbles\values\ResourceLoader`


7.0.0 (2016-01-11)
------------------

  * split off from [stubbles/core](https://github.com/stubbles/stubbles-core)
