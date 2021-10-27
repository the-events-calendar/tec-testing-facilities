<?php
/**
 * The common test case for testing Legacy View Partials
 *
 * Differently from the `HtmlPartialTestCase` one, this test case does not require or assume the use of the
 * `Spatie\Snapshots\MatchesSnapshots` trait.
 *
 * @since TBD
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
	 * The path, relative to $folder_path, to the partial.
	 * Extending test classes must override this.
	 *
	 * @var string
	 */
	protected $partial_path;

	/**
	 * The path, relative to the project root, to the base folder
	 * where partials will be loaded from.
	 *
	 * Extending test classes may override this.
	 *
	 * @var string
	 */
	protected $folder_path = 'src/views/';

	/**
	 * The URL to replace in templates, to avoid breaking snapshots because of different test urls
	 *
	 * @var string
	 */
	protected $mock_url = 'http://test.tri.be';

	/**
	 * The date to replace in templates, to avoid breaking snapshots because of different test dates
	 *
	 * @var string
	 */
	protected $mock_date = '2018/08';

	/**
	 * The Template instance used to load and render partials.
	 *
	 * @var \Tribe__Template|\Tribe__Tickets__Admin__Views;
	 */
	protected $template;

	/**
	 * {@inheritDoc}
	 *
	 * @throws \RuntimeException If the extending test case is not defining a `$partial_path` property, or if
	 *                           `$partial_path` contains a php extension
	 */
	public function setUp() {
		parent::setUp();
		if ( empty( $this->partial_path ) ) {
			throw new \RuntimeException( 'Any test case extending [' . __CLASS__ . '] must define the `$partial_path` property.' );
		}

		if ( preg_match( '/\\.php$/', $this->partial_path ) ) {
			throw new \RuntimeException( 'The `$partial_path` property must not contain the `.php` extension.' );
		}

		add_filter( 'option_home', array( $this, 'get_mock_url' ) );
		add_filter( 'option_siteurl', array( $this, 'get_mock_url' ) );
		add_filter( 'wp_get_attachment_url', array( $this, 'replace_urls' ) );

		$this->set_template();
		$this->reset_uploads();
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

	/**
	 * Before each test make sure to empty the whole uploads directory to avoid file enumeration issues.
	 */
	public function reset_uploads() {
		$uploads = wp_upload_dir();
		\tad\WPBrowser\rrmdir( $uploads['basedir'] );
	}

	/*
	* To make sure we're not breaking snapshots by a change in the local URL generating them change the `home_url` to a fixed value.
	*/
	public function get_mock_url() {
		return $this->mock_url;
	}

	/*
	* To make sure we're not breaking snapshots by a change in the current date when generating them change date() to a fixed value.
	*/
	public function get_mock_date() {
		return $this->mock_date;
	}

	/**
	 * Replace variable values in urls to fixed ones
	 *
	 * @param string $url the url to cleanup
	 *
	 * @return string
	 */
	public function replace_urls( $url ) {
		return str_replace( array( home_url(), date( 'Y/m' ) ), array( $this->get_mock_url(), $this->get_mock_date() ), $url );
	}

	/**
	 * Set template object and properties
	 */
	private function set_template() {
		$this->template = new Tribe__Template();
		$this->template->set_template_origin( Tribe__Tickets__Main::instance() );
		$this->template->set_template_folder( $this->folder_path );
		$this->template->set_template_folder_lookup( true );
		$this->template->set_template_context_extract( true );
	}

	/**
	 * Renders the partial and returns its HTML.
	 *
	 * @param array $context An array that will be passed to the partial to render.
	 *
	 * @return string The partial rendered HTML.
	 */
	protected function get_partial_html( array $context = array() ) {
		$this->template->set_values( $context, false );

		return $this->template->template( $this->partial_path, $context );
	}
}
