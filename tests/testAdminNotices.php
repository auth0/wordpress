<?php
/**
 * Contains Class TestAdminNotices.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestAdminNotices.
 */
class TestAdminNotices extends WP_Auth0_Test_Case {

	public function testThatSetupNoticeDoesNotAppearIfPluginIsReady() {
		self::auth0Ready( true );
		ob_start();
		$this->assertFalse( wp_auth0_create_account_message() );
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatSetupNoticeDoesNotAppearIfNotOnSettingsPage() {
		self::auth0Ready( false );
		$_GET['page'] = uniqid();
		ob_start();
		$this->assertFalse( wp_auth0_create_account_message() );
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatSetupNoticeAppearsCorrectly() {
		self::auth0Ready( false );
		$_GET['page'] = 'wpa0';
		ob_start();
		$this->assertTrue( wp_auth0_create_account_message() );

		$notice_html = ob_get_clean();
		$this->assertContains( 'class="update-nag"', $notice_html );
		$this->assertContains( 'Login by Auth0 is not yet configured', $notice_html );
		$this->assertContains( 'wp-admin/admin.php?page=wpa0-setup', $notice_html );
		$this->assertContains( 'Setup Wizard', $notice_html );
		$this->assertContains( 'https://auth0.com/docs/cms/wordpress/installation', $notice_html );
		$this->assertContains( 'Manual setup instructions', $notice_html );
	}
}
