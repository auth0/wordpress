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

	use setUpTestDb {
		setUp as setUpDb;
	}

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Original request IP address.
	 *
	 * @var string
	 */
	public static $backup_ip;

	/**
	 * Run before the test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		self::$backup_ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		$_SERVER['REMOTE_ADDR'] = self::$backup_ip;
		parent::setUp();
		$this->setUpDb();
		self::$opts->reset();
	}

	/**
	 * Test that a specific region and domain return the correct number of IP addresses.
	 */
	public function testThatIpCountDidNotChange() {
		$ip_check = new WP_Auth0_Ip_Check( self::$opts );

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

		$us_ips = $ip_check->get_ip_by_region( 'us' );
		self::$opts->set( 'domain', 'test.auth0.com' );
		$domain_ips = $ip_check->get_ips_by_domain();
		$this->assertEquals( $us_ips, $domain_ips );
	}

	/**
	 * Test that unauthorized URLs are not allowed.
	 */
	public function testThatInvalidConnectionsAreNotAllowed() {
		$ip_check = new WP_Auth0_Ip_Check( self::$opts );

		$_SERVER['REMOTE_ADDR'] = '1.2.3.4';
		$this->assertFalse( $ip_check->connection_is_valid() );
		$this->assertFalse( $ip_check->connection_is_valid( '4.3.2.1' ) );

		self::$opts->set( 'domain', 'test.auth0.com' );
		$this->assertFalse( $ip_check->connection_is_valid() );
		$this->assertFalse( $ip_check->connection_is_valid( '4.3.2.1' ) );
	}

	/**
	 * Test that authorized IPs are allowed.
	 */
	public function testThatValidConnectionsAreAllowed() {
		$ip_check = new WP_Auth0_Ip_Check( self::$opts );

		$_SERVER['REMOTE_ADDR'] = '1.2.3.4';
		$this->assertTrue( $ip_check->connection_is_valid( '1.2.3.4' ) );
		$this->assertTrue( $ip_check->connection_is_valid( '1.2.3.0 - 1.2.3.10' ) );
	}

	/**
	 * Test that the default Auth0 IPs are always allowed
	 */
	public function testThatDefaultConnectionsAreAllowed() {
		$ip_check = new WP_Auth0_Ip_Check( self::$opts );

		$_SERVER['REMOTE_ADDR'] = '34.195.142.251';
		self::$opts->set( 'domain', 'test.auth0.com' );
		$this->assertTrue( $ip_check->connection_is_valid() );
		$this->assertTrue( $ip_check->connection_is_valid( '1.2.3.4' ) );

		$_SERVER['REMOTE_ADDR'] = '34.253.4.94';
		self::$opts->set( 'domain', 'test.eu.auth0.com' );
		$this->assertTrue( $ip_check->connection_is_valid() );
		$this->assertTrue( $ip_check->connection_is_valid( '1.2.3.4' ) );

		$_SERVER['REMOTE_ADDR'] = '13.54.254.182';
		self::$opts->set( 'domain', 'test.au.auth0.com' );
		$this->assertTrue( $ip_check->connection_is_valid() );
		$this->assertTrue( $ip_check->connection_is_valid( '1.2.3.4' ) );
	}

	/**
	 * Test that a proxy IP address can be used.
	 */
	public function testThatProxyConnectionsAreAllowed() {
		$ip_check = new WP_Auth0_Ip_Check( self::$opts );

		self::$opts->set( 'valid_proxy_ip', '1.2.3.4' );
		$_SERVER['REMOTE_ADDR']          = '1.2.3.4';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '2.3.4.5';
		$this->assertTrue( $ip_check->connection_is_valid( '2.3.4.5' ) );
	}
}
