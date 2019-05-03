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
	 * Test that the form_title setting is skipped if empty and removes HTML.
	 */
	public function testThatFormTitleIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertEquals( '', $validated['form_title'] );

		$validated = self::$admin->basic_validation( [], [ 'form_title' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['form_title'] );
	}

	/**
	 * Test that the icon_url setting is skipped if empty and tries to create a valid URL for display.
	 */
	public function testThatIconUrlIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertEquals( '', $validated['icon_url'] );

		$validated = self::$admin->basic_validation( [], [ 'icon_url' => 'example.org' ] );
		$this->assertEquals( 'http://example.org', $validated['icon_url'] );
	}

	/**
	 * Test that the language setting is skipped if empty and removes HTML.
	 */
	public function testThatLanguageIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertEquals( '', $validated['language'] );

		$validated = self::$admin->basic_validation( [], [ 'language' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['language'] );
	}

	/**
	 * Test that the primary_color setting is skipped if empty and removes HTML.
	 */
	public function testThatPrimaryColorIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertEquals( '', $validated['primary_color'] );

		$validated = self::$admin->basic_validation( [], [ 'primary_color' => '<script>alert("hi")</script>' ] );
		$this->assertNotContains( '<script>', $validated['primary_color'] );
	}

	/**
	 * Test that the language_dictionary setting is skipped if empty and removes HTML.
	 */
	public function testThatLanguageDictIsBlankIfNotSet() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertEquals( '', $validated['language_dictionary'] );
	}

	/**
	 * Test that the language_dictionary setting is blank and error is set if invalid JSON and no fallback value.
	 */
	public function testThatLanguageDictIsBlankIfInvalidJson() {
		$validated = self::$admin->basic_validation( [], [ 'language_dictionary' => uniqid() ] );
		$this->assertEquals( '', $validated['language_dictionary'] );

		global $wp_settings_errors;
		$this->assertCount( 1, $wp_settings_errors );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['setting'] );
		$this->assertEquals( 'error', $wp_settings_errors[0]['type'] );
		$this->assertEquals( 'The language dictionary parameter should be a valid JSON object.', $wp_settings_errors[0]['message'] );
	}

	/**
	 * Test that the language_dictionary setting is set to the previous value and an error is set if invalid JSON.
	 */
	public function testThatLanguageDictIsPreviousValIfInvalidJson() {
		$validated = self::$admin->basic_validation(
			[ 'language_dictionary' => '{"previous":"value"}' ],
			[ 'language_dictionary' => uniqid() ]
		);
		$this->assertEquals( '{"previous":"value"}', $validated['language_dictionary'] );

		global $wp_settings_errors;
		$this->assertCount( 1, $wp_settings_errors );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['setting'] );
		$this->assertEquals( 'error', $wp_settings_errors[0]['type'] );
		$this->assertEquals( 'The language dictionary parameter should be a valid JSON object.', $wp_settings_errors[0]['message'] );
	}

	/**
	 * Test that the social_big_buttons option is gone.
	 */
	public function testThatSocialBigButtonsIsNotAdded() {
		$validated = self::$admin->basic_validation( [], [] );
		$this->assertArrayNotHasKey( 'social_big_buttons', $validated );
	}
}
