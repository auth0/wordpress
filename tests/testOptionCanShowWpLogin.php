<?php
/**
 * Contains Class TestOptionCanShowWpLogin.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestOptionCanShowWpLogin.
 */
class TestOptionCanShowWpLogin extends WP_Auth0_Test_Case {

	public function testThatWpLoginCanBeShownIfPluginNotReady() {
		$this->assertTrue( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCanBeShownIfOnResetpassPage() {
		self::auth0Ready();
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = 'resetpass';
		self::$opts->set( 'wordpress_login_enabled', 'link' );
		$this->assertTrue( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCanBeShownIfOnRpPage() {
		self::auth0Ready();
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = 'rp';
		self::$opts->set( 'wordpress_login_enabled', 'link' );
		$this->assertTrue( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCannotBeShownIfNotWle() {
		self::auth0Ready();
		self::$opts->set( 'wordpress_login_enabled', 'link' );
		$this->assertFalse( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCannotBeShownIfWleOff() {
		self::auth0Ready();
		$_REQUEST['wle'] = '__test_wle_code__';
		self::$opts->set( 'wordpress_login_enabled', 'no' );
		$this->assertFalse( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCanBeShownIfWlePresent() {
		self::auth0Ready();
		$_REQUEST['wle'] = '';

		self::$opts->set( 'wordpress_login_enabled', 'link' );
		$this->assertTrue( self::$opts->can_show_wp_login_form() );

		self::$opts->set( 'wordpress_login_enabled', 'isset' );
		$this->assertTrue( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCanBeShownIfWleCodeMatches() {
		self::auth0Ready();
		$_REQUEST['wle'] = '__test_wle_code__';
		self::$opts->set( 'wordpress_login_enabled', 'code' );
		self::$opts->set( 'wle_code', '__test_wle_code__' );
		$this->assertTrue( self::$opts->can_show_wp_login_form() );
	}

	public function testThatWpLoginCannotBeShownIfWleCodeNotMatches() {
		self::auth0Ready();
		$_REQUEST['wle'] = '__invalid_wle_code__';
		self::$opts->set( 'wordpress_login_enabled', 'code' );
		self::$opts->set( 'wle_code', '__test_wle_code__' );
		$this->assertFalse( self::$opts->can_show_wp_login_form() );
	}
}
