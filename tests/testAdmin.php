<?php
/**
 * Contains Class TestAdmin.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestAdmin.
 */
class TestAdmin extends TestCase {

	use SetUpTestDb {
		setUp as setUpDb;
	}

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Instance of WP_Auth0_Admin.
	 *
	 * @var WP_Auth0_Admin
	 */
	public static $admin;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Default expected success message.
	 *
	 * @var string
	 */
	protected static $default_msg = 'Settings saved.';

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		$router          = new WP_Auth0_Routes( self::$opts );
		self::$admin     = new WP_Auth0_Admin( self::$opts, $router );
		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Runs after each test method.
	 */
	public function setUp() {
		parent::setUp();
		self::setUpDb();
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$error_log->clear();
	}

	/**
	 * Test that a default success message is added if there is not one.
	 */
	public function testThatSettingsPageRendersWithDefaultNotice() {
		ob_start();
		self::$admin->render_settings_page();
		ob_end_clean();

		$notifications = get_settings_errors();

		$this->assertCount( 1, $notifications );
		$this->assertEquals( self::$default_msg, $notifications[0]['message'] );
	}

	/**
	 * Test that a default success message is not added if there is already one.
	 */
	public function testThatSettingsPageRendersWithoutDefaultNotice() {
		add_settings_error( 'wp_auth0_settings', 'wp_auth0_settings', self::$default_msg, 'updated' );

		ob_start();
		self::$admin->render_settings_page();
		ob_end_clean();

		$notifications = get_settings_errors();

		$this->assertCount( 1, $notifications );
		$this->assertEquals( self::$default_msg, $notifications[0]['message'] );
	}

	/**
	 * Test that a default success message is added if there is another non-default message present.
	 */
	public function testThatSettingsPageRendersWithAdditionalNotice() {
		$message_1 = __( 'Another message', 'wp-auth0' );
		add_settings_error( 'wp_auth0_settings', 'wp_auth0_settings', $message_1, 'updated' );

		ob_start();
		self::$admin->render_settings_page();
		ob_end_clean();

		$notifications = get_settings_errors();

		$this->assertCount( 2, $notifications );
		$this->assertEquals( $message_1, $notifications[0]['message'] );
		$this->assertEquals( self::$default_msg, $notifications[1]['message'] );
	}
}
