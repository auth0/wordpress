<?php
/**
 * Contains Trait RedirectHelpers.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Trait RedirectHelpers.
 */
trait RedirectHelpers {

	/**
	 * Start halting all redirects.
	 * Use this at the top of tests that should check redirects.
	 */
	public function startRedirectHalting() {
		add_filter( 'wp_redirect', [ $this, 'haltRedirect' ], 1, 2 );
	}

	/**
	 * Halt all redirects with request data serialized in the error message.
	 *
	 * @param string  $location - Original redirect URL.
	 * @param integer $status   - HTTP status code.
	 *
	 * @throws Exception - Always.
	 */
	public function haltRedirect( $location, $status ) {
		$error_msg = serialize(
			[
				'location' => $location,
				'status'   => $status,
			]
		);
		throw new Exception( $error_msg );
	}

	/**
	 * Stop halting redirects.
	 * Use this in a tearDown() method in the test suite.
	 */
	public function stopRedirectHalting() {
		remove_filter( 'wp_redirect', [ $this, 'haltRedirect' ], 1 );
	}
}
