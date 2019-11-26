<?php
/**
 * Contains Class TestWPAuth0DbMigrations.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

/**
 * Class TestWPAuth0DbMigrations.
 * Tests for database upgrade processes.
 */
class TestWPAuth0DbMigrations extends WP_Auth0_Test_Case {

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
		$db_manager->install_db( $test_version );

		// Check that the correct IPs were removed.
		$remaining_ips = explode( ',', self::$opts->get( 'migration_ips' ) );

		$this->assertContains( '1.2.3.4', $remaining_ips );
		$this->assertContains( '2.3.4.5', $remaining_ips );
		$this->assertContains( '34.253.4.94', $remaining_ips );
		$this->assertContains( '13.54.254.182', $remaining_ips );

		$this->assertNotContains( '34.195.142.251', $remaining_ips );
		$this->assertNotContains( '35.160.3.103', $remaining_ips );
	}

	/**
	 * Test a DB upgrade from v20 to v21.
	 */
	public function testV21Update() {
		$test_version = 21;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		// Set CDN URL to previous default.
		self::$opts->set( 'auth0js-cdn', uniqid() );
		self::$opts->set( 'passwordless_cdn_url', uniqid() );
		self::$opts->set( 'cdn_url_legacy', uniqid() );

		// Set options to be deleted.
		update_option( 'wp_auth0_client_grant_failed', 1 );
		update_option( 'wp_auth0_grant_types_failed', 1 );
		update_option( 'wp_auth0_client_grant_success', 1 );
		update_option( 'wp_auth0_grant_types_success', 1 );

		// Run the update.
		$db_manager->install_db( $test_version );

		// Check that unused settings were nullified.
		$this->assertNull( self::$opts->get( 'auth0js-cdn' ) );
		$this->assertNull( self::$opts->get( 'passwordless_cdn_url' ) );
		$this->assertNull( self::$opts->get( 'cdn_url_legacy' ) );

		// Check that unused options were removed.
		$this->assertFalse( get_option( 'wp_auth0_client_grant_failed' ) );
		$this->assertFalse( get_option( 'wp_auth0_grant_types_failed' ) );
		$this->assertFalse( get_option( 'wp_auth0_client_grant_success' ) );
		$this->assertFalse( get_option( 'wp_auth0_grant_types_success' ) );

		// Check that unused settings were removed.
		$updated_options = get_option( self::$opts->get_options_name() );
		$this->assertArrayNotHasKey( 'auth0js-cdn', $updated_options );
		$this->assertArrayNotHasKey( 'passwordless_cdn_url', $updated_options );
		$this->assertArrayNotHasKey( 'cdn_url_legacy', $updated_options );
	}

	/**
	 * Test that 20 -> 21 DB migration updates cdn_url when appropriate.
	 */
	public function testThatV21UpdatesCdnUrl() {
		$test_version = 21;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		// Set the previous default CDN URL.
		self::$opts->set( 'cdn_url', 'https://cdn.auth0.com/js/lock/11.5/lock.min.js' );

		$db_manager->install_db( $test_version );

		// Check that Lock URL was updated.
		$this->assertEquals( WPA0_LOCK_CDN_URL, self::$opts->get( 'cdn_url' ) );
		$this->assertNull( self::$opts->get( 'custom_cdn_url' ) );

		self::$opts->reset();
		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		// Set CDN URL to something other than previous version.
		self::$opts->set( 'cdn_url', 'https://cdn.auth0.com/js/lock/12.0/lock.min.js' );

		$db_manager->install_db( $test_version );

		// Check that Lock URL was not updated.
		$this->assertEquals( 'https://cdn.auth0.com/js/lock/12.0/lock.min.js', self::$opts->get( 'cdn_url' ) );
		$this->assertEquals( 1, self::$opts->get( 'custom_cdn_url' ) );
	}

	/**
	 * Test that 20 -> 21 DB migration updates wordpress_login_enabled and generates wle_code.
	 */
	public function testThatV21UpdatesWle() {
		$test_version = 21;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'wordpress_login_enabled', 1 );
		$db_manager->install_db( $test_version );
		$wle_code_1 = self::$opts->get( 'wle_code' );
		$this->assertEquals( 'link', self::$opts->get( 'wordpress_login_enabled' ) );
		$this->assertGreaterThan( 24, strlen( $wle_code_1 ) );

		self::$opts->set( 'wordpress_login_enabled', 0 );
		self::$opts->set( 'wle_code', '' );
		$db_manager->install_db( $test_version );
		$this->assertEquals( 'isset', self::$opts->get( 'wordpress_login_enabled' ) );
		$this->assertGreaterThan( 24, strlen( self::$opts->get( 'wle_code' ) ) );
		$this->assertNotEquals( $wle_code_1, self::$opts->get( 'wle_code' ) );
	}

	/**
	 * Test that 21 -> 22 DB migration removes the social_big_buttons option.
	 */
	public function testThatV22RemovesSocialBigButtons() {
		$test_version = 22;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'social_big_buttons', '__test_val__' );
		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'social_big_buttons' ) );
	}

	/**
	 * Test that 22 -> 23 DB migration deletes removed settings from the options.
	 */
	public function testThatV23RemovesSettings() {
		$test_version = 23;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'jwt_auth_integration', 1 );
		self::$opts->set( 'link_auth0_users', 1 );
		self::$opts->set( 'custom_css', '__test_css__' );
		self::$opts->set( 'custom_js', '__test_js__' );
		self::$opts->set( 'auth0_implicit_workflow', 1 );
		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'jwt_auth_integration' ) );
		$this->assertNull( self::$opts->get( 'link_auth0_users' ) );
		$this->assertNull( self::$opts->get( 'custom_css' ) );
		$this->assertNull( self::$opts->get( 'custom_js' ) );
		$this->assertNull( self::$opts->get( 'auth0_implicit_workflow' ) );
	}

	/**
	 * Test that 22 -> 23 DB migration adds the language setting into extra Lock conf.
	 */
	public function testThatV23AddsLanguageValue() {
		$test_version = 23;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'language', 'es' );

		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'language' ) );

		$extra_conf = json_decode( self::$opts->get( 'extra_conf' ), true );
		$this->assertEquals( 'es', $extra_conf['language'] );
	}

	/**
	 * Test that 22 -> 23 DB migration does not replace an existing language value in the extra Lock conf.
	 */
	public function testThatV23ReplacesLanguageValue() {
		$test_version = 23;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'language', 'es' );
		self::$opts->set( 'extra_conf', json_encode( [ 'language' => 'pt' ] ) );

		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'language' ) );

		$extra_conf = json_decode( self::$opts->get( 'extra_conf' ), true );
		$this->assertEquals( 'es', $extra_conf['language'] );
	}

	/**
	 * Test that 22 -> 23 DB migration adds the language_dictionary setting into extra Lock conf.
	 */
	public function testThatV23AddsLanguageDictionaryValue() {
		$test_version = 23;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'language_dictionary', json_encode( [ 'key' => 'value' ] ) );

		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'language_dictionary' ) );

		$extra_conf = json_decode( self::$opts->get( 'extra_conf' ), true );
		$this->assertArrayHasKey( 'languageDictionary', $extra_conf );
		$this->assertArrayHasKey( 'key', $extra_conf['languageDictionary'] );
		$this->assertEquals( 'value', $extra_conf['languageDictionary']['key'] );
	}

	/**
	 * Test that 22 -> 23 DB migration does not replace an existing language_dictionary value in the extra Lock conf.
	 */
	public function testThatV23ReplacesLanguageDictionaryValue() {
		$test_version = 23;

		update_option( 'auth0_db_version', $test_version - 1 );
		$db_manager = new WP_Auth0_DBManager( self::$opts );
		$db_manager->init();

		self::$opts->set( 'language_dictionary', json_encode( [ '__migrate_key__' => '__migrate_value__' ] ) );
		self::$opts->set(
			'extra_conf',
			json_encode(
				[
					'languageDictionary' => [
						'__existing_key__' => '__existing_value__',
					],
				]
			)
		);

		$db_manager->install_db( $test_version );
		$this->assertNull( self::$opts->get( 'language_dictionary' ) );

		$extra_conf = json_decode( self::$opts->get( 'extra_conf' ), true );
		$this->assertArrayNotHasKey( '__existing_key__', $extra_conf['languageDictionary'] );
		$this->assertArrayHasKey( '__migrate_key__', $extra_conf['languageDictionary'] );
		$this->assertEquals( '__migrate_value__', $extra_conf['languageDictionary']['__migrate_key__'] );
	}
}
