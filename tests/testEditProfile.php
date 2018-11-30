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
		self::$options     = WP_Auth0_Options::Instance();
		self::$dbManager   = new WP_Auth0_DBManager( self::$options );
		self::$usersRepo   = new WP_Auth0_UsersRepo( self::$options );
		self::$editProfile = new WP_Auth0_EditProfile( self::$dbManager, self::$usersRepo, self::$options );
	}

	/**
	 * Test that correct hooks are loaded
	 */
	public function testInitHooks() {
		global $pagenow;

		// Test page-specific JS enqueuing.
		$expect_hooked = [
			'admin_enqueue_scripts' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];

		$pagenow = 'profile.php';
		$this->clear_hooks( 'admin_enqueue_scripts' );
		self::$editProfile->init();
		$this->assertHooked( 'admin_enqueue_scripts', 'WP_Auth0_EditProfile', $expect_hooked );

		$pagenow = 'user-edit.php';
		$this->clear_hooks( 'admin_enqueue_scripts' );
		self::$editProfile->init();
		$this->assertHooked( 'admin_enqueue_scripts', 'WP_Auth0_EditProfile', $expect_hooked );
	}
}
