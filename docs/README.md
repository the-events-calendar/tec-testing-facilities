# The Events Calendar Testing Facilities Documentations

Here's a list of what's included in the library.

* Composer scripts
    * [code-sniff](Composer/Scripts.md#code-sniff)
    * [code-fix](Composer/Scripts.md#code-fix)
    * [wp-install](Composer/Scripts.md#wp-install)
    * [wp-empty](Composer/Scripts.md#wp-empty)
    * [wp-db-dump](Composer/Scripts.md#wp-db-dump)
    * [wp-server-start](Composer/Scripts.md#wp-server-start)
    * [wp-server-stop](Composer/Scripts.md#wp-server-stop)
    * [php-logs](Composer/Scripts.md#php-logs)
    * [test](Composer/Scripts.md#test)
* [Running WordPress with the PHP built-in server](Running_WordPress.md)
* Codeception
    * Extensions
        * [Function Mocker](Codeception/Extensions/Function_Mocker.md)
* Products
    * Traits
        * `With_Event_Data_Fetching`a - provides methods to fetch event information directly from the database.
    * WPBrowser
        * Views
            * V2 - a set of test cases and utils to support integration, or "WordPress unit", testing of Views v2
* PHPUnit
    * Traits
        * [With_Post_Remapping](PHPUnit/Traits/With_Post_Remapping.md)
        * [With_Filter_Manipulation](PHPUnit/Traits/With_Filter_Manipulation.md)
        * [With_Tribe_Service_Mocks](PHPUnit/Traits/With_Tribe_Service_Mocks.md)
