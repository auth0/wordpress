<?php
/**
 * Contains Class TestLockOptions.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

/**
 * Class TestLockOptions.
 * Tests that Lock options output expected values based on given conditions.
 */
class TestLockOptions extends WP_Auth0_Test_Case {

	public function tearDown() {
		remove_filter( 'auth0_lock_options', [ __CLASS__, 'setLockLanguage' ] );
		parent::tearDown();
	}

	/**
	 * Test that a custom domain adds a correct key for CDN configuration to Lock options.
	 */
	public function testThatLockConfigBaseUrlIsBuiltProperly() {
		self::$opts->set( 'domain', 'test.auth0.com' );
		$lock_options     = new WP_Auth0_Lock10_Options( [], self::$opts );
		$lock_options_arr = $lock_options->get_lock_options();
		$this->assertArrayNotHasKey( 'configurationBaseUrl', $lock_options_arr );

		self::$opts->set( 'custom_domain', 'login.example.com' );
		$this->assertEquals( 'https://cdn.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );

		self::$opts->set( 'domain', 'test.eu.auth0.com' );
		$this->assertEquals( 'https://cdn.eu.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );

		self::$opts->set( 'domain', 'test.au.auth0.com' );
		$this->assertEquals( 'https://cdn.au.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );
	}

	/**
	 * Test that the social_big_buttons option is not used.
	 */
	public function testThatSocialButtonStyleStaysBig() {
		self::$opts->set( 'social_big_buttons', false );
		$lock_options = new WP_Auth0_Lock10_Options( [], self::$opts );

		$lock_opts = $lock_options->get_lock_options();
		$this->assertEquals( 'big', $lock_opts['socialButtonStyle'] );
	}

	/**
	 * Test that the social_big_buttons option cannot be overridden.
	 */
	public function testThatSocialButtonStyleCannotBeOverridden() {
		$lock_options = new WP_Auth0_Lock10_Options( [ 'social_big_buttons' => false ], self::$opts );

		$lock_opts = $lock_options->get_lock_options();
		$this->assertEquals( 'big', $lock_opts['socialButtonStyle'] );
	}
}
