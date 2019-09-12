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
	 * @param \WP_Post             $event The event post object.
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
}
