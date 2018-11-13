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
	 * Test that empty password fields will skip password update.
	 */
	public function testThatEmptyPasswordFieldSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		$mock_api_test_password = $this->getStub( true );
		$change_password        = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );

		// Test core WP password field.
		unset( $_POST['pass1'] );
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		$_POST['password_1'] = uniqid();
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );

		// Test WooCommerce password field.
		unset( $_POST['password_1'] );
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
	}

	/**
	 * Test that empty user data will skip the password update.
	 */
	public function testThatMissingUserDataSkipsUpdate() {
		$user_obj = $this->createUser();
		$user_id  = $user_obj->ID;
		$errors   = new WP_Error();

		$mock_api_test_password = $this->getStub( true );
		$change_password        = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );

		// Test core WP profile update screen field.
		unset( $_POST['user_id'] );
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		// Test user object.
		$this->assertTrue( $change_password->validate_new_password( $errors, $user_obj ) );
	}

	/**
	 * Test that a user without Auth0 data or with a non-DB strategy skips update.
	 */
	public function testThatNonAuth0UserSkipsUpdate() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		$mock_api_test_password = $this->getStub( true );
		$change_password        = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

		$_POST['pass1']   = uniqid();
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );

		// Test that an unlinked user will not be updated.
		self::$users_repo->delete_auth0_object( $user_id );
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		// Test that a linked, non-DB user will not be updated.
		$this->storeAuth0Data( $user_id, 'not-a-db-strategy' );
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
	}

	/**
	 * Test that an API failure will set UI banners and cancel the password change.
	 */
	public function testThatApiFailureSetsErrorsUnsetsPassword() {
		$user_id = $this->createUser()->ID;
		$errors  = new WP_Error();

		// API call mocked to succeed.
		$mock_api_test_password = $this->getStub( true );
		$change_password        = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

		// Confirm that data is set to succeed.
		$_POST['pass1']   = uniqid();
		$_POST['pass2']   = $_POST['pass1'];
		$_POST['user_id'] = $user_id;
		$this->storeAuth0Data( $user_id );
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );
		$this->assertEmpty( $errors->get_error_messages() );

		// API call mocked to fail.
		$mock_api_test_password = $this->getStub( false );
		$change_password        = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

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
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function getStub( $success ) {
		$mock_api_test_password = $this
			->getMockBuilder( WP_Auth0_Api_Change_Password::class )
			->setMethods( [ 'call' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock_api_test_password->method( 'call' )->willReturn( $success );
		return $mock_api_test_password;
	}
}
