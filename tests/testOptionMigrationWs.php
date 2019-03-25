<?php
/**
 * Contains Class TestOptionMigrationWs.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

/**
 * Class TestOptionMigrationWs.
 */
class TestOptionMigrationWs extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Admin_Advanced.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * Runs before each test starts.
	 */
	public function setUp() {
		parent::setUp();
		$router      = new WP_Auth0_Routes( self::$opts );
		self::$admin = new WP_Auth0_Admin_Advanced( self::$opts, $router );
	}

	/**
	 * Test that the migration WS setting field is rendered properly.
	 */
	public function testThatSettingsFieldRendersProperly() {
		$field_args = [
			'label_for' => 'wpa0_migration_ws',
			'opt_name'  => 'migration_ws',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_migration_ws( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'checkbox', $input->item( 0 )->getAttribute( 'type' ) );
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
	}

	/**
	 * Test that correct settings field documentation appears when the setting is off.
	 */
	public function testThatCorrectFieldDocsShowWhenMigrationIsOff() {
		$field_args = [
			'label_for' => 'wpa0_migration_ws',
			'opt_name'  => 'migration_ws',
		];

		$this->assertFalse( self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_migration_ws( $field_args );
		$field_html = ob_get_clean();

		$this->assertContains( 'User migration endpoints deactivated', $field_html );
		$this->assertContains( 'Custom database connections can be deactivated', $field_html );
		$this->assertContains( 'https://manage.auth0.com/#/connections/database', $field_html );
	}

	/**
	 * Test that correct settings field documentation and additional controls appear when the setting is on.
	 */
	public function testThatCorrectFieldDocsShowWhenMigrationIsOn() {
		$field_args = [
			'label_for' => 'wpa0_migration_ws',
			'opt_name'  => 'migration_ws',
		];

		self::$opts->set( $field_args['opt_name'], 1 );

		// Get the field HTML.
		ob_start();
		self::$admin->render_migration_ws( $field_args );
		$field_html = ob_get_clean();

		$this->assertContains( 'User migration endpoints activated', $field_html );
		$this->assertContains( 'The custom database scripts need to be configured manually', $field_html );
		$this->assertContains( 'https://auth0.com/docs/cms/wordpress/user-migration', $field_html );

		$code_block = $this->getDomListFromTagName( $field_html, 'code' );
		$this->assertEquals( 'code-block', $code_block->item( 0 )->getAttribute( 'class' ) );
		$this->assertEquals( 'auth0_migration_token', $code_block->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'disabled', $code_block->item( 0 )->getAttribute( 'disabled' ) );
		$this->assertEquals( 'No migration token', $code_block->item( 0 )->nodeValue );

		$token_button = $this->getDomListFromTagName( $field_html, 'button' );
		$this->assertEquals( 'auth0_rotate_migration_token', $token_button->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'Generate New Migration Token', trim( $token_button->item( 0 )->nodeValue ) );
		$this->assertContains(
			'This will change your migration token immediately',
			$token_button->item( 0 )->getAttribute( 'data-confirm-msg' )
		);
		$this->assertContains(
			'The new token must be changed in the custom scripts for your database Connection',
			$token_button->item( 0 )->getAttribute( 'data-confirm-msg' )
		);
	}

	/**
	 * Test that the AJAX rotate token endpoint fails when there is a bad nonce value.
	 */
	public function testThatAjaxTokenRotationFailsWithBadNonce() {
		$this->startAjaxHalting();

		$caught_exception = false;
		$error_msg        = 'No exception';
		try {
			$_REQUEST['_ajax_nonce'] = uniqid();
			self::$admin->auth0_rotate_migration_token();
		} catch ( Exception $e ) {
			$error_msg        = $e->getMessage();
			$caught_exception = ( 'bad_nonce' === $error_msg );
		}
		$this->assertTrue( $caught_exception, $error_msg );
	}

	/**
	 * Test that the AJAX rotate token endpoint saves a new token when the endpoint succeeds.
	 */
	public function testThatAjaxTokenRotationSavesNewToken() {
		$this->startAjaxReturn();

		$old_token = uniqid();
		self::$opts->set( 'migration_token', $old_token );

		ob_start();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'auth0_rotate_migration_token' );
		self::$admin->auth0_rotate_migration_token();
		$return_json = explode( PHP_EOL, ob_get_clean() );

		$this->assertEquals( '{"success":true}', end( $return_json ) );
		$this->assertNotEquals( $old_token, self::$opts->get( 'migration_token' ) );
		$this->assertGreaterThanOrEqual( 64, strlen( self::$opts->get( 'migration_token' ) ) );
	}

	/**
	 * Test that turning migration endpoints off does not affect new input.
	 */
	public function testThatChangingMigrationToOffKeepsTokenData() {
		self::$opts->set( 'migration_token', 'existing_token' );
		$input     = [
			'migration_ws'       => 0,
			'migration_token_id' => 'existing_token_id',
		];
		$validated = self::$admin->migration_ws_validation( [], $input );

		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
		$this->assertEquals( $input['migration_token_id'], $validated['migration_token_id'] );
		$this->assertEquals( 'existing_token', $validated['migration_token'] );
	}

	/**
	 * Test that turning on migration keeps the existing token and sets an admin notification.
	 */
	public function testThatChangingMigrationToOnKeepsToken() {
		self::$opts->set( 'migration_token', 'new_token' );
		$input = [
			'migration_ws'  => 1,
			'client_secret' => '__test_client_secret__',
		];

		$validated = self::$admin->migration_ws_validation( [], $input );

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
		$input = [
			'migration_ws'  => 1,
			'client_secret' => $client_secret,
		];

		$validated = self::$admin->migration_ws_validation( [], $input );

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
		$input = [
			'migration_ws'              => 1,
			'client_secret'             => JWT::urlsafeB64Encode( $client_secret ),
			'client_secret_b64_encoded' => 1,
		];

		$validated = self::$admin->migration_ws_validation( [], $input );

		$this->assertEquals( '__test_token_id__', $validated['migration_token_id'] );
	}

	/**
	 * Test that turning on migration endpoints without a stored token will generate one.
	 */
	public function testThatChangingMigrationToOnGeneratesNewToken() {
		$input = [ 'migration_ws' => 1 ];

		$validated = self::$admin->migration_ws_validation( [], $input );

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
		$input = [
			'migration_ws'  => 1,
			'client_secret' => '__test_client_secret__',
		];

		$opts   = new WP_Auth0_Options();
		$router = new WP_Auth0_Routes( $opts );
		$admin  = new WP_Auth0_Admin_Advanced( $opts, $router );

		$validated = $admin->migration_ws_validation( [], $input );

		$this->assertNull( $validated['migration_token_id'] );
		$this->assertEquals( $input['migration_ws'], $validated['migration_ws'] );
		$this->assertEquals( AUTH0_ENV_MIGRATION_TOKEN, $validated['migration_token'] );
	}
}
