<?php
/**
 * Contains Class TestImportExportSettings.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestImportExportSettings.
 */
class TestImportExportSettings extends WP_Auth0_Test_Case {

	use HookHelpers;

	use RedirectHelpers;

	use UsersHelper;

	use WpDieHelper;

	public function testThatImportExportHooksAreSet() {
		$expect_hooked = [
			'wp_auth0_export_settings_admin_action' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_action_wpauth0_export_settings', $expect_hooked );

		$expect_hooked = [
			'wp_auth0_import_settings_admin_action' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_action_wpauth0_import_settings', $expect_hooked );

		$expect_hooked = [
			'wp_auth0_settings_admin_action_error' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_notices', $expect_hooked );
	}

	public function testThatSettingsImportNoticeDoesNotAppearIfNoError() {
		$GLOBALS['current_screen'] = new class { public function in_admin() {
				return true;
		} };
		$_REQUEST['page']          = 'wpa0-import-settings';

		ob_start();
		$this->assertFalse( wp_auth0_settings_admin_action_error() );
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatSettingsImportNoticeDoesNotAppearIfWrongPage() {
		$GLOBALS['current_screen'] = new class { public function in_admin() {
				return true;
		} };
		$_REQUEST['page']          = uniqid();
		$_REQUEST['error']         = '__test_error_message__';

		ob_start();
		$this->assertFalse( wp_auth0_settings_admin_action_error() );
		$this->assertEmpty( ob_get_clean() );
	}

	public function testThatSettingsImportNoticeAppearsCorrectly() {
		$GLOBALS['current_screen'] = new class { public function in_admin() {
				return true;
		} };
		$_REQUEST['page']          = 'wpa0-import-settings';
		$_REQUEST['error']         = '__test_error_message__';

		ob_start();
		$this->assertTrue( wp_auth0_settings_admin_action_error() );

		$notice_html = ob_get_clean();
		$this->assertContains( 'class="notice notice-error is-dismissible"', $notice_html );
		$this->assertContains( '<p><strong>__test_error_message__</strong></p>', $notice_html );
	}

	public function testThatExportFailsIfNoNonce() {
		$this->startWpDieHalting();

		try {
			wp_auth0_export_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'The link you followed has expired.', $output );
	}

	public function testThatExportFailsIfBadNonce() {
		$this->startWpDieHalting();
		$_POST['_wpnonce'] = '__invalid_export_nonce__';

		try {
			wp_auth0_export_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'The link you followed has expired.', $output );
	}

	public function testThatExportFailsIfNotLoggedIn() {
		$this->startWpDieHalting();

		$_POST['_wpnonce'] = wp_create_nonce( WP_Auth0_Import_Settings::EXPORT_NONCE_ACTION );

		try {
			wp_auth0_export_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'Unauthorized.', $output );
	}

	public function testThatExportFailsIfNotAdmin() {
		$this->startWpDieHalting();

		// Create a new user that is not an admin.
		$user = $this->createUser();
		$this->setGlobalUser( $user->ID );

		$_POST['_wpnonce'] = wp_create_nonce( WP_Auth0_Import_Settings::EXPORT_NONCE_ACTION );

		try {
			wp_auth0_export_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'Unauthorized.', $output );
	}

	public function testThatImportFailsIfNoNonce() {
		$this->startWpDieHalting();

		try {
			wp_auth0_import_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'The link you followed has expired.', $output );
	}

	public function testThatImportFailsIfBadNonce() {
		$this->startWpDieHalting();
		$_POST['_wpnonce'] = '__invalid_import_nonce__';

		try {
			wp_auth0_import_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'The link you followed has expired.', $output );
	}

	public function testThatImportFailsIfNotLoggedIn() {
		$this->startWpDieHalting();
		$_POST['_wpnonce'] = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );

		try {
			wp_auth0_import_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'Unauthorized.', $output );
	}

	public function testThatImportFailsIfNotAdmin() {
		$this->startWpDieHalting();

		// Create a new user that is not an admin.
		$user = $this->createUser();
		$this->setGlobalUser( $user->ID );
		$_POST['_wpnonce'] = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );

		try {
			wp_auth0_import_settings_admin_action();
			$output = 'Not caught';
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertEquals( 'Unauthorized.', $output );
	}

	public function testThatErrorRedirectHappensIfJsonEmpty() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		$_POST['_wpnonce'] = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-import-settings&error=No%20settings%20JSON%20entered.',
			$redirect_data['location']
		);

		$this->assertEquals( 302, $redirect_data['status'] );
	}

	public function testThatErrorRedirectHappensIfJsonInvalid() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		$_POST['_wpnonce']      = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );
		$_POST['settings-json'] = uniqid();

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-import-settings&error=Settings%20JSON%20entered%20is%20not%20valid.',
			$redirect_data['location']
		);

		$this->assertEquals( 302, $redirect_data['status'] );
	}

	public function testThatSettingsAreUpdatedWithValidJson() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		$_POST['_wpnonce']      = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );
		$_POST['settings-json'] = '{"domain":"__test_imported_domain__"}';

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( '__test_imported_domain__', wp_auth0_get_option( 'domain' ) );
		$this->assertEquals( 'http://example.org/wp-admin/admin.php?page=wpa0', $redirect_data['location'] );
		$this->assertEquals( 302, $redirect_data['status'] );
	}

	public function testThatImportedSettingsAreValidated() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		$_POST['_wpnonce']      = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );
		$_POST['settings-json'] = '{"client_signing_algorithm":"__invalid_alg__"}';

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'http://example.org/wp-admin/admin.php?page=wpa0', $redirect_data['location'] );
		$this->assertNotEquals( '__invalid_alg__', wp_auth0_get_option( 'client_signing_algorithm' ) );
	}

	public function testThatOnlyImportedSettingsAreSaved() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		self::$opts->set( 'client_id', '__test_existing_client_id__' );
		$_POST['_wpnonce']      = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );
		$_POST['settings-json'] = '{"domain":"__test_domain__"}';

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'http://example.org/wp-admin/admin.php?page=wpa0', $redirect_data['location'] );
		$this->assertEquals( '__test_domain__', wp_auth0_get_option( 'domain' ) );
		$this->assertEquals( '__test_existing_client_id__', wp_auth0_get_option( 'client_id' ) );
	}

	public function testThatUnknownImportedKeysAreRemoved() {
		$this->startRedirectHalting();
		$this->setGlobalUser();
		$_POST['_wpnonce']      = wp_create_nonce( WP_Auth0_Import_Settings::IMPORT_NONCE_ACTION );
		$_POST['settings-json'] = '{"domain":"__test_domain__", "__invalid_key__": "__test_val__"}';

		try {
			wp_auth0_import_settings_admin_action();
			$redirect_data = [ 'location' => 'No redirect caught' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'http://example.org/wp-admin/admin.php?page=wpa0', $redirect_data['location'] );

		$db_options = get_option( 'wp_auth0_settings' );
		$this->assertEquals( '__test_domain__', $db_options['domain'] );
		$this->assertArrayNotHasKey( '__invalid_key__', $db_options );
	}
}
