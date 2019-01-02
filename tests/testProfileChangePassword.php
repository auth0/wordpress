<?php
/**
 * Contains Class TestProfileChangePassword.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestProfileChangePassword.
 */
class TestProfileChangePassword extends TestCase {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use HookHelpers;

	use SetUpTestDb;

	use UsersHelper;

	use httpHelpers;

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $options;

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	public static $api_client_creds;

	/**
	 * WP_Auth0_Api_Change_Password instance.
	 *
	 * @var WP_Auth0_Api_Change_Password
	 */
	public static $api_change_password;

	/**
	 * WP_Auth0_Profile_Change_Password instance.
	 *
	 * @var WP_Auth0_Profile_Change_Password
	 */
	public static $change_password;

	/**
	 * WP_Auth0_UsersRepo instance.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	protected static $users_repo;

	/**
	 * Setup before the class starts.
	 */
	public static function setUpBeforeClass() {
		self::$options             = WP_Auth0_Options::Instance();
		self::$api_client_creds    = new WP_Auth0_Api_Client_Credentials( self::$options );
		self::$api_change_password = new WP_Auth0_Api_Change_Password( self::$options, self::$api_client_creds );
		self::$change_password     = new WP_Auth0_Profile_Change_Password( self::$api_change_password );
		self::$users_repo          = new WP_Auth0_UsersRepo( self::$options );
	}

	/**
	 * Test that correct hooks are loaded.
	 */
	public function testInitHooks() {

		$expect_hooked = [
			'validate_new_password' => [
				'priority'      => 10,
				'accepted_args' => 2,
			],
		];
		// Same method hooked to all 3 actions.
		$class_name = 'WP_Auth0_Profile_Change_Password';
		$this->assertHooked( 'user_profile_update_errors', $class_name, $expect_hooked );
		$this->assertHooked( 'validate_password_reset', $class_name, $expect_hooked );
		$this->assertHooked( 'woocommerce_save_account_details_errors', $class_name, $expect_hooked );
	}

	/**
	 * Test that password update succeeds when run in the user_profile_update_errors hook.
	 */
	public function testSuccessfulPasswordChangeDuringProfileUpdate() {
		$user_id  = $this->createUser()->ID;
		$errors   = new WP_Error();
		$password = uniqid();

		// Core WP profile update fields.
		$_POST['pass1']   = $password;
		$_POST['pass2']   = $password;
		$_POST['user_id'] = $user_id;

		// API call mocked to succeed.
		$change_password = $this->getStub( true );

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user_id, 'auth0' );

		$this->assertTrue( $change_password->validate_new_password( $errors, true ) );
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

		// API call mocked to succeed.
		$change_password = $this->getStub( true );

		// Core WP form fields sent for password update.
		$_POST['pass1'] = $password;
		$_POST['pass2'] = $password;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		$this->assertTrue( $change_password->validate_new_password( $errors, $user ) );
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

		// API call mocked to succeed.
		$change_password = $this->getStub( true );

		// WooCommerce form fields sent for password update.
		$_POST['password_1'] = uniqid();

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		$this->assertTrue( $change_password->validate_new_password( $errors, $user ) );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that the change password process handles escaped data.
	 */
	public function testThatPasswordIsUnescapedBeforeSending() {
		$this->startHttpHalting();
		self::$options->set( 'domain', 'example.auth0.com' );

		$user   = $this->createUser();
		$errors = new WP_Error();

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		// Set a password with special characters.
		$new_password        = uniqid() . '"' . uniqid() . "'" . uniqid() . '\\' . uniqid();
		$_POST['password_1'] = wp_slash( $new_password );

		// API call mocked to pass bearer check.
		$mock_api = $this
			->getMockBuilder( WP_Auth0_Api_Change_Password::class )
			->setMethods( [ 'set_bearer' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock_api->method( 'set_bearer' )->willReturn( true );
		$change_password = new WP_Auth0_Profile_Change_Password( $mock_api );

		$decoded_res = [];
		try {
			$change_password->validate_new_password( $errors, $user );
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
		$change_password  = $this->getStub( true );
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );

		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );
	}

	/**
	 * Test that the password update is skipped if no user record is provided.
	 */
	public function testThatMissingUserRecordSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except a user record.
		$change_password = $this->getStub( true );
		$_POST['pass1']  = uniqid();
		$this->storeAuth0Data( $user_id );

		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
	}

	/**
	 * Test that a user without Auth0 data skips update.
	 */
	public function testThatNonAuth0UserSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except Auth0 userinfo.
		$change_password  = $this->getStub( true );
		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;

		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
	}

	/**
	 * Test that a user with a non-DB strategy skips update.
	 */
	public function testThatNonDbStrategySkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// Provide everything except Auth0 userinfo for a DB user.
		$change_password  = $this->getStub( true );
		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id, 'not-a-db-strategy' );

		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
	}

	/**
	 * Test that an API failure will set UI banners and cancel the password change.
	 */
	public function testThatApiFailureSetsErrorsUnsetsPassword() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// API call mocked to fail.
		$change_password = $this->getStub( false );

		// Setup correct user data.
		$_POST['pass1']   = uniqid();
		$_POST['pass2']   = $_POST['pass1'];
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );

		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
		$this->assertEquals( 'Password could not be updated.', $errors->errors['auth0_password'][0] );
		$this->assertEquals( 'pass1', $errors->error_data['auth0_password']['form-field'] );
		$this->assertFalse( isset( $_POST['pass1'] ) );
		$this->assertFalse( isset( $_POST['pass2'] ) );
	}

	/**
	 * Get an API stub set to pass or fail.
	 *
	 * @param boolean $success - True for the API call to succeed, false for it to fail.
	 *
	 * @return WP_Auth0_Profile_Change_Password
	 */
	public function getStub( $success ) {
		$mock_api_test_password = $this
			->getMockBuilder( WP_Auth0_Api_Change_Password::class )
			->setMethods( [ 'call' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock_api_test_password->method( 'call' )->willReturn( $success );
		return new WP_Auth0_Profile_Change_Password( $mock_api_test_password );
	}
}
