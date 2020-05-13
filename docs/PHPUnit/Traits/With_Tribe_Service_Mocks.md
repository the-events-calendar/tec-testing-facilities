# With_Tribe_Service_Mocks Trait

Safely replace services in the `tribe()` service locators and restore them in tests.

## What is this for?

In most of our code we use a `tribe( $service )` function that is, in fact, an implementation of the Service Locator pattern.  

While convenient and easy to use this might prove challenging to test; this trait purpose is to ease the pain of testing code that relies on the service locator and to do so without side-effects (e.g. altering the service locator permanently).

## Example

In one of our plugins we have this code:

```php
<?php
tribe_register( 'service.one', new Service_One() );
tribe_singleton( Service_Two::class, Service_Two::class );
tribe_singleton( 'service.three', static function(){
    return new Service_Three;
} );
```

All the bindings listed above are legitimate and working, but each, in its own way, would pose a challenge in tests.  
Without indulging in the service locator implementation details, the first one is a singleton binding in disguise, the second is an identity singleton, the third is a pretty Dependency Injection late instantiation implementation.  

All those can be safely mocked in tests using the trait:

```php
<?php
use Tribe\Test\PHPUnit\Traits\With_Tribe_Service_Mocks;

class Class_Depending_On_Service_Locator{

    public function go(){
        return tribe( 'service.one' )->should_load()
            && tribe( Service_Two::class )->is_ok()
            && tribe('service.three')->all_works();
    }

}


class SomeTest extends \Codeception\TestCase\WPTestCase
{
	use With_Tribe_Service_Mocks;
	public function test_code_depending_on_service_locator() {
        // Build the mocks using Codeception Stubs.
		$mock_service_one = $this->makeEmpty( Service_One::class,[
			'should_load' => false,
		] );
		$mock_service_two = $this->makeEmpty( Service_Two::class,[
			'is_ok' => true,
		] );
		$mock_service_three = $this->makeEmpty( Service_Three::class,[
			'all_works' => true,
		] );

		$replacement_map=[
            'service.one'      => $mock_service_one,
            Service_Two::class => $mock_service_two,
            'service.three'    => $mock_service_three,
        ];

        $this->replacing_tribe_services( $replacement_map, function () {
            $instance = new Class_Depending_On_Service_Locator();
			$this->assertFalse( $instance->go() );
		} );
	}
}

```
