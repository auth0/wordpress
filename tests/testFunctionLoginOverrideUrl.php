<?php
/**
 * Contains Class TestFunctionLoginOverrideUrl.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestFunctionLoginOverrideUrl.
 */
class TestFunctionLoginOverrideUrl extends WP_Auth0_Test_Case {

	public function testThatOverrideUrlIsEmptyIfWleOff() {
		self::$opts->set( 'wordpress_login_enabled', 'no' );
		$this->assertEmpty( wp_auth0_login_override_url() );
		$this->assertEmpty( wp_auth0_login_override_url( uniqid() ) );
	}

	public function testThatOverrideUrlIsCorrectIfWleIsLink() {
		self::$opts->set( 'wordpress_login_enabled', 'link' );
		$this->assertEquals( 'http://example.org/wp-login.php?wle', wp_auth0_login_override_url() );
		$this->assertEquals(
			'http://example.org/login?wle',
			wp_auth0_login_override_url( 'http://example.org/login' )
		);
	}

	public function testThatOverrideUrlIsCorrectIfWleIsCode() {
		self::$opts->set( 'wordpress_login_enabled', 'code' );
		self::$opts->set( 'wle_code', '__test_wle_code__' );
		$this->assertEquals(
			'http://example.org/wp-login.php?wle=__test_wle_code__',
			wp_auth0_login_override_url()
		);
		$this->assertEquals(
			'http://example.org/login?wle=__test_wle_code__',
			wp_auth0_login_override_url( 'http://example.org/login' )
		);
	}
}
