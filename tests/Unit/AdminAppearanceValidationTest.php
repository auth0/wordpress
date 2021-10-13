<?php
/**
 * Contains Class TestAdminAppearanceValidation.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

class AdminAppearanceValidationTest extends WP_Auth0_Test_Case {

	/**
	 * WP_Auth0_Admin instance.
	 *
	 * @var WP_Auth0_Admin
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin( self::$opts, new WP_Auth0_Routes( self::$opts ) );
	}

	/**
	 * Test that the form_title setting is skipped if empty and removes HTML.
	 */
	public function testThatFormTitleIsValidatedProperly() {
		$validated = self::$admin->input_validator( [] );
		$this->assertEquals( '', $validated['form_title'] );

		$validated = self::$admin->input_validator( [ 'form_title' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['form_title'] );
	}

	/**
	 * Test that the icon_url setting is skipped if empty and tries to create a valid URL for display.
	 */
	public function testThatIconUrlIsValidatedProperly() {
		$validated = self::$admin->input_validator( [] );
		$this->assertEquals( '', $validated['icon_url'] );

		$validated = self::$admin->input_validator( [ 'icon_url' => 'example.org' ] );
		$this->assertEquals( 'http://example.org', $validated['icon_url'] );
	}

	/**
	 * Test that the primary_color setting is skipped if empty and removes HTML.
	 */
	public function testThatPrimaryColorIsValidatedProperly() {
		$validated = self::$admin->input_validator( [] );
		$this->assertEquals( '', $validated['primary_color'] );

		$validated = self::$admin->input_validator( [ 'primary_color' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['primary_color'] );
	}
}
