<?php
/**
 * Handles configuration and initialization of Function Mocker.
 *
 * @example
 * ```yaml
 * extensions:
 *    enabled:
 *        - Tribe\Events\Test\Extensions\FunctionMocker
 *    config:
 *        Tribe\Events\Test\Extensions\FunctionMocker:
 *            suites: [ 'views_integration' ]
 *            initFile: tests/_function-mocker-bootstrap.php
 * ```
 *
 * @package Tribe\Events\Test\Extensions
 */

namespace Tribe\Test\Codeception\Extensions;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;

/**
 * Class FunctionMocker
 *
 * @package Tribe\Events\Test\Extensions
 */
class Function_Mocker extends Extension {

	/**
	 * A map of events the extension will listen for and react to.
	 *
	 * @var array
	 */
	public static $events = [
		Events::MODULE_INIT => 'init',
	];

	/**
	 * When the modules initialize then the extension will load an ad-hoc initialization file.
	 *
	 * @param SuiteEvent $event The current suite event.
	 *
	 * @return boolean Whether the initialization file was loaded or not.
	 *
	 * @throws ExtensionException If no initialization file path is specified or the specified path is not valid.
	 */
	public function init( SuiteEvent $event ) {
		if ( empty( $this->config['initFile'] ) ) {
			throw new ExtensionException( __CLASS__, 'You must specify an `initFile` parameter.' );
		}

		$init_file = file_exists( $this->config['initFile'] ) ?
			realpath( $this->config['initFile'] )
			: realpath( getcwd() . '/' . trim( $this->config['initFile'], '/\\' ) );

		if ( ! is_readable( $init_file ) || ! is_file( $init_file ) ) {
			throw new ExtensionException( __CLASS__, "[{$init_file}] does not exist, is not a file or is not readable." );
		}

		$suites = ! empty( $this->config['suites'] ) ? (array) $this->config['suites'] : [];

		if ( ! empty( $suites ) && ! in_array( $event->getSuite()->getName(), $suites, true ) ) {
			return false;
		}

		include $init_file;

		return true;
	}
}
