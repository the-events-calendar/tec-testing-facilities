# Modern Tribe testing facilities

A set of testing helpers, add-ons and gimmicks to make Modern Tribe Products and Agency testing easier.

## Requirements
The library requires PHP 7.0 or above.  
While most of our code is compatible with PHP 5.6 or above the **test** code will run on PHP 7.0 or above CI environments.

## Installation
Use [Composer](https://getcomposer.org/) to require the library in your project.  
Since this package is not on Packagist you'll need to define the package in your project `composer.json` file by adding an entry in the `repositories` section:

```json
"repositories": [
{
  "name": "moderntribe/tribe-testing-facilities",
  "type": "github",
  "url": "https://github.com/moderntribe/tribe-testing-facilities",
  "no-api": true
}
],
```

You'll now be able to pull in the library with the following command:

```bash
composer require --dev moderntribe/tribe-testing-facilities:dev-master
```

## What's inside?
Read more [in the documentation](docs/index.md).
