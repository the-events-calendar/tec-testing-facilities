<?php
/**
 * Renders an ASCII version of the calendar.
 *
 * @package Tribe\Test\Products\Utils
 */

namespace Tribe\Test\Products\Utils;

use Tribe__Date_Utils as Dates;

/**
 * Class Ascii_Calendar
 *
 * @package Tribe\Test\Products\Utils
 */
class Ascii_Calendar {

	/**
	 * The calendar start day.
	 *
	 * @var \DateTime
	 */
	protected $start_day;

	/**
	 * The calendar end day.
	 *
	 * @var \DateTime
	 */
	protected $end_day;
	/**
	 * A map of events by day, days are in the `Y-m-d` format, events are either event IDs or post objects.
	 *
	 * @var array<string,array<int|\WP_Post>>
	 */
	protected $events_by_day;
	/**
	 * The length, in days, of a week.
	 * The term "week" here is an abuse of notations: weeks can be as short as 1 day (Day View) or as long as required.
	 *
	 * @var int
	 */
	protected $week_size = 7;
	/**
	 * The post field, or callable, to return a single element (an event) representation.
	 *
	 * @var callable|string
	 */
	protected $element_representation = 'ID';

	/**
	 * The string that will represent an empty element in the calendar.
	 * Not used if the element representation is a user-defined callback.
	 *
	 * @var string
	 */
	protected $empty_element_representation = '';

	/**
	 * The horizontal width of each calendar cell.
	 * @var int
	 */
	protected $cell_width = 5;

	/**
	 * Ascii_Calendar constructor.
	 *
	 * @param       $start_day
	 * @param       $end_day
	 * @param array $events_by_day
	 * @param int   $week_size
	 */
	public function __construct( $start_day, $end_day, array $events_by_day = [], $week_size = 7 ) {
		if ( false === $this->start_day = Dates::build_date_object( $start_day ) ) {
			throw new \InvalidArgumentException( 'Start day is not valid.' );
		}

		if ( false === $this->end_day = Dates::build_date_object( $end_day ) ) {
			throw new \InvalidArgumentException( 'Start day is not valid.' );
		}

		$one_day = Dates::interval( 'P1D' );
		// Cope with non inclusivness of DatePeriod and add one day.
		$period_end = clone $this->end_day;
		$period_end->add( $one_day );
		$period         = new \DatePeriod( $this->start_day, $one_day, $period_end );
		$all_empty_days = [];
		/** @var \DateTime $day */
		foreach ( $period as $day ) {
			$all_empty_days[ $day->format( 'Y-m-d' ) ] = [];
		}

		$this->events_by_day = array_merge( $all_empty_days, $events_by_day );

		if ( ( $this->week_size = absint( $week_size ) ) < 1 ) {
			throw new \InvalidArgumentException( 'Week size should be an integer value gt 1.' );
		}
	}

	/**
	 * Renders the calendar.
	 *
	 * @param int             $cell_width             The width of each calendar cell.
	 * @param string|callable $element_representation The post field to display for each event on the calendar, this
	 *                                                should be a post field or a callable to fine control the output;
	 *                                                the callback will receive the event ID or post object as input.
	 *
	 * @return string The calendar, as a long string.
	 */
	public function render( $cell_width = 5, $element_representation = 'ID' ) {
		$this->cell_width             = $cell_width;
		$this->element_representation = $element_representation;
		$week_chunks                  = array_chunk( $this->events_by_day, $this->week_size, true );
		$calendar_header_row          = $this->build_calendar_header_row( array_keys( $week_chunks[0] ) );
		$raw_weeks                    = $this->build_raw_weeks( $week_chunks );

		$rendered_weeks = array_map( [ $this, 'render_week' ], $raw_weeks );

		$weeks = implode( "\n", $rendered_weeks );

		$first_header_row = implode( ' | ', $calendar_header_row ) . '|';
		$closing_line     = str_repeat( '------', $this->week_size );
		$ascii_calendar   = $first_header_row . "\n" . $weeks . "\n" . $closing_line;

		return $ascii_calendar;
	}

	/**
	 * Builds the calendar header row, the one that contains each day name.
	 *
	 * @param array<string,array> $week A calendar week, usually the first.
	 *
	 * @return array<string> The calendar header row, each entry a day 3-letter name.
	 */
	protected function build_calendar_header_row( array $week ) {
		$header_row = [];

		foreach ( $week as $day_date ) {
			$date_time = Dates::build_date_object( $day_date );
			// 3-letter representation, e.g. "Sun".
			$day_name = $date_time->format( 'D' );

			$header_row[] = $day_name;
		}

		return $header_row;
	}

	/**
	 * From the week chunks build each week in a vertically unaligned and not padded format.
	 *
	 * @param array<array<string|array>> $week_chunks Each weeek "chunk", each chunk as long as the week size.
	 *
	 * @return array<array<string|array>> The same chunks, filled with events.
	 */
	protected function build_raw_weeks( array $week_chunks ) {
		$week_rows = [];

		foreach ( $week_chunks as $week ) {
			$week_key               = array_keys( $week )[0];
			$week_rows[ $week_key ] = [];

			foreach ( $week as $day_date => $events ) {
				// 3-letter representation.
				$date_time = Dates::build_date_object( $day_date );
				$day_num   = $date_time->format( 'd' );

				$week_rows[ $week_key ][ $day_num ] = $this->events_by_day[ $day_date ];
			}
		}

		return $week_rows;
	}

