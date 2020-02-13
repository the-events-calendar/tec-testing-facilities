<?php
/**
 * Provides methods to load large data sets from JSON files.
 *
 * The JSON files are expected to be in: `<test_case_dir>/data-sets/<test_case_class>/*.json`.
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

/**
 * Trait With_Json_Data_Sets
 *
 * @package Tribe\Test\Products\Traits
 */
trait With_Json_Data_Sets {

	/**
	 * Yields the JSON data sets stored in the test case case `data-sets/<short_class_name>` sibling directory.
	 *
	 * The method will scaffold the directories where the JSON data sets are expected to be found.
	 *
	 * @param string $test_method The test case method, usually passed by PHPUnit, if specified and if a data-sets
	 *                            sub-dir dedicated to the case exists, then data-sets will be loaded from that.
	 *
	 * @return \Generator The decoded JSON data sets.
	 *
	 * @throws \RuntimeException If the data set directory does not exist and cannot be created.
	 */
	public function json_data_sets( $test_method = null ) {
		$r   = new \ReflectionClass( $this );
		$dir = implode( '/', [ dirname( $r->getFileName() ), 'data-sets', $r->getShortName() ] );

		if ( ! ( is_dir( $dir ) || mkdir( $dir, 0777, true ) ) ) {
			throw new \RuntimeException( "Cannot create data-set dir: {$dir}" );
		}

		$case_dir = $dir . '/' . $test_method;
		if ( is_dir( $case_dir ) ) {
			$dir = $case_dir;
		}

		foreach ( glob( $dir . '/*.json' ) as $data_set ) {
			yield basename( $data_set, '.json' ) => json_decode( file_get_contents( $data_set ), true );
		}
	}
}
