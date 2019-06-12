# Running WordPress using the built-in PHP Server
If you need to quickly spin up a portable WordPress installation you can use PHP built-in server to test your code.

## Installation
1. place this library (`tribe-testing-facilities`) as a sibling of the plugin(s) folder.  
2. run `composer update` from this library root folder
3. run `composer run wp-install`; this will configure and install WordPress in this library `vendor/wordpress/wordpress` folder and symbolically link the folder containing the library in the `plugins` folder.

It's better explained with an example:
```
\ Repos
     |
     \ the-events-calendar
     \ tribe-testing-facilities
```

In the example above the `Repos` folder will become the installation `plugins` folder.

Finally start the server with `composer run wp-server-start`.