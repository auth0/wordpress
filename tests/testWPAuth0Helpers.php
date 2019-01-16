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

	/**
	 * Test the basic options functionality.
	 */
	public function testGetTenantInfo() {

		// Test the default.
		$this->assertEquals( 'us', WP_Auth0::get_tenant_region( 'banana' ) );
		$this->assertEquals( 'us', WP_Auth0::get_tenant_region( 'banana.auth0.com' ) );
		$this->assertEquals( 'us', WP_Auth0::get_tenant_region( 'banana.us.auth0.com' ) );
		$this->assertEquals( 'eu', WP_Auth0::get_tenant_region( 'apple.eu.auth0.com' ) );
		$this->assertEquals( 'au', WP_Auth0::get_tenant_region( 'orange.au.auth0.com' ) );
		$this->assertEquals( 'xx', WP_Auth0::get_tenant_region( 'mango.xx.auth0.com' ) );

		// Test full tenant name getting.
		$this->assertEquals( 'banana@us', WP_Auth0::get_tenant( 'banana' ) );
		$this->assertEquals( 'banana@us', WP_Auth0::get_tenant( 'banana.auth0.com' ) );
		$this->assertEquals( 'banana@us', WP_Auth0::get_tenant( 'banana.us.auth0.com' ) );
		$this->assertEquals( 'apple@eu', WP_Auth0::get_tenant( 'apple.eu.auth0.com' ) );
		$this->assertEquals( 'orange@au', WP_Auth0::get_tenant( 'orange.au.auth0.com' ) );
		$this->assertEquals( 'mango@xx', WP_Auth0::get_tenant( 'mango.xx.auth0.com' ) );
	}
}
