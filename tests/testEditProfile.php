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

	use domDocumentHelpers;

	use setUpTestDb;

	use hookHelpers;

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
	 * WP_Auth0_EditProfile instance.
	 *
	 * @var WP_Auth0_EditProfile
	 */
	public static $editProfile;

	/**
	 * Setup before the class starts.
	 */
	public static function setUpBeforeClass() {
		self::$options     = WP_Auth0_Options::Instance();
		self::$dbManager   = new WP_Auth0_DBManager( self::$options );
		self::$usersRepo   = new WP_Auth0_UsersRepo( self::$options );
		self::$editProfile = new WP_Auth0_EditProfile( self::$dbManager, self::$usersRepo, self::$options );
		parent::setUpBeforeClass();
	}

	/**
	 * Test that correct hooks are loaded
	 */
	public function testInitHooks() {
		$personal_options_update = $this->getHooked( 'personal_options_update' );
		$found                   = 0;
		foreach ( $personal_options_update as $hooked ) {
			if ( ! is_array( $hooked['function'] ) ) {
				continue;
			}
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'override_email_update':
					$this->assertEquals( 1, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 1, $found );

		$edit_user_profile = $this->getHooked( 'edit_user_profile' );
		$found             = 0;
		foreach ( $edit_user_profile as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'show_delete_identity':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
				case 'show_delete_mfa':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 2, $found );

		$show_user_profile = $this->getHooked( 'show_user_profile' );
		$found             = 0;
		foreach ( $show_user_profile as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'show_delete_identity':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
				case 'show_delete_mfa':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 2, $found );

		$wp_ajax_auth0_delete_data = $this->getHooked( 'wp_ajax_auth0_delete_data' );
		$found                     = 0;
		foreach ( $wp_ajax_auth0_delete_data as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'delete_user_data':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 1, $found );

		$wp_ajax_auth0_delete_mfa = $this->getHooked( 'wp_ajax_auth0_delete_mfa' );
		$found                    = 0;
		foreach ( $wp_ajax_auth0_delete_mfa as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'delete_mfa':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 1, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 1, $found );

		$user_profile_update_errors = $this->getHooked( 'user_profile_update_errors' );
		$found                      = 0;
		foreach ( $user_profile_update_errors as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'validate_new_password':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 2, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 1, $found );

		$validate_password_reset = $this->getHooked( 'validate_password_reset' );
		$found                   = 0;
		foreach ( $validate_password_reset as $hooked ) {
			$this->assertEquals( 'WP_Auth0_EditProfile', $hooked['function'][0] );
			switch ( $hooked['function'][1] ) {
				case 'validate_new_password':
					$this->assertEquals( 10, $hooked['priority'] );
					$this->assertEquals( 2, $hooked['accepted_args'] );
					$found++;
					break;
			}
		}
		$this->assertEquals( 1, $found );
	}

	/**
	 * Test that the ID delete control appears under certain conditions.
	 */
	public function testShowDeleteIdentity() {
		// Should not show this control if not an admin.
		ob_start();
		self::$editProfile->show_delete_identity();
		$this->assertEmpty( ob_get_clean() );

		$user_id = $this->setGlobalUser( 1 );

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

		$user_id = $this->setGlobalUser( 1 );

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

	/**
	 * Set the global WP user.
	 * TODO: Move to tests/traits/users.php when rebased.
	 *
	 * @param int $user_id - WP user ID to set.
	 *
	 * @return int
	 */
	private function setGlobalUser( $user_id ) {
		global $user_id;
		$user_id = 1;
		wp_set_current_user( $user_id );
		$this->assertTrue( current_user_can( 'edit_users' ) );
		return $user_id;
	}

	/**
	 * Store dummy Auth0 data.
	 * TODO: Move to tests/traits/users.php when rebased.
	 *
	 * @param int $user_id - WP user ID to set.
	 */
	private function storeAuth0Data( $user_id ) {
		$userinfo        = new stdClass();
		$userinfo->sub   = 'auth0|' . uniqid();
		$userinfo->email = uniqid() . '@example.com';
		self::$usersRepo->update_auth0_object( $user_id, $userinfo );
	}
}
