<?php
/**
 * Contains Class TestProfileChangeEmail.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

/**
 * Class TestProfileChangeEmail.
 */
class TestProfileChangeEmail extends WP_Auth0_Test_Case {

	use HookHelpers;

	use HttpHelpers;

	use RedirectHelpers;

	use UsersHelper;

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	public static $api_client_creds;

	/**
	 * Run before the test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );
		self::$users_repo       = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Test that correct hooks are loaded.
	 */
	public function testThatHooksAreLoaded() {
		$expect_hooked = [
			'wp_auth0_profile_change_email' => [
				'priority'      => 100,
				'accepted_args' => 2,
			],
		];
		$this->assertHookedFunction( 'profile_update', $expect_hooked );
	}

	/**
	 * Test that an email update works.
	 */
	public function testSuccessfulEmailUpdate() {
		$this->startHttpMocking();
		$this->setApiToken( 'update:users' );
		$this->http_request_type = 'success_empty_body';

		$user                       = $this->createUser( [], false );
		$new_email                  = $user->data->user_email;
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $new_email;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		$this->assertTrue( wp_auth0_profile_change_email( $user->ID, $old_user ) );
		$this->assertEquals( $new_email, get_user_by( 'id', $user->ID )->data->user_email );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( $user->ID, 'auth0_transient_email_update' ) );
	}

	/**
	 * Test that a non-Auth0 user will skip the email update.
	 */
	public function testThatNonAuth0UserSkipsUpdate() {
		$user                       = $this->createUser( [], false );
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $old_user->data->user_email;

		$this->assertFalse( wp_auth0_profile_change_email( $user->ID, $old_user ) );
	}

	/**
	 * Test that a non-DB strategy user will skip the email update.
	 */
	public function testThatNonDbUserSkipsUpdate() {
		$user                       = $this->createUser( [], false );
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $old_user->data->user_email;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'not-auth0' );

		$this->assertFalse( wp_auth0_profile_change_email( $user->ID, $old_user ) );
	}

	/**
	 * Test that a user change without an email update will skip the email update.
	 */
	public function testThatSameEmailSkipsUpdate() {
		$user     = $this->createUser( [], false );
		$old_user = clone $user;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'not-auth0' );

		$this->assertFalse( wp_auth0_profile_change_email( $user->ID, $old_user ) );
	}

	/**
	 * Test that a failed API call changes the email address back.
	 */
	public function testThatFailedApiCallStopsEmailUpdate() {
		$user                       = $this->createUser( [], false );
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $old_user->data->user_email;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		// Store the usermeta value set for email verification.
		update_user_meta( $user->ID, '_new_email', $user->data->user_email );

		// Need to remove existing filters and re-init with filters from the test class.
		remove_all_filters( 'profile_update' );

		$this->assertFalse( wp_auth0_profile_change_email( $user->ID, $old_user ) );
		$this->assertEquals( $old_user->data->user_email, get_user_by( 'id', $user->ID )->data->user_email );
		$this->assertEmpty( get_user_meta( $user->ID, '_new_email', true ) );
		$this->assertEmpty( WP_Auth0_UsersRepo::get_meta( $user->ID, 'auth0_transient_email_update' ) );
	}

	/**
	 * Test that failed API calls from the user edit or user profile page redirect properly.
	 */
	public function testThatFailedApiRedirectsOnUserEditPage() {
		$this->startRedirectHalting();

		$user                       = $this->createUser( [], false );
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $old_user->data->user_email;

		// Store userinfo for a DB strategy user.
		$this->storeAuth0Data( $user->ID, 'auth0' );

		// Need to remove existing filters and re-init with filters from the test class.
		remove_all_filters( 'profile_update' );

		// Set current page to the user profile.
		global $pagenow;
		$pagenow = 'user-edit.php';

		$caught_redirect = [];
		try {
			wp_auth0_profile_change_email( $user->ID, $old_user );
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $caught_redirect );
		$this->assertEquals( 302, $caught_redirect['status'] );
		$this->assertContains( 'wp-admin/user-edit.php', $caught_redirect['location'] );
		$this->assertContains( 'user_id=' . $user->ID, $caught_redirect['location'] );
		$this->assertContains( 'error=new-email', $caught_redirect['location'] );
	}

	/**
	 * Test that the email update flag is set for a user before the Auth0 API call is made.
	 */
	public function testThatEmailUpdateFlagIsSetBeforeApiCall() {
		$this->startHttpHalting();

		$user                       = $this->createUser( [], false );
		$new_email                  = $user->data->user_email;
		$old_user                   = clone $user;
		$old_user->data->user_email = 'OLD-' . $new_email;
		$this->storeAuth0Data( $user->ID, 'auth0' );

		try {
			wp_auth0_profile_change_email( $user->ID, $old_user );
		} catch ( Exception $e ) {
			// Just need to stop the API call.
		}

		$this->assertEquals( $new_email, WP_Auth0_UsersRepo::get_meta( $user->ID, 'auth0_transient_email_update' ) );
	}
}
