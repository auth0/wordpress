<?php
/**
 * Contains Class TestOptionMigrationIps.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

/**
 * Class TestOptionMigrationIps.
 */
class TestOptionMigrationIps extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use DomDocumentHelpers;

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Admin_Advanced.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * Instance of WP_Auth0_Ip_Check.
	 *
	 * @var WP_Auth0_Ip_Check
	 */
	public static $ip_check;

	/**
	 * Runs before each test starts.
	 */
	public function setUp() {
		parent::setUp();
		$router         = new WP_Auth0_Routes( self::$opts );
		self::$admin    = new WP_Auth0_Admin_Advanced( self::$opts, $router );
		self::$ip_check = new WP_Auth0_Ip_Check();
	}


	public function testThatSettingsFieldRendersProperly() {
		self::$opts->set( 'domain', 'test.eu.auth0.com' );
		$field_args = [
			'label_for' => 'wpa0_migration_ws_ips',
			'opt_name'  => 'migration_ips',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_migration_ws_ips( $field_args );
		$field_html = ob_get_clean();

		$textarea = $this->getDomListFromTagName( $field_html, 'textarea' );
		$this->assertEquals( 1, $textarea->length );
		$this->assertEquals( $field_args['label_for'], $textarea->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$textarea->item( 0 )->getAttribute( 'name' )
		);

		$whitelist_ips = self::$ip_check->get_ips_by_domain( 'test.eu.auth0.com', null );

		$ips = $this->getDomListFromTagName( $field_html, 'code' );
		$this->assertEquals( count( $whitelist_ips ), $ips->length );
		for ( $item_index = 0; $item_index < $ips->length; $item_index++ ) {
			$this->assertContains( $ips->item( $item_index )->nodeValue, $whitelist_ips );
		}
	}

	public function testThatEmptyIpsAreValidatedToAnEmptyString() {
		$input     = [ 'migration_ips' => 0 ];
		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '', $validated['migration_ips'] );

		$input     = [ 'migration_ips' => false ];
		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '', $validated['migration_ips'] );

		$input     = [ 'migration_ips' => null ];
		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '', $validated['migration_ips'] );
	}

	public function testThatDuplicateIpsAreRemovedDuringValidation() {
		$input = [ 'migration_ips' => '1.2.3.4, 2.3.4.5,1.2.3.4,3.4.5.6, 2.3.4.5' ];

		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '1.2.3.4, 2.3.4.5, 3.4.5.6', $validated['migration_ips'] );
	}

	public function testThatExistingWhitelistIpsAreRemovedDuringValidation() {
		$whitelist_ips         = self::$ip_check->get_ip_by_region( 'eu', null );
		$random_whitelisted_ip = $whitelist_ips[ array_rand( $whitelist_ips ) ];
		$input                 = [
			'migration_ips' => '4.5.6.7,' . $random_whitelisted_ip . ',5.6.7.8',
			'domain'        => 'test.eu.auth0.com',
		];

		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '4.5.6.7, 5.6.7.8', $validated['migration_ips'] );
	}

	public function testThatUnsafeValuesAreRemovedDuringValidation() {
		$input = [ 'migration_ips' => '6.7.8.9,<script>alert("Hello")</script>,7.8.9.10' ];

		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '6.7.8.9, 7.8.9.10', $validated['migration_ips'] );
	}

	public function testThatEmptyValuesAreRemovedDuringValidation() {
		$input = [ 'migration_ips' => '8.9.10.11, , 9.10.11.12, 0' ];

		$validated = self::$admin->migration_ips_validation( [], $input );
		$this->assertEquals( '8.9.10.11, 9.10.11.12', $validated['migration_ips'] );
	}
}
