<?php
/**
 * The base test case to test a View in its full render.
 *
 * @since   TBD
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */

namespace Tribe\Test\Products\WPBrowser\Views\V2;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\Traits\With_Event_Data_Fetching;

/**
 * Class ViewTestCase
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */
class ViewTestCase extends TestCase {

	use With_Post_Remapping;
	use With_Event_Data_Fetching;

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
	 * Sets up the View test context mocking some commonly used functions and setting up the code to filter some time,
	 * or date, dependant values to keep the snapshots consistent across time.
	 */
	public function setUp() {
		parent::setUp();

		// Start Function Mocker.
		Test::setUp();

		// Mock calls to the date function to return a fixed value when getting the current date.
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

		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );

		// Let's make sure we can create as many recurring events as we want.
		$return_int_max = static function () {
			return PHP_INT_MAX;
		};
		add_filter( 'tribe_events_pro_recurrence_small_batch_size', $return_int_max );
		add_filter( 'tribe_events_pro_recurrence_batch_size', $return_int_max );
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
	public function tearDown() {
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
}

