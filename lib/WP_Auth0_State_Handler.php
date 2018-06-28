<?php
/**
 * Contains WP_Auth0_State_Handler.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_State_Handler for handling state storage and verification.
 */
final class WP_Auth0_State_Handler extends WP_Auth0_Nonce_Handler {

	/**
	 * State cookie name used for storage and verification.
	 *
	 * @var string
	 */
	const STATE_COOKIE_NAME = 'auth0_state';

	/**
	 * Singleton class instance.
	 *
	 * @var WP_Auth0_State_Handler|null
	 */
	protected static $_instance = null;

	/**
	 * Get the name of the cookie to validate.
	 *
	 * @return string
	 */
	public static function get_storage_cookie_name() {
		return apply_filters( 'auth0_state_cookie_name', self::STATE_COOKIE_NAME );
	}
}
