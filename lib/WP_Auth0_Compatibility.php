<?php

class WP_Auth0_Compatibility {

	/**
	 * 3rd-party plugins targeted by this compatibility class.
	 * @var array
	 */
	protected static $plugins;

	public function __construct() {
		self::$plugins = self::get_supported_plugins();

		// Register AJAX method that will be called after any failed login attempts.
		add_action( 'wp_ajax_nopriv_a0_record_failed_login_attempt', array( $this, 'record_failed_login_attempt' ) );

		add_action( 'auth0_login_form_after_lock_js', array( $this, 'after_lock_js_output' ) );
	}

	/**
	 * Returns an array of 3rd-party plugins targeted by this compatibility class.
	 * @return array
	 */
	public static function get_supported_plugins() {
		return array(
			'security' => array(
				'wordfence' => array(
					'class_name' => 'wordfence',
				)
			)
		);
	}

	public static function after_lock_js_output() { ?>
		// Grab the value of email/username inc ase login fails so we can record the failure.
		lock.on( 'signin submit', function() {
			last_attempt = jQuery('#a0-signin_easy_email').val();
		});

		// If login fails we will send the details to WordPress via AJAX.
		lock.on( 'signin error', function() {
			jQuery.ajax({
				type: "POST",
				url: wpa0_lock_settings.ajaxurl,
				data: {
					action: 'a0_record_failed_login_attempt',
					a0_failed_login_nonce: wpa0_lock_settings.a0_failed_login_nonce,
					user_login: last_attempt
				}
			});
		}); <?php
	}

	/**
	 * Returns the sanitized value of an HTTP POST variable.
	 *
	 * @param string $var The name of the variable.
	 * @param string $type The expected type of value (for sanitization).
	 *
	 * @return string
	 */
	public static function get_sanitized_post_var( $var, $type ) {
		$post_var = isset( $_POST[$var] ) ? $_POST[$var] : '';
		$clean_value = '';

		if ( 'text' === $type ) {
			$clean_value = sanitize_text_field( $post_var );
		} else if ( 'user' === $type ) {
			$clean_value = sanitize_user( $post_var );
		} else if ( 'email' === $type ) {
			$clean_value = sanitize_email( $post_var );
		}

		return $clean_value;
	}

	/**
	 * Returns the username of a failed login attempt made via lock widget.
	 *
	 * @return array The "username" and whether or not it "is_valid".
	 */
	public static function get_failed_login_username() {
		$login = self::get_sanitized_post_var( 'user_login', 'user' );
		$email = self::get_sanitized_post_var( 'user_login', 'email' );
		$username = '';
		$result = array();

		if ( '' !== $login && username_exists( $login ) ) {
			$username = $login;

		} else if ( '' !== $email && is_email( $email ) && email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );

			if ( $user ) {
				$username = $user->login;
			}
		}

		if ( '' !== $username ) {
			$result['username'] = $username;
			$result['is_valid'] = true;
		} else {
			$result['username'] = ( '' !== $email && is_email( $email ) ) ? $email : $login;
			$result['is_valid'] = false;
		}

		return $result;
	}

	/**
	 * Handles AJAX requests sent from the frontend after failed login attempts.
	 * Currently, only Wordfence is supported though other plugins can be easily added.
	 */
	public function record_failed_login_attempt() {
		$nonce = isset( $_POST['a0_failed_login_nonce'] ) ? $_POST['a0_failed_login_nonce'] : '';
		$nonce_valid = wp_verify_nonce( $nonce , 'a0_failed_login' );

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || '' === $nonce || false === $nonce_valid ) {
			die( -1 );
		}

		$username = self::get_failed_login_username();
		$result = array( 'a0_failed_login_nonce' => wp_create_nonce( 'a0_failed_login' ) );

		if ( is_array( self::$plugins['security'] ) && ! empty( self::$plugins['security'] ) ) {
			foreach ( self::$plugins['security'] as $plugin => $plugin_info ) {
				if ( ! isset( $plugin_info['class_name'] ) || ! class_exists( $plugin_info['class_name'] ) ) {
					continue;
				}

				$method_for_current_security_plugin = 'record_failed_login_attempt_' . $plugin;

				$this->$method_for_current_security_plugin( $plugin_info, $username );
			}
		}

		die( json_encode( $result ) );
	}

	/**
	 * Records a failed login attempt to Wordfence's logs.
	 *
	 * @param array $plugin_info
	 * @param array $username The "username" and whether or not it "is_valid".
	 */
	private function record_failed_login_attempt_wordfence( $plugin_info, $username ) {
		$wf_class = $plugin_info['class_name'];
		$wf_log = $wf_class::getLog();
		$action = ( true === $username['is_valid'] ) ? 'loginFailValidUsername' : 'loginFailInvalidUsername';
		$fail = 1;

		$wf_log->logLogin( $action, $fail, $username['username'] );
	}
}