<?php
/**
 * Contains Class TestEditProfile.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestEditProfile.
 * Test the edit profile class.
 */
class TestEditProfile extends TestCase {

	use ajaxHelpers;

	use domDocumentHelpers;

	use hookHelpers;

	use setUpTestDb;

	use usersHelper;

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $options;

	/**
	 * WP_Auth0_DBManager instance.
	 *
	 * @var WP_Auth0_DBManager
	 */
	public static $dbManager;

	/**
	 * WP_Auth0_UsersRepo instance.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	public static $usersRepo;

	/**
	 * WP_Auth0_Api_Change_Password instance.
	 *
	 * @var WP_Auth0_Api_Change_Password
	 */
	public static $apiChangePassword;

	/**
	 * WP_Auth0_Api_Delete_User_Mfa instance.
	 *
	 * @var WP_Auth0_Api_Delete_User_Mfa
	 */
	public static $apiDeleteMfa;

	/**
	 * WP_Auth0_EditProfile instance.
	 *
	 * @var WP_Auth0_EditProfile
	 */
	public static $editProfile;

	/**
	 * Setup before the class starts.
	 */
	public static function setUpBeforeClass() {
		self::$options           = WP_Auth0_Options::Instance();
		self::$dbManager         = new WP_Auth0_DBManager( self::$options );
		self::$usersRepo         = new WP_Auth0_UsersRepo( self::$options );
		self::$apiChangePassword = new WP_Auth0_Api_Change_Password( self::$options );
		self::$apiDeleteMfa      = new WP_Auth0_Api_Delete_User_Mfa( self::$options );
		self::$editProfile       = new WP_Auth0_EditProfile(
			self::$dbManager,
			self::$usersRepo,
			self::$options,
			self::$apiChangePassword,
			self::$apiDeleteMfa
		);
	}

