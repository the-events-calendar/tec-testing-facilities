<?php
/**
 * Provides methods to fetch information about events directly from the database.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

/**
 * Class With_Event_Data_Fetching
 * @package Tribe\Test\Products\Traits
 */
trait With_Event_Data_Fetching {

	/**
	 * Fetches directly from the database, skipping the caching layer, the dates for a group of post IDs.
	 *
	 * Results will be ordered by the same order in which the post IDs are entered!
	 *
	 * @param array              $post_ids  The IDs of the posts to read the date meta values for.
	 *
	 * @param string|array|false $meta_key  `false` to fetch all 4 date values, a string, or an array of strings, to
	 *                                      fetch only specific meta values.
	 *
	 * @return array An array in the shape `[<post_id> => ['_EventStartDate' => <date>, ...]]`.
	 */
	protected function fetch_event_dates_from_db( array $post_ids, $meta_key = false ) {
		global $wpdb;
		$ids = implode( ',', array_map( 'absint', $post_ids ) );

		$results = $wpdb->get_results( "select post_id, meta_key, meta_value 
				from {$wpdb->postmeta} 
				where post_id in ({$ids}) 
			  	and meta_key regexp '^_Event(Start|End)Date(UTC)*$'
			  	order by field (post_id, {$ids})"
			, ARRAY_A );

		$whitelist = [ '_EventStartDate', '_EventStartDateUTC', '_EventEndDate', '_EventEndDateUTC' ];

		if ( ! empty( $meta_key ) ) {
			$meta_keys = (array) $meta_key;
			if ( count( array_diff( $meta_keys, $whitelist ) ) ) {
				throw new \InvalidArgumentException(
					'Only the following meta keys are supported: ' . json_encode( $whitelist )
				);
			}
			$whitelist = array_intersect( $whitelist, $meta_keys );
		}


		$dates = array_reduce( $results, static function ( array $acc, array $result ) use ( $whitelist ) {
			if ( ! in_array( $result['meta_key'], $whitelist, true ) ) {
				return $acc;
			}
			$acc[ $result['post_id'] ][ $result['meta_key'] ] = $result['meta_value'];

			return $acc;
		}, [] );

		return $dates;
	}
}