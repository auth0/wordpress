<?php
/**
 * Contains Class TestAdminSettingsValidationPath.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestAdminSettingsValidationPath.
 */
class TestAdminSettingsValidationPath extends WP_Auth0_Test_Case {

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
}