	/**
	 * Test that correct hooks are loaded
	 */
	public function testInitHooks() {

		$expect_hooked = [
			'override_email_update' => [
				'priority'      => 1,
				'accepted_args' => 1,
			],
		];
		$this->assertHooked( 'personal_options_update', 'WP_Auth0_EditProfile', $expect_hooked );

		$expect_hooked = [
			'show_delete_identity' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
			'show_delete_mfa'      => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		// Same method hooked to both actions.
		$this->assertHooked( 'edit_user_profile', 'WP_Auth0_EditProfile', $expect_hooked );
		$this->assertHooked( 'show_user_profile', 'WP_Auth0_EditProfile', $expect_hooked );

		$expect_hooked = [
			'delete_user_data' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHooked( 'wp_ajax_auth0_delete_data', 'WP_Auth0_EditProfile', $expect_hooked );

		$expect_hooked = [
			'delete_mfa' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHooked( 'wp_ajax_auth0_delete_mfa', 'WP_Auth0_EditProfile', $expect_hooked );

		$expect_hooked = [
			'validate_new_password' => [
				'priority'      => 10,
				'accepted_args' => 2,
			],
		];
		// Same method hooked to both actions.
		$this->assertHooked( 'user_profile_update_errors', 'WP_Auth0_EditProfile', $expect_hooked );
		$this->assertHooked( 'validate_password_reset', 'WP_Auth0_EditProfile', $expect_hooked );

		// Test page-specific JS enqueuing.
		global $pagenow;
		$pagenow = 'profile.php';
		self::$editProfile->init();
		$expect_hooked = [
			'admin_enqueue_scripts' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHooked( 'admin_enqueue_scripts', 'WP_Auth0_EditProfile', $expect_hooked );
		$pagenow = 'user-edit.php';
		self::$editProfile->init();
		$this->assertHooked( 'admin_enqueue_scripts', 'WP_Auth0_EditProfile', $expect_hooked );
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
			->setMethods( [ 'call', 'init_path' ] )
			->setConstructorArgs( [ self::$options ] )
			->getMock();
		$mock_api_test_password->method( 'call' )->willReturn( true, false );
		$mock_api_test_password->method( 'init_path' )->willReturn( $mock_api_test_password );

		$edit_profile = new WP_Auth0_EditProfile(
			self::$dbManager,
			self::$usersRepo,
			self::$options,
			$mock_api_test_password,
			self::$apiDeleteMfa
		);

		// Call should fail because of a missing password.
		$this->assertFalse( $edit_profile->validate_new_password( $errors, false ) );

		$_POST['pass1'] = uniqid();

		// Call should fail with a password because of a missing user ID.
		$this->assertFalse( $edit_profile->validate_new_password( $errors, false ) );

		// Call should fail with a user object or user_id in $_POST because of no Auth0 data stored.
		$this->assertFalse( $edit_profile->validate_new_password( $errors, $user_obj ) );
		$_POST['user_id'] = $user_id;
		$this->assertFalse( $edit_profile->validate_new_password( $errors, false ) );

		$this->storeAuth0Data( $user_id, 'not-auth0' );

		// Call should fail with Auth0 data stored because of a wrong strategy.
		$this->assertFalse( $edit_profile->validate_new_password( $errors, false ) );

		$this->storeAuth0Data( $user_id );

		// Call should succeed with a mocked API.
		$this->assertTrue( $edit_profile->validate_new_password( $errors, false ) );

		// Call should fail on the second call with a mocked API.
		$this->assertFalse( $edit_profile->validate_new_password( $errors, false ) );
		$this->assertEquals( 'Password could not be updated.', $errors->errors['auth0_password'][0] );
		$this->assertEquals( 'pass1', $errors->error_data['auth0_password']['form-field'] );
		$this->assertFalse( isset( $_POST['pass1'] ) );
	}

	/**
	 * Test that the delete user data action works as expected.
	 */
	public function testDeleteUserDataAjax() {
		$this->startAjax();

		// No nonce set should fail.
		$caught_exception = false;
		try {
			self::$editProfile->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'bad_nonce' === $e->getMessage() );
		}
		$this->assertTrue( $caught_exception );

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_identity' );

		// Nonce passes but no user_id to use.
		ob_start();
		$caught_exception = false;
		try {
			self::$editProfile->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Empty user_id"}}', $return_json );

		// Set the user ID.
		$_POST['user_id'] = 1;

		// Nonce and user_id pass, but user is not authorized.
		ob_start();
		$caught_exception = false;
		try {
			self::$editProfile->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Forbidden"}}', $return_json );

		// Set the admin user, store Auth0 profile data to delete, and reset the nonce.
		$this->setGlobalUser();
		$this->storeAuth0Data( 1 );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_identity' );

		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_id' ) );
		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_obj' ) );
		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'last_update' ) );

		ob_start();
		$caught_exception = false;
		try {
			self::$editProfile->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":true}', $return_json );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'last_update' ) );
	}

	/**
	 * Test that the delete MFA action works as expected.
	 */
	public function testDeleteMfaAjax() {
		$this->startAjax();

		// Create a stub for the WP_Auth0_Api_Change_Password class.
		$mock_api_delete_mfa = $this
			->getMockBuilder( WP_Auth0_Api_Delete_User_Mfa::class )
			->setMethods( [ 'call', 'init_path' ] )
			->setConstructorArgs( [ self::$options ] )
			->getMock();
		$mock_api_delete_mfa->method( 'call' )->willReturn( true, false );
		$mock_api_delete_mfa->method( 'init_path' )->willReturn( $mock_api_delete_mfa );

		$edit_profile = new WP_Auth0_EditProfile(
			self::$dbManager,
			self::$usersRepo,
			self::$options,
			self::$apiChangePassword,
			$mock_api_delete_mfa
		);

		// No nonce set should fail.
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'bad_nonce' === $e->getMessage() );
		}
		$this->assertTrue( $caught_exception );

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Nonce passes but no user_id to use.
		ob_start();
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Empty user_id"}}', $return_json );

		// Set the user ID.
		$_POST['user_id'] = 1;

		// Nonce and user_id pass, but user is not authorized.
		ob_start();
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Forbidden"}}', $return_json );

		// Set the admin user.
		$this->setGlobalUser();

		// Have to reset the nonce as well.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Nonce, user_id, and admin user pass but user is not authorized.
		ob_start();
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Auth0 profile data not found"}}', $return_json );

		// Set Auth0 profile.
		$this->storeAuth0Data( 1 );

		// Have to reset the nonce as well.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Mocked to simulate a successful API call.
		ob_start();
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":true}', $return_json );

		// Mocked to simulate a failed API call.
		ob_start();
		$caught_exception = false;
		try {
			$edit_profile->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"API call failed"}}', $return_json );
	}

	/**
	 * Test that the ID delete control appears under certain conditions.
	 */
	public function testShowDeleteIdentity() {
		// Should not show this control if not an admin.
		ob_start();
		self::$editProfile->show_delete_identity();
		$this->assertEmpty( ob_get_clean() );

		$user_id = $this->setGlobalUser();

		// Should not show this control if user is not an Auth0-connected user.
		ob_start();
		self::$editProfile->show_delete_identity();
		$this->assertEmpty( ob_get_clean() );

		$this->storeAuth0Data( $user_id );

		ob_start();
		self::$editProfile->show_delete_identity();
		$delete_id_html = ob_get_clean();

		// Make sure we have the id attribute that connects to the AJAX action.
		$input = $this->getDomListFromTagName( $delete_id_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'auth0_delete_data', $input->item( 0 )->getAttribute( 'id' ) );

		// Make sure we have a table with the right class.
		$table = $this->getDomListFromTagName( $delete_id_html, 'table' );
		$this->assertEquals( 1, $table->length );
		$this->assertEquals( 'form-table', $table->item( 0 )->getAttribute( 'class' ) );
	}

	/**
	 * Test that the ID delete control appears under certain conditions.
	 */
	public function testShowDeleteMfa() {
		// Should not show this control if not an admin.
		ob_start();
		self::$editProfile->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		$user_id = $this->setGlobalUser();

		// Should not show this control if MFA is not turned on.
		ob_start();
		self::$editProfile->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		self::$options->set( 'mfa', 1 );

		// Should not show this control if user is not an Auth0-connected user.
		ob_start();
		self::$editProfile->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		$this->storeAuth0Data( $user_id );

		ob_start();
		self::$editProfile->show_delete_mfa();
		$delete_mfa_html = ob_get_clean();

		$this->assertNotEmpty( $delete_mfa_html );

		// Make sure we have the id attribute that connects to the AJAX action.
		$input = $this->getDomListFromTagName( $delete_mfa_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'auth0_delete_mfa', $input->item( 0 )->getAttribute( 'id' ) );

		// Make sure we have a table with the right class.
		$table = $this->getDomListFromTagName( $delete_mfa_html, 'table' );
		$this->assertEquals( 1, $table->length );
		$this->assertEquals( 'form-table', $table->item( 0 )->getAttribute( 'class' ) );
	}
}
