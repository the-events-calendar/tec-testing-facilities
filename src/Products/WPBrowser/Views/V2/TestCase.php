<?php
/**
 * The base test case to test v2 Views.
 *
 * It provides utility methods and assertions useful and required in Views testing.
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */

namespace Tribe\Test\Products\WPBrowser\Views\V2;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use tad\WP\Snapshots\WPHtmlOutputDriver;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Test\Products\Traits\With_Caches_Reset;
use Tribe\Test\Products\Traits\With_Context;

/**
 * Class TestCase
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2
 */
abstract class TestCase extends WPTestCase {

	use MatchesSnapshots;
	use With_Context;
	use With_Caches_Reset;

	/**
	 * The current Context Mocker instance.
	 *
	 * @var \Tribe\Events\Views\V2\ContextMocker
	 */
	protected $context_mocker;

	/**
	 * The HTML driver for our code
	 *
	 * @var \tad\WP\Snapshots\WPHtmlOutputDriver
	 */
	protected $driver;

	/**
	 * After the test method ran try and restore the global context to its previous state and make sure that
	 * is the case.
	 *
	 * @since 4.9.2
	 */
	public function tearDown(): void {
		$this->restore_context();

		parent::tearDown();
	}

	/**
	 * Before each test method take a snapshot of the global context state to make sure it's restored
	 * as it was after each test.
	 *
	 * We do this here, before each test method, as `setupBeforeClass` would be too early.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->backup_context();

		// Always set the `is_main_query` value to `false` to have a clean starting fixture.
		tribe_context()->alter( [ 'is_main_query' => false ] )->dangerously_set_global_context( [ 'is_main_query' ] );

		/*
		 * Filter the `home_url` to make sure URLs printed on the page are consistent across environments.
		 */
		add_filter(
			'home_url',
			static function( $url, $path = null ) {
				return 'http://test.tri.be/' . ltrim( $path, '/' );
			},
			10,
			2
		);

		// Setup a new HTML output driver to make sure our stuff is tolerable.
		$this->driver = new WPHtmlOutputDriver( home_url(), 'http://views.dev' );
		$this->driver->setTimeDependentKeys( [ 'tribe-events-views[_wpnonce]' ] );
		$this->driver->setTimeDependentAttributes( [ 'data-view-breakpoint-pointer' ] );

		add_filter(
			'tribe_events_views_v2_view_breakpoint_pointer',
			static function( $pointer ) {
				return 'random-id';
			}
		);

		/*
		 * Set up the event, venue and organizer factories to make them available in the tests on the `static::factory`
		 * method.
		 */
		static::factory()->event     = new Event();
		static::factory()->venue     = new Venue();
		static::factory()->organizer = new Organizer();

		$this->flush_all_caches();

		// Let's restore this value to avoid hijacking all Views.
		$_SERVER['REQUEST_URI'] = '';
	}

	/**
	 * Starts the chain to replace the global context using the Context Mocker.
	 *
	 * @return ContextMocker The context mocker instance.
	 */
	protected function given_a_main_query_request(): ContextMocker {
		$context_mocker = new ContextMocker();
		$context_mocker->set( 'is_main_query', true );
		$this->context_mocker = $context_mocker;

		return $context_mocker;
	}

	/**
	 * Asserts a view current HTML output matches a stored HTML snapshot.
	 *
	 * @param View_Interface $view The view instance.
	 */
	protected function assert_view_snapshot( View_Interface $view ) {
		if ( null !== $this->context_mocker && ! $this->context_mocker->did_mock() ) {
			// Let's alter the global context now.
			$this->context_mocker->alter_global_context();
		}

		$this->assertMatchesSnapshot( $view->get_html() );
	}
}
