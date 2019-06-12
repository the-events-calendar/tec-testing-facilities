# Modern Tribe testing facilities

A set of testing helpers, add-ons and gimmicks to make Modern Tribe Products and Agency testing easier.

## Requirements
The library requires PHP 7.0 or above.  
While most of our code is compatible with PHP 5.6 or above the **test** code will run on PHP 7.0 or above CI environments.

## Installation
Use [Composer](https://getcomposer.org/) to require the library in your project:

```bash
composer require --dev moderntribe/tribe-testing-facilities
```

## What's inside?
Read more [in the documentation](docs/index.md).

### Codeception
[Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") is our testing framework of choice in most cases; along with it [wp-browser](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub") to get some WordPress specific helpers.  

#### Extensions
* `Tribe\Test\Codeception\Extensions\FunctioMocker` - an extension to wrap [function-mocker](https://github.com/lucatume/function-mocker) initialization in a Codeception-compatible manner.
