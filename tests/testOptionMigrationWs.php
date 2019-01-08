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
		self::$opts->reset();
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
	 * Test that turning migration endpoints off does not affect new input.
	 */
	public function testThatChangingMigrationToOffKeepsTokenData() {
		self::$opts->set( 'migration_token', 'new_token' );
		$input     = [
			'migration_ws'       => 0,
			'migration_token_id' => 'new_token_id',
		];
		$old_input = [ 'migration_ws' => 1 ];
		$validated = self::$admin->migration_ws_validation( $old_input, $input );
		$this->assertEquals( $input, $validated );
	}

	/**
	 * Test that turning on migration keeps the existing token and sets an admin notification.
	 */
	public function testThatChangingMigrationToOnKeepsToken() {
		self::$opts->set( 'migration_token', 'new_token' );
		$input     = [
			'migration_ws'  => 1,
			'client_secret' => '__test_client_secret__',
		];
		$old_input = [ 'migration_ws' => 0 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertEquals( 'new_token', $validated['migration_token'] );
		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
	}

	/**
	 * Test that turning on migration keeps the existing token and sets an admin notification.
	 */
	public function testThatChangingMigrationToOnKeepsWithJwtSetsId() {
		$client_secret   = '__test_client_secret__';
		$migration_token = JWT::encode( [ 'jti' => '__test_token_id__' ], $client_secret );
		self::$opts->set( 'migration_token', $migration_token );
		$input     = [
			'migration_ws'  => 1,
			'client_secret' => $client_secret,
		];
		$old_input = [ 'migration_ws' => 0 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
		$this->assertEquals( $migration_token, $validated['migration_token'] );
		$this->assertEquals( '__test_token_id__', $validated['migration_token_id'] );
	}

	/**
	 * Test that turning on migration keeps the existing token and sets an admin notification.
	 */
	public function testThatChangingMigrationToOnKeepsWithBase64JwtSetsId() {
		$client_secret = '__test_client_secret__';
		self::$opts->set( 'migration_token', JWT::encode( [ 'jti' => '__test_token_id__' ], $client_secret ) );
		$input     = [
			'migration_ws'              => 1,
			'client_secret'             => JWT::urlsafeB64Encode( $client_secret ),
			'client_secret_b64_encoded' => 1,
		];
		$old_input = [ 'migration_ws' => 0 ];

		$validated = self::$admin->migration_ws_validation( $old_input, $input );

		$this->assertEquals( '__test_token_id__', $validated['migration_token_id'] );
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
		self::$opts->set( 'migration_token', '__test_saved_setting__' );
		$input     = [
			'migration_ws'  => 1,
			'client_secret' => '__test_client_secret__',
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
