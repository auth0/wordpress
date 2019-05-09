<?php
/**
 * Contains Class TestProfileDeleteData.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestProfileDeleteData.
 * Tests functionality of the WP_Auth0_Profile_Delete_Data class.
 */
class TestProfileDeleteData extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use HookHelpers;

	use UsersHelper;

	/**
	 * WP_Auth0_Profile_Delete_Data instance.
	 *
	 * @var WP_Auth0_Profile_Delete_Data
	 */
	protected static $delete_data;

	/**
	 * Setup before the class starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$users_repo  = new WP_Auth0_UsersRepo( self::$opts );
		self::$delete_data = new WP_Auth0_Profile_Delete_Data( self::$users_repo );
	}

	/**
	 * Test that correct hooks are loaded.
	 */
	public function testInitHooks() {

		$expect_hooked = [
			'show_delete_identity' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		// Same method hooked to both actions.
		$this->assertHookedClass( 'edit_user_profile', 'WP_Auth0_Profile_Delete_Data', $expect_hooked );
		$this->assertHookedClass( 'show_user_profile', 'WP_Auth0_Profile_Delete_Data', $expect_hooked );

		$expect_hooked = [
			'delete_user_data' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedClass( 'wp_ajax_auth0_delete_data', 'WP_Auth0_Profile_Delete_Data', $expect_hooked );
	}

	/**
	 * Test that a delete_user_data AJAX call with no nonce fails.
	 */
	public function testThatAjaxFailsWithNoNonce() {
		$this->startAjaxHalting();
		$caught_exception = false;
		try {
			self::$delete_data->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'bad_nonce' === $e->getMessage() );
		}
		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that a delete_user_data AJAX call with no user_id fails.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatAjaxFailsWithNoUserId() {
		$this->startAjaxHalting();

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_identity' );

		$caught_exception = false;
		ob_start();
		try {
			self::$delete_data->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Empty user_id"}}', $return_json );
	}

	/**
	 * Test that a delete_user_data AJAX call with a non-admin user fails.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatAjaxFailsWithNoAdmin() {
		$this->startAjaxHalting();

		// Set the nonce.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_identity' );

		// Set the user ID.
		$_POST['user_id'] = 1;

		$caught_exception = false;
		ob_start();
		try {
			self::$delete_data->delete_user_data();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();

		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"Forbidden"}}', $return_json );
	}

	/**
	 * Test that a delete_user_data AJAX call can succeed.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatAjaxCallSucceeds() {
		$this->startAjaxReturn();

		// Set the user ID.
		$_POST['user_id'] = 1;

		// Set the admin user, store Auth0 profile data to delete, and reset the nonce.
		$this->setGlobalUser();
		$this->storeAuth0Data( 1 );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'delete_auth0_identity' );

		// Make sure we have data to delete.
		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_id' ) );
		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_obj' ) );
		$this->assertNotEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'last_update' ) );

		ob_start();
		self::$delete_data->delete_user_data();
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( 1, 'last_update' ) );
	}

	/**
	 * Test that the ID delete control appears under certain conditions.
	 */
	public function testShowDeleteIdentity() {
		// Should not show this control if not an admin.
		ob_start();
		self::$delete_data->show_delete_identity();
		$this->assertEmpty( ob_get_clean() );

		$user_id = $this->setGlobalUser();

		// Should not show this control if user is not an Auth0-connected user.
		ob_start();
		self::$delete_data->show_delete_identity();
		$this->assertEmpty( ob_get_clean() );

		$this->storeAuth0Data( $user_id );

		ob_start();
		self::$delete_data->show_delete_identity();
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
}