	/**
	 * Renders a single calendar week, as long as the week size, with events vertically sorted to keep them aligned
	 * across days.
	 *
	 * @param array<string,array<int|\WP_Post>> $raw_week The "raw" week, unsorted and not vertically aligned.
	 *
	 * @return string The rendered week.
	 */
	public function render_week( $raw_week ) {
		$week_height    = $this->calculate_week_max_height( $raw_week );
		$week_positions = $this->build_week_positions( $raw_week );

		// Move events up and down each day in the week to keep them aligned.
		$vertically_sorted_week = $this->vertically_sort_events( $raw_week, $week_positions, $week_height );
		$padded_sorted_weeks    = $this->pad_weeks( $vertically_sorted_week, $week_height );

		$result_header = str_repeat( '______', count( $raw_week ) );
		$result_header .= "\n" . implode( '|', array_map( [ $this, 'fit' ], array_keys( $raw_week ) ) );
		$result_header .= "\n" . str_repeat( '------', count( $raw_week ) );

		return $result_header . "\n" . implode( "\n", $padded_sorted_weeks );
	}

	/**
	 * Calculates the week max height.
	 *
	 * @param array<string,array<int|\WP_Post>> $raw_week The "raw" week, unsorted and not vertically aligned.
	 *
	 * @return int The week max height, usually the height of the highest day stack in the week.
	 */
	protected function calculate_week_max_height( $raw_week ) {
		return max( ...array_values( array_map( 'count', $raw_week ) ) );
	}

	/**
	 * Parses a raw week to fill an array defining each event vertical position in the week.
	 *
	 * This method is the one that will make it so that events will be aligned across days in the week.
	 *
	 * @param array<string,array<int|\WP_Post>> $raw_week The "raw" week, unsorted and not vertically aligned.
	 *
	 * @return array<int,int> A map of each event ID to its vertical position in the context of the week.
	 */
	protected function build_week_positions( array $raw_week ) {
		$week_positions = [];

		array_walk( $raw_week, static function ( $week_line ) use ( &$week_positions ) {
			// Flip to have an ID to Vertical Position in Week map, we assume events are already sorted in the day.
			$new_positions = array_flip( $week_line );
			// Any previously set position stand, add the new ones for events we've never seen.
			$week_positions = array_replace( $new_positions, $week_positions );
		} );

		return $week_positions;
	}

	/**
	 * Provided each event vertical position in the week, sort the events in each day week to make them align across
	 * days.
	 *
	 * This vertical ordering will leave "gaps" in the week days when events that span multiple days are "pushed down"
	 * to align with their positions on the previous days.
	 *
	 * @param array<string,array<int|\WP_Post>> $raw_week       The "raw" week, unsorted and not vertically aligned.
	 * @param array<int,int>                    $week_positions A map of each event ID to its vertical position in the
	 *                                                          context of the week.
	 * @param int                               $week_height    The week height.
	 *
	 * @return array<string,array> A map of each week day (`Y-m-d` format) to its vertically ordered events IDs.
	 */
	protected function vertically_sort_events( array $raw_week, array $week_positions, $week_height ) {
		array_walk( $raw_week, static function ( &$week_line ) use ( $week_positions, $week_height ) {
			$empty_day_column = array_fill( 0, $week_height, ' ' );
			$the_day_ids      = array_intersect_key( $week_positions, array_flip( $week_line ) );
			$week_line        = array_replace( $empty_day_column, array_flip( $the_day_ids ) );
			ksort( $week_line );
		} );

		return $raw_week;
	}

	/**
	 * Pads each week to make sure each week is as high as its highest day stack or, at a minimun, one.
	 *
	 * @param array<string,array<int|\WP_Post>> $unpadded_weeks The variable height weeks.
	 * @param int                               $week_height    The week height.
	 *
	 * @return array<string,array<int|\WP_Post>> The padded weeks, unsorted and not vertically aligned.
	 */
	protected function pad_weeks( $unpadded_weeks, $week_height ) {
		$result = [];

		// Build the week line, at a min add 1 line.
		foreach ( range( 0, max( 0, $week_height - 1 ) ) as $i ) {
			$week_line = array_replace(
				array_fill( 0, $this->week_size, ' ' ),
				array_column( $unpadded_weeks, $i )
			);
			$result[]  = implode( '|', array_map( [ $this, 'fit_element' ], $week_line ) );
		}

		return $result;
	}

	/**
	 * Fits an element representation.
	 *
	 * @param int|\WP_Post $post The post ID or object.
	 *
	 * @return string The fitted element representation.
	 */
	protected function fit_element( $post ) {
		$representation = $this->get_element_representation( $post );

		return $this->fit( $representation );
	}

	/**
	 * Returns the representation of each calendar element.
	 *
	 * This method does not grant absolute control on the output as elements will still be cut, or padded, to fit.
	 *
	 * @param int|\WP_Post $post The event post ID or object.
	 *
	 * @return string The string representation of the element.
	 */
	protected function get_element_representation( $post ) {
		if ( ! is_callable( $this->element_representation ) ) {
			$post = get_post( $post );
			if ( ! $post instanceof \WP_Post ) {
				return $this->empty_element_representation;
			}

			return $post->{$this->element_representation};
		}

		return call_user_func( $this->element_representation, $post );
	}

	/**
	 * Pads, or cuts, the input string to the cell width.
	 *
	 * @param string $input The string to pad, or cut, to length.
	 *
	 * @return string The padded or cut string.
	 */
	protected function fit( $input ) {
		$length       = $this->cell_width;
		$input_length = strlen( $input );

		if ( $input_length > $length ) {
			return substr( $input, 0, $length - 1 ) . '…';
		}

		return str_pad( $input, $length, ' ', STR_PAD_BOTH );
	}
}
