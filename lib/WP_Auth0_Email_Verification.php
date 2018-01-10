<?php

class WP_Auth0_Email_Verification {
	
	private $resend_nonce_action = 'auth0_resend_verification_email';
	
	public function __construct () {
		
		add_action( 'wp_ajax_nopriv_resend_verification_email', array( $this, 'ajax_resend_email' ) );
	}
	
	/**
	 * Stop the login process and show email verification prompt
	 *
	 * @param object $userinfo
	 * @param string $id_token - DEPRECATED 3.5.0
	 * @param string $access_token - DEPRECATED
	 */
	public function render_die( $userinfo, $id_token, $access_token ) {
		
		$html = sprintf(
			
			// Format
		
			'<p>%s<br><a id="js-a0-resend-verification" href="#">%s</a></p>
			<p><a href="%s?%d">%s</a></p>
			<script>var WPAuth0EmailVerification={ajaxUrl:"%s",sub:"%s",nonce:"%s",e_msg:"%s",s_msg:"%s"}</script>
			<script src="%s"></script>
			<script src="%s"></script>',
			
			// Replacements
			
			__( 'Please verify your email and log in again.', 'wp-auth0' ),
			__( 'Resend verification email.', 'wp-auth0' ),
			wp_login_url(),
			time(),
			__( '← Login', 'wp-auth0' ),
			esc_url( admin_url( 'admin-ajax.php' ) ),
			esc_js ( $userinfo->sub ),
			esc_js( wp_create_nonce( $this->resend_nonce_action ) ),
			esc_js( __( 'Something went wrong; please attempt to login again.', 'wp-auth0' ) ),
			esc_js( __( 'Email successfully re-sent to ' . $userinfo->email . '!', 'wp-auth0' ) ),
			'//code.jquery.com/jquery-1.12.4.js',
			WPA0_PLUGIN_URL . 'assets/js/die-with-verify-email.js?ver=' . WPA0_VERSION
		);
		
		$html = apply_filters( 'auth0_verify_email_page' , $html, $userinfo, $id_token, $access_token );
		
		wp_die( $html );
	}
	
	/**
	 * AJAX handler to request that the verification email be resent
	 * Triggered in $this->render_die
	 */
	public function ajax_resend_email() {
		
		check_ajax_referer( $this->resend_nonce_action, 'nonce' );
		
		$a0_options = WP_Auth0_Options::Instance();
		
		$token = WP_Auth0_Api_Client::get_token(
			$a0_options->get( 'domain' ),
			$a0_options->get( 'client_id' ),
			$a0_options->get( 'client_secret' ),
			'client_credentials',
			array(
				'audience' => 'https://joshc-test.auth0.com/api/v2/'
			)
		);
		
		echo '<pre>' . print_r( get_option('wp_auth0_settings'), TRUE ) . '</pre>';
		echo '<pre>' . print_r( $token, TRUE ) . '</pre>'; die();
		
		$response = WP_Auth0_Api_Client::resend_verification_email(
			$a0_options->get( 'domain' ),
			'',
			sanitize_text_field( $_POST[ 'sub' ] )
		);
		
		die( '1' );
	}
}