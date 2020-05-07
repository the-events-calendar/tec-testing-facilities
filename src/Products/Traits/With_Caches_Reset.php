<?php
/**
 * Provides methods to reset all caches that might alter a test execution.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use Tribe\Events\Views\V2\Template\Settings\Advanced_Display;
use Tribe__Context as Context;

/**
 * Trait With_Caches_Reset
 *
 * @package Tribe\Test\Products\Traits
 */
trait With_Caches_Reset {

	/**
	 * Flushes all the caches used by our plugins.
	 *
	 * Due to the eterogenous nature of our caching methods, the method contains a curated list of caching locations,
	 * methodologies and applications.
	 */
	protected function flush_all_caches() {
		// Ensure earliest and latest date, related to the creation of events, are reset.
		tribe_update_option( 'earliest_date', '' );
		tribe_update_option( 'latest_date', '' );

		// Let's make sure there are no left-over events between tests.
		tribe_events()->delete();

		/*
		 * During tests events created by diff. tests might have the same ID: this will apply date details settings
		 * previously cached to new events. This reset will ensure that's not the case.
		 */
		tribe_set_var( 'tribe_events_event_schedule_details', [] );
		tribe_set_var( 'tribe_get_start_date', [] );
		tribe_set_var( 'tribe_get_end_date', [] );
		tribe_set_var( 'tribe_events_get_the_excerpt', [] );

		// Ensure cached URLs are cleaned.
		tribe( 'events.rewrite' )->reset_caches();
		tribe( 'events.rewrite' )->setup();

		// Reset the JSON-LD cached data.
		tribe_cache()['json-ld-data'] = [];

		$this->reset_before_after_html_data();
	}

	/**
	 * This method ensures that before/after HTML data is correctly printed for each test.
	 *
	 * The data is, normally, printed once per request, but this works against tests that are, from WordPress
	 * perspective one long, single, request.
	 *
	 * @throws \RuntimeException If there's a problem reflecting on The Events Calendar Main class or setting the
	 *                           property value.
	 */
	protected function reset_before_after_html_data() {
		// Let's start by ensuring we're not adding any value to the before/after HTML using the option.
		tribe_update_option( Advanced_Display::$key_before_events_html, '' );
		tribe_update_option( Advanced_Display::$key_after_events_html, '' );

		/*
		 * The Events Calendar `Main::(before|after)_html_data_wrapper` will use a private property to know if it should
		 * print the before and after data or not. This means the data is printed on the first test that will trigger
		 * it and won't be printed for any later test. This creates differences in snapshots when generated alone (they
		 * will have the before/after data) or when generated in the context of a whole suite run. Since the before
		 * and after HTML is indeed part of the code we want to test in snapshots, here we use reflection to reset the
		 * (private) property so that the before/after HTML data will be printed on each test.
		 */
		$main = tribe( 'tec.main' );
		if ( property_exists( $main, 'show_data_wrapper' ) ) {
			try {
				$reflection_property = new \ReflectionProperty( $main, 'show_data_wrapper' );
				$reflection_property->setAccessible( true );
				$reflection_property->setValue(
					$main,
					[
						'before' => true,
						'after'  => true,
					]
				);
				$reflection_property->setAccessible( false );
			} catch ( \ReflectionException $e ) {
				$message = sprintf(
					'Error while trying to reset %s property in ViewTestCase: %s',
					'Tribe__Events__Main::show_data_wrapper',
					$e->getMessage()
				);
				throw new \RuntimeException( $message );
			}
		}
	}
}
