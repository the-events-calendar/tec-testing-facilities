<?php
/**
 * Provides methods to setup and handle View-related settings and context in tests.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use tad\WPBrowser\Exceptions\WpCliException;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Template__Month as Month;
use Tribe__Utils__Array as Arr;

/**
 * Trait With_View_Settings
 *
 * @package Tribe\Test\Products\Traits
 *
 * @property array<string,\WP_Post> $events An array of events generated during the `setup_context` method.
 */
trait With_View_Context {
	/**
	 * Sets up the context, intended in the broader sense as both the Context object and the set of options and other
	 * sources of data the View test will require.
	 *
	 * @param array<string,mixed> $alterations An array of alterations to apport to the context.
	 *                                         Some keys are processed and removed (`options`, `tribe_options`,
	 *                                         `events`). Those left are set on the returned Context object.
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

		unset( $alterations['tribe_options'] );
		if ( ! empty( $alterations['tribe_options'] ) ) {
			foreach ( $alterations['tribe_options'] as $option_name => $option_value ) {
				tribe_update_option( $option_name, $option_value );
			}
		}
		unset( $alterations['tribe_options'] );

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
	 * Builds a "legend" of each event, by post ID.
	 *
	 * @return string The event legend.
	 * @todo move this to the Trait.
	 *
	 * The legend should be used to provide more information during debug of failures.
	 */
	protected function build_event_legend() {
		return "Event legend:\n\n" . implode(
			"\n",
			array_map(
				static function ( $event ) {
					return sprintf(
						'%d - tz: %s; start: %s; end: %s; all-day: %s',
						$event->ID,
						get_post_meta( $event->ID, '_EventTimezone', true ),
						get_post_meta( $event->ID, '_EventStartDate', true ),
						get_post_meta( $event->ID, '_EventEndDate', true ),
						get_post_meta( $event->ID, '_EventAllDay', true ) ? 'yes' : 'no'
					);
				},
				$this->events
			)
		) . "\n";
	}

	/**
	 * Parses the expected entry of the data provider to build an array of expectations that allow referring to the
	 * events by name, rather than by post ID.
	 *
	 * @param array<string,array> $expected An map of the expectations.
	 *
	 * @return array The expectations, in a `list` compatible format.
	 */
	protected function parse_expected_events( array $expected ) {
		$expected_events = array_combine(
			array_keys( $expected['events'] ),
			array_map(
				function ( array $event_names ) {
					$event_ids = [];
					foreach ( $event_names as $event_name ) {
						$event_ids[] = ( $this->events[ $event_name ] )->ID;
					}

					return $event_ids;
				},
				$expected['events']
			)
		);

		$expected_stack = array_combine(
			array_keys( $expected['stack'] ),
			array_map(
				function ( array $event_names ) {
					$event_ids = [];
					foreach ( $event_names as $event_name ) {
						// Take stack spacers into account.
						$event_ids[] = null !== $event_name ?
							( $this->events[ $event_name ] )->ID
							: null;
					}

					return $event_ids;
				},
				$expected['stack']
			)
		);

		return [ $expected_events, $expected_stack ];
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
