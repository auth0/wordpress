<?php
/**
 * Contains Class TestEditProfile.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestEditProfile.
 * Test the edit profile class.
 */
class TestEditProfile extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use HookHelpers;

	use UsersHelper;

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
		parent::setUpBeforeClass();
		self::$dbManager   = new WP_Auth0_DBManager( self::$opts );
		self::$usersRepo   = new WP_Auth0_UsersRepo( self::$opts );
		self::$editProfile = new WP_Auth0_EditProfile( self::$dbManager, self::$usersRepo, self::$opts );
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
