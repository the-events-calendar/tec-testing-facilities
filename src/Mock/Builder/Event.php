<?php
/**
 * Builds and returns a mock event.
 *
 * @since   TBD
 *
 * @package Tribe\Test\Mock\Builder
 */

namespace Tribe\Test\Mock\Builder;

use Tribe\Utils\Post_Thumbnail;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Timezones as Timezones;

/**
 * Class Event
 *
 * @since   TBD
 *
 * @package Tribe\Test\Mock\Builder
 */
class Event {
	/**
	 * An event post object.
	 *
	 * @since TBD
	 *
	 * @var \WP_Post
	 */
	protected $event;

	/**
	 * The factory the mock builder will use to build.
	 *
	 * @since TBD
	 *
	 * @var \WP_UnitTest_Factory
	 */
	protected $factory;

	/**
	 * Event constructor.
	 *
	 * @param \WP_UnitTest_Factory $factory The factory the mock builder will use to build objects.
	 * @param \WP_Post             $event   The event post object.
	 */
	public function __construct( \WP_UnitTest_Factory $factory, \WP_Post $event ) {
		$this->factory = $factory;
		// Clone to avoid side-effects.
		$this->event = clone $event;
	}

	/**
	 * Creates an attachment for the specified file and assigns it to the event as thumbnail.
	 *
	 * @since TBD
	 *
	 * @param string $path The path, relative to the Codeception `_data` directory, to the image.
	 *
	 * @return $this For chaining.
	 */
	public function with_thumbnail( $path = 'images/featured-image.jpg' ) {
		$thumbnail_id = $this->factory->attachment->create_upload_object( codecept_data_dir( $path ) );
		set_post_thumbnail( $this->event->ID, $thumbnail_id );
		$this->event->thumbnail = new Post_Thumbnail( $this->event->ID );

		return $this;
	}

	/**
	 * The tail of the chain, returns the finished event.
	 *
	 * @since TBD
	 *
	 * @return \WP_Post The finished event, modified by the class methods.
	 */
	public function get() {
		return $this->event;
	}

	/**
	 * Updates the event end date to make it multi-day.
	 *
	 * @since TBD
	 *
	 * @param int $day_duration The multi-day duration, in days, min. 2.
	 *
	 * @return $this For chaining.
	 *
	 * @throws \InvalidArgumentException If the day duration value is less than 2.
	 */
	public function is_multiday( $day_duration = 2 ) {
		$duration = $day_duration - 1;
		if ( $duration < 1 ) {
			throw new \InvalidArgumentException( 'Day duration should be at least 2' );
		}
		$date_interval             = new \DateInterval( 'P' . ( $duration ) . 'D' );
		$new_end_date              = $this->event->dates->end->add( $date_interval );
		$new_end_date_utc          = $this->event->dates->end_utc->add( $date_interval );
		$this->event->end_date     = $new_end_date->format( Dates::DBDATETIMEFORMAT );
		$this->event->end_date_utc = $new_end_date_utc->format( Dates::DBDATETIMEFORMAT );
		$this->event->dates        = (object) [
			'start'     => $this->event->dates->start,
			'start_utc' => $this->event->dates->start_utc,
			'end'       => $new_end_date,
			'end_utc'   => $new_end_date_utc,
		];
		$this->event->duration     = $new_end_date->getTimestamp() - $this->event->dates->start->getTimestamp();
		$this->event->multiday     = $day_duration;

		return $this;
	}

	/**
	 * Updates the event dates to be an all-day one.
	 *
	 * @since TBD
	 *
	 * @return $this For chaining.
	 */
	public function is_all_day() {
		$this->event->all_day = true;

		$timezone = Timezones::build_timezone_object( Timezones::get_event_timezone_string( $this->event->timezone ) );
		$utc      = new \DateTimeZone( 'UTC' );

		$start = Dates::build_date_object(
			tribe_beginning_of_day( $this->event->dates->start->format( Dates::DBDATETIMEFORMAT ) ),
			$timezone
		);
		$end   = Dates::build_date_object(
			tribe_end_of_day( $this->event->dates->end->format( Dates::DBDATETIMEFORMAT ) ),
			$timezone
		);

		$this->event->start_date     = $start->format( Dates::DBDATETIMEFORMAT );
		$this->event->start_date_utc = $start->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$this->event->end_date       = $end->format( Dates::DBDATETIMEFORMAT );
		$this->event->end_date_utc   = $end->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$this->event->dates          = (object) [
			'start'     => $start,
			'start_utc' => $start->setTimezone( $utc ),
			'end'       => $end,
			'end_utc'   => $end->setTimezone( $utc ),
		];
		$this->event->duration       = $end->getTimestamp() - $start->getTimestamp();

		return $this;
	}

	/**
	 * Filters the check for a recurring event to make an event look like it is.
	 *
	 * @since TBD
	 *
	 * @return $this For chaining.
	 */
	public function is_recurring() {
		add_filter(
			'tribe_is_recurring_event',
			function ( $recurring, $post_id ) {
				return (int) $post_id === $this->event->ID;
			}
		);
		$this->event->recurring = true;

		return $this;
	}
}
