<?php
use PHPUnit\Framework\TestCase;

/**
 * Class TestWPAuth0DbMigrations.
 * Tests for database upgrade processes.
 */
class TestWPAuth0DbMigrations extends TestCase {

	use setUpTestDb;

	/**
	 * Test the basic options functionality.
	 */
	public function testV19Update() {
		$opts = new WP_Auth0_Options();

		$initial_connections = [
			'social_twitter_key'     => '__twitter_key_test__',
			'social_twitter_secret'  => '__twitter_secret_test__',
			'social_facebook_key'    => '__facebook_key_test__',
			'social_facebook_secret' => '__facebook_secret_test__',
		];

		$connection_keys = array_keys( $initial_connections );

			// Save a 'connections' settings array.
		$opts->set( 'connections', $initial_connections );
		$saved_connections = $opts->get( 'connections' );
		$this->assertEquals( $initial_connections, $saved_connections );

		// Run the migration for v19.
		update_option( 'auth0_db_version', 18 );
		$db_manager = new WP_Auth0_DBManager( $opts );
		$db_manager->init();

		foreach ( $connection_keys as $key ) {
			$this->assertEmpty( $opts->get( $key ) );
		}

		$db_manager->install_db( 19, null );
		foreach ( $connection_keys as $key ) {
			$this->assertEquals( $initial_connections[ $key ], $opts->get( $key ) );
		}
	}
}
