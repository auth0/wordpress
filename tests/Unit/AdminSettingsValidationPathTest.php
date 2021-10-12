<?php
/**
 * Contains Class TestAdminSettingsValidationPath.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

class AdminSettingsValidationPathTest extends WP_Auth0_Test_Case {

	use HookHelpers;

	public function testThatClearAdminActionFunctionIsHooked() {
		$expect_hooked = [
			'wp_auth0_init_admin' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'admin_init', $expect_hooked );
	}

	public function testThatAuth0SettingIsRegistered() {
		global $wp_registered_settings;

		wp_auth0_init_admin();

		$this->assertArrayHasKey( WP_Auth0_Options::Instance()->get_options_name(), $wp_registered_settings );
		$this->assertTrue( is_array( $wp_registered_settings['wp_auth0_settings']['sanitize_callback'] ) );
		$this->assertInstanceOf(
			WP_Auth0_Admin::class,
			$wp_registered_settings['wp_auth0_settings']['sanitize_callback'][0]
		);
		$this->assertEquals(
			'input_validator',
			$wp_registered_settings['wp_auth0_settings']['sanitize_callback'][1]
		);
	}

	public function testThatCustomSettingsFieldsAreKeptDuringValidation() {
		$admin = new WP_Auth0_Admin(self::$opts, new WP_Auth0_Routes(self::$opts));

		add_filter( 'auth0_settings_fields', [$this, 'addCustomSettings'], 10, 2 );
		$result = $admin->input_validator( ['test_opt_name' => '__test_value__'] );
		remove_filter( 'auth0_settings_fields', [$this, 'addCustomSettings'], 10 );

		$this->assertArrayHasKey('test_opt_name', $result);
		$this->assertEquals( '__test_value__', $result['test_opt_name'] );
	}

	public function testThatUnknownSettingsFieldsAreRemovedDuringValidation() {
		$admin = new WP_Auth0_Admin(self::$opts, new WP_Auth0_Routes(self::$opts));

		$result = $admin->input_validator( ['unknown_opt_name' => '__test_value__'] );

		$this->assertArrayNotHasKey('unknown_opt_name', $result);
	}

	public function addCustomSettings( $options, $id ) {
		// From https://auth0.com/docs/cms/wordpress/extending#auth0_settings_fields
		if ( 'basic' === $id ) {
			$options[] = ['opt' => 'test_opt_name'];
		}

		return $options;
	}
}
