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
