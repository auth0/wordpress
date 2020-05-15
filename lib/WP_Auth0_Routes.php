<?php
/**
 * Contains class WP_Auth0_Routes.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_Routes.
 * Handles all custom routes used by Auth0 except login callback.
 */
class WP_Auth0_Routes {

	/**
	 * WP_Auth0_Options instance for this class.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * WP_Auth0_Ip_Check instance for this class.
	 *
	 * @var WP_Auth0_Ip_Check
	 */
	protected $ip_check;

	/**
	 * WP_Auth0_Routes constructor.
	 *
	 * @param WP_Auth0_Options       $a0_options - WP_Auth0_Options instance.
	 * @param WP_Auth0_Ip_Check|null $ip_check - WP_Auth0_Ip_Check instance.
	 */
	public function __construct( WP_Auth0_Options $a0_options, WP_Auth0_Ip_Check $ip_check = null ) {
		$this->a0_options = $a0_options;
		$this->ip_check   = $ip_check instanceof WP_Auth0_Ip_Check ? $ip_check : new WP_Auth0_Ip_Check( $a0_options );
	}

	/**
	 * Add rewrite tags and rules.
	 */
	public function setup_rewrites() {
		add_rewrite_tag( '%auth0%', '([^&]+)' );
		add_rewrite_tag( '%auth0fallback%', '([^&]+)' );
		add_rewrite_tag( '%code%', '([^&]+)' );
		add_rewrite_tag( '%state%', '([^&]+)' );
		add_rewrite_tag( '%auth0_error%', '([^&]+)' );
		add_rewrite_tag( '%a0_action%', '([^&]+)' );

		add_rewrite_rule( '^\.well-known/oauth2-client-configuration', 'index.php?a0_action=oauth2-config', 'top' );
	}

	/**
	 * Route incoming Auth0 actions.
	 *
	 * @param WP   $wp - WP object for current request.
	 * @param bool $return - True to return the data, false to echo and exit.
	 *
	 * @return bool|string
	 */
	public function custom_requests( $wp, $return = false ) {
		$page = null;

		if ( isset( $wp->query_vars['auth0fallback'] ) ) {
			$page = 'coo-fallback';
		}

		if ( isset( $wp->query_vars['a0_action'] ) ) {
			$page = $wp->query_vars['a0_action'];
		}

		if ( null === $page && isset( $wp->query_vars['pagename'] ) ) {
			$page = $wp->query_vars['pagename'];
		}

		if ( empty( $page ) ) {
			return false;
		}

		$json_header = true;
		switch ( $page ) {
			case 'oauth2-config':
				$output = wp_json_encode( $this->oauth2_config() );
				break;
			case 'migration-ws-login':
				$output = wp_json_encode( $this->migration_ws_login() );
				break;
			case 'migration-ws-get-user':
				$output = wp_json_encode( $this->migration_ws_get_user() );
				break;
			case 'coo-fallback':
				$json_header = false;
				$output      = $this->coo_fallback();
				break;
			default:
				return false;
		}

		if ( $return ) {
			return $output;
		}

		if ( $json_header ) {
			add_filter( 'wp_headers', [ $this, 'add_json_header' ] );
			$wp->send_headers();
		}

		echo $output;
		exit;
	}

	/**
	 * Use with the wp_headers filter to add a Content-Type header for JSON output.
	 *
	 * @param array $headers - Existing headers to modify.
	 *
	 * @return mixed
	 */
	public function add_json_header( array $headers ) {
		$headers['Content-Type'] = 'application/json; charset=' . get_bloginfo( 'charset' );
		return $headers;
	}

	protected function coo_fallback() {
		$protocol = $this->a0_options->get( 'force_https_callback', false ) ? 'https' : null;
		return sprintf(
			'<!DOCTYPE html><html><head><script src="%s"></script><script type="text/javascript">
			var auth0 = new auth0.WebAuth({clientID:"%s",domain:"%s",redirectUri:"%s"});
			auth0.crossOriginAuthenticationCallback();
			</script></head><body></body></html>',
			esc_url( apply_filters( 'auth0_coo_auth0js_url', WPA0_AUTH0_JS_CDN_URL ) ),
			esc_attr( $this->a0_options->get( 'client_id' ) ),
			esc_attr( $this->a0_options->get_auth_domain() ),
			esc_url( $this->a0_options->get_wp_auth0_url( $protocol ) )
		);
	}

