<?php
/**
 * Contains WP_Auth0_Nonce_Handler.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_Nonce_Handler for generating and storing nonce-type values.
 */
class WP_Auth0_Nonce_Handler {

	/**
	 * Cookie name used for storage and verification.
	 *
	 * @var string
	 */
	const NONCE_COOKIE_NAME = 'auth0_nonce';

	/**
	 * Time, in seconds, for the cookie to last.
	 * Added to time() to determine expiration time.
	 *
	 * @var integer
	 */
	const COOKIE_EXPIRES = HOUR_IN_SECONDS;

	/**
	 * Singleton class instance.
	 *
	 * @var WP_Auth0_Nonce_Handler|null
	 */
	protected static $_instance = null;

	/**
	 * Unique ID used as a nonce or salting.
	 *
	 * @var string
	 */
	private $unique;

	/**
	 * SameSite cookie attribute set to None.
	 * Used for Implicit login flow.
	 *
	 * @var bool
	 */
	private $same_site_none;

	/**
	 * Private to prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Private to prevent unserializing.
	 */
	private function __wakeup() {}

	/**
	 * WP_Auth0_Nonce_Handler constructor.
	 * Private to prevent new instances of this class.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Start-up process to make sure we have something stored.
	 */
	protected function init() {
		// If a NONCE_COOKIE_NAME is not defined then we don't need to persist the nonce value.
		if ( defined( static::NONCE_COOKIE_NAME ) && isset( $_COOKIE[ static::get_storage_cookie_name() ] ) ) {
			// Have a cookie, don't want to generate a new one.
			$this->unique = $_COOKIE[ static::get_storage_cookie_name() ];
		} else {
			// No cookie, need to create one.
			$this->unique = $this->generate_unique();
		}

		$this->same_site_none = (bool) wp_auth0_get_option( 'auth0_implicit_workflow' );
	}

	/**
	 * Get the internal instance of the singleton.
	 *
	 * @return WP_Auth0_State_Handler|WP_Auth0_Nonce_Handler
	 */
	final public static function get_instance() {
		if ( is_null( static::$_instance ) ) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}

	/**
	 * Get the unique value.
	 *
	 * @return string
	 */
	public function get_unique() {
		return $this->unique;
	}

	/**
	 * Return the cookie expiration time to set.
	 *
	 * @return integer
	 */
	public function get_cookie_exp() {
		return time() + self::COOKIE_EXPIRES;
	}

	/**
	 * Set the cookie value to verify.
	 *
	 * @param mixed $value - null to use uniqid or any other valid cookie value.
	 *
	 * @return bool
	 */
	public function set_cookie( $value = null ) {
		if ( is_null( $value ) ) {
			$value = $this->unique;
		}
		return $this->handle_cookie( static::get_storage_cookie_name(), $value, $this->get_cookie_exp() );
	}

