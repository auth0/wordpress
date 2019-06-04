<?php
/**
 * Contains Class TestFunctionIsCurrentLoginAction.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestFunctionIsCurrentLoginAction.
 */
class TestFunctionIsCurrentLoginAction extends WP_Auth0_Test_Case {

	public function testThatCurrentActionIsFalseIfNotOnWpLogin() {
		unset( $GLOBALS['pagenow'] );
		$this->assertFalse( wp_auth0_is_current_login_action( [] ) );

		$GLOBALS['pagenow'] = uniqid();
		$this->assertFalse( wp_auth0_is_current_login_action( [] ) );
	}

	public function testThatCurrentActionIsFalseIfActionNotSet() {
		$GLOBALS['pagenow'] = 'wp-login.php';
		unset( $_REQUEST['action'] );
		$this->assertFalse( wp_auth0_is_current_login_action( [] ) );
	}

	public function testThatCurrentActionIsFalseIfActionDoesNotMatch() {
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = '__invalid_action__';
		$this->assertFalse( wp_auth0_is_current_login_action( [ '__valid_action__' ] ) );
	}

	public function testThatCurrentActionIsTrueIfActionDoesMatch() {
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = '__valid_action__';
		$this->assertTrue( wp_auth0_is_current_login_action( [ '__valid_action__' ] ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatCurrentActionIsTrueIfActionDoesMatchAndLoginHeaderExists() {
		function login_header() {}
		$_REQUEST['action'] = '__valid_action__';
		$this->assertTrue( wp_auth0_is_current_login_action( [ '__valid_action__' ] ) );
	}
}
