<?php
/**
 * Contains Trait WpDieHelper.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

/**
 * Trait WpDieHelper.
 */
trait WpDieHelper {

	/**
	 * Start halting all wp_die calls.
	 * Use this at the top of tests that should check HTTP requests.
	 */
	public function startWpDieHalting() {
		add_filter( 'wp_die_handler', [ $this, 'wpDieHandler' ] );
	}

	/**
	 * Provide the function to handle wp_die.
	 *
	 * @return array
	 */
	public function wpDieHandler() {
		return [ $this, 'haltWpDie' ];
	}

	/**
	 * Handle wp_die.
	 *
	 * @param string $html - Passed-in HTML to display.
	 *
	 * @throws \Exception - Always.
	 */
	public function haltWpDie( $html ) {
		throw new Exception( $html );
	}

	/**
	 * Stop halting wp_die.
	 */
	public function stopWpDieHalting() {
		remove_filter( 'wp_die_handler', [ $this, 'wpDieHandler' ] );
	}
}
