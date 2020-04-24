<?php
/**
 * Provides methods to interact and manipulate the Context in test cases.
 *
 * @since   TBD
 *
 * @package Tribe\Test\Products\Traits
 */

namespace Tribe\Test\Products\Traits;

use Tribe__Context as Context;

/**
 * Trait With_Context
 *
 * @since   TBD
 *
 * @package Tribe\Test\Products\Traits
 */
trait With_Context {

	/**
	 * A backup of the context in its initial state, taken at the start of the test case `setUp` method.
	 * This is static to "snapshot" the context once, when the first test case of this type runs.
	 *
	 * @var Context
	 */
	protected static $context_backup;

	/**
	 * Resets the global, shared instance of the Context to a new, blank, empty instance.
	 *
	 * @since TBD
	 *
	 * @see   With_Context::restore_context For the method that will restore the context to a previous state.
	 * @see   With_Context::backup_context For the method to backup the global, shared instance of the Context.
	 */
	protected function reset_context() {
		$this->restore_context( new Context() );
	}

	/**
	 * Restores the global instance of the Context, the one returned from the bound `context` slug, to its initial
	 * state and instance.
	 *
	 * During tests we might need to use the `Context::dangerously_set_global_context` method and that would alter the
	 * global, shared, instance of the context for all tests.
	 * This method provides a fix.
	 * Differently from the `reset_context` method this method might be useful to restore the context to a previously
	 * set state in place of a resetting it to a "blank" one.
	 *
	 * @since TBD
	 *
	 * @param Context|null $context The Context instance to reset the context to. This should be `null`, but room is
	 *                              left for savvy developers to reset to an explicit context instance.
	 *                              With great power comes great responsibility.
	 *
	 * @see   With_Context::backup_context For the method that sets up the Context instance that will be restored here.
	 * @see   With_Context::reset_context For the method that will restore the context to an empty, new instance.
	 */
	protected function restore_context( Context $context = null ) {
		$restore_to = $context ? $context : static::$context_backup;

		if ( ! $restore_to instanceof Context ) {
			// It might not have been backed up or not be a Context object.
			return;
		}

		tribe_singleton( 'context', $restore_to );
	}

	/**
	 * Backs up the current, global, shared instance of the Context to restore it later.
	 *
	 * "Current" is the keyword here: if the Context has been altered BEFORE this method runs, then the instance
	 * of the Context that will be restored with the `restore_context` method will be this altered one.
	 *
	 * @param array<string,mixed> $alterations An array of alterations that will be applied to the context before it's
	 *                                         backed up. This allows setting the Context to a desired state that will
	 *                                         be enforced on restore.
	 *
	 * @since TBD
	 */
	protected function backup_context( array $alterations = null ) {
		if ( tribe()->isBound( 'context' ) ) {
			$context = tribe( 'context' );
			if ( null !== $alterations ) {
				$context = $context->alter( $alterations );
			}
			static::$context_backup = $context;
		}
	}

	/**
	 * Checks whether the Context is backed up or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Context is backed up or not.
	 */
	protected function context_backed_up() {
		return static::$context_backup instanceof Context;
	}
}
