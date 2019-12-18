<?php
/**
 * Contains Class TestInitialSetupErrors.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestInitialSetupErrors.
 */
class TestInitialSetupErrors extends WP_Auth0_Test_Case {

	use HookHelpers;

	use RedirectHelpers;

	//public function testInitHooks() {
	//	$expect_hooked = [
	//		'wp_auth0_init_initial_setup' => [
	//			'priority'      => 1,
	//			'accepted_args' => 1,
	//		],
	//	];
	//	$this->assertHookedFunction( 'init', $expect_hooked );
	//}

	public function testThatInitialSetupIsSkippedIfNotSetupErrorPage() {
		$setup = new WP_Auth0_InitialSetup( WP_Auth0_Options::Instance() );
		$this->assertFalse($setup->init_setup());

		$_REQUEST['page'] = uniqid();
		$_REQUEST['callback'] = uniqid();
		$this->assertFalse($setup->init_setup());

		$_REQUEST['wpa0-setup'] = 'wpa0-setup';
		unset($_REQUEST['callback']);
		$this->assertFalse($setup->init_setup());
	}

	public function testThatRejectedRedirectOccurs() {
		$this->startRedirectHalting();
		$_REQUEST['page'] = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();
		$_REQUEST['error'] = 'rejected';

		$setup = new WP_Auth0_InitialSetup( WP_Auth0_Options::Instance() );

		try {
			$setup->init_setup();
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
		$_REQUEST['page'] = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();
		$_REQUEST['error'] = 'access_denied';

		$setup = new WP_Auth0_InitialSetup( WP_Auth0_Options::Instance() );

		try {
			$setup->init_setup();
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
		$_REQUEST['page'] = 'wpa0-setup';
		$_REQUEST['callback'] = uniqid();

		$setup = new WP_Auth0_InitialSetup( WP_Auth0_Options::Instance() );

		try {
			$setup->init_setup();
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
