<?php
/**
 * Contains Class TestRenderForm.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestRenderForm.
 * Tests that the login form is rendered with the right conditions.
 */
class TestRenderForm extends TestCase {

	use OptionsHelpers;

	use RedirectHelpers;

	use SetUpTestDb;

	use UsersHelper;

	/**
	 * WP_Auth0 instance.
	 *
	 * @var WP_Auth0
	 */
	public static $wp_auth0;

	/**
	 * Initial HTML value for render_form filter value.
	 *
	 * @var string
	 */
	public static $html = '__initial_html__';

	/**
	 * Run before test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts     = WP_Auth0_Options::Instance();
		self::$wp_auth0 = new WP_Auth0( self::$opts );
	}

	/**
	 * Run after each test.
	 */
	public function tearDown() {
		parent::tearDown();
		self::auth0Ready( false );
	}

	/**
	 * Test that a specific region and domain return the correct number of IP addresses.
	 */
	public function testThatRenderFormPassesThrough() {
		self::auth0Ready( false );

		$this->assertArrayNotHasKey( 'action', $_GET );

		// Should pass through initially because WP-Auth0 is not configured.
		$this->assertEquals( self::$html, self::$wp_auth0->render_form( self::$html ) );

		// Configure Auth0.
		self::auth0Ready();
		$this->assertTrue( WP_Auth0::ready() );

		// Should pass through for certain core WP login conditions.
		$_GET['action'] = 'lostpassword';
		$this->assertEquals( self::$html, self::$wp_auth0->render_form( self::$html ) );
		$_GET['action'] = 'rp';
		$this->assertEquals( self::$html, self::$wp_auth0->render_form( self::$html ) );
	}

	/**
	 * Test that a specific region and domain return the correct number of IP addresses.
	 */
	public function testThatFormRendersWhenAuth0IsReady() {

		// Should pass through initially because WP-Auth0 is not configured.
		$this->assertEquals( self::$html, self::$wp_auth0->render_form( self::$html ) );

		// Configure Auth0.
		$this->auth0Ready();
		$this->assertTrue( WP_Auth0::ready() );

		$this->assertContains( 'auth0-login-form', self::$wp_auth0->render_form( self::$html ) );
	}
}
