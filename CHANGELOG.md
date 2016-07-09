8.0.0 (2016-??-??)
------------------

### BC breaks

  * raised minimum required PHP version to 7.0.0
  * introduced scalar type hints and strict type checking
  * `stubbles\values\Value::containsAnyOf()` now evaluates to `false` if it is checked that a number is contained in another number, except the value is a string


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