	/**
	 * Validate a received value against the stored value.
	 *
	 * @param string $value - value to validate against what was stored.
	 *
	 * @return bool
	 */
	public function validate( $value ) {
		$cookie_name = static::get_storage_cookie_name();
		$valid       = isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] === $value : false;
		if ( $this->same_site_none && ! $valid ) {
			$valid = isset( $_COOKIE[ '_' . $cookie_name ] ) ? $_COOKIE[ '_' . $cookie_name ] === $value : false;
		}
		$this->reset();
		return $valid;
	}

	/**
	 * Reset/delete a cookie.
	 *
	 * @return bool
	 */
	public function reset() {
		return $this->handle_cookie( static::get_storage_cookie_name(), '', 0 );
	}

	/**
	 * Generate a unique value to use.
	 * If using on PHP 7, it will be cryptographically secure.
	 *
	 * @see https://secure.php.net/manual/en/function.random-bytes.php
	 *
	 * @param int $bytes - number of bytes to generate.
	 *
	 * @return string
	 */
	public function generate_unique( $bytes = 32 ) {
		$nonce_bytes = function_exists( 'random_bytes' )
			// phpcs:ignore
			? random_bytes( $bytes )
			: openssl_random_pseudo_bytes( $bytes );
		return bin2hex( $nonce_bytes );
	}

	/**
	 * Set or delete a cookie value.
	 *
	 * @param string $cookie_name - name of the cookie to set.
	 * @param mixed  $cookie_value - value to set for the cookie.
	 * @param int    $cookie_exp - cookie expiration, pass any value less than now to delete the cookie.
	 *
	 * @return bool
	 */
	protected function handle_cookie( $cookie_name, $cookie_value, $cookie_exp ) {
		$illegal_chars = ",; \t\r\n\013\014";
		if ( strpbrk( $cookie_name, $illegal_chars ) != null ) {
			WP_Auth0_ErrorManager::insert_auth0_error(
				__METHOD__,
				new WP_Error(
					'invalid_cookie',
					'Cookie names and values cannot contain any of the following: ' . wp_slash( $illegal_chars )
				)
			);
			return false;
		}

		// Cookie is being deleted.
		if ( $cookie_exp <= time() ) {

			// Delete SameSite=None fallback cookie.
			if ( $this->same_site_none ) {
				unset( $_COOKIE[ '_' . $cookie_name ] );
				$this->write_cookie( '_' . $cookie_name, '', 0 );
			}

			unset( $_COOKIE[ $cookie_name ] );
			return $this->write_cookie( $cookie_name, '', 0 );
		}

		$_COOKIE[ $cookie_name ] = $cookie_value;

		// Set SameSite=None fallback cookie and use headers for main cookie.
		if ( $this->same_site_none ) {
			$_COOKIE[ '_' . $cookie_name ] = $cookie_value;
			$this->write_cookie_header( $cookie_name, $cookie_value, $cookie_exp );
			return $this->write_cookie( '_' . $cookie_name, $cookie_value, $cookie_exp );
		}

		return $this->write_cookie( $cookie_name, $cookie_value, $cookie_exp );
	}

	/**
	 * Build the header to use when setting SameSite cookies.
	 *
	 * @param string  $name   Cookie name.
	 * @param string  $value  Cookie value.
	 * @param integer $expire Cookie expiration timecode.
	 *
	 * @return string
	 *
	 * @see https://github.com/php/php-src/blob/master/ext/standard/head.c#L77
	 *
	 * @codeCoverageIgnore
	 */
	protected function get_same_site_cookie_header( $name, $value, $expire ) {
		$date = new \Datetime();
		$date->setTimestamp( $expire )->setTimezone( new \DateTimeZone( 'GMT' ) );

		return sprintf(
			'Set-Cookie: %s=%s; path=/; expires=%s; HttpOnly; SameSite=None; Secure',
			$name,
			$value,
			$date->format( $date::COOKIE )
		);
	}

	/**
	 * Wrapper around PHP core setcookie() function to assist with testing.
	 *
	 * @param string  $name   Complete cookie name to set.
	 * @param string  $value  Value of the cookie to set.
	 * @param integer $expire Expiration time in Unix timecode format.
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	protected function write_cookie( $name, $value, $expire ) {
		return setcookie( $name, $value, $expire, '/', '', false, true );
	}

	/**
	 * Wrapper around PHP core header() function to assist with testing.
	 *
	 * @param string  $name   Complete cookie name to set.
	 * @param string  $value  Value of the cookie to set.
	 * @param integer $expire Expiration time in Unix timecode format.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function write_cookie_header( $name, $value, $expire ) {
		header( $this->get_same_site_cookie_header( $name, $value, $expire ), false );
	}

	/**
	 * Get the name of the cookie to store.
	 *
	 * @return string
	 */
	public static function get_storage_cookie_name() {
		// phpcs:ignore
		return apply_filters( 'auth0_nonce_cookie_name', static::NONCE_COOKIE_NAME );
	}
}
