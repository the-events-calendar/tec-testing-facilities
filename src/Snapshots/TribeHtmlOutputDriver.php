<?php
/**
 * Temporary extension of WPHtmlOutputDriver until the upstream PR is merged.
 *
 * @see https://github.com/lucatume/wp-snapshot-assertions/pull/2
 *
 * @package Tribe\Test\Snapshots
 */

namespace Tribe\Test\Snapshots;

use tad\WP\Snapshots\WPHtmlOutputDriver;

/**
 * Class TribeHtmlOutputDriver
 *
 * @package Tribe\Test\Snapshots
 */
class TribeHtmlOutputDriver extends WPHtmlOutputDriver {
	/**
	 * @var array An array of keys to identify time-dependent attributes that hold
	 *            unique/time-dependent data values.
	 */
	protected $timeDependentAttributes = [];

	/**
	 * Match an expectation with a snapshot's actual contents. Should throw an
	 * `ExpectationFailedException` if it doesn't match. This happens by
	 * default if you're using PHPUnit's `Assert` class for the match.
	 *
	 * @param mixed $expected
	 * @param mixed $actual
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 */
	public function match($expected, $actual) {
		$evaluated = $this->evalCode($expected);

		$normalizedExpected = $this->normalizeHtml($this->removeTimeAttributes($this->removeTimeValues($this->replaceUrls($evaluated))));
		$normalizedActual = $this->normalizeHtml($this->removeTimeAttributes($this->removeTimeValues($actual)));

		if ( ! empty($this->tolerableDifferences)) {
			$normalizedActual = $this->applyTolerableDifferences($normalizedExpected, $normalizedActual);
		}

		Assert::assertEquals($normalizedExpected, $normalizedActual);
	}

	protected function removeTimeAttributes(string $input): string {
		$doc = \phpQuery::newDocument($input);

		foreach ($this->timeDependentAttributes as $name) {
			$doc->find("*[{$name}]")->each(function (\DOMElement $t) use ($name) {
				$t->setAttribute($name, '');
			});
		}

		return $this->normalizeHtml($doc->__toString());
	}

	/**
	 * Returns an array that the driver will use to identify and void
	 * by attribute name time-dependent values like data attributes.
	 *
	 * @return array
	 */
	public function getTimeDependentAttributes(): array {
		return $this->timeDependentAttributes;
	}

	/**
	 * Sets the array that the driver will use to identify and void
	 * by attribute name time-dependent values like data attributes.
	 *
	 * @param array $timeDependentAttributes
	 */
	public function setTimeDependentAttributes(array $timeDependentAttributes) {
		$this->timeDependentAttributes = $timeDependentAttributes;
	}
}
