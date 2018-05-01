<?php
/**
 * WP_Auth0_LoginManager class
 *
 * @package WordPress
 * @subpackage WP-Auth0
 * @since 2.0.0
 */

/**
 * Handles login callbacks and auto-login redirecting
 *
 * @since 2.0.0
 */
class WP_Auth0_LoginManager {

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var null|WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * Should the new user have an administrator role?
	 *
	 * @var bool|null
	 */
	protected $admin_role;

	/**
	 * Ignore verified email requirement in Settings > Advanced.
	 *
	 * @var bool
	 */
	protected $ignore_unverified_email;

	/**
	 * User strategy to use.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	protected $users_repo;

	/**
	 * State value returned from successful Auth0 login.
	 *
	 * @var string
	 *
	 * @see WP_Auth0_Lock10_Options::get_state_obj()
	 */
	protected $state;

	/**
	 * Decoded version of $this>state.
	 *
	 * @var object
	 */
	protected $state_decoded;

	/**
	 * WP_Auth0_LoginManager constructor.
	 *
	 * @param WP_Auth0_UsersRepo    $users_repo - see member variable doc comment.
	 * @param WP_Auth0_Options|null $a0_options - see member variable doc comment.
	 * @param null|bool             $admin_role - see member variable doc comment.
	 * @param bool                  $ignore_unverified_email - see member variable doc comment.
	 */
	public function __construct(
		WP_Auth0_UsersRepo $users_repo,
		$a0_options = null,
		$admin_role = null,
		$ignore_unverified_email = false
	) {
		$this->admin_role              = $admin_role;
		$this->ignore_unverified_email = $ignore_unverified_email;
		$this->users_repo              = $users_repo;

		if ( $a0_options instanceof WP_Auth0_Options ) {
			$this->a0_options = $a0_options;
		} else {
			$this->a0_options = WP_Auth0_Options::Instance();
		}
	}

	/**
	 * Attach methods to hooks.
	 * See method comments for functionality.
	 *
	 * @see WP_Auth0::init()
	 */
	public function init() {
		add_action( 'login_init', array( $this, 'login_auto' ) );
		add_action( 'template_redirect', array( $this, 'init_auth0' ), 1 );
		add_action( 'wp_logout', array( $this, 'logout' ) );
		add_filter( 'login_message', array( $this, 'auth0_sso_footer' ) );
		add_action( 'wp_footer', array( $this, 'auth0_singlelogout_footer' ) );
		add_action( 'wp_login', array( $this, 'end_session' ) );
	}

