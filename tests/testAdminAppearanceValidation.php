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
	 * Test that the form_title setting is skipped if empty and removes HTML.
	 */
	public function testThatFormTitleIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [] );
		$this->assertEquals( '', $validated['form_title'] );

		$validated = self::$admin->basic_validation( [ 'form_title' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['form_title'] );
	}

	/**
	 * Test that the icon_url setting is skipped if empty and tries to create a valid URL for display.
	 */
	public function testThatIconUrlIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [] );
		$this->assertEquals( '', $validated['icon_url'] );

		$validated = self::$admin->basic_validation( [ 'icon_url' => 'example.org' ] );
		$this->assertEquals( 'http://example.org', $validated['icon_url'] );
	}

	/**
	 * Test that the primary_color setting is skipped if empty and removes HTML.
	 */
	public function testThatPrimaryColorIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [] );
		$this->assertEquals( '', $validated['primary_color'] );

		$validated = self::$admin->basic_validation( [ 'primary_color' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['primary_color'] );
	}
}
