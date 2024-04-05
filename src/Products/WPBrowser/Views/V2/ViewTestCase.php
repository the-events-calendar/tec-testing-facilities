<?php
/**
 * The base test case to test a View in its full render.
 *
 * @since   TBD
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */

namespace Tribe\Test\Products\WPBrowser\Views\V2;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\Template\Settings\Advanced_Display;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\Traits\With_Caches_Reset;
use Tribe\Test\Products\Traits\With_Context;
use Tribe\Test\Products\Traits\With_Event_Data_Fetching;

/**
 * Class ViewTestCase
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */
class ViewTestCase extends TestCase {

	use With_Post_Remapping;
	use With_Event_Data_Fetching;
	use With_Context;
	use With_Caches_Reset;

	/**
	 * In the `reset_post_dates` methods all date-related post fields will be set to this value.
	 *
	 * @var string
	 */
	protected $mock_post_date = '2019-01-01 09:00:00';

	/**
	 * When mocking the `date` function this is the value that will be used to generate the date in place of the real
	 * one.
	 *
	 * @var string
	 */
	protected $mock_date_value = '2019-01-01 09:00:00';

	/**
	 * Today date, unmocked, in the `Y-m-d` format.
	 *
	 * @var string
	 */
	protected $today_date;

	/**
	 * Sets up the View test context mocking some commonly used functions and setting up the code to filter some time,
	 * or date, dependant values to keep the snapshots consistent across time.
	 */
	public function setUp(): void {
		parent::setUp();

		// Start Function Mocker.
		Test::setUp();

		// phpcs:ignore
		$this->today_date = date( 'Y-m-d' );

		// Mock calls to the `date` function to return a fixed value when getting the current date.
		Test::replace(
			'date',
			function ( $format, $date = null ) {
				$date = $date ?? $this->mock_date_value;

				if ( \Tribe__Date_Utils::is_timestamp( $date ) ) {
					$date = '@' . $date;
				}

				$date_time = new \DateTime( $date, new \DateTimeZone( 'UTC' ) );

				return $date_time->format( $format );
			}
		);

		// Mock calls to the `time` function too to make sure "now" timestamp is a controlled value.
		Test::replace(
			'time',
			function () {
				return ( new \DateTime( $this->mock_date_value, new \DateTimeZone( 'UTC' ) ) )->getTimestamp();
			}
		);

		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );

		// Let's make sure we can create as many recurring events as we want.
		$return_int_max = static function () {
			return PHP_INT_MAX;
		};
		add_filter( 'tribe_events_pro_recurrence_small_batch_size', $return_int_max );
		add_filter( 'tribe_events_pro_recurrence_batch_size', $return_int_max );

		$this->start_checking_template_vars();
	}

	/**
	 * Start filtering the Views template vars to ensure none of the variables contains today's date.
	 *
	 * If a View template variable contains today's date, then it's a sign that it's not correctly mocked.
	 */
	protected function start_checking_template_vars() {
		$this->date_dependent_template_vars = [];
		add_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'collect_date_dependent_values' ] );
	}

	/**
	 * Suspend, either until the next test or until a call to the `start_checking_template_vars` method, the
	 * check on Views template variables to ensure none contains today's date.
	 *
	 * @param string $reason The reason why the check is being suspended. This message is not used in the code,
	 *                       but is meant to force developers to explain themselves when suspending this check.
	 */
	protected function suspend_template_vars_check( $reason ) {
		remove_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'collect_date_dependent_values' ] );
	}


	/**
	 * Sets the date/time-dependant fields of a post to a fixed value.
	 *
	 * @param \WP_Post $post The post opbject to modify.
	 *
	 * @return \WP_Post The modified post object.
	 */
	public function reset_post_dates( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return $post;
		}

		foreach ( [ 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ] as $field ) {
			$post->{$field} = $this->mock_post_date;
		}

		return $post;
	}

	/**
	 * Tears down the View test context having care to reset function mocks too.
	 */
	public function tearDown(): void {
		Test::tearDown();
		parent::tearDown();
	}

	/**
	 * Sets the date/time-dependant fields of an array of posts to a fixed value.
	 *
	 * @param array $posts An array of `\WP_Posts` to reset the dates for.
	 *
	 * @return array The modified posts object.
	 */
	public function reset_posts_dates( array $posts ) {
		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return $posts;
		}
		array_walk( $posts, [ $this, 'reset_post_dates' ] );

		return $posts;
	}

	/**
	 * Alters the global context and returns a version of it mocking date and time dependent values.
	 *
	 * By default the context will be altered to set the `posts_per_page` to 20, and `today`, `now` and `event_date` to
	 * the test case `mock_date_value` property value.
	 *
	 * @param array $overrides An associative array of overrides that should be used to alter the context.
	 *
	 * @return \Tribe__Context An altered clone of the global context.
	 */
	protected function get_mock_context( array $overrides = [] ): \Tribe__Context {
		return tribe_context()->alter(
			wp_parse_args(
				$overrides,
				[
					'today'          => $this->mock_date_value,
					'now'            => $this->mock_date_value,
					// Set the default start of week to Sunday, Saturday is `6`.
					'start_of_week'  => 0,
					'event_date'     => $this->mock_date_value,
					'posts_per_page' => 20,
				]
			)
		);
	}

	/**
	 * Collects, while the View template vars are set up, any value that contains today date
	 * to spot unmocked, date-dependent, template vars.
	 *
	 * @param array $template_vars An array of template variables, as set up from the View and
	 *                             and filtering functions.
	 */
	public function collect_date_dependent_values( $template_vars ) {
		$date_dependant = array_filter(
			$template_vars,
			function ( $v ) {
				// The pretty print will print each value on diff. lines.
				$encoded = json_encode( $v, JSON_PRETTY_PRINT );

				// Ignore the `post_date` and `post_modified` fields.
				$encoded = implode(
					PHP_EOL,
					array_filter(
						explode( PHP_EOL, $encoded ),
						static function ( $line ) {
							return ! preg_match( '/post_(date|modified)(_gmt)*/', $line );
						}
					)
				);

				return false !== strpos( $encoded, $this->today_date );
			}
		);

		$this->assertEmpty(
			$date_dependant,
			sprintf(
				"Date-dependent template vars found matching today date: all dates should be mocked!\n%s",
				json_encode( $date_dependant, JSON_PRETTY_PRINT )
			)
		);

		return $template_vars;
	}
}
