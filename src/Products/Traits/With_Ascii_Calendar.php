<?php
/**
 * Provides methods to render an ASCII calendar from a set of events per day.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use Tribe\Test\Products\Utils\Ascii_Calendar;

/**
 * Trait With_Ascii_Calendar
 *
 * @package Tribe\Test\Products\Traits
 */
trait With_Ascii_Calendar {

	/**
	 * Renders an ASCII calendar.
	 *
	 * @param string|\DateTimeInterface|int $start_day                     The start date.
	 * @param string|\DateTimeInterface|int $end_day                       The end date.
	 * @param array<string,array>           $events_by_day                 A complete, including all days, or partial
	 *                                                                     (w/ gaps), list of each day expected event
	 *                                                                     post IDs.
	 * @param int                           $week_size                     The size of the week to format the calendar
	 *                                                                     to.
	 *
	 * @param int                           $cell_width                    The width of each calendar cell.
	 * @param string|callable               $field_representation          The field that will represent each element
	 *                                                                     in  the calendar, either a post field or a
	 *                                                                     callable that will receive the event post as
	 *                                                                     input and should return a string.
	 *
	 * @return string The ASCII representation of the calendar.
	 */
	protected function render_ascii_calendar(
		$start_day,
		$end_day,
		array $events_by_day = [],
		$week_size = 7,
		$cell_width = 5,
		$field_representation = 'ID'
	) {
		$ascii_calendar = new Ascii_Calendar( $start_day, $end_day, $events_by_day, $week_size );

		return $ascii_calendar->render( $cell_width, $field_representation );
	}
}
