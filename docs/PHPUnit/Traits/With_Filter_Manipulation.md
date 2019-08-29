# With_Filter_Manipulation Trait

Manipulate WordPress filters, and actions, in test casees.

## Requirements
The trait uses WordPress functions and systems, WordPress should be loaded in the context of the test to use this trait successfully.

## Usage

### suspending_filter_do

You might need to test code that is hooked on the `shutdown` action.  

```php
<?php
function maybe_bacon(){
    isset( $_REQUEST['bacon'] ) && update_option( 'bacon_requested', true );
}

add_action( 'shutdown', 'maybe_bacon' );
```

The `shutdown` action is one of WordPress cornerstone actions and there's quite some logic already attached to it, from WordPress and from our code.  
Due to this, doing the `shutdown` action in a test case is a bad idea that will generate output errors, and possibly `die` calls.  

```php
<?php
class Bacon_Test extends WPTestCase{
    public function test_bacon_request(){
        $_REQUEST['bacon']  = true;
                
        do_action( 'shutdown' );
        
        $this->assertTrue( get_option( 'bacon_requested', false ) );
    }
}
```

The `With_Filter_Manipulation::suspending_filter_do` method provides a solution by temporarily un-hooking all filters from a filter and re-hooking them after the test.  
The test code above can be rewritten in a safer way with it:

```php
<?php
class Bacon_Test extends WPTestCase{
    use  Tribe\Test\PHPUnit\Traits\With_Filter_Manipulation;

    public function test_bacon_request(){
        $_REQUEST['bacon']  = true;
                    
        $this->suspending_filter_do( 'shutdown', function(){
            do_action( 'shutdown' );

            $this->assertTrue( get_option( 'bacon_requested', false ) );
        } );
    }
}
```
