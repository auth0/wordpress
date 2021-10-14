<?php
/**
 * Contains Class TestApiClient.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

class ApiClientTest extends WP_Auth0_Test_Case {

	use HttpHelpers;

	/**
	 * Test that the default grant types used on install are correct.
	 */
	public function testThatGrantTypesAreCorrect() {
		$grant_types = WP_Auth0_Api_Client::get_client_grant_types();
		$this->assertCount( 3, $grant_types );
		$this->assertContains( 'authorization_code', $grant_types );
		$this->assertContains( 'refresh_token', $grant_types );
		$this->assertContains( 'client_credentials', $grant_types );
	}


	/**
	 * Test that the API scopes requested are correct.
	 */
	public function testThatConsentScopesAreCorrect() {
		$scopes = WP_Auth0_Api_Client::ConsentRequiredScopes();
		$this->assertCount( 7, $scopes );
		$this->assertContains( 'create:clients', $scopes );
		$this->assertContains( 'create:client_grants', $scopes );
		$this->assertContains( 'create:connections', $scopes );
		$this->assertContains( 'read:connections', $scopes );
		$this->assertContains( 'update:connections', $scopes );
		$this->assertContains( 'read:users', $scopes );
		$this->assertContains( 'update:users', $scopes );
	}
}
