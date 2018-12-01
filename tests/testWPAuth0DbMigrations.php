<?php
/**
 * Contains Class TestWPAuth0DbMigrations.
 *
 * @package WP-Auth0
 * @since 3.7.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestWPAuth0DbMigrations.
 * Tests for database upgrade processes.
 */
class TestWPAuth0DbMigrations extends TestCase {

	use setUpTestDb;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts = WP_Auth0_Options::Instance();
	}

	/**
	 * Test a DB upgrade from v18 to v19.
	 */
	public function testV19Update() {
		$test_version        = 19;
		$initial_connections = [
			'social_twitter_key'     => '__twitter_key_test__',
			'social_twitter_secret'  => '__twitter_secret_test__',
			'social_facebook_key'    => '__facebook_key_test__',
			'social_facebook_secret' => '__facebook_secret_test__',
		];

		$connection_keys = array_keys( $initial_connections );

			// Save a 'connections' settings array.
		self::$opts->set( 'connections', $initial_connections );
		$saved_connections = self::$opts->get( 'connections' );
		$this->assertEquals( $initial_connections, $saved_connections );

		// Run the migration for v19.
		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		foreach ( $connection_keys as $key ) {
			$this->assertEmpty( self::$opts->get( $key ) );
		}

		$db_manager->install_db( $test_version, null );

		foreach ( $connection_keys as $key ) {
			$this->assertEquals( $initial_connections[ $key ], self::$opts->get( $key ) );
		}
	}

	/**
	 * Test a DB upgrade from v19 to v20.
	 */
	public function testV20Update() {
		$test_version = 20;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		// Set a US domain.
		self::$opts->set( 'domain', 'test.auth0.com' );

		// Save custom IPs, default IPs for US, and default IPs for other regions.
		self::$opts->set( 'migration_ips', '1.2.3.4,2.3.4.5,34.195.142.251,35.160.3.103,34.253.4.94,13.54.254.182' );

		// Run the update.
		$db_manager->install_db( $test_version, null );

		// Check that the correct IPs were removed.
		$remaining_ips = explode( ',', self::$opts->get( 'migration_ips' ) );

		$this->assertContains( '1.2.3.4', $remaining_ips );
		$this->assertContains( '2.3.4.5', $remaining_ips );
		$this->assertContains( '34.253.4.94', $remaining_ips );
		$this->assertContains( '13.54.254.182', $remaining_ips );

		$this->assertNotContains( '34.195.142.251', $remaining_ips );
		$this->assertNotContains( '35.160.3.103', $remaining_ips );
	}
}
