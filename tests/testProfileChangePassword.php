<?php
/**
 * Contains Class TestProfileChangePassword.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestProfileChangePassword.
 */
class TestProfileChangePassword extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use HookHelpers;

	use UsersHelper;

	use HttpHelpers;

	/**
	 * Setup before the class starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Test that correct hooks are loaded.
	 */
	public function testInitHooks() {
		$expect_hooked = [
			'wp_auth0_validate_new_password' => [
				'priority'      => 10,
				'accepted_args' => 2,
			],
		];
		// Same method hooked to all 3 actions.
		$this->assertHookedFunction( 'user_profile_update_errors', $expect_hooked );
		$this->assertHookedFunction( 'validate_password_reset', $expect_hooked );
		$this->assertHookedFunction( 'woocommerce_save_account_details_errors', $expect_hooked );
	}

	public function testThatIncorrectUserIdStopsProcess() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_empty_body';

		$user_id  = $this->createUser()->ID;
		$errors   = new WP_Error();
		$password = uniqid();

		// Core WP profile update fields.
		$_POST['pass1']   = $password;
		$_POST['pass2']   = $password;
		$_POST['user_id'] = 3;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user_id, 'auth0' );
		$this->setGlobalUser( $user_id );

		self::setApiToken( 'update:users' );

		$this->assertFalse( wp_auth0_validate_new_password( $errors, true ) );
	}

	/**
	 * Test that password update succeeds when run in the user_profile_update_errors hook.
	 */
	public function testSuccessfulPasswordChangeDuringProfileUpdate() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_empty_body';

		$user_id  = $this->createUser()->ID;
		$errors   = new WP_Error();
		$password = uniqid();

		// Core WP profile update fields.
		$_POST['pass1']   = $password;
		$_POST['pass2']   = $password;
		$_POST['user_id'] = $user_id;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user_id, 'auth0' );
		$this->setGlobalUser( $user_id );

		self::setApiToken( 'update:users' );

		$this->assertTrue( wp_auth0_validate_new_password( $errors, true ) );
		$this->assertEquals( $password, $_POST['pass1'] );
		$this->assertEquals( $password, $_POST['pass2'] );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that password update succeeds when run in the validate_password_reset hook.
	 */
	public function testSuccessfulPasswordChangeDuringPasswordReset() {
		$password = uniqid();
		$user     = $this->createUser();
		$errors   = new WP_Error();
		$this->startHttpMocking();
		$this->http_request_type = 'success_empty_body';

		// Core WP form fields sent for password update.
		$_POST['pass1'] = $password;
		$_POST['pass2'] = $password;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		self::setApiToken( 'update:users' );

		self::setGlobalUser( $user->ID );

		$this->assertTrue( wp_auth0_validate_new_password( $errors, $user ) );
		$this->assertEquals( $password, $_POST['pass1'] );
		$this->assertEquals( $password, $_POST['pass2'] );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that password update succeeds when run in the woocommerce_save_account_details_errors hook.
	 */
	public function testSuccessfulPasswordChangeDuringWooAccountEdit() {
		$user   = $this->createUser();
		$errors = new WP_Error();
		$this->startHttpMocking();
		$this->http_request_type = 'success_empty_body';

		// WooCommerce form fields sent for password update.
		$_POST['password_1'] = uniqid();

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		self::setApiToken( 'update:users' );

		self::setGlobalUser( $user->ID );

		$this->assertTrue( wp_auth0_validate_new_password( $errors, $user ) );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that the change password process handles escaped data.
	 */
	public function testThatPasswordIsUnescapedBeforeSending() {
		$this->startHttpHalting();
		self::$opts->set( 'domain', 'example.auth0.com' );

		$user   = $this->createUser();
		$errors = new WP_Error();

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		// Set a password with special characters.
		$new_password        = uniqid() . '"' . uniqid() . "'" . uniqid() . '\\' . uniqid();
		$_POST['password_1'] = wp_slash( $new_password );

		self::setApiToken( 'update:users' );

		self::setGlobalUser( $user->ID );

		$decoded_res = [];
		try {
			wp_auth0_validate_new_password( $errors, $user );
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertEquals( $new_password, $decoded_res['body']['password'] );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that empty password fields will skip password update.
	 */
	public function testThatEmptyPasswordFieldSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except a password field.
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );

		$this->assertFalse( wp_auth0_validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that the password update is skipped if no user record is provided.
	 */
	public function testThatMissingUserRecordSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except a user record.
		$_POST['pass1'] = uniqid();
		$this->storeAuth0Data( $user_id );

		$this->assertFalse( wp_auth0_validate_new_password( $errors, false ) );
	}

	/**
	 * Test that a user without Auth0 data skips update.
	 */
	public function testThatNonAuth0UserSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except Auth0 userinfo.
		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;

		$this->assertFalse( wp_auth0_validate_new_password( $errors, false ) );
	}

	/**
	 * Test that a user with a non-DB strategy skips update.
	 */
	public function testThatNonDbStrategySkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except Auth0 userinfo for a DB user.
		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id, 'not-a-db-strategy' );

		$this->assertFalse( wp_auth0_validate_new_password( $errors, false ) );
	}

	/**
	 * Test that an API failure will set UI banners and cancel the password change.
	 */
	public function testThatApiFailureSetsErrorsUnsetsPassword() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Setup correct user data.
		$_POST['pass1']   = uniqid();
		$_POST['pass2']   = $_POST['pass1'];
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );
		$this->setGlobalUser();

		$this->assertFalse( wp_auth0_validate_new_password( $errors, false ) );
		$this->assertEquals( 'Password could not be updated.', $errors->errors['auth0_password'][0] );
		$this->assertEquals( 'pass1', $errors->error_data['auth0_password']['form-field'] );
		$this->assertFalse( isset( $_POST['pass1'] ) );
		$this->assertFalse( isset( $_POST['pass2'] ) );
	}
}
