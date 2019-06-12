# Composer Scripts
The library comes with a collection of example [Composer](https://getcomposer.org/) scripts you can use in your projects or use as a starting example.

## code-sniff
Runs PHP Code Sniffer on the `src` and `tests` folder, according to the rules defined the `cs-ruleset.xml` file.

## code-fix
Runs PHP Code Sniffer Fixer on the `src` and `tests` folder, according to the rules defined the `cs-ruleset.xml` file.

## wp-install
Installs and configures WordPress in the `vendor/wordpres/wordpress` folder, the current folder parent is assumed to contain plugins.

## wp-empty
Empties the WordPress database of any post and uploads.

## wp-db-dump
Dumps the current WordPress database contents in the `tests/_data/dump.sql` file; useful to generate a database testing fixture after its manual setup.

## wp-server-start
Starts the built-in PHP server to run WordPress on the port specified in the `.env.testing` file.  
The server is started in the background.

## wp-server-stop
Stops the built-in PHP server that is running WordPress in background.

## php-logs
Opens the PHP error log.

## test
Runs the test suites, one by one, with [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.").