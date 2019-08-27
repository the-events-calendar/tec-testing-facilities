<?php
/**
 * Provides methods to manipulate WordPress filters during tests.
 *
 * @since   TBD
 *
 * @package Tribe\Test\PHPUnit\Traits
 */


namespace Tribe\Test\PHPUnit\Traits;

/**
 * Trait With_Filter_Manipulation
 *
 * @since   TBD
 *
 * @package Tribe\Test\PHPUnit\Traits
 */
trait With_Filter_Manipulation {

	/**
	 * Suspends a WordPress filter by backing up and re-attaching it after a custom code executed.
	 *
	 * @since TBD
	 *
	 * @param string   $filter   The filter, it can be an action too, to suspend.
	 * @param callable $callback The callback to call while the filter is suspended.
	 */
	protected function suspending_filter_do( $filter, callable $callback ) {
		global $wp_filter;
		$shutdown_backup      = $wp_filter[ $filter ];
		$wp_filter[ $filter ] = new \WP_Hook();

		$callback();

		$wp_filter[ $filter ] = $shutdown_backup;
	}
}
