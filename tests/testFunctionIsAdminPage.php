<?php
/**
 * Contains Class TestFunctionIsAdminPage.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestFunctionIsAdminPage.
 */
class TestFunctionIsAdminPage extends WP_Auth0_Test_Case {

	public function testThatEmptyPageReturnsFalse() {
		$this->assertFalse( wp_auth0_is_admin_page( null ) );
		$this->assertFalse( wp_auth0_is_admin_page( false ) );
		$this->assertFalse( wp_auth0_is_admin_page( '' ) );
		$this->assertFalse( wp_auth0_is_admin_page( uniqid() ) );
	}

	public function testThatNotAdminReturnsFalse() {
		$_REQUEST['page'] = '__test_page__';
		$this->assertFalse( wp_auth0_is_admin_page( '__test_page__' ) );

		$_REQUEST['page'] = 'wpa0';
		$this->assertFalse( wp_auth0_is_admin_page( 'wpa0' ) );
	}

	public function testThatIncorrectPageReturnsFalse() {
		$GLOBALS['current_screen'] = new class { public function in_admin() {
				return true;
		} };
		$_REQUEST['page']          = '__current_page__';
		$this->assertFalse( wp_auth0_is_admin_page( '__page_requested__' ) );
	}

	public function testThatCorrectPageReturnsTrue() {
		$GLOBALS['current_screen'] = new class { public function in_admin() {
				return true;
		} };
		$_REQUEST['page']          = '__current_page__';
		$this->assertTrue( wp_auth0_is_admin_page( '__current_page__' ) );
	}
}
