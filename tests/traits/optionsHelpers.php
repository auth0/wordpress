<?php
/**
 * Contains Trait OptionsHelpers.
 *
 * @package WP-Auth0
 *
 * @since 3.8.1
 */

/**
 * Trait Users.
 */
trait OptionsHelpers {

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Set the Auth0 plugin settings.
	 *
	 * @param boolean $on - True to turn Auth0 on, false to turn off.
	 */
	public static function auth0Ready( $on = true ) {
		$value = $on ? uniqid() : null;
		self::$opts->set( 'domain', $value );
		self::$opts->set( 'client_id', $value );
		self::$opts->set( 'client_secret', $value );
	}
}
