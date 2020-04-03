<?php
/**
 * The base test case to test v2 Views HTML.
 *
 * It provides utility methods and assertions useful and required in Views testing.
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2;
 */

namespace Tribe\Test\Products\WPBrowser\Views\V2;

use DOMWrap\Document;
use Tribe\Events\Views\V2\Template;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;

/**
 * Class TestCase
 *
 * @package Tribe\Test\Products\WPBrowser\Views\V2;
 */
abstract class HtmlTestCase extends TestCase {
	/**
	 * Store the views loader
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Store the DOM handler
	 *
	 * @var Document
	 */
	protected $document;

	/**
	 * The support View used to test the HTML partial.
	 *
	 * @var View_Interface
	 */
	protected $view;

	/**
	 * {@inheritDoc}
	 */
	public function setUp() {
		parent::setUp();

		$this->view     = $this->make_view_instance();
		$this->template = $this->make_template_instance();
		$this->template->set_values( $this->view->get_template_vars(), false );
		$this->document = $this->make_document_instance();

		$home_url = home_url();

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

		/**
		* Consider using this setting instead of hacking the `wp_get_attachment_url`.
		*/
		// add_filter( 'option_uploads_use_yearmonth_folders', '__return_empty_string' );.
		add_filter(
			'wp_get_attachment_url',
			static function ( $url ) use ( $home_url ) {
				// phpcs:ignore
				return str_replace( [ $home_url, date( 'Y/m' ) ], [ 'http://test.tri.be', '2018/08' ], $url );
			}
		);
	}

	/**
	 * Returns a "safe" View to use in HTML partial testing.
	 *
	 * @return View_Interface A View instance safe to use in partial HTML testing.
	 */
	protected function make_view_instance() {
		return View::make( 'reflector' );
	}

	/**
	 * Returns a ready Template instance.
	 *
	 * @return Template The ready template instance, set up with the View.
	 */
	protected function make_template_instance() {
		$template = new Template( $this->view );

		$template->set_values( $this->view->get_context()->to_array(), false );
		$template->set_values( $this->view->get_template_vars(), false );

		return $template;
	}

	/**
	 * Returns a built DOM Document handler instance.
	 *
	 * @return Document The built DOM Document handler instance.
	 */
	protected function make_document_instance() {
		return new Document();
	}
}
