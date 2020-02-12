<?php
/**
 * Provides methods to render an ASCII calendar from a set of events per day.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use Tribe__Date_Utils as Dates;

/**
 * Trait With_Ascii_Calendar
 *
 * @package Tribe\Test\Products\Traits
 */
trait With_Ascii_Calendar {

	/**
	 * Renders an ASCII calendar.
	 *
	 * @param string|\DateTimeInterface|int $start_day     The start date.
	 * @param string|\DateTimeInterface|int $end_day       The end date.
	 * @param array<string,array>           $events_by_day A complete, including all days, or partial (w/ gaps), list
	 *                                                     of each day expected event post IDs.
	 * @param int                           $week_size     The size of the week to format the calendar to.
	 *
	 * @return string The ASCII representation of the calendar.
	 */
	protected function render_ascii_calendar( $start_day, $end_day, array $events_by_day = [], $week_size = 7 ) {
		$pad        = static function ( $input ) {
			if ( $input instanceof \WP_Post ) {
				$input = $input->ID;
			}

			return str_pad( $input, 5, ' ', STR_PAD_BOTH );
		};
		$all_days   = [];
		$header_row = [];
		$week_rows  = [];
		$one_day    = Dates::interval( 'P1D' );
		$period     = new \DatePeriod(
			Dates::build_date_object( $start_day ),
			$one_day,
			Dates::build_date_object( $end_day )->add( $one_day )
		);
		foreach ( $period as $day ) {
			$all_days[ $day->format( 'Y-m-d' ) ] = [];
		}
		$events_by_day = array_merge( $all_days, $events_by_day );
		foreach ( array_chunk( $events_by_day, $week_size, true ) as $week ) {
			$week_key               = array_keys( $week )[0];
			$week_rows[ $week_key ] = [];

			foreach ( $week as $day_date => $events ) {
				// 3-letter representation.
				$date_time = Dates::build_date_object( $day_date );
				$day_name  = $date_time->format( 'D' );
				$day_num   = $date_time->format( 'd' );

				if ( count( $header_row ) < $week_size ) {
					$header_row[] = $day_name;
				}
				$week_rows[ $week_key ][ $day_num ] = $events_by_day[ $day_date ];
			}
		}

		$weeks = implode(
			"\n",
			array_map(
				static function ( $week_row ) use ( $week_size, $pad ) {
					$week_height    = max( ...array_values( array_map( 'count', $week_row ) ) );
					$week_positions = [];
					$result         = [];
					// Build the week positions.
					array_walk( $week_row, static function ( $week_line ) use ( &$week_positions ) {
						// Ids > Positions
						$new_positions = array_flip( $week_line );
						// Prev Positions override new positions.
						$week_positions = array_replace( $new_positions, $week_positions );
					} );

					// Move events up and down each day in the week to keep them aligned.
					array_walk( $week_row, static function ( &$week_line ) use ( $week_positions, $week_height ) {
						$empty_day_column = array_fill( 0, $week_height, ' ' );
						$the_day_ids      = array_intersect_key( $week_positions, array_flip( $week_line ) );
						$week_line        = array_replace( $empty_day_column, array_flip( $the_day_ids ) );
						ksort( $week_line );
					} );

					// Build the week line, at a min add 1 line.
					foreach ( range( 0, max( 0, $week_height - 1 ) ) as $i ) {
						$week_line = array_replace(
							array_fill( 0, $week_size, ' ' ),
							array_column( $week_row, $i )
						);
						$result[]  = implode( '|', array_map( $pad, $week_line ) );
					}

					$result_header = str_repeat( '______', count( $week_row ) );
					$result_header .= "\n" . implode( '|', array_map( $pad, array_keys( $week_row ) ) );
					$result_header .= "\n" . str_repeat( '------', count( $week_row ) );

					return $result_header . "\n" . implode( "\n", $result );
				},
				$week_rows
			)
		);

		$ascii_calendar = implode( ' | ', $header_row ) . "|\n" . $weeks . "\n" . str_repeat( '------', $week_size );

		return $ascii_calendar;
	}
}
