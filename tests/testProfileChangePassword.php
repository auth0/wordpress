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
	 * Test that correct hooks are loaded
	 */
	public function testInitHooks() {

		$expect_hooked = [
			'validate_new_password' => [
				'priority'      => 10,
				'accepted_args' => 2,
			],
		];
		// Same method hooked to both actions.
		$this->assertHooked( 'user_profile_update_errors', 'WP_Auth0_Profile_Change_Password', $expect_hooked );
		$this->assertHooked( 'validate_password_reset', 'WP_Auth0_Profile_Change_Password', $expect_hooked );
	}

	/**
	 * Test that the validate_new_password method works as expected.
	 */
	public function testValidateNewPassword() {
		$errors = new WP_Error();

		$user_data = $this->createUser();
		$user_id   = $user_data->ID;
		$user_obj  = get_user_by( 'id', $user_id );

		// Create a stub for the WP_Auth0_Api_Change_Password class.
		$mock_api_test_password = $this
			->getMockBuilder( WP_Auth0_Api_Change_Password::class )
			->setMethods( [ 'call' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock_api_test_password->method( 'call' )->willReturn( true, false );

		$change_password = new WP_Auth0_Profile_Change_Password( $mock_api_test_password );

		// Call should fail because of a missing password.
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		$_POST['pass1'] = uniqid();

		// Call should fail with a password because of a missing user ID.
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		// Call should fail with a user object or user_id in $_POST because of no Auth0 data stored.
		$this->assertFalse( $change_password->validate_new_password( $errors, $user_obj ) );
		$_POST['user_id'] = $user_id;
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		$this->storeAuth0Data( $user_id, 'not-auth0' );

		// Call should fail with Auth0 data stored because of a wrong strategy.
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );

		$this->storeAuth0Data( $user_id );

		// Call should succeed with a mocked API.
		$this->assertTrue( $change_password->validate_new_password( $errors, false ) );

		// Call should fail on the second call with a mocked API.
		$this->assertFalse( $change_password->validate_new_password( $errors, false ) );
		$this->assertEquals( 'Password could not be updated.', $errors->errors['auth0_password'][0] );
		$this->assertEquals( 'pass1', $errors->error_data['auth0_password']['form-field'] );
		$this->assertFalse( isset( $_POST['pass1'] ) );
	}
}
