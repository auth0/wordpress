<?php
/**
 * Contains Class TestInitialSetupErrors.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

class InitialSetupErrorsTest extends WP_Auth0_Test_Case {

	use HookHelpers;

	use RedirectHelpers;

	public function testInitHooks() {
		$expect_hooked = [
			'wp_auth0_initial_setup_init' => [
				'priority'      => 1,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'init', $expect_hooked );
	}

	public function testThatInitialSetupIsSkippedIfNotSetupErrorPage() {
		$this->assertFalse( wp_auth0_initial_setup_init() );

		$_REQUEST['page']     = uniqid();
		$_REQUEST['callback'] = uniqid();
		$this->assertFalse( wp_auth0_initial_setup_init() );

		$_REQUEST['wpa0-setup'] = 'wpa0-setup';
		unset( $_REQUEST['callback'] );
		$this->assertFalse( wp_auth0_initial_setup_init() );
	}

	public function testThatRejectedRedirectOccurs() {
		$this->startRedirectHalting();
		$_REQUEST['page']     = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();
		$_REQUEST['error']    = 'rejected';

		try {
			wp_auth0_initial_setup_init();
			$caught = [ 'Nothing caught.' ];
		} catch ( Exception $e ) {
			$caught = unserialize( $e->getMessage() );
		}

		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-setup&error=rejected',
			$caught['location']
		);
	}

	public function testThatAccessDeniedRedirectOccurs() {
		$this->startRedirectHalting();
		$_REQUEST['page']     = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();
		$_REQUEST['error']    = 'access_denied';

		try {
			wp_auth0_initial_setup_init();
			$caught = [ 'Nothing caught.' ];
		} catch ( Exception $e ) {
			$caught = unserialize( $e->getMessage() );
		}

		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-setup&error=access_denied',
			$caught['location']
		);
	}

	public function testThatInitialSetupCallsConsentCallback() {
		$this->startRedirectHalting();
		$_REQUEST['page']     = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();

		try {
			wp_auth0_initial_setup_init();
			$caught = [ 'Nothing caught.' ];
		} catch ( Exception $e ) {
			$caught = unserialize( $e->getMessage() );
		}

		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-setup&error=cant_exchange_token',
			$caught['location']
		);
	}
}
