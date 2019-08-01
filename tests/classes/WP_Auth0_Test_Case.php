<?php
/**
 * Contains Class WP_Auth0_Test_Case.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class WP_Auth0_Test_Case
 */
abstract class WP_Auth0_Test_Case extends \PHPUnit\Framework\TestCase {

	const TEST_DOMAIN = 'test.domain.com';

	const OPTIONS_NAME = 'wp_auth0_settings';

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	public static $error_log;

	/**
	 * Existing home_url value before tests.
	 *
	 * @var string
	 */
	public static $home_url;

	/**
	 * Runs before test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		self::$error_log = new WP_Auth0_ErrorLog();
		self::$home_url  = home_url();
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();

		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors     = true;
		$wpdb->db_connect();
		ini_set( 'display_errors', 1 );

		self::$opts->reset();
		self::$error_log->clear();
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();

		update_option( 'users_can_register', false );
		update_option( 'home_url', self::$home_url );

		delete_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME );

		if ( method_exists( $this, 'stopAjaxHalting' ) ) {
			$this->stopAjaxHalting();
		}

		if ( method_exists( $this, 'stopAjaxReturn' ) ) {
			$this->stopAjaxReturn();
		}

		if ( method_exists( $this, 'stopHttpHalting' ) ) {
			$this->stopHttpHalting();
		}

		if ( method_exists( $this, 'stopHttpMocking' ) ) {
			$this->stopHttpMocking();
		}

		if ( method_exists( $this, 'stopRedirectHalting' ) ) {
			$this->stopRedirectHalting();
		}

		if ( method_exists( $this, 'stopWpDieHalting' ) ) {
			$this->stopWpDieHalting();
		}

		global $wpdb;
		delete_user_meta( 1, $wpdb->prefix . 'auth0_id' );
		delete_user_meta( 1, $wpdb->prefix . 'auth0_obj' );
		delete_user_meta( 1, $wpdb->prefix . 'last_update' );
	}

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
