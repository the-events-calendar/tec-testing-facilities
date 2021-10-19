<?php
/**
 * The common test case for testing Legacy View Partials
 *
 * Differently from the `HtmlPartialTestCase` one, this test case does not require or assume the use of the
 * `Spatie\Snapshots\MatchesSnapshots` trait.
 *
 * @since   TBD
 */

namespace Tribe\Test\Products\WPBrowser\Views\Legacy;

use Codeception\TestCase\WPTestCase;
use Tribe\Test\Products\Traits\With_Caches_Reset;
use Tribe\Test\Products\Traits\With_Context;
use Tribe__Template;
use Tribe__Tickets__Main;

/**
 * Class HtmlPartialTestCase
 *
 * @package Tribe\Test\Products\WPBrowser\Views\Legacy
 */
class PartialTestCase extends WPTestCase {
	use With_Caches_Reset;
	use With_Context;

	/**
	 * The path, relative to the src folder, to the partial.
	 * Extending test classes must override this.
	 *
	 * @var string
	 */
	protected $partial_path;

	/**
	 * The Template instance used to load and render partials.
	 *
	 * @var \Tribe__Tickets__Templates;
	 */
	protected $template;

	/**
	 * {@inheritDoc}
	 *
	 * @throws \RuntimeException If the extending test case is not defining a `$partial_path` property.
	 */
	public function setUp() {
		parent::setUp();
		if ( empty( $this->partial_path ) ) {
			throw new \RuntimeException( 'Any test case extending [' . __CLASS__ . '] must define the `$partial_path` property.' );
		}

		if ( preg_match( '/\\.php$/', $this->partial_path ) ) {
			throw new \RuntimeException( 'The `$partial_path` property must not contain the `.php` extension.' );
		}

		$home_url = home_url();
		$this->set_template( $this->partial_path );

		// Before each test make sure to empty the whole uploads directory to avoid file enumeration issues.
		$uploads = wp_upload_dir();
		if ( function_exists( '\tad\WPBrowser\rrmdir' ) ) {
			\tad\WPBrowser\rrmdir( $uploads['basedir'] );
		} else {
			rrmdir( $uploads['basedir'] );
		}

		/*
		 * To make sure we're not breaking snapshots by a change in the local URL generating them change the `home_url`
		 * to a fixed value.
		 */
		$mock_url = static function () {
			return 'http://test.tri.be';
		};
		add_filter( 'option_home', $mock_url );
		add_filter( 'option_siteurl', $mock_url );
		add_filter(
			'wp_get_attachment_url',
			static function ( $url ) use ( $home_url ) {
				// phpcs:ignore
				return str_replace( [ $home_url, date( 'Y/m' ) ], [ 'http://test.tri.be', '2018/08' ], $url );
			}
		);

		$this->backup_context();
		$this->flush_all_caches();
	}

	/**
	 * {@inheritdoc}
	 */
	public function tearDown() {
		$this->restore_context();
		parent::tearDown();
	}

	private function set_template( $folder_path ) {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( Tribe__Tickets__Main::instance() );
			$this->template->set_template_folder( $folder_path );
			$this->template->set_template_context_extract( true );
		}
	}

	/**
	 * Renders the partial and returns its HTML.
	 *
	 * @param array $context An array that will be passed to the partial to render.
	 *
	 * @return string The partial rendered HTML.
	 */
	protected function get_partial_html( array $context = [] ) {
		$this->template->set_values( $context, false );

		return $this->template->template( $this->partial_path, $context );
	}
}

