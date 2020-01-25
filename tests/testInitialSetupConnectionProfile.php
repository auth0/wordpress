<?php
/**
 * Contains Class TestInitialSetupConnectionProfile.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestInitialSetupConnectionProfile.
 */
class TestInitialSetupConnectionProfile extends WP_Auth0_Test_Case {

	use RedirectHelpers;

	/**
	 * Test that the create client call is made.
	 */
	public function testThatConsentUrlIsBuiltProperly() {
		self::$opts->set( 'auth0_server_domain', '__test_auth0_domain__' );
		$conn_profile = new WP_Auth0_InitialSetup_ConnectionProfile( self::$opts );

		$consent_url = parse_url( $conn_profile->build_consent_url() );

		$this->assertEquals( 'https', $consent_url['scheme'] );
		$this->assertEquals( '__test_auth0_domain__', $consent_url['host'] );
		$this->assertEquals( '/authorize', $consent_url['path'] );

		$consent_url_query = explode( '&', $consent_url['query'] );

		$this->assertContains( 'client_id=http%3A%2F%2Fexample.org', $consent_url_query );
		$this->assertContains( 'response_type=code', $consent_url_query );
		$this->assertContains(
			'redirect_uri=http%3A%2F%2Fexample.org%2Fwp-admin%2Fadmin.php%3Fpage%3Dwpa0-setup%26callback%3D1',
			$consent_url_query
		);
		$this->assertContains(
			'scope=create%3Aclients+create%3Aclient_grants+update%3Aconnections+create%3Aconnections+read%3Aconnections+read%3Ausers+update%3Ausers',
			$consent_url_query
		);
	}

	public function testThatNewConnectionIsCreatedWithExistingMigrationToken() {
		$this->startRedirectHalting();
		$conn_profile = new WP_Auth0_InitialSetup_ConnectionProfile( self::$opts );

		try {
			$conn_profile->callback();
			$redirect_found = [ 'No redirect' ];
		} catch ( Exception $e ) {
			$redirect_found = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 302, $redirect_found['status'] );
		$this->assertStringStartsWith( 'https://auth0.auth0.com/authorize', $redirect_found['location'] );
	}

}
