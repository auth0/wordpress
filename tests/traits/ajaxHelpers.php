<?php
/**
 * Contains Trait AjaxHelpers.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Trait AjaxHelpers.
 */
trait AjaxHelpers {

	/**
	 * Set a filter to halt AJAX requests with an exception.
	 * Call at the top of tests that use AJAX handler functions.
	 */
	public function startAjaxHalting() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', [ $this, 'startAjaxHaltingHook' ] );
	}

	/**
	 * Returns the function used to halt an AJAX request.
	 *
	 * @return array
	 */
	public function startAjaxHaltingHook() {
		return [ $this, 'haltAjax' ];
	}

	/**
	 * Stop AJAX requests by throwing an exception.
	 * Hooked to: wp_die_ajax_handler
	 *
	 * @param string}int $message - Message for die page.
	 * @param string     $title - Title for die page, not used.
	 * @param array      $args - Other args.
	 *
	 * @throws Exception - Always, to stop AJAX process.
	 */
	public function haltAjax( $message, $title, $args ) {
		if ( -1 === $message && ! empty( $args['response'] ) && 403 === $args['response'] && empty( $title ) ) {
			$error_msg = 'bad_nonce';
		} else {
			$error_msg = 'die_ajax';
		}
		throw new Exception( $error_msg );
	}

	/**
	 * Remove the filter that halts AJAX requests.
	 * Call this in a test suites tearDown method.
	 */
	public function stopAjaxHalting() {
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'startAjaxHaltingHook' ] );
	}

	/**
	 * Return AJAX request messages.
	 * Call at the top of tests that use AJAX handler functions.
	 */
	public function startAjaxReturn() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', [ $this, 'startAjaxReturnHook' ] );
	}

	/**
	 * Returns the function used to return an AJAX request message.
	 *
	 * @return array
	 */
	public function startAjaxReturnHook() {
		return [ $this, 'ajaxReturn' ];
	}

	/**
	 * Prevent the wp_die page from dying and echo the message passed.
	 * Hooked to: wp_die_handler
	 *
	 * @param string $message - HTML to show on the wp_die page.
	 */
	public function ajaxReturn( $message ) {
		echo $message;
	}

	/**
	 * Remove the filter that returns AJAX messages.
	 * Call this in a test suites tearDown method.
	 */
	public function stopAjaxReturn() {
		remove_filter( 'wp_doing_ajax', '__return_true', 10 );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'startAjaxReturnHook' ], 10 );
	}
}
