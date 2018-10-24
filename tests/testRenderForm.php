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

	use SetUpTestDb;

	use RedirectHelpers;

	use UsersHelper;

	/**
	 * WP_Auth0 instance.
	 *
	 * @var WP_Auth0
	 */
	public static $wp_auth0;

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

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

	/**
	 * Test that a specific region and domain return the correct number of IP addresses.
	 */
	public function testThatLoggedInUserIsRedirected() {
		$this->startRedirectHalting();

		// Configure Auth0.
		self::auth0Ready();
		$this->assertTrue( WP_Auth0::ready() );

		$this->assertContains( 'auth0-login-form', self::$wp_auth0->render_form( self::$html ) );

		// Set the current user to admin.
		$this->setGlobalUser();

		// Use the default login redirection.
		$caught_exception = false;
		try {
			self::$wp_auth0->render_form( self::$html );
		} catch ( Exception $e ) {
			$err_msg          = unserialize( $e->getMessage() );
			$caught_exception = 0 === strpos( $err_msg['location'], 'http://example.org' ) && 302 === $err_msg['status'];
		}
		$this->assertTrue( $caught_exception );

		// Set a login redirect URL.
		$_REQUEST['redirect_to'] = 'http://example.org/custom';

		$caught_exception = false;
		try {
			self::$wp_auth0->render_form( self::$html );
		} catch ( Exception $e ) {
			$err_msg          = unserialize( $e->getMessage() );
			$caught_exception = 0 === strpos( $err_msg['location'], $_REQUEST['redirect_to'] ) && 302 === $err_msg['status'];
		}
		$this->assertTrue( $caught_exception );
	}

	/**
	 * Set the Auth0 plugin settings.
	 *
	 * @param boolean $on - True to turn Auth0 on, false to turn off.
	 */
	public static function auth0Ready( $on = true ) {
		$value = $on ? uniqid() : null;
		self::$opts->set( 'domain', $value );
		self::$opts->set( 'client_id', $value );
		self::$opts->set( 'client_secret', $value );
	}

	/**
	 * Run after each test.
	 */
	public function tearDown() {
		parent::tearDown();
		self::auth0Ready( false );
		$this->stopRedirectHalting();
	}
}
