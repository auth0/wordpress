<?php
use PHPUnit\Framework\TestCase;

/**
 * Class TestLockOptions.
 * Tests that Lock options output expected values based on given conditions.
 */
class TestLockOptions extends TestCase {

	use setUpTestDb;

	/**
	 * Test that a custom domain adds a correct key for CDN configuration to Lock options.
	 */
	public function testLockConfigBaseUrl() {
		$opts = WP_Auth0_Options::Instance();

		$opts->set( 'domain', 'test.auth0.com' );
		$lock_options     = new WP_Auth0_Lock10_Options( [], $opts );
		$lock_options_arr = $lock_options->get_lock_options();
		$this->assertArrayNotHasKey( 'configurationBaseUrl', $lock_options_arr );

		$opts->set( 'custom_domain', 'login.example.com' );
		$this->assertEquals( 'https://cdn.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );

		$opts->set( 'domain', 'test.eu.auth0.com' );
		$this->assertEquals( 'https://cdn.eu.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );

		$opts->set( 'domain', 'test.au.auth0.com' );
		$this->assertEquals( 'https://cdn.au.auth0.com', $lock_options->get_lock_options()['configurationBaseUrl'] );
	}
}
