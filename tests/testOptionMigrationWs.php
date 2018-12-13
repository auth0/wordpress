<?php
/**
 * Contains Class TestOptionMigrationWs.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestOptionMigrationWs.
 */
class TestOptionMigrationWs extends TestCase {

	use DomDocumentHelpers;

	use SetUpTestDb {
		setUp as setUpDb;
	}

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Instance of WP_Auth0_Admin_Advanced.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Runs before each test starts.
	 */
	public function setUp() {
		parent::setUp();
		self::setUpDb();
		$router      = new WP_Auth0_Routes( self::$opts );
		self::$admin = new WP_Auth0_Admin_Advanced( self::$opts, $router );
	}

	/**
	 * Runs after each test completes.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$error_log->clear();
	}

	/**
	 * Test that no change in migration setting does not change the token.
	 */
	public function testThatMigrationNotChangingKeepsOldTokenData() {
		$input     = [
			'migration_ws'    => 1,
			'migration_token' => '',
		];
		$old_input = [
			'migration_ws'       => 1,
			'migration_token'    => 'old_token',
			'migration_token_id' => 'old_id',
		];
		$validated = self::$admin->migration_ws_validation( $old_input, $input );
		$this->assertEquals( $old_input['migration_ws'], $validated['migration_ws'] );
		$this->assertEquals( $old_input['migration_token'], $validated['migration_token'] );
		$this->assertNull( $validated['migration_token_id'] );
	}

	/**
	 * Test that turning migration endpoints off will clear out the token and set an admin notice.
	 */
	public function testThatChangingMigrationToOffClearsTokensSetsError() {
		$input     = [
			'migration_ws'    => 0,
			'migration_token' => 'new_token',
		];
		$old_input = [ 'migration_ws' => 1 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertNull( $validated['migration_token'] );
		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );

		$errors = get_settings_errors();
		$this->assertEquals( 'wp_auth0_settings', $errors[0]['setting'] );
		$this->assertEquals( 'wp_auth0_settings', $errors[0]['code'] );
		$this->assertEquals( 'updated', $errors[0]['type'] );
		$this->assertContains( 'User migration endpoints deactivated', $errors[0]['message'] );
		$this->assertContains( 'Custom database connections can be deactivated in the', $errors[0]['message'] );
		$this->assertContains( 'https://manage.auth0.com/#/connections/database', $errors[0]['message'] );
	}

	/**
	 * Test that turning on migration keeps the existing token and sets an admin notification.
	 */
	public function testThatChangingMigrationToOnKeepsTokenSetsError() {
		$input     = [
			'migration_ws'    => 1,
			'migration_token' => 'new_token',
		];
		$old_input = [ 'migration_ws' => 0 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertEquals( $input['migration_token'], $validated['migration_token'] );
		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );

		$errors = get_settings_errors();
		$this->assertEquals( 'wp_auth0_settings', $errors[0]['setting'] );
		$this->assertEquals( 'wp_auth0_settings', $errors[0]['code'] );
		$this->assertEquals( 'updated', $errors[0]['type'] );
		$this->assertContains( 'User migration endpoints activated', $errors[0]['message'] );
		$this->assertContains( 'The custom database scripts needs to be configured manually', $errors[0]['message'] );
		$this->assertContains( 'https://auth0.com/docs/users/migrations/automatic', $errors[0]['message'] );
		$this->assertContains( 'Please see Advanced > Users Migration below for the token to use', $errors[0]['message'] );
	}

	/**
	 * Test that turning on migration endpoints without a stored token will generate one.
	 */
	public function testThatChangingMigrationToOnGeneratesNewToken() {
		$input     = [ 'migration_ws' => 1 ];
		$old_input = [ 'migration_ws' => 0 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertGreaterThan( 64, strlen( $validated['migration_token'] ) );
		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
	}

	/**
	 * Test that a migration token in a constant setting is picked up and validated.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatMigrationTokenInConstantSettingIsValidated() {
		define( 'AUTH0_ENV_MIGRATION_TOKEN', '__test_constant_setting__' );
		$input     = [
			'migration_ws'    => 1,
			'migration_token' => AUTH0_ENV_MIGRATION_TOKEN,
		];
		$old_input = [ 'migration_ws' => 0 ];

		$opts   = new WP_Auth0_Options();
		$router = new WP_Auth0_Routes( $opts );
		$admin  = new WP_Auth0_Admin_Advanced( $opts, $router );

		$validated = $admin->migration_ws_validation( $old_input, $input );

		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
		$this->assertEquals( AUTH0_ENV_MIGRATION_TOKEN, $validated['migration_token'] );
	}
}
