<?php
/**
 * Builds and returns a mock event.
 *
 * @since   TBD
 *
 * @package Tribe\Test\Mock\Builder
 */

namespace Tribe\Test\Mock\Builder;

use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
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
	use With_Post_Remapping;

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
	 * An instance of the factory object that will be used to build the Venues.
	 *
	 * @since TBD
	 *
	 * @var Venue
	 */
	protected $venue_factory;

	/**
	 * An instance of the factory object that will be used to build the Organizers.
	 *
	 * @since TBD
	 *
	 * @var Organizer
	 */
	protected $organizer_factory;

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

		$this->ensure_post_ids( [ $thumbnail_id ] );

		$this->update_post_meta( '_thumbnail_id', $thumbnail_id );
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
		$date_interval = new \DateInterval( 'P' . ( $duration ) . 'D' );
		foreach ( [ 'end', 'end_utc', 'end_site', 'end_display' ] as $update ) {
			if ( ! isset( $this->event->dates->{$update} ) ) {
				continue;
			}
			$this->event->dates->{$update} = $this->event->dates->{$update}->add( $date_interval );
		}
		$this->event->duration = $this->event->dates->end->getTimestamp() - $this->event->dates->start->getTimestamp();
		$this->event->multiday = $day_duration;

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

		$start            = \DateTimeImmutable::createFromMutable(
			Dates::build_date_object(
				tribe_beginning_of_day( $this->event->dates->start->format( Dates::DBDATETIMEFORMAT ) ),
				$timezone
			)
		);
		$end              = \DateTimeImmutable::createFromMutable(
			Dates::build_date_object(
				tribe_end_of_day( $this->event->dates->end->format( Dates::DBDATETIMEFORMAT ) ),
				$timezone
			)
		);
		$site_timezone    = Timezones::build_timezone_object();
		$display_timezone = Timezones::is_mode( Timezones::SITE_TIMEZONE ) ? $site_timezone : $timezone;

		$this->event->start_date     = $start->format( Dates::DBDATETIMEFORMAT );
		$this->event->start_date_utc = $start->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$this->event->end_date       = $end->format( Dates::DBDATETIMEFORMAT );
		$this->event->end_date_utc   = $end->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$this->event->dates          = (object) [
			'start'         => $start,
			'start_utc'     => $start->setTimezone( $utc ),
			'start_site'    => $start->setTimezone( $site_timezone ),
			'start_display' => $start->setTimezone( $display_timezone ),
			'end'           => $end,
			'end_utc'       => $end->setTimezone( $utc ),
			'end_site'      => $end->setTimezone( $site_timezone ),
			'end_display'   => $end->setTimezone( $display_timezone ),
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
			function ( $recurring, $post_id = null ) {
				$post_id = $post_id ? $post_id : \Tribe__Main::post_id_helper( $post_id );

				return (int) $post_id === $this->event->ID;
			}
		);
		$this->event->recurring = true;

		return $this;
	}

	/**
	 * Flags the event as a featured one.
	 *
	 * @since TBD
	 *
	 * @return $this For chaining.
	 */
	public function is_featured() {
		$this->update_post_meta( '_tribe_featured', '1' );
		$this->event->featured = true;

		return $this;
	}

	/**
	 * Fills the event venue property with a collection of 1 Venue.
	 *
	 * @since TBD
	 *
	 * @param string     $target              The path, relative to the the plugin `tests/_data/remap` directory, to the
	 *                                        static JSON file or JSON file template.
	 * @param array|null $template_vars       If specified the content of the specified JSON file target will be used as
	 *                                        a template, its values filled to those specified in the template variables.
	 *                                        Variables will be replaced to their `{{ <key> }}` counterpart in the
	 *                                        template.
	 *
	 * @return $this For chaining.
	 */
	public function with_venue( $target = null, array $template_vars = null ) {
		if ( null !== $target ) {
			$venue    = $this->get_mock_venue( $target, $template_vars );
			$venue_id = $venue->ID;
		} else {
			$this->venue_factory = $this->venue_factory ? $this->venue_factory : new  Venue();
			$venue_id            = $this->venue_factory->create();
		}

		$this->ensure_post_ids( [ $venue_id ] );

		$this->update_post_meta( '_EventVenueID', $venue_id );

		return $this;
	}

	/**
	 * Creates n Organizers and links them to the event.
	 *
	 * @since TBD
	 *
	 * @param int        $count The number of Organizers to create and link to the event.
	 * @param string     $target_template     The path, relative to the the plugin `tests/_data/remap` directory, to the
	 *                                        static JSON file or JSON file template.
	 * @param array|null $template_vars_array If specified the content of the specified JSON file target will be used as
	 *                                        a template, its values filled to those specified in the template variables.
	 *                                        Variables will be replaced to their `{{ <key> }}` counterpart in the
	 *                                        template.
	 *
	 * @return $this For chaining.
	 */
	public function with_organizers( $count = 1, $target_template = null, array $template_vars_array = null ) {
		if ( null !== $target_template ) {
			$create = function ( array $template_vars = null ) use ( $target_template ) {
				return $this->get_mock_organizer( $target_template, $template_vars );
			};

			if ( ! empty( $template_vars_array ) ) {
				$template_vars_array = count( $template_vars_array ) === $count
					? $template_vars_array
					: array_pad( $template_vars_array, $count, end( $template_vars_array ) );
			} else {
				$template_vars_array = array_fill( 0, $count, [] );
			}

			$organizer_ids = array_map( $create, $template_vars_array );
		} else {
			$this->organizer_factory = $this->organizer_factory ? $this->organizer_factory : new Organizer();

			$create = function () {
				return $this->organizer_factory->create();
			};

			$organizer_ids = $count > 1
				? array_map( $create, range( 1, $count ) )
				: [ $create() ];
		}

		$organizer_ids = array_map(
			static function ( $post_id ) {
				return $post_id instanceof \WP_Post ? $post_id->ID : $post_id;
			},
			$organizer_ids
		);

		$this->ensure_post_ids( $organizer_ids );

		$this->update_post_meta( '_EventOrganizerID', $organizer_ids );

		return $this;
	}

	/**
	 * Updates the remapped post meta value in cache.
	 *
	 * @since TBD
	 *
	 * @param string $meta_key   The meta key to update.
	 * @param mixed  $meta_value The meta value.
	 */
	protected function update_post_meta( $meta_key, $meta_value ) {
		$all_meta              = get_post_meta( $this->event->ID );
		$all_meta[ $meta_key ] = (array) $meta_value;
		wp_cache_set( $this->event->ID, $all_meta, 'post_meta' );
	}

	/**
	 * Checks a list of generated post IDs for conflicts with the current mocked event post ID.
	 *
	 * @since TBD
	 *
	 * @param array $post_ids An array of generated post IDs to check.
	 *
	 * @throws \RuntimeException If one of the generated post IDs conflicts with the currently mocked event one.
	 */
	protected function ensure_post_ids( array $post_ids ) {
		$array_intersect = array_intersect( $post_ids, [ $this->event->ID ] );
		if ( count( $array_intersect ) === 0 ) {
			return;
		}

		$post_type = get_post_type( reset( $array_intersect ) );

		throw new \RuntimeException(
			"One of the generated posts ({$post_type}) has the same post ID as the " .
			"current event being mocked (post ID {$this->event->ID})."
			. PHP_EOL
			. 'To avoid this either update, or change, the Event template you\'re using to have a different, ' .
			'hard-coded, ID' .
			( in_array( $post_type, [ 'tribe_venue', 'tribe_organizer' ], true )
				? ( ', use a Venue or Organizer template if you\'re not using it, or use a different Venue or Organizer ' .
					'template.' )
				: '.' )
		);
	}
}
