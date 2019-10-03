<?php
/**
 * Utility functions.
 *
 * @since TBD
 *
 * @package Tribe\Test
 */

namespace Tribe\Test;

use NilPortugues\Sql\QueryFormatter\Formatter as Sql_Formatter;

/**
 * Formats a SQL string in a "pretty" format.
 *
 * @since TBD
 *
 * @param string $sql_string The SQL string to format.
 *
 * @return string The formatted SQL string.
 *
 * @uses \NilPortugues\Sql\QueryFormatter\Formatter::format()
 */
function format_sql( $sql_string ) {
	static $formatter;
	if ( null === $formatter ) {
		$formatter = new Sql_Formatter();
	}

	return $formatter->format( $sql_string );
}
