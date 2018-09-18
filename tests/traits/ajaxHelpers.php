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
	 * Set filters for processing AJAX tests.
	 * Call at the top of tests that use AJAX handler functions.
	 */
	public function start_ajax() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			function() {
				return [ $this, 'stop_ajax' ];
			}
		);
	}

	/**
	 * Stop AJAX requests from dying.
	 * Hooked to: wp_die_ajax_handler
	 *
	 * @param string}int $message - Message for die page.
	 * @param string     $title - Title for die page, not used.
	 * @param array      $args - Other args.
	 *
	 * @throws Exception - Always, to stop AJAX process.
	 */
	public function stop_ajax( $message, $title, $args ) {
		if ( -1 === $message && ! empty( $args['response'] ) && 403 === $args['response'] && empty( $title ) ) {
			$error_msg = 'bad_nonce';
		} else {
			$error_msg = 'die_ajax';
		}
		throw new Exception( $error_msg );
	}
}
