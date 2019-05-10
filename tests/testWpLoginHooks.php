<?php
/**
 * Contains Class TestWpLoginHooks.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestWpLoginHooks.
 * Tests that wp-login.php hook work as expected.
 */
class TestWpLoginHooks extends WP_Auth0_Test_Case {

	use HookHelpers;

	public function testThatWpRedirectHookIsSet() {
		$expect_hooked = [
			'wp_auth0_filter_wp_redirect_lostpassword' => [
				'priority'      => 100,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'wp_redirect', $expect_hooked );
	}

	public function testThatWpRedirectIsNotChangedIfNotCheckEmailLocation() {
		$location = '__test_location__';
		$this->assertEquals( $location, wp_auth0_filter_wp_redirect_lostpassword( $location ) );
	}

	public function testThatWpRedirectIsNotChangedIfNotLostpasswordAction() {
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = '__not_lostpassword__';
		$location           = 'wp-login.php?checkemail=confirm';
		$this->assertEquals( $location, wp_auth0_filter_wp_redirect_lostpassword( $location ) );
	}

	public function testThatWpRedirectIsNotChangedIfIncorrectReferrer() {
		$GLOBALS['pagenow']      = 'wp-login.php';
		$_REQUEST['action']      = 'lostpassword';
		$location                = 'wp-login.php?checkemail=confirm';
		$_SERVER['HTTP_REFERER'] = '__incorrect_referrer__';
		$this->assertEquals( $location, wp_auth0_filter_wp_redirect_lostpassword( $location ) );
	}

	public function testThatWpRedirectIsChangedIfCorrectReferrer() {
		self::$opts->set( 'wordpress_login_enabled', 'code' );
		self::$opts->set( 'wle_code', 'test_wle_code' );
		$GLOBALS['pagenow']      = 'wp-login.php';
		$_REQUEST['action']      = 'lostpassword';
		$location                = 'wp-login.php?checkemail=confirm';
		$_SERVER['HTTP_REFERER'] = wp_login_url() . '?action=lostpassword&wle=test_wle_code';
		$this->assertEquals( $location . '&wle=test_wle_code', wp_auth0_filter_wp_redirect_lostpassword( $location ) );
	}

	public function testThatWpLoginOverrideUrlHooksAreSet() {
		$expect_hooked = [
			'wp_auth0_filter_login_override_url' => [
				'priority'      => 100,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'lostpassword_url', $expect_hooked );
		$this->assertHookedFunction( 'login_url', $expect_hooked );
	}

	public function testThatWpLoginOverrideUrlIsNotModifiedIfNoWle() {
		$this->assertEquals( '__test_url__', wp_auth0_filter_login_override_url( '__test_url__' ) );
	}

	public function testThatWpLoginOverrideUrlIsModifiedIfWle() {
		$_REQUEST['wle'] = '__test_wle_code__';
		$this->assertEquals(
			'http://login.org?wle=__test_wle_code__',
			wp_auth0_filter_login_override_url( 'http://login.org' )
		);
	}

	public function testThatWpLoginOverrideUrlIsModifiedIfResetPassPage() {
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = 'resetpass';
		$this->assertEquals( 'http://login.org?wle', wp_auth0_filter_login_override_url( 'http://login.org' ) );
	}

	public function testThatWpLoginFormHooksAreSet() {
		$expect_hooked = [
			'wp_auth0_filter_login_override_form' => [
				'priority'      => 100,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'login_form', $expect_hooked );
		$this->assertHookedFunction( 'lostpassword_form', $expect_hooked );
	}

	public function testThatWpLoginFormHookReturnsNothingIfPluginNotReady() {
		ob_start();
		wp_auth0_filter_login_override_form();
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatWpLoginFormHookReturnsNothingIfPluginReadyNoWle() {
		self::auth0Ready();
		ob_start();
		wp_auth0_filter_login_override_form();
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatWpLoginFormHookReturnsInputIfWlePresent() {
		self::auth0Ready();
		$_REQUEST['wle'] = '';
		ob_start();
		wp_auth0_filter_login_override_form();
		$this->assertEquals( '<input type="hidden" name="wle" value="" />', ob_get_clean() );
	}

	public function testThatWpLoginFormHookReturnsInputIfWleCode() {
		self::auth0Ready();
		$_REQUEST['wle'] = '__test_wle_code__';
		ob_start();
		wp_auth0_filter_login_override_form();
		$this->assertEquals( '<input type="hidden" name="wle" value="__test_wle_code__" />', ob_get_clean() );
	}

}
