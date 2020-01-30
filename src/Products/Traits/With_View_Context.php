<?php
/**
 * Provides methods to setup and handle View-related settings and context in tests.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use Tribe__Utils__Array as Arr;

/**
 * Trait With_View_Settings
 *
 * @package Tribe\Test\Products\Traits
 *
 * @property array<\WP_Post> $events An array of events generated during the `setup_context` method.
 */
trait With_View_Context {
	/**
	 * Sets up the context, intended in the broader sense as both the Context object and the set of options and other
	 * sources of data the View test will require.
	 *
	 * @param array<string,mixed> $alterations An array of alterations to apport to the context.
	 *                                         Some keys are processed and removed (`options`, `events`).
	 *                                         Those left are set on the returned Context object.
	 *
	 * @return \Tribe__Context The context as altered per the alterations.
	 *
	 * @throws \Tribe__Repository__Usage_Error If there are events to create and there are issue with it.
	 */
	protected function setup_context( array $alterations = [] ) {
		if ( ! empty( $alterations['options'] ) ) {
			foreach ( $alterations['options'] as $option_name => $option_value ) {
				update_option( $option_name, $option_value );
			}
		}
		unset( $alterations['options'] );

		$this->events = [];
		if ( ! empty( $alterations['events'] ) ) {
			foreach ( $alterations['events'] as $key => $event_data ) {
				$this->events[ $key ] = tribe_events()->set_args( $event_data )->create();
			}
		}
		unset( $alterations['events'] );

		// Sanity check.
		$this->assertContainsOnlyInstancesOf( \WP_Post::class, $this->events );

		return tribe_context()->alter( $alterations );
	}

	/**
	 * Ensures an alteration key, or keys, is set.
	 *
	 * @param string               $method      The method making the check.
	 * @param string|array<string> $keys        Either a list of keys to check, nested, or a string in the format
	 *                                          `foo.bar.baz`.
	 * @param array<string,mixed>  $alterations The alterations to check.
	 *
	 * @throws \InvalidArgumentException If a required alteration entry is missing.
	 */
	protected function ensure_alteration( $method, $keys, array $alterations = [] ) {
		if ( '__not_found__' === Arr::get( $alterations, Arr::list_to_array( $keys, '.' ), '__not_found__' ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Required alteration key "%s" is missing for call to "%s"',
					Arr::to_list( $keys, '.' ),
					$method
				)
			);
		}
	}
}
