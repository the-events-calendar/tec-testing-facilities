<?php
/**
 * Provides methods to safely mock and restore services handled by the `tribe()` service locator.
 *
 * @since   TBD
 *
 * @package Tribe\Test\PHPUnit\Traits
 */

namespace Tribe\Test\PHPUnit\Traits;

use function tad\WPBrowser\readPrivateProperty;
use function tad\WPBrowser\setPrivateProperties;

/**
 * Trait With_Tribe_Service_Mocks
 *
 * @since   TBD
 *
 * @package Tribe\Test\PHPUnit\Traits
 */
trait With_Tribe_Service_Mocks {

	/**
	 * Runs a callback replacing a `tribe()` service with a mock instance.
	 *
	 * To backup the service locator, the singleton instance of the `Tribe__Container` class, the method will use
	 * Reflection.
	 * This is the only way we can avoid eager instantiation of services that might have side effects and correct
	 * handling of Closure implementations.
	 * The original service locator instance is restored after the test ran.
	 *
	 * @since  TBD
	 *
	 * @param array<string,mixed> $replacement_map The map defining the replacement for each service.
	 * @param callable            $do              The callback to call in the context of the altered service locator.
	 *
	 * @throws \ReflectionException If there's an issue reflecting on the service locator instance.
	 *
	 * @example
	 *         ```php
	 *         $mock = $this->makeEmpty( Some_Service::class, [ 'some_method' => 'foo' ] );
	 *         $this->replacing_tribe_service( [ 'service.slug' => $mock ], function() {
	 *              $this->assertEquals( 'foo', tribe( 'service.slug' )->some_method() );
	 *         } );
	 *         ```
	 */
	protected function replacing_tribe_services( array $replacement_map, callable $do ) {
		// Get the current singleton instance of the service locator.
		$locator_instance = readPrivateProperty( tribe(), 'instance' );

		// Use Reflection to get hold of the singleton service locator instance.
		$replacement_locator = clone $locator_instance;

		foreach ( $replacement_map as $service => $replacement ) {
			// Inject the replacement in the cloned service locator.
			$replacement_locator->singleton( $service, $replacement );
		}

		// Replace the service locator instance w/ a shallow clone.
		setPrivateProperties( tribe(), [ 'instance' => $replacement_locator ] );

		// Register the replacement as a singleton.
		tribe_singleton( $service, $replacement );

		// Run the method, the specified services will be replaced.
		try {
			$do();
		} catch ( \Exception $e ) {
			// Then throw.
			throw $e;
		} finally {
			// Restore the service locator original instance.
			setPrivateProperties( tribe(), [ 'instance' => $locator_instance ] );
		}
	}
}