	protected function getAuthorizationHeader() {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$authorization = false;

		if ( isset( $_POST['access_token'] ) ) {
			// No need to sanitize, value is returned and checked.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_POST['access_token'] );
		} elseif ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( isset( $headers['Authorization'] ) ) {
				$authorization = $headers['Authorization'];
			} elseif ( isset( $headers['authorization'] ) ) {
				$authorization = $headers['authorization'];
			}
		} elseif ( isset( $_SERVER['Authorization'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_SERVER['Authorization'] );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] );
		}

		return $authorization;

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * User migration login route used by custom database Login script.
	 *
	 * @return array
	 *
	 * @see lib/scripts-js/db-login.js
	 */
	protected function migration_ws_login() {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$code = $this->check_endpoint_access_error();
		if ( $code ) {
			return $this->error_return_array( $code );
		}

		try {
			$this->check_endpoint_request( true );

			// Input is sanitized by core wp_authenticate function.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$user = wp_authenticate( $_POST['username'], $_POST['password'] );

			if ( is_wp_error( $user ) ) {
				throw new Exception( __( 'Invalid credentials', 'wp-auth0' ), 401 );
			}

			unset( $user->data->user_pass );
			return apply_filters( 'auth0_migration_ws_authenticated', $user );

		} catch ( Exception $e ) {
			WP_Auth0_ErrorLog::insert_error( __METHOD__, $e );
			return [
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			];
		}

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * User migration get user route used by custom database Login script.
	 * This is used for email changes made in Auth0.
	 *
	 * @return array
	 *
	 * @see lib/scripts-js/db-get-user.js
	 */
	protected function migration_ws_get_user() {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$code = $this->check_endpoint_access_error();
		if ( $code ) {
			return $this->error_return_array( $code );
		}

		try {
			$this->check_endpoint_request();

			// Input is sanitized by core get_user_by function.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$user = get_user_by( 'email', $_POST['username'] );

			if ( ! $user ) {
				throw new Exception( __( 'User not found', 'wp-auth0' ), 401 );
			}

			$updated_email = WP_Auth0_UsersRepo::get_meta( $user->ID, WP_Auth0_Profile_Change_Email::UPDATED_EMAIL );
			if ( $updated_email === $user->data->user_email ) {
				return [
					'status' => 200,
					'error'  => 'Email update in process',
				];
			}

			unset( $user->data->user_pass );
			return apply_filters( 'auth0_migration_ws_authenticated', $user );

		} catch ( Exception $e ) {
			WP_Auth0_ErrorLog::insert_error( __METHOD__, $e );
			return [
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			];
		}

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	protected function oauth2_config() {

		return [
			'client_name'   => get_bloginfo( 'name' ),
			'redirect_uris' => [ WP_Auth0_InitialSetup::get_setup_redirect_uri() ],
		];
	}

	/**
	 * Check the migration endpoint status and IP filter for incoming requests.
	 *
	 * @return int
	 */
	private function check_endpoint_access_error() {

		// Migration web service is not turned on.
		if ( ! $this->a0_options->get( 'migration_ws' ) ) {
			return 403;
		}

		// IP filtering is on and incoming IP address does not match filter.
		if ( $this->a0_options->get( 'migration_ips_filter' ) ) {
			$allowed_ips = $this->a0_options->get( 'migration_ips' );
			if ( ! $this->ip_check->connection_is_valid( $allowed_ips ) ) {
				return 401;
			}
		}

		return 0;
	}

	/**
	 * Check the incoming request for token and required data.
	 *
	 * @param bool $require_password - True to check for a POSTed password, false to ignore.
	 *
	 * @throws Exception
	 */
	private function check_endpoint_request( $require_password = false ) {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$authorization = $this->getAuthorizationHeader();
		$authorization = trim( str_replace( 'Bearer ', '', $authorization ) );

		if ( empty( $authorization ) ) {
			throw new Exception( __( 'Unauthorized: missing authorization header', 'wp-auth0' ), 401 );
		}

		if ( $authorization !== $this->a0_options->get( 'migration_token' ) ) {
			throw new Exception( __( 'Invalid token', 'wp-auth0' ), 401 );
		}

		if ( empty( $_POST['username'] ) ) {
			throw new Exception( __( 'Username is required', 'wp-auth0' ) );
		}

		if ( $require_password && empty( $_POST['password'] ) ) {
			throw new Exception( __( 'Password is required', 'wp-auth0' ) );
		}

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Default error arrays.
	 *
	 * @param integer $code - Error code.
	 *
	 * @return array
	 */
	private function error_return_array( $code ) {

		switch ( $code ) {
			case 401:
				return [
					'status' => 401,
					'error'  => __( 'Unauthorized', 'wp-auth0' ),
				];

			default:
				return [
					'status' => 403,
					'error'  => __( 'Forbidden', 'wp-auth0' ),
				];
				break;
		}
	}
}
