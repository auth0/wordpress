<?php
/**
 * Contains Class TestWPAuth0Helpers.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

/**
 * Class TestWPAuth0Helpers
 */
class TestWPAuth0Helpers extends WP_Auth0_Test_Case {

	use HookHelpers;

	/**
	 * Test the basic options functionality.
	 */
	public function testGetTenantInfo() {

		// Test the default.
		$this->assertEquals( 'us', wp_auth0_get_tenant_region( 'banana' ) );
		$this->assertEquals( 'us', wp_auth0_get_tenant_region( 'banana.auth0.com' ) );
		$this->assertEquals( 'us', wp_auth0_get_tenant_region( 'banana.us.auth0.com' ) );
		$this->assertEquals( 'eu', wp_auth0_get_tenant_region( 'apple.eu.auth0.com' ) );
		$this->assertEquals( 'au', wp_auth0_get_tenant_region( 'orange.au.auth0.com' ) );
		$this->assertEquals( 'xx', wp_auth0_get_tenant_region( 'mango.xx.auth0.com' ) );

		// Test full tenant name getting.
		$this->assertEquals( 'banana@us', wp_auth0_get_tenant( 'banana' ) );
		$this->assertEquals( 'banana@us', wp_auth0_get_tenant( 'banana.auth0.com' ) );
		$this->assertEquals( 'banana@us', wp_auth0_get_tenant( 'banana.us.auth0.com' ) );
		$this->assertEquals( 'apple@eu', wp_auth0_get_tenant( 'apple.eu.auth0.com' ) );
		$this->assertEquals( 'orange@au', wp_auth0_get_tenant( 'orange.au.auth0.com' ) );
		$this->assertEquals( 'mango@xx', wp_auth0_get_tenant( 'mango.xx.auth0.com' ) );
	}

	/**
	 * Test that the correct plugin links are shown when the plugin has NOT been configured.
	 */
	public function testThatPluginSettingsLinksAreCorrectWhenNotReady() {
		$plugin_links = wp_auth0_plugin_action_links( [] );

		$this->assertCount( 2, $plugin_links );

		$this->assertContains(
			'<a href="http://example.org/wp-admin/admin.php?page=wpa0">Settings</a>',
			$plugin_links
		);

		$this->assertContains(
			'<a href="http://example.org/wp-admin/admin.php?page=wpa0-setup">Setup Wizard</a>',
			$plugin_links
		);
	}

	/**
	 * Test that the correct plugin links are shown when the plugin has been configured.
	 */
	public function testThatPluginSettingsLinksAreCorrectWhenReady() {
		self::auth0Ready( true );
		$plugin_links = wp_auth0_plugin_action_links( [] );

		$this->assertCount( 1, $plugin_links );

		$this->assertContains(
			'<a href="http://example.org/wp-admin/admin.php?page=wpa0">Settings</a>',
			$plugin_links
		);
	}

	public function testThatQueryVarHookIsAdded() {
		$expect_hooked = [
			'wp_auth0_register_query_vars' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'query_vars', $expect_hooked );
	}

	public function testThatAuth0QueryVarsArePresent() {
		$vars = wp_auth0_register_query_vars( [] );
		$this->assertCount( 6, $vars );
		$this->assertContains( 'error', $vars );
		$this->assertContains( 'error_description', $vars );
		$this->assertContains( 'a0_action', $vars );
		$this->assertContains( 'auth0', $vars );
		$this->assertContains( 'state', $vars );
		$this->assertContains( 'code', $vars );
	}

	public function testThatQueryVarsAreAddedProperly() {
		$vars = wp_auth0_register_query_vars( [ '__test_var__' ] );
		$this->assertCount( 7, $vars );
		$this->assertContains( '__test_var__', $vars );
	}
}
