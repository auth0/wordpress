<?php
/**
 * Contains Class TestInitialSetup.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestInitialSetup.
 */
class TestInitialSetup extends WP_Auth0_Test_Case {

	use HookHelpers;

	public function testThatClearAdminActionFunctionsAreHooked() {
		$expect_hooked = [
			'wp_auth0_setup_callback_step3_social' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_action_wpauth0_callback_step3_social', $expect_hooked );

		$expect_hooked = [
			'wp_auth0_setup_callback_step1' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_action_wpauth0_callback_step1', $expect_hooked );

		$expect_hooked = [
			'wp_auth0_setup_error_admin_notices' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_notices', $expect_hooked );
	}

	public function testThatNoErrorReturnsFalseWithNoOutput() {
		ob_start();
		$this->assertFalse( wp_auth0_setup_error_admin_notices() );
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatCantCreateClientHasCorrectNotice() {
		$_REQUEST['error'] = 'cant_create_client';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( 'There was an error creating the Auth0 App', $notice_html );
	}

	public function testThatCantCreateGrantHasCorrectNotice() {
		$_REQUEST['error'] = 'cant_create_client_grant';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( 'There was an error creating the necessary client grants', $notice_html );
	}

	public function testThatCantExchangeTokenHasCorrectNotice() {
		$_REQUEST['error'] = 'cant_exchange_token';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( 'There was an error retrieving your Auth0 credentials', $notice_html );
	}

	public function testThatRejectedHasCorrectNotice() {
		$_REQUEST['error'] = 'rejected';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( 'The required scopes were rejected', $notice_html );
	}

	public function testThatAccessDeniedHasCorrectNotice() {
		$_REQUEST['error'] = 'access_denied';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( 'Please create your Auth0 account first', $notice_html );
	}

	public function testThatUnknownErrorHasCorrectNotice() {
		$_REQUEST['error'] = '__test_unknown_error__';
		ob_start();
		$this->assertTrue( wp_auth0_setup_error_admin_notices() );
		$notice_html = ob_get_clean();

		$this->assertContains( '<div class="notice notice-error">', $notice_html );
		$this->assertContains( '__test_unknown_error__', $notice_html );
	}
}
