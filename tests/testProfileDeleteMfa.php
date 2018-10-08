<?php
/**
 * Contains Class TestProfileDeleteMfa.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestProfileDeleteMfa.
 */
class TestProfileDeleteMfa extends TestCase {

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
	 * WP_Auth0_Api_Delete_User_Mfa instance.
	 *
	 * @var WP_Auth0_Api_Delete_User_Mfa
	 */
	public static $api_delete_mfa;

	/**
	 * WP_Auth0_Profile_Delete_Mfa instance.
	 *
	 * @var WP_Auth0_Profile_Delete_Mfa
	 */
	public static $delete_mfa;

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
		self::$options          = WP_Auth0_Options::Instance();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$options );
		self::$api_delete_mfa   = new WP_Auth0_Api_Delete_User_Mfa( self::$options, self::$api_client_creds );
		self::$delete_mfa       = new WP_Auth0_Profile_Delete_Mfa( self::$options, self::$api_delete_mfa );
		self::$users_repo       = new WP_Auth0_UsersRepo( self::$options );
	}

	/**
	 * Test that correct hooks are loaded
	 */
	public function testInitHooks() {

		$expect_hooked = [
			'show_delete_mfa' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		// Same method hooked to both actions.
		$this->assertHooked( 'edit_user_profile', 'WP_Auth0_Profile_Delete_Mfa', $expect_hooked );
		$this->assertHooked( 'show_user_profile', 'WP_Auth0_Profile_Delete_Mfa', $expect_hooked );

		$expect_hooked = [
			'delete_mfa' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHooked( 'wp_ajax_auth0_delete_mfa', 'WP_Auth0_Profile_Delete_Mfa', $expect_hooked );
	}

	/**
	 * Test that an AJAX call with no nonce fails.
	 */
	public function testThatAjaxFailsWithNoNonce() {
		$this->startAjaxHalting();

		// No nonce set should fail.
		$caught_exception = false;
		try {
			self::$delete_mfa->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'bad_nonce' === $e->getMessage() );
		}
		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that an AJAX call with no user_id fails.
	 */
	public function testThatAjaxFailsWithNoUserId() {
		$this->startAjaxHalting();

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		ob_start();
		$caught_exception = false;
		try {
			self::$delete_mfa->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Empty user_id"}}', ob_get_clean() );
	}

	/**
	 * Test that an AJAX call with no admin user fails.
	 */
	public function testThatAjaxFailsWithNoAdmin() {
		$this->startAjaxHalting();

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Set the user ID.
		$_POST['user_id'] = 1;

		ob_start();
		$caught_exception = false;
		try {
			self::$delete_mfa->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Forbidden"}}', ob_get_clean() );
	}

	/**
	 * Test that an AJAX call with no Auth0 data fails.
	 */
	public function testThatAjaxFailsWithNoAuth0Data() {
		$this->startAjaxHalting();

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Set the user ID.
		$_POST['user_id'] = 1;

		// Set the admin user and nonce.
		$this->setGlobalUser();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		ob_start();
		$caught_exception = false;
		try {
			self::$delete_mfa->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Auth0 profile data not found"}}', ob_get_clean() );
	}

	/**
	 * Test that the delete MFA action works as expected.
	 */
	public function testDeleteMfaAjax() {
		$this->startAjaxHalting();

		// Set the user ID.
		$_POST['user_id'] = 1;

		// Set the admin user and nonce.
		$this->setGlobalUser();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );

		// Set Auth0 profile.
		$this->storeAuth0Data( 1 );

		// Mocked to simulate a failed API call.
		$delete_mfa       = $this->getStub( false );
		$caught_exception = false;

		ob_start();
		try {
			$delete_mfa->delete_mfa();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"API call failed"}}', ob_get_clean() );
	}

	/**
	 * Test that an AJAX call will succeed.
	 */
	public function testThatAjaxCallSucceeds() {
		$this->startAjaxReturn();

		$this->setGlobalUser();
		$this->storeAuth0Data( 1 );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_mfa' );
		$_POST['user_id']        = 1;

		// Mocked to simulate a successful API call.
		$delete_mfa = $this->getStub( true );

		ob_start();
		$delete_mfa->delete_mfa();
		$this->assertEquals( '{"success":true}', ob_get_clean() );
	}

	/**
	 * Test that the ID delete control appears under certain conditions.
	 */
	public function testShowDeleteMfa() {
		// Should not show this control if not an admin.
		ob_start();
		self::$delete_mfa->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		$user_id = $this->setGlobalUser();

		// Should not show this control if MFA is not turned on.
		ob_start();
		self::$delete_mfa->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		self::$options->set( 'mfa', 1 );

		// Should not show this control if user is not an Auth0-connected user.
		ob_start();
		self::$delete_mfa->show_delete_mfa();
		$this->assertEmpty( ob_get_clean() );

		$this->storeAuth0Data( $user_id );

		ob_start();
		self::$delete_mfa->show_delete_mfa();
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

	/*
	 * PHPUnit overrides to run after tests.
	 */

	/**
	 * Runs after each test completes.
	 */
	public function tearDown() {
		parent::tearDown();
		$this->stopAjaxHalting();
		$this->stopAjaxReturn();
		self::$users_repo->delete_auth0_object( 1 );
	}

	/*
	 * Test helper functions.
	 */

	/**
	 * Creates a new WP_Auth0_Profile_Delete_Mfa object with a mocked API.
	 *
	 * @param boolean $return - Whether the call method should return true or false.
	 *
	 * @return WP_Auth0_Profile_Delete_Mfa
	 */
	public function getStub( $return ) {
		// Create a stub for the WP_Auth0_Api_Delete_User_Mfa class.
		$mock_api_delete_mfa = $this
			->getMockBuilder( WP_Auth0_Api_Delete_User_Mfa::class )
			->setMethods( [ 'call', 'set_bearer' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock_api_delete_mfa->method( 'set_bearer' )->willReturn( true );
		$mock_api_delete_mfa->method( 'call' )->willReturn( $return );

		return new WP_Auth0_Profile_Delete_Mfa( self::$options, $mock_api_delete_mfa );
	}
}
