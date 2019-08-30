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
	 * Test that callback URLs are built properly
	 */
	public function testThatAuthCallbacksAreCorrect() {
		$lock_options = new WP_Auth0_Lock10_Options( [], self::$opts );

		$this->assertEquals( 'http://example.org/index.php?auth0=implicit', $lock_options->get_implicit_callback_url() );
		$this->assertEquals( 'http://example.org/index.php?auth0=1', $lock_options->get_code_callback_url() );

		self::$opts->set( 'force_https_callback', 1 );
		$lock_options = new WP_Auth0_Lock10_Options( [], self::$opts );

		$this->assertEquals( 'https://example.org/index.php?auth0=implicit', $lock_options->get_implicit_callback_url() );
		$this->assertEquals( 'https://example.org/index.php?auth0=1', $lock_options->get_code_callback_url() );
	}

	/**
	 * Test that the SSO options are built properly.
	 */
	public function testThatDefaultSsoOptionsAreCorrect() {
		$lock_options = new WP_Auth0_Lock10_Options( [], self::$opts );

		$sso_opts = $lock_options->get_sso_options();
		$this->assertEquals( 'openid email profile', $sso_opts['scope'] );
		$this->assertEquals( 'id_token', $sso_opts['responseType'] );
		$this->assertEquals( 'http://example.org/index.php?auth0=implicit', $sso_opts['redirectUri'] );
		$this->assertEquals( WP_Auth0_Nonce_Handler::get_instance()->get_unique(), $sso_opts['nonce'] );
		$this->assertEquals( $lock_options->get_state_obj(), $sso_opts['state'] );
		$this->assertArrayNotHasKey( 'authParams', $sso_opts );
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