	/**
	 * Redirect to a specific connection designated in Settings > Advanced
	 */
	public function login_auto() {
		if (
			// Nothing to do
			( ! $this->a0_options->get( 'auto_login', FALSE ) )
			// Auth0 is not ready to process logins
			|| ! WP_Auth0::ready()
			// Do not redirect POST requests
			|| strtolower( $_SERVER['REQUEST_METHOD'] ) !== 'get'
			// Do not redirect login page override
			|| isset( $_GET['wle'] )
			// Do not redirect log out action
			|| ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] )
			// Do not redirect Auth0 login processing
			|| null !== $this->query_vars( 'auth0' )
			// Do not redirect if already authenticated
			|| is_user_logged_in()
		) {
			return;
		}

		$connection = apply_filters( 'auth0_get_auto_login_connection', $this->a0_options->get( 'auto_login_method' ) );
		$auth_params = self::get_authorize_params( $connection );

		$auth_url = 'https://' . $this->a0_options->get( 'domain' ) . '/authorize';
		$auth_url = add_query_arg( array_map( 'rawurlencode', $auth_params ), $auth_url );

		setcookie( WPA0_STATE_COOKIE_NAME, $auth_params['state'], time() + WP_Auth0_Nonce_Handler::COOKIE_EXPIRES, '/' );
		wp_redirect( $auth_url );
		exit;
	}

	/**
	 * Process an incoming successful login from Auth0, aka login callback.
	 * Auth0 must be configured and 'auth0' URL parameter not empty.
	 * Handles errors and state validation
	 */
	public function init_auth0() {

		// Not an Auth0 login process or settings are not configured to allow logins.
		if ( ! $this->query_vars( 'auth0' ) || ! WP_Auth0::ready() ) {
			return;
		}

		// Catch any incoming errors and stop the login process.
		// See https://auth0.com/docs/libraries/error-messages for more info.
		if ( $this->query_vars( 'error' ) || $this->query_vars( 'error_description' ) ) {
			$error_msg  = sanitize_text_field( rawurldecode( $_REQUEST[ 'error_description' ] ) );
			$error_code = sanitize_text_field( rawurldecode( $_REQUEST[ 'error' ] ) );
			$this->die_on_login( $error_msg, $error_code );
		}

		// Check for valid state nonce, set in WP_Auth0_Lock10_Options::get_state_obj().
		// See https://auth0.com/docs/protocols/oauth2/oauth-state for more info.
		if ( ! $this->validate_state() ) {
			$this->die_on_login( __( 'Invalid state', 'wp-auth0' ) );
		}

		try {
			if ( $this->query_vars( 'auth0' ) === 'implicit' ) {
				$this->implicit_login();
			} else {
				$this->redirect_login();
			}
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {

			// Errors encountered during the OAuth login flow.
			$this->die_on_login( $e->getMessage(), $e->getCode() );

		} catch ( WP_Auth0_BeforeLoginException $e ) {

			// Errors encountered during the WordPress login flow.
			$this->die_on_login( $e->getMessage(), $e->getCode(), false );
		}
	}

	/**
	 * Main login flow using the Authorization Code Grant.
	 *
	 * @throws WP_Auth0_LoginFlowValidationException - OAuth login flow errors.
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 *
	 * @link https://auth0.com/docs/api-auth/tutorials/authorization-code-grant
	 */
	public function redirect_login() {
		$domain             = $this->a0_options->get( 'domain' );
		$client_id          = $this->a0_options->get( 'client_id' );
		$client_secret      = $this->a0_options->get( 'client_secret' );
		$userinfo_resp_code = null;
		$userinfo_resp_body = null;

		// Exchange authorization code for an access token.
		$exchange_resp = WP_Auth0_Api_Client::get_token(
			$domain, $client_id, $client_secret, 'authorization_code', array(
				'redirect_uri' => home_url(),
				'code'         => $this->query_vars( 'code' ),
			)
		);

		$exchange_resp_code = (int) wp_remote_retrieve_response_code( $exchange_resp );
		$exchange_resp_body = wp_remote_retrieve_body( $exchange_resp );

		if ( 401 === $exchange_resp_code ) {

			// Not authorized to access the site.
			WP_Auth0_ErrorManager::insert_auth0_error(
				__METHOD__ . ' L:' . __LINE__,
				__( 'An /oauth/token call triggered a 401 response from Auth0. ', 'wp-auth0' ) .
				__( 'Please check the Client Secret saved in the Auth0 plugin settings. ', 'wp-auth0' )
			);
			throw new WP_Auth0_LoginFlowValidationException( __( 'Not Authorized', 'wp-auth0' ), 401 );

		} elseif ( empty( $exchange_resp_body ) ) {

			// Unsuccessful for another reason.
			if ( $exchange_resp instanceof WP_Error ) {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' L:' . __LINE__, $exchange_resp );
			}

			throw new WP_Auth0_LoginFlowValidationException( __( 'Unknown error', 'wp-auth0' ), $exchange_resp_code );
		}

		$data = json_decode( $exchange_resp_body );

		if ( empty( $data->access_token ) ) {

			// Look for clues as to what went wrong.
			$e_message = ! empty( $data->error_description ) ? $data->error_description : __( 'Unknown error', 'wp-auth0' );
			$e_code    = ! empty( $data->error ) ? $data->error : $exchange_resp_code;
			throw new WP_Auth0_LoginFlowValidationException( $e_message, $e_code );
		}

		// Decode the incoming ID token for the Auth0 user.
		$decoded_token = JWT::decode(
			$data->id_token,
			$this->a0_options->get_client_secret_as_key(),
			array( $this->a0_options->get_client_signing_algorithm() )
		);

		// Attempt to authenticate with the Management API.
		$client_credentials_token = WP_Auth0_Api_Client::get_client_token();

		if ( $client_credentials_token ) {
			$userinfo_resp      = WP_Auth0_Api_Client::get_user( $domain, $client_credentials_token, $decoded_token->sub );
			$userinfo_resp_code = (int) wp_remote_retrieve_response_code( $userinfo_resp );
			$userinfo_resp_body = wp_remote_retrieve_body( $userinfo_resp );
		}

		// Management API call failed, fallback to userinfo.
		if ( 200 !== $userinfo_resp_code || empty( $userinfo_resp_body ) ) {

			$userinfo_resp      = WP_Auth0_Api_Client::get_user_info( $domain, $data->access_token );
			$userinfo_resp_code = (int) wp_remote_retrieve_response_code( $userinfo_resp );
			$userinfo_resp_body = wp_remote_retrieve_body( $userinfo_resp );

			if ( 200 !== $userinfo_resp_code || empty( $userinfo_resp_body ) ) {

				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' L:' . __LINE__, $userinfo_resp );
				throw new WP_Auth0_LoginFlowValidationException(
					__( 'Error getting user information', 'wp-auth0' ),
					$userinfo_resp_code
				);
			}
		}

		$userinfo = json_decode( $userinfo_resp_body );

		if ( $this->login_user( $userinfo, $data->id_token, $data->access_token ) ) {
			$state_decoded = $this->get_state( true );
			if ( ! empty( $state_decoded->interim ) ) {
				include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
			} else {
				if ( ! empty( $state_decoded->redirect_to ) && wp_login_url() !== $state_decoded->redirect_to ) {
					$redirect_url = $state_decoded->redirect_to;
				} else {
					$redirect_url = $this->a0_options->get( 'default_login_redirection' );
				}
				wp_safe_redirect( $redirect_url );
			}
			exit();
		}
	}

	/**
	 * Secondary login flow, Implicit Grant.
	 * Application type must be set to "Single Page App" to use this flow.
	 *
	 * @throws WP_Auth0_LoginFlowValidationException - OAuth login flow errors.
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 *
	 * @link https://auth0.com/docs/api-auth/tutorials/implicit-grant
	 *
	 * @see /wp-content/plugins/auth0/assets/js/implicit-login.js
	 *
	 * TODO: Add a WP nonce!
	 */
	public function implicit_login() {
		if ( empty( $_POST['token'] ) ) {
			throw new WP_Auth0_LoginFlowValidationException( __( 'No ID token found', 'wp-auth0' ) );
		}

		// Posted from the login page to the callback URL.
		$token = sanitize_text_field( wp_unslash( $_POST['token'] ) );

		try {
			$decoded_token = JWT::decode(
				$token,
				$this->a0_options->get_client_secret_as_key(),
				array( $this->a0_options->get_client_signing_algorithm() )
			);
		} catch ( UnexpectedValueException $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
			throw new WP_Auth0_LoginFlowValidationException();
		}

		// Validate that this JWT was made for us.
		if ( $this->a0_options->get( 'client_id' ) !== $decoded_token->aud ) {
			throw new WP_Auth0_LoginFlowValidationException(
				__( 'This token is not intended for us', 'wp-auth0' )
			);
		}

		// Validate the nonce if one was included in the request if using auto-login.
		$nonce = isset( $decoded_token->nonce ) ? $decoded_token->nonce : null;
		if ( ! WP_Auth0_Nonce_Handler::getInstance()->validate( $nonce ) ) {
			throw new WP_Auth0_LoginFlowValidationException(
				__( 'Invalid nonce', 'wp-auth0' )
			);
		}

		// Populate legacy userinfo property.
		$decoded_token->user_id = $decoded_token->sub;

		if ( $this->login_user( $decoded_token, $token, null ) ) {

			// Validated above in $this->init_auth0().
			$state_decoded = $this->get_state( true );

			if ( ! empty( $state_decoded->interim ) ) {
				include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
			} else {
				$redirect_to = ! empty( $state_decoded->redirect_to ) ? $state_decoded->redirect_to : null;
				if ( ! $redirect_to || wp_login_url() === $redirect_to ) {
					$redirect_to = $this->a0_options->get( 'default_login_redirection' );
				}
				wp_safe_redirect( $redirect_to );
			}
			exit;
		}
	}

	/**
	 * Attempts to log the user in and create a new user, if possible/needed.
	 *
	 * @param object $userinfo - Auth0 profile of the user.
	 * @param string $id_token - user's ID token returned from Auth0.
	 * @param string $access_token - user's access token returned from Auth0; not provided when using implicit_login().
	 *
	 * @return bool
	 *
	 * @throws WP_Auth0_LoginFlowValidationException - OAuth login flow errors.
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 */
	public function login_user( $userinfo, $id_token, $access_token ) {
		// Check that the user has a verified email, if required.
		if ( ! $this->ignore_unverified_email && $this->a0_options->get( 'requires_verified_email' ) ) {
			if ( empty( $userinfo->email ) ) {
				throw new WP_Auth0_LoginFlowValidationException(
					__( 'This account does not have an email associated, as required by your site administrator.', 'wp-auth0' )
				);
			}
			if ( ! $userinfo->email_verified ) {
				WP_Auth0_Email_Verification::render_die( $userinfo );
			}
		}

		// See if there is a user linked to the same Auth0 user_id.
		if ( isset( $userinfo->identities ) ) {
			foreach ( $userinfo->identities as $identity ) {
				$user = $this->users_repo->find_auth0_user( "{$identity->provider}|{$identity->user_id}" );
				if ( $user ) {
					break;
				}
			}
		} else {
			$user = $this->users_repo->find_auth0_user( $userinfo->sub );
		}

		$user = apply_filters( 'auth0_get_wp_user', $user, $userinfo );

		if ( ! is_null( $user ) ) {
			// User exists so log them in.
			if ( isset( $userinfo->email ) && $user->data->user_email !== $userinfo->email ) {
				$description = $user->data->description;
				if ( empty( $description ) ) {
					if ( isset( $userinfo->headline ) ) {
						$description = $userinfo->headline;
					}
					if ( isset( $userinfo->description ) ) {
						$description = $userinfo->description;
					}
					if ( isset( $userinfo->bio ) ) {
						$description = $userinfo->bio;
					}
					if ( isset( $userinfo->about ) ) {
						$description = $userinfo->about;
					}
				}

				wp_update_user( array(
					'ID'          => $user->data->ID,
					'user_email'  => $userinfo->email,
					'description' => $description,
				) );
			}

			$this->users_repo->update_auth0_object( $user->data->ID, $userinfo );
			$user = apply_filters( 'auth0_get_wp_user', $user, $userinfo );
			$this->do_login( $user, $userinfo, false, $id_token, $access_token );
			return true;
		} else {

			try {

				$creator = new WP_Auth0_UsersRepo( $this->a0_options );
				$user_id = $creator->create(
					$userinfo,
					$id_token,
					$access_token,
					$this->admin_role,
					$this->ignore_unverified_email
				);
				$user    = get_user_by( 'id', $user_id );
				$this->do_login( $user, $userinfo, true, $id_token, $access_token );
			} catch ( WP_Auth0_CouldNotCreateUserException $e ) {

				throw new WP_Auth0_LoginFlowValidationException( $e->getMessage() );
			} catch ( WP_Auth0_RegistrationNotEnabledException $e ) {

				$msg = __(
					'Could not create user. The registration process is not available. Please contact your site’s administrator.',
					'wp-auth0'
				);
				throw new WP_Auth0_LoginFlowValidationException( $msg );
			} catch ( WP_Auth0_EmailNotVerifiedException $e ) {

				WP_Auth0_Email_Verification::render_die( $e->userinfo );
			}
			return true;
		}
	}

	/**
	 * Does all actions required to log the user in to WordPress, invoking hooks as necessary
	 *
	 * @param object $user - the WP user object, such as returned by get_user_by().
	 * @param object $userinfo - the Auth0 profile of the user.
	 * @param bool   $is_new - `true` if the user was created in the WordPress database, `false` if not.
	 * @param string $id_token - user's ID token returned from Auth0.
	 * @param string $access_token - user's access token returned from Auth0; not provided when using implicit_login().
	 *
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 */
	private function do_login( $user, $userinfo, $is_new, $id_token, $access_token ) {
		$remember_users_session = $this->a0_options->get( 'remember_users_session' );

		try {
			do_action( 'auth0_before_login', $user );
		} catch ( Exception $e ) {
			throw new WP_Auth0_BeforeLoginException( $e->getMessage() );
		}

		$secure_cookie = is_ssl();

		// See wp_signon() for documentation on this filter.
		$secure_cookie = apply_filters(
			'secure_signon_cookie', $secure_cookie, array(
				'user_login'    => $user->user_login,
				'user_password' => null,
				'remember'      => $remember_users_session,
			)
		);

		wp_set_auth_cookie( $user->ID, $remember_users_session, $secure_cookie );
		do_action( 'wp_login', $user->user_login, $user );
		do_action( 'auth0_user_login', $user->ID, $userinfo, $is_new, $id_token, $access_token );
	}

	/**
	 * Complete the logout process based on settings.
	 * Hooked to `wp_logout` action.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @see WP_Auth0_LoginManager::init()
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_logout
	 */
	public function logout() {
		$is_sso = (bool) $this->a0_options->get( 'sso' );
		$is_slo = (bool) $this->a0_options->get( 'singlelogout' );
		$is_auto_login = (bool) $this->a0_options->get( 'auto_login' );

		// Redirected here after checkSession in the footer (templates/auth0-singlelogout-handler.php).
		if ( $is_slo && isset( $_REQUEST['SLO'] ) ) {
			if ( ! empty( $_REQUEST['redirect_to'] ) && filter_var( $_REQUEST['redirect_to'], FILTER_VALIDATE_URL ) ) {
				$redirect_url = $_REQUEST['redirect_to'];
			} else {
				$redirect_url = home_url();
			}
			wp_redirect( $redirect_url );
			exit;
		}

		// If SSO is in use, redirect to Auth0 to logout there as well.
		if ( $is_sso ) {
			$telemetry_headers = WP_Auth0_Api_Client::get_info_headers();
			$redirect_url = sprintf(
				'https://%s/v2/logout?returnTo=%s&client_id=%s&auth0Client=%s',
				$this->a0_options->get( 'domain' ),
				rawurlencode( home_url() ),
				$this->a0_options->get( 'client_id' ),
				$telemetry_headers[ 'Auth0-Client' ]
			);
			wp_redirect( $redirect_url );
			exit;
		}

		// If auto-login is in use, cannot redirect back to login page
		if ( $is_auto_login ) {
			wp_redirect( home_url() );
			exit;
		}
	}

	/**
	 *
	 * Outputs JS on wp-login.php to log a user in if an Auth0 session is found.
	 * Hooked to `login_message` filter.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param string $previous_html - HTML passed into the login_message filter.
	 *
	 * @see WP_Auth0_LoginManager::init()
	 *
	 * @return mixed
	 */
	public function auth0_sso_footer( $previous_html ) {

		// No need to checkSession if already logged in.
		// URL parameter `no_sso` is set to skip checkSession.
		if ( is_user_logged_in() || ! empty( $_GET[ 'no_sso' ] ) || ! $this->a0_options->get( 'sso' ) ) {
			return $previous_html;
		}

		wp_enqueue_script( 'wpa0_auth0js', $this->a0_options->get( 'auth0js-cdn' ) );
		ob_start();
		include WPA0_PLUGIN_DIR . 'templates/auth0-sso-handler-lock10.php';
		return $previous_html . ob_get_clean();
	}

	/**
	 * Outputs JS on all pages to log a user out if no Auth0 session is found.
	 * Hooked to `wp_footer` action.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @see WP_Auth0_LoginManager::init()
	 */
	public function auth0_singlelogout_footer() {
		if ( is_user_logged_in() && $this->a0_options->get( 'singlelogout' ) ) {
			include WPA0_PLUGIN_DIR . 'templates/auth0-singlelogout-handler.php';
		}
	}

	/**
	 * End the PHP session.
	 */
	public function end_session() {
		if ( session_id() ) {
			session_destroy();
		}
	}

	/**
	 * Get and filter the scope used for access and ID tokens
	 *
	 * @param string $context - how are the scopes being used?
	 *
	 * @return string
	 */
	public static function get_userinfo_scope( $context = '' ) {
		$default_scope = array( 'openid', 'email', 'name', 'nickname', 'picture' );
		$filtered_scope = apply_filters( 'auth0_auth_token_scope', $default_scope, $context );
		return implode( ' ', $filtered_scope );
	}

	/**
	 * Get authorize URL parameters for handling Universal Login Page redirects.
	 *
	 * @param null|string $connection - a specific connection to use; pass null to use all enabled connections.
	 * @param null|string $redirect_to - URL to redirect upon successful authentication.
	 *
	 * @return array
	 */
	public static function get_authorize_params( $connection = null, $redirect_to = null ) {
		$params = array();
		$options = WP_Auth0_Options::Instance();
		$lock_options = new WP_Auth0_Lock10_Options();
		$is_implicit = (bool) $options->get( 'auth0_implicit_workflow', FALSE );
		$nonce = WP_Auth0_Nonce_Handler::getInstance()->get();

		$params[ 'client_id' ] = $options->get( 'client_id' );
		$params[ 'scope' ] = self::get_userinfo_scope( 'authorize_url' );
		$params[ 'response_type' ] = $is_implicit ? 'id_token': 'code';
		$params[ 'redirect_uri' ] = $is_implicit
			? $lock_options->get_implicit_callback_url()
			: $options->get_wp_auth0_url( null );

		if ( $is_implicit ) {
			$params[ 'nonce' ] = $nonce;
		}

		if ( ! empty( $connection ) ) {
			$params[ 'connection' ] = $connection;
		}

		// Get the telemetry header.
		$telemetry = WP_Auth0_Api_Client::get_info_headers();
		$params[ 'auth0Client' ] = $telemetry[ 'Auth0-Client' ];

		// Where should the user be redirected after logging in?
		if ( empty( $redirect_to ) && ! empty( $_GET['redirect_to'] ) ) {
			$redirect_to = $_GET['redirect_to'];
		} elseif ( empty( $redirect_to ) ) {
			$redirect_to = $options->get( 'default_login_redirection' );
		}

		// State parameter, checked during login callback.
		$params[ 'state' ] = base64_encode( json_encode( array(
			'interim' => false,
			'nonce' => $nonce,
			'redirect_to' => filter_var( $redirect_to, FILTER_SANITIZE_URL ),
		) ) );

		return $params;
	}

	/**
	 * Get a value from query_vars or $_REQUEST global.
	 *
	 * @param string $key - query var key to return.
	 *
	 * @return string|null
	 */
	protected function query_vars( $key ) {
		global $wp_query;
		if ( isset( $wp_query->query_vars[ $key ] ) ) {
			return $wp_query->query_vars[ $key ];
		}
		if ( isset( $_REQUEST[ $key ] ) ) {
			return $_REQUEST[ $key ];
		}
		return null;
	}

	/**
	 * Get the state value returned from Auth0 during login processing.
	 *
	 * @param bool $decoded - pass `true` to return decoded state, leave blank for raw string.
	 *
	 * @return string|object|null
	 */
	protected function get_state( $decoded = false ) {

		if ( empty( $this->state ) ) {
			// Get and store base64 encoded state.
			$state_val   = isset( $_REQUEST['state'] ) ? $_REQUEST['state'] : '';
			$state_val   = urldecode( $state_val );
			$this->state = $state_val;

			// Decode and store the state.
			$state_val           = base64_decode( $state_val );
			$this->state_decoded = json_decode( $state_val );
		}

		if ( $decoded ) {
			return is_object( $this->state_decoded ) ? $this->state_decoded : null;
		} else {
			return $this->state;
		}
	}

	/**
	 * Check the state send back from Auth0 with the one stored in the user's browser.
	 *
	 * @return bool
	 */
	protected function validate_state() {
		$valid = isset( $_COOKIE[ WPA0_STATE_COOKIE_NAME ] )
			? $_COOKIE[ WPA0_STATE_COOKIE_NAME ] === $this->get_state()
			: false;
		setcookie( WPA0_STATE_COOKIE_NAME, '', 0, '/' );
		return $valid;
	}

	/**
	 * Die during login process with a message
	 *
	 * @param string     $msg - translated error message to display.
	 * @param string|int $code - error code, if given.
	 * @param bool       $login_link - TRUE for login link, FALSE for logout link.
	 */
	protected function die_on_login( $msg = '', $code = 0, $login_link = true ) {
		wp_die(
			sprintf(
				'%s: %s [%s: %s]<br><br><a href="%s">%s</a>',
				$login_link
					? __( 'There was a problem with your log in', 'wp-auth0' )
					: __( 'You have logged in successfully, but there is a problem accessing this site', 'wp-auth0' ),
				! empty( $msg )
					? sanitize_text_field( $msg )
					: __( 'Please see the site administrator', 'wp-auth0' ),
				__( 'error code', 'wp-auth0' ),
				$code ? sanitize_text_field( $code ) : __( 'unknown', 'wp-auth0' ),
				$login_link ? add_query_arg( 'no_sso', 1, wp_login_url() ) : wp_logout_url(),
				$login_link
					? __( '← Login', 'wp-auth0' )
					: __( '← Logout', 'wp-auth0' )
			)
		);
	}

	/**
	 * Login using oauth/ro endpoint
	 *
	 * @deprecated 3.6.0 - Use Password Grant instead.
	 *
	 * @param string $username - Username from the login form.
	 * @param string $password - Password from the login form.
	 * @param string $connection - Database connection to use.
	 *
	 * @return bool
	 *
	 * @throws WP_Auth0_LoginFlowValidationException - OAuth login flow errors.
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 *
	 * @link https://auth0.com/docs/api-auth/intro#other-authentication-api-endpoints
	 */
	public function login_with_credentials( $username, $password, $connection = 'Username-Password-Authentication' ) {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$domain    = $this->a0_options->get( 'domain' );
		$client_id = $this->a0_options->get( 'client_id' );
		$secret    = $this->a0_options->get_client_secret_as_key();
		$response  = WP_Auth0_Api_Client::ro(
			$domain, $client_id, $username, $password, $connection,
			'openid name email nickname email_verified identities'
		);
		try {
			// Decode the returned ID token.
			$decoded_token = JWT::decode(
				$response->id_token,
				$secret,
				array( $this->a0_options->get_client_signing_algorithm() )
			);
			// Validate that this JWT was made for us.
			if ( $this->a0_options->get( 'client_id' ) !== $decoded_token->aud ) {
				throw new WP_Auth0_LoginFlowValidationException( 'This token is not intended for us.' );
			}
			$decoded_token->user_id = $decoded_token->sub;
			if ( $this->login_user( $decoded_token, $response->id_token, $response->access_token ) ) {
				return false;
			}
		} catch ( UnexpectedValueException $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
		}
		return false;
	}

	/**
	 * Deprecated to improve the functionality and move to a new class
	 *
	 * @deprecated 3.5.0
	 *
	 * @param object $userinfo - Auth0 profile.
	 * @param string $id_token - ID token.
	 *
	 * @see WP_Auth0_Email_Verification::render_die()
	 */
	// phpcs:ignore
	private function dieWithVerifyEmail( $userinfo, $id_token = '' ) {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		WP_Auth0_Email_Verification::render_die( $userinfo );
	}
}
