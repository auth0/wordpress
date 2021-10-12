<?php
/**
 * Contains Class TestAdminMenu.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

class AdminMenuTest extends WP_Auth0_Test_Case {

	public function testThatcorrectMenusAreAddedWhenPluginIsReady() {
		global $menu, $_wp_submenu_nopriv;
		self::auth0Ready();
		wp_auth0_init_admin_menu();

		$this->assertArrayHasKey( 86, $menu );
		$this->assertEquals( 'Auth0', $menu[86][0] );
		$this->assertEquals( 'manage_options', $menu[86][1] );
		$this->assertEquals( 'wpa0', $menu[86][2] );
		$this->assertEquals( 'Auth0', $menu[86][3] );
		$this->assertContains( 'assets/img/a0icon.png', $menu[86][6] );

		$this->assertArrayHasKey( 'wpa0', $_wp_submenu_nopriv );
		$submenu_items = array_keys( $_wp_submenu_nopriv['wpa0'] );
		$this->assertEquals( 'wpa0', $submenu_items[0] );
		$this->assertEquals( 'wpa0-help', $submenu_items[1] );
		$this->assertEquals( 'wpa0-errors', $submenu_items[2] );
		$this->assertEquals( 'wpa0-import-settings', $submenu_items[3] );
	}

	public function testThatcorrectMenusAreAddedWhenPluginIsNotReady() {
		global $menu, $_wp_submenu_nopriv;
		self::auth0Ready( false );
		wp_auth0_init_admin_menu();

		$this->assertArrayHasKey( 86, $menu );
		$this->assertEquals( 'Auth0', $menu[86][0] );
		$this->assertEquals( 'manage_options', $menu[86][1] );
		$this->assertEquals( 'wpa0-setup', $menu[86][2] );
		$this->assertEquals( 'Auth0', $menu[86][3] );
		$this->assertContains( 'assets/img/a0icon.png', $menu[86][6] );

		$this->assertArrayHasKey( 'wpa0-setup', $_wp_submenu_nopriv );
		$submenu_items = array_keys( $_wp_submenu_nopriv['wpa0-setup'] );

		$this->assertEquals( 'wpa0-setup', $submenu_items[0] );
		$this->assertEquals( 'wpa0', $submenu_items[1] );
		$this->assertEquals( 'wpa0-errors', $submenu_items[2] );
		$this->assertEquals( 'wpa0-import-settings', $submenu_items[3] );
	}
}
