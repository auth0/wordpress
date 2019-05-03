<?php
/**
 * Contains Class TestAdminAppearanceValidation.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestAdminAppearanceValidation.
 */
class TestAdminAppearanceValidation extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Appearance instance.
	 *
	 * @var WP_Auth0_Admin_Appearance
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin_Appearance( self::$opts );
	}

	/**
	 * Test that the social_big_buttons option is gone.
	 */
	public function testThatSocialBigButtonsIsNotAdded() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertArrayNotHasKey( 'social_big_buttons', $validated );
	}
}
