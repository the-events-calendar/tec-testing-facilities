# Contributing
There are some guidelines you should follow to contribute code to this library.

## PHP version
To meet the common needs of Agency and Products you should really make an effort to stick to a maximum required PHP
version of PHP 7.0.
This limit is not set in stone, exceptions will apply.

## Naming things is hard, namespacing them is harder
Follow the "What tool? What is this?" principle when namespacing and naming things.
Some examples:
* a `BrokenSites` Codeception module Products will use to setup a specific fixture in Codeceptions tests: `Tribe\Test\Products\Codeception\Modules\BrokenSites`
* a `Stripe` mock factory Agency will use in PHPUnit tests: `Tribe\Test\Agency\PHPUnit\Factories\Stripe`
* a general-purpose, Codeception extension to init a test MailHog server: `Tribe\Test\Codeception\Extension\MailHog`

Try to make your code as general-purpose as possible.
We use `Snake_Case` for class names, and `snake_case` for methods.

## No functions, please
Functions are nice, useful and everyone loves them. But PHP will not autoload them.
To avoid front-loading them "just in case" or littering the code with curated `require` and `include` statements think long
and hard before adding functions to this package.
Static methods on classes will do; they will autoload and will work the same.
You will be able to cover your head in shame and regret for such an abuse of the language later.

## Coding styles
We follow WordPress coding standards; you should follow them too.
You can check your code with the following Composer script:
```bash
composer run code-sniff
```
And fix it with this:
```bash
composer run code-fix
```
