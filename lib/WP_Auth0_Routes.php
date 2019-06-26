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
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
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
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function init() {
		add_action( 'parse_request', [ $this, 'custom_requests' ] );
	}

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
			sanitize_text_field( $this->a0_options->get( 'client_id' ) ),
			sanitize_text_field( $this->a0_options->get_auth_domain() ),
			esc_url( $this->a0_options->get_wp_auth0_url( $protocol ) )
		);
	}

	protected function getAuthorizationHeader() {
		$authorization = false;

		if ( isset( $_POST['access_token'] ) ) {
			$authorization = $_POST['access_token'];
		} elseif ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( isset( $headers['Authorization'] ) ) {
				$authorization = $headers['Authorization'];
			} elseif ( isset( $headers['authorization'] ) ) {
				$authorization = $headers['authorization'];
			}
		} elseif ( isset( $_SERVER['Authorization'] ) ) {
			$authorization = $_SERVER['Authorization'];
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$authorization = $_SERVER['HTTP_AUTHORIZATION'];
		}

		return $authorization;
	}

	/**
	 * User migration login route used by custom database Login script.
	 *
	 * @return array
	 *
	 * @see lib/scripts-js/db-login.js
	 */
	protected function migration_ws_login() {

		$code = $this->check_endpoint_access_error();
		if ( $code ) {
			return $this->error_return_array( $code );
		}

		try {
			$this->check_endpoint_request( true );

			$user = wp_authenticate( $_POST['username'], $_POST['password'] );

			if ( is_wp_error( $user ) ) {
				throw new Exception( __( 'Invalid credentials', 'wp-auth0' ), 401 );
			}

			unset( $user->data->user_pass );
			return apply_filters( 'auth0_migration_ws_authenticated', $user );

		} catch ( Exception $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
			return [
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			];
		}
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

		$code = $this->check_endpoint_access_error();
		if ( $code ) {
			return $this->error_return_array( $code );
		}

		try {
			$this->check_endpoint_request();

			$username = $_POST['username'];

			$user = get_user_by( 'email', $username );

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
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
			return [
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			];
		}
	}

	protected function oauth2_config() {

		return [
			'client_name'   => get_bloginfo( 'name' ),
			'redirect_uris' => [ admin_url( 'admin.php?page=wpa0-setup&callback=1' ) ],
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
		$authorization = $this->getAuthorizationHeader();
		$authorization = trim( str_replace( 'Bearer ', '', $authorization ) );

		if ( empty( $authorization ) ) {
			throw new Exception( __( 'Unauthorized: missing authorization header', 'wp-auth0' ), 401 );
		}

		if ( ! $this->valid_token( $authorization ) ) {
			throw new Exception( __( 'Invalid token', 'wp-auth0' ), 401 );
		}

		if ( empty( $_POST['username'] ) ) {
			throw new Exception( __( 'Username is required', 'wp-auth0' ) );
		}

		if ( $require_password && empty( $_POST['password'] ) ) {
			throw new Exception( __( 'Password is required', 'wp-auth0' ) );
		}
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

	/**
	 * Check if a token or token JTI is the same as what is stored.
	 *
	 * @param string $authorization - Incoming migration token.
	 *
	 * @return bool
	 */
	private function valid_token( $authorization ) {
		$token = $this->a0_options->get( 'migration_token' );
		if ( $token === $authorization ) {
			return true;
		}
		$client_secret = $this->a0_options->get( 'client_secret' );
		if ( $this->a0_options->get( 'client_secret_base64_encoded' ) ) {
			$client_secret = JWT::urlsafeB64Decode( $client_secret );
		}

		try {
			$decoded = JWT::decode( $token, $client_secret, [ 'HS256' ] );
			return isset( $decoded->jti ) && $decoded->jti === $this->a0_options->get( 'migration_token_id' );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
