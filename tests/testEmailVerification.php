<?php
/**
 * Contains Class TestEmailVerification.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestEmailVerification.
 * Tests for class WP_Auth0_Email_Verification.
 */
class TestEmailVerification extends TestCase {

	use AjaxHelpers;

	use HookHelpers;

	use SetUpTestDb;

	use UsersHelper;

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected static $options;

	/**
	 * WP_Auth0_Api_Jobs_Verification instance.
	 *
	 * @var WP_Auth0_Api_Jobs_Verification
	 */
	protected static $api_jobs_resend;

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	protected static $api_client_creds;

	/**
	 * WP_Auth0_Email_Verification instance.
	 *
	 * @var WP_Auth0_Email_Verification
	 */
	protected static $email_verification;

	/**
	 * Set up before entire test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$options = WP_Auth0_Options::Instance();
	}

	/**
	 * Test the the AJAX handler function is hooked properly.
	 */
	public function testHooks() {
		$hooked = $this->get_hook( 'wp_ajax_nopriv_resend_verification_email' );

		$this->assertNotEmpty( $hooked[0] );
		$this->assertEquals( 'wp_auth0_ajax_resend_verification_email', $hooked[0]['function'] );
		$this->assertEquals( 10, $hooked[0]['priority'] );
		$this->assertEquals( 1, $hooked[0]['accepted_args'] );
	}

	/**
	 * Test wp_die output when email needs to be verified.
	 */
	public function testWpRenderDie() {
		add_filter(
			'wp_die_handler',
			function() {
				return [ $this, 'wp_die_handler' ];
			},
			10
		);

		$userinfo = $this->getUserinfo( 'not-auth0' );

		// 1. Check that only the default message appears if this is not an Auth0 strategy.
		ob_start();
		WP_Auth0_Email_Verification::render_die( $userinfo );
		$this->assertEquals( '<p>This site requires a verified email address.</p>', ob_get_clean() );

		// Set the userinfo as an Auth0 strategy.
		$userinfo = $this->getUserinfo( 'auth0' );

		ob_start();
		WP_Auth0_Email_Verification::render_die( $userinfo );
		$html = ob_get_clean();

		// 2. Check that required HTML and JS elements exist
		$this->assertContains( 'This site requires a verified email address', $html );
		$this->assertContains( 'id="js-a0-resend-verification"', $html );
		$this->assertContains( 'Resend verification email', $html );
		$this->assertContains( 'var WPAuth0EmailVerification', $html );
		$this->assertContains( 'nonce:"' . wp_create_nonce( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION ) . '"', $html );
		$this->assertContains( 'sub:"' . $userinfo->sub . '"', $html );
		$this->assertContains( '//code.jquery.com/jquery-', $html );
		$this->assertContains( 'assets/js/die-with-verify-email.js?ver=' . WPA0_VERSION, $html );

		add_filter(
			'auth0_verify_email_page',
			function() {
				return '__test_auth0_verify_email_page__';
			},
			10
		);

		// 3. Test that the auth0_verify_email_page returns passed-in content.
		ob_start();
		WP_Auth0_Email_Verification::render_die( $userinfo );
		$this->assertEquals( '__test_auth0_verify_email_page__', ob_get_clean() );
	}

	/**
	 * Test AJAX email verification send.
	 *
	 * @runInSeparateProcess
	 */
	public function testResendVerificationEmail() {
		$this->startAjaxHalting();

		// 1. Should fail with a bad nonce.
		$caught_exception = false;
		$error_msg        = 'No exception';
		try {
			// Use the hooked function to perform default DI.
			wp_auth0_ajax_resend_verification_email();
		} catch ( Exception $e ) {
			$error_msg        = $e->getMessage();
			$caught_exception = ( 'bad_nonce' === $error_msg );
		}
		$this->assertTrue( $caught_exception, $error_msg );

		// Set the nonce value that check_ajax_referrer looks for.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION );

		// 2. Should fail without a user sub value.
		ob_start();
		$caught_exception = false;
		$error_msg        = 'No exception';
		try {
			wp_auth0_ajax_resend_verification_email();
		} catch ( Exception $e ) {
			$error_msg        = $e->getMessage();
			$caught_exception = ( 'die_ajax' === $error_msg );
		}
		$return_json = ob_get_clean();
		$this->assertTrue( $caught_exception, $error_msg );
		$this->assertEquals( '{"success":false,"data":{"error":"No Auth0 user ID provided."}}', $return_json );

		// Set the sub value that the method looks for.
		$_POST['sub'] = $this->getUserinfo()->sub;

		// Mock the API call.
		$api_jobs_resend_mock = $this->getMockBuilder( WP_Auth0_Api_Jobs_Verification::class )
			->setMethods( [ 'call' ] )
			->setConstructorArgs(
				[
					self::$options,
					new WP_Auth0_Api_Client_Credentials( self::$options ),
					$_POST['sub'],
				]
			)
			->getMock();

		// Fail on first call (#3) and succeed on the second (#4).
		$api_jobs_resend_mock->method( 'call' )->will( $this->onConsecutiveCalls( false, true ) );
		$email_verification = new WP_Auth0_Email_Verification( $api_jobs_resend_mock );

		// 3. Should fail when mocked API call fails.
		ob_start();
		$caught_exception = false;
		try {
			$email_verification->resend_verification_email();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();
		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":false,"data":{"error":"API call failed."}}', $return_json );

		// 4. Should succeed when mocked API call returns true.
		ob_start();
		$caught_exception = false;
		try {
			$email_verification->resend_verification_email();
		} catch ( Exception $e ) {
			$caught_exception = ( 'die_ajax' === $e->getMessage() );
		}
		$return_json = ob_get_clean();
		$this->assertTrue( $caught_exception );
		$this->assertEquals( '{"success":true}', $return_json );
	}

	/**
	 * Prevent the wp_die page from dying and echo the message passed.
	 * Hooked to: wp_die_handler
	 *
	 * @param string $message - HTML to show on the wp_die page.
	 */
	public function wp_die_handler( $message ) {
		echo $message;
	}
}
