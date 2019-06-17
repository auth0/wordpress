<?php
/**
 * Contains class WP_Auth0_Email_Verification
 *
 * @package WP-Auth0
 *
 * @since 3.5.0
 */

/**
 * Class WP_Auth0_Email_Verification.
 */
class WP_Auth0_Email_Verification {

	const RESEND_NONCE_ACTION = 'auth0_resend_verification_email';

	/**
	 * WP_Auth0_Api_Jobs_Verification instance.
	 *
	 * @var WP_Auth0_Api_Jobs_Verification
	 */
	protected $api_jobs_resend;

	/**
	 * WP_Auth0_Email_Verification constructor.
	 *
	 * @param WP_Auth0_Api_Jobs_Verification $api_jobs_resend - WP_Auth0_Api_Jobs_Verification instance.
	 */
	public function __construct( WP_Auth0_Api_Jobs_Verification $api_jobs_resend ) {
		$this->api_jobs_resend = $api_jobs_resend;
	}

	/**
	 * Set up hooks tied to functions that can be dequeued.
	 *
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function init() {
		add_action( 'wp_ajax_nopriv_resend_verification_email', 'wp_auth0_ajax_resend_verification_email' );
	}

	/**
	 * Stop the login process and show email verification prompt.
	 *
	 * @param object $userinfo - User profile object returned from Auth0.
	 */
	public static function render_die( $userinfo ) {
		$user_id = isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub;

		$html = sprintf( '<p>%s</p>', __( 'This site requires a verified email address.', 'wp-auth0' ) );

		// Only provide resend verification link for DB connection users.
		if ( 0 === strpos( $user_id, 'auth0|' ) ) {
			$html .= sprintf(
				'<p><a id="js-a0-resend-verification" href="#">%s</a></p>
				<p><a href="%s?%d">%s</a></p>
				<script>var WPAuth0EmailVerification={ajaxUrl:"%s",sub:"%s",nonce:"%s",e_msg:"%s",s_msg:"%s"}</script>
				<script src="%s"></script>
				<script src="%s"></script>',
				__( 'Resend verification email.', 'wp-auth0' ),
				wp_login_url(),
				time(),
				__( '← Login', 'wp-auth0' ),
				esc_url( admin_url( 'admin-ajax.php' ) ),
				esc_js( $user_id ),
				esc_js( wp_create_nonce( self::RESEND_NONCE_ACTION ) ),
				esc_js( __( 'Something went wrong; please login and try again.', 'wp-auth0' ) ),
				esc_js( __( 'Email successfully re-sent to ', 'wp-auth0' ) . $userinfo->email ),
				'//code.jquery.com/jquery-1.12.4.js',
				WPA0_PLUGIN_URL . 'assets/js/die-with-verify-email.js?ver=' . WPA0_VERSION
			);
		}

		$html = apply_filters( 'auth0_verify_email_page', $html, $userinfo, '' );
		wp_die( $html );
	}

	/**
	 * AJAX handler to request that the verification email be resent.
	 * Triggered in $this->render_die
	 *
	 * @codeCoverageIgnore - Tested in TestEmailVerification::testResendVerificationEmail()
	 */
	public function resend_verification_email() {
		check_ajax_referer( self::RESEND_NONCE_ACTION );

		if ( empty( $_POST['sub'] ) ) {
			wp_send_json_error( [ 'error' => __( 'No Auth0 user ID provided.', 'wp-auth0' ) ] );
		}

		if ( ! $this->api_jobs_resend->call( $_POST['sub'] ) ) {
			wp_send_json_error( [ 'error' => __( 'API call failed.', 'wp-auth0' ) ] );
		}

		wp_send_json_success();
	}

	/*
	 *
	 * DEPRECATED
	 *
	 */

	/**
	 * AJAX handler to request that the verification email be re-sent.
	 *
	 * @deprecated - 3.8.0, use $this->resend_verification_email().
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function ajax_resend_email() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		check_ajax_referer( self::RESEND_NONCE_ACTION );
		if ( ! empty( $_POST['sub'] ) ) {
			$user_id = sanitize_text_field( $_POST['sub'] );
			$result  = WP_Auth0_Api_Client::resend_verification_email( $user_id );
			echo $result ? 'success' : 'fail';
		}
	}
}

/**
 * AJAX handler to re-send verification email.
 * Hooked to: wp_ajax_nopriv_resend_verification_email
 *
 * @codeCoverageIgnore - Tested in TestEmailVerification::testResendVerificationEmail()
 */
function wp_auth0_ajax_resend_verification_email() {
	$options               = WP_Auth0_Options::Instance();
	$api_client_creds      = new WP_Auth0_Api_Client_Credentials( $options );
	$api_jobs_verification = new WP_Auth0_Api_Jobs_Verification( $options, $api_client_creds );
	$email_verification    = new WP_Auth0_Email_Verification( $api_jobs_verification );

	$email_verification->resend_verification_email();
}
