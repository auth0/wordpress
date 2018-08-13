<?php
/**
 * Contains Class TestIpCheck.
 *
 * @package WP-Auth0
 * @since 3.7.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestIpCheck.
 * Tests that IP addresses are returned correctly for a specific region.
 */
class TestIpCheck extends TestCase {

	use setUpTestDb;

	/**
	 * Test that a specific region and domain return the correct number of IP addresses.
	 */
	public function testGetIpByDomain() {
		$opts     = WP_Auth0_Options::Instance();
		$ip_check = new WP_Auth0_Ip_Check( $opts );

		$us_ips = explode( ',', $ip_check->get_ip_by_region( 'us' ) );
		$this->assertCount( 16, $us_ips );
		$us_ips = explode( ',', $ip_check->get_ips_by_domain( 'test.auth0.com' ) );
		$this->assertCount( 16, $us_ips );

		$eu_ips = explode( ',', $ip_check->get_ip_by_region( 'eu' ) );
		$this->assertCount( 16, $eu_ips );
		$eu_ips = explode( ',', $ip_check->get_ips_by_domain( 'test.eu.auth0.com' ) );
		$this->assertCount( 16, $eu_ips );

		$au_ips = explode( ',', $ip_check->get_ip_by_region( 'au' ) );
		$this->assertCount( 11, $au_ips );
		$au_ips = explode( ',', $ip_check->get_ips_by_domain( 'test.au.auth0.com' ) );
		$this->assertCount( 11, $au_ips );
	}
}
