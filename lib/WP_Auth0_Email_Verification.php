<?php

/**
 * Class WP_Auth0_Email_Verification
 */
class WP_Auth0_Email_Verification {

	const RESEND_NONCE_ACTION = 'auth0_resend_verification_email';

	/**
	 * Setup hooks tied to functions that can be dequeued
	 */
	public static function init() {
		add_action( 'wp_ajax_nopriv_resend_verification_email', 'wp_auth0_ajax_resend_verification_email' );
	}

	/**
	 * Stop the login process and show email verification prompt
	 *
	 * @param object $userinfo
	 */
	public static function render_die( $userinfo ) {

		$html = sprintf(
			'<p>%s<br><a id="js-a0-resend-verification" href="#">%s</a></p>
			<p><a href="%s?%d">%s</a></p>
			<script>var WPAuth0EmailVerification={ajaxUrl:"%s",sub:"%s",nonce:"%s",e_msg:"%s",s_msg:"%s"}</script>
			<script src="%s"></script>
			<script src="%s"></script>',

			__( 'Please verify your email and log in again.', 'wp-auth0' ),
			__( 'Resend verification email.', 'wp-auth0' ),
			wp_login_url(),
			time(),
			__( '← Login', 'wp-auth0' ),
			esc_url( admin_url( 'admin-ajax.php' ) ),
			esc_js( $userinfo->sub ),
			esc_js( wp_create_nonce( self::RESEND_NONCE_ACTION ) ),
			esc_js( __( 'Something went wrong; please login and try again.', 'wp-auth0' ) ),
			esc_js( __( 'Email successfully re-sent to ' . $userinfo->email . '!', 'wp-auth0' ) ),
			'//code.jquery.com/jquery-1.12.4.js',
			WPA0_PLUGIN_URL . 'assets/js/die-with-verify-email.js?ver=' . WPA0_VERSION
		);

		$html = apply_filters( 'auth0_verify_email_page', $html, $userinfo, '' );

		wp_die( $html );
	}

	/**
	 * AJAX handler to request that the verification email be resent
	 * Triggered in $this->render_die
	 */
	public static function ajax_resend_email() {

		check_ajax_referer( self::RESEND_NONCE_ACTION, 'nonce' );

		if ( empty( $_POST['sub'] ) ) {
			die();
		}

		$connect_info = WP_Auth0_Api_Client::get_connect_info();

		$token = WP_Auth0_Api_Client::get_token(
			$connect_info['domain'],
			$connect_info['client_id'],
			$connect_info['client_secret'],
			'client_credentials',
			array(
				'audience' => $connect_info['audience']
			)
		);

		$tokenDecoded = json_decode( $token['body'] );

		if ( empty( $tokenDecoded->access_token ) ) {
			die();
		}

		echo WP_Auth0_Api_Client::resend_verification_email(
			$tokenDecoded->access_token,
			sanitize_text_field( $_POST['sub'] )
		) ? 'success' : 'fail';

		die();
	}
}

function wp_auth0_ajax_resend_verification_email() {
	WP_Auth0_Email_Verification::ajax_resend_email();
}