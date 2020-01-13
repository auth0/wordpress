<?php
/**
 * Contains Class TestFunctionUsesSecureCallback.
 *
 * @package WP-Auth0
 *
 * @since 3.11.2
 */

/**
 * Class TestFunctionUsesSecureCallback.
 */
class TestFunctionUsesSecureCallback extends WP_Auth0_Test_Case {

	public function tearDown() {
		parent::tearDown();
		unset( $_SERVER['HTTPS'] );
	}

	public function testThatDefaultCallbackUrlIsNotSecure() {
		$this->assertFalse( wp_auth0_uses_secure_callback() );
	}

	public function testThatDefaultCallbackUrlIsSecureIfSsl() {
		$this->assertFalse( wp_auth0_uses_secure_callback() );
		$_SERVER['HTTPS'] = 1;
		$this->assertTrue( wp_auth0_uses_secure_callback() );
	}

	public function testThatDefaultCallbackUrlIsSecureIfForced() {
		$this->assertFalse( wp_auth0_uses_secure_callback() );
		self::$opts->set( 'force_https_callback', 1 );
		$this->assertTrue( wp_auth0_uses_secure_callback() );
	}
}
