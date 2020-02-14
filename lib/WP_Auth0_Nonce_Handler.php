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
	private function __construct() {
		$this->init();
	}

	/**
	 * Start-up process to make sure we have something stored.
	 */
	protected function init() {
		// If a NONCE_COOKIE_NAME is not defined then we don't need to persist the nonce value.
		if ( defined( static::NONCE_COOKIE_NAME ) && isset( $_COOKIE[ static::get_storage_cookie_name() ] ) ) {
			// Have a cookie, don't want to generate a new one.
			// TODO: validate whether we need to persist this value and sanitize if so.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$this->unique = $_COOKIE[ static::get_storage_cookie_name() ];
		} else {
			// No cookie, need to create one.
			$this->unique = $this->generate_unique();
		}
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
		$this->reset();
		return $valid;
	}

	/**
	 * Get and delete the stored value.
	 *
	 * @return string|null
	 */
	public function get_once() {
		$cookie_name = static::get_storage_cookie_name();
		// Null coalescing validates the input variable.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$value = $_COOKIE[ $cookie_name ] ?? null;
		$this->reset();
		return $value;
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
	 * Generate a cryptographically secure unique value to use.
	 *
	 * @see https://secure.php.net/manual/en/function.random-bytes.php
	 *
	 * @param int $bytes - number of bytes to generate.
	 *
	 * @return string
	 */
	public function generate_unique( $bytes = 32 ) {
		return bin2hex( random_bytes( $bytes ) );
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
		if ( $cookie_exp <= time() ) {
			unset( $_COOKIE[ $cookie_name ] );
			return setcookie( $cookie_name, $cookie_value, 0, '/' );
		} else {
			$_COOKIE[ $cookie_name ] = $cookie_value;
			return setcookie( $cookie_name, $cookie_value, $cookie_exp, '/', '', false, true );
		}
	}

	/**
	 * Get the name of the cookie to store.
	 *
	 * @return string
	 */
	public static function get_storage_cookie_name() {
		return apply_filters( 'auth0_nonce_cookie_name', static::NONCE_COOKIE_NAME );
	}
}
