<?php
/**
 * Contains Class TestEmailVerification.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestEmailVerification.
 * Tests for class WP_Auth0_Email_Verification.
 */
class TestEmailVerification extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use HookHelpers;

	use HttpHelpers;

	use UsersHelper;

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

	public function tearDown() {
		parent::tearDown();
		WP_Auth0_Api_Client_Credentials::delete_store();
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

	public function testThatResendActionFailsWhenBadAjaxNonce() {
		$this->startAjaxHalting();

		$_REQUEST['_ajax_nonce'] = uniqid();
		try {
			wp_auth0_ajax_resend_verification_email();
			$error_msg = 'No exception caught';
		} catch ( Exception $e ) {
			$error_msg = $e->getMessage();
		}
		$this->assertEquals( 'bad_nonce', $error_msg );
	}

	public function testThatResendActionFailsWithMissingSub() {
		$this->startAjaxHalting();

		$_REQUEST['_ajax_nonce'] = wp_create_nonce( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION );

		ob_start();
		try {
			wp_auth0_ajax_resend_verification_email();
			$error_msg = 'No exception caught';
		} catch ( Exception $e ) {
			$error_msg = $e->getMessage();
		}
		$this->assertEquals( 'die_ajax', $error_msg );
		$this->assertEquals( '{"success":false,"data":{"error":"No Auth0 user ID provided."}}', ob_get_clean() );
	}

	public function testThatResendActionFailsWhenApiCallFails() {
		$this->startAjaxHalting();

		$_REQUEST['_ajax_nonce'] = wp_create_nonce( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION );
		$_POST['sub']            = $this->getUserinfo()->sub;

		ob_start();
		try {
			wp_auth0_ajax_resend_verification_email();
			$error_msg = 'No exception caught';
		} catch ( Exception $e ) {
			$error_msg = $e->getMessage();
		}
		$this->assertEquals( 'die_ajax', $error_msg );
		$this->assertEquals( '{"success":false,"data":{"error":"API call failed."}}', ob_get_clean() );
	}

	/**
	 * Test AJAX email verification send.
	 */
	public function testResendVerificationEmail() {
		$this->startHttpMocking();
		$this->startAjaxHalting();
		$this->setApiToken( 'update:users' );

		$_REQUEST['_ajax_nonce'] = wp_create_nonce( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION );
		$_POST['sub']            = $this->getUserinfo()->sub;
		$this->http_request_type = 'success_create_empty_body';

		ob_start();
		try {
			wp_auth0_ajax_resend_verification_email();
			$error_msg = 'No exception caught';
		} catch ( Exception $e ) {
			$error_msg = $e->getMessage();
		}
		$this->assertEquals( 'die_ajax', $error_msg );

		$this->assertEquals( '{"success":true}', ob_get_clean() );
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
