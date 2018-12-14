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

	public function init() {
		add_action( 'parse_request', array( $this, 'custom_requests' ) );
	}

	public function setup_rewrites( $force_ws = false ) {
		add_rewrite_tag( '%auth0%', '([^&]+)' );
		add_rewrite_tag( '%auth0fallback%', '([^&]+)' );
		add_rewrite_tag( '%code%', '([^&]+)' );
		add_rewrite_tag( '%state%', '([^&]+)' );
		add_rewrite_tag( '%auth0_error%', '([^&]+)' );
		add_rewrite_tag( '%a0_action%', '([^&]+)' );

		add_rewrite_rule( '^auth0', 'index.php?auth0=1', 'top' );
		add_rewrite_rule( '^\.well-known/oauth2-client-configuration', 'index.php?a0_action=oauth2-config', 'top' );
	}

	/**
	 * Route incoming Auth0 actions.
	 *
	 * @param WP_Query $wp - WP_Query object for current request.
	 *
	 * @return bool|false|string
	 */
	public function custom_requests( $wp ) {
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

		$output = null;
		if ( ! empty( $page ) ) {
			switch ( $page ) {
				case 'oauth2-config':
					$this->oauth2_config();
					exit;
				case 'migration-ws-login':
					$output = $this->migration_ws_login();
					break;
				case 'migration-ws-get-user':
					$output = $this->migration_ws_get_user();
					break;
				case 'coo-fallback':
					$this->coo_fallback();
					exit;
				default:
					return false;
			}

			if ( $output ) {
				if ( empty( $wp->query_vars['custom_requests_return'] ) ) {
					echo wp_json_encode( $output );
					exit;
				} else {
					return wp_json_encode( $output );
				}
			}
		}
	}

	protected function coo_fallback() {
		$cdn          = $this->a0_options->get( 'auth0js-cdn' );
		$client_id    = $this->a0_options->get( 'client_id' );
		$domain       = $this->a0_options->get_auth_domain();
		$protocol     = $this->a0_options->get( 'force_https_callback', false ) ? 'https' : null;
		$redirect_uri = $this->a0_options->get_wp_auth0_url( $protocol );
		echo <<<EOT
		<!DOCTYPE html>
		<html>
		<head>
		<script src="$cdn"></script>
		<script type="text/javascript">
		  var auth0 = new auth0.WebAuth({
			clientID: '$client_id',
			domain: '$domain',
			redirectUri: '$redirect_uri'
		  });
		  auth0.crossOriginAuthenticationCallback();
		</script>
		</head>
		<body></body>
		</html>	  
EOT;
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

		// Migration web service is not turned on.
		if ( ! $this->a0_options->get( 'migration_ws' ) ) {
			return $this->error_return_array( 403 );
		}

		// IP filtering is on and incoming IP address does not match filter.
		if ( $this->a0_options->get( 'migration_ips_filter' ) ) {
			$allowed_ips = $this->a0_options->get( 'migration_ips' );
			if ( ! $this->ip_check->connection_is_valid( $allowed_ips ) ) {
				return $this->error_return_array( 401 );
			}
		}

		$authorization = $this->getAuthorizationHeader();
		$authorization = trim( str_replace( 'Bearer ', '', $authorization ) );

		try {
			if ( empty( $authorization ) ) {
				throw new Exception( __( 'Unauthorized: missing authorization header', 'wp-auth0' ), 401 );
			}

			if ( ! $this->valid_token( $authorization ) ) {
				throw new Exception( __( 'Invalid token', 'wp-auth0' ), 401 );
			}

			if ( empty( $_POST['username'] ) ) {
				throw new Exception( __( 'Username is required', 'wp-auth0' ) );
			}

			if ( empty( $_POST['password'] ) ) {
				throw new Exception( __( 'Password is required', 'wp-auth0' ) );
			}

			$user = wp_authenticate( $_POST['username'], $_POST['password'] );

			if ( is_wp_error( $user ) ) {
				throw new Exception( __( 'Invalid Credentials', 'wp-auth0' ), 401 );
			}

			unset( $user->data->user_pass );
			return apply_filters( 'auth0_migration_ws_authenticated', $user );

		} catch ( Exception $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
			return array(
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			);
		}
	}

	/**
	 * User migration get user route used by custom database Login script.
	 *
	 * @return array
	 *
	 * @see lib/scripts-js/db-get-user.js
	 */
	protected function migration_ws_get_user() {

		// Migration web service is not turned on.
		if ( ! $this->a0_options->get( 'migration_ws' ) ) {
			return $this->error_return_array( 403 );
		}

		// IP filtering is on and incoming IP address does not match filter.
		if ( $this->a0_options->get( 'migration_ips_filter' ) ) {
			$allowed_ips = $this->a0_options->get( 'migration_ips' );
			if ( ! $this->ip_check->connection_is_valid( $allowed_ips ) ) {
				return $this->error_return_array( 401 );
			}
		}

		$authorization = $this->getAuthorizationHeader();
		$authorization = trim( str_replace( 'Bearer ', '', $authorization ) );
		$user          = null;

		try {
			if ( empty( $authorization ) ) {
				throw new Exception( __( 'Unauthorized: missing authorization header', 'wp-auth0' ), 401 );
			}

			if ( ! $this->valid_token( $authorization ) ) {
				throw new Exception( __( 'Invalid token', 'wp-auth0' ), 401 );
			}

			if ( ! isset( $_POST['username'] ) ) {
				throw new Exception( __( 'Username is required', 'wp-auth0' ) );
			}

			$username = $_POST['username'];

			$user = get_user_by( 'email', $username );
			if ( ! $user ) {
				$user = get_user_by( 'slug', $username );
			}

			if ( ! $user ) {
				throw new Exception( __( 'Invalid Credentials', 'wp-auth0' ), 401 );
			}

			unset( $user->data->user_pass );
			return apply_filters( 'auth0_migration_ws_authenticated', $user );

		} catch ( Exception $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
			return array(
				'status' => $e->getCode() ?: 400,
				'error'  => $e->getMessage(),
			);
		}
	}

	protected function oauth2_config() {

		$callback_url = admin_url( 'admin.php?page=wpa0-setup&callback=1' );

		echo json_encode(
			array(
				'client_name'   => get_bloginfo( 'name' ),
				'redirect_uris' => array(
					$callback_url,
				),
			)
		);
		exit;
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
				return array(
					'status' => 401,
					'error'  => __( 'Unauthorized', 'wp-auth0' ),
				);

			case 403:
				return array(
					'status' => 403,
					'error'  => __( 'Forbidden', 'wp-auth0' ),
				);
				break;
		}
	}

	/**
	 * Check if a token or token JTI is the same as what is stored.
	 *
	 * @param string $authorization - Incoming migration token;
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
			$decoded = JWT::decode( $token, $client_secret, array( 'HS256' ) );
			return isset( $decoded->jti ) && $decoded->jti === $this->a0_options->get( 'migration_token_id' );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
