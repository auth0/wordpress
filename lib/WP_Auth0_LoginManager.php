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
	 * @deprecated - 3.8.0
	 *
	 * @var bool|null
	 */
	protected $admin_role;

	/**
	 * Ignore verified email requirement in Settings > Advanced.
	 *
	 * @deprecated - 3.8.0
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
	 * WP_Auth0_LoginManager constructor.
	 *
	 * @param WP_Auth0_UsersRepo    $users_repo - see member variable doc comment.
	 * @param WP_Auth0_Options|null $a0_options - see member variable doc comment.
	 * @param null|bool             $admin_role - @deprecated - 3.8.0.
	 * @param bool                  $ignore_unverified_email - @deprecated - 3.8.0.
	 */
	public function __construct(
		WP_Auth0_UsersRepo $users_repo,
		$a0_options = null,
		$admin_role = null,
		$ignore_unverified_email = false
	) {
		$this->users_repo = $users_repo;

		if ( $a0_options instanceof WP_Auth0_Options ) {
			$this->a0_options = $a0_options;
		} else {
			$this->a0_options = WP_Auth0_Options::Instance();
		}

		$this->admin_role              = $admin_role;
		$this->ignore_unverified_email = $ignore_unverified_email;

		if ( func_num_args() > 2 ) {
			// phpcs:ignore
			@trigger_error(
				sprintf( __( '$admin_role and $ignore_unverified_email are deprecated.', 'wp-auth0' ), __METHOD__ ),
				E_USER_DEPRECATED
			);
		}
	}

	/**
	 * Attach methods to hooks.
	 * See method comments for functionality.
	 *
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @see WP_Auth0::init()
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function init() {
		add_action( 'login_init', array( $this, 'login_auto' ) );
		add_action( 'template_redirect', array( $this, 'init_auth0' ), 1 );
		add_action( 'wp_logout', array( $this, 'logout' ) );
	}

	/**
	 * Redirect logged-in users from wp-login.php.
	 * Redirect to Universal Login Page under certain conditions and if the option is turned on.
	 *
	 * @return bool
	 */
	public function login_auto() {

		// Do not redirect anywhere if this is a logout action.
		if ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] ) {
			return false;
		}

		// Do not redirect login page override.
		if ( $this->a0_options->can_show_wp_login_form() ) {
			return false;
		}

		// Do not redirect non-GET requests.
		if ( strtolower( $_SERVER['REQUEST_METHOD'] ) !== 'get' ) {
			return false;
		}

		// If the user has a WP session, determine where they should end up and redirect.
		if ( is_user_logged_in() ) {
			$login_redirect = empty( $_REQUEST['redirect_to'] ) ?
				$this->a0_options->get( 'default_login_redirection' ) :
				filter_var( $_REQUEST['redirect_to'], FILTER_SANITIZE_URL );

			// Add a cache buster to avoid an infinite redirect loop on pages that check for auth.
			$login_redirect = add_query_arg( time(), '', $login_redirect );
			wp_safe_redirect( $login_redirect );
			exit;
		}

		// Do not use the ULP if the setting is off or if the plugin is not configured.
		if ( ! $this->a0_options->get( 'auto_login', false ) || ! WP_Auth0::ready() ) {
			return false;
		}

		$connection  = apply_filters( 'auth0_get_auto_login_connection', $this->a0_options->get( 'auto_login_method' ) );
		$auth_params = self::get_authorize_params( $connection );

		WP_Auth0_State_Handler::get_instance()->set_cookie( $auth_params['state'] );

		if ( isset( $auth_params['nonce'] ) ) {
			WP_Auth0_Nonce_Handler::get_instance()->set_cookie();
		}

		$auth_url = self::build_authorize_url( $auth_params );
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
		if ( ! empty( $_REQUEST['error'] ) || ! empty( $_REQUEST['error_description'] ) ) {
			$error_msg  = sanitize_text_field( rawurldecode( $_REQUEST['error_description'] ) );
			$error_code = sanitize_text_field( rawurldecode( $_REQUEST['error'] ) );
			$this->die_on_login( $error_msg, $error_code );
		}

		// No need to process a login if the user is already logged in and there is no error.
		if ( is_user_logged_in() ) {
			wp_redirect( $this->a0_options->get( 'default_login_redirection' ) );
			exit;
		}

		// Check for valid state nonce, set in WP_Auth0_Lock10_Options::get_state_obj().
		// See https://auth0.com/docs/protocols/oauth2/oauth-state for more info.
		$state_returned = isset( $_REQUEST['state'] ) ? rawurldecode( $_REQUEST['state'] ) : null;
		if ( ! $state_returned || ! WP_Auth0_State_Handler::get_instance()->validate( $state_returned ) ) {
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
		} catch ( DomainException $e ) {

			// JWT:decode error - Algorithm was not provided.
			$this->die_on_login( __( 'Invalid ID token (no algorithm)', 'wp-auth0' ), $e->getCode(), false );
		} catch ( InvalidArgumentException $e ) {

			// JWT:decode error - Key provided to decode was empty.
			$this->die_on_login( __( 'Invalid ID token (failed signature verification)', 'wp-auth0' ), $e->getCode(), false );
		} catch ( SignatureInvalidException $e ) {

			// JWT:decode error - Provided JWT was invalid because the signature verification failed.
			$this->die_on_login( __( 'Invalid ID token (failed signature verification)', 'wp-auth0' ), $e->getCode(), false );
		} catch ( BeforeValidException $e ) {

			// JWT:decode error - Provided JWT is trying to be used before it's eligible as defined by 'nbf'.
			// JWT:decode error - Provided JWT is trying to be used before it's been created as defined by 'iat'.
			$this->die_on_login( __( 'Invalid ID token (used too early)', 'wp-auth0' ), $e->getCode(), false );
		} catch ( ExpiredException $e ) {

			// JWT:decode error - Provided JWT has since expired, as defined by the 'exp' claim.
			$this->die_on_login( __( 'Expired ID token', 'wp-auth0' ), $e->getCode(), false );
		} catch ( UnexpectedValueException $e ) {

			// JWT:decode error - Provided JWT was invalid.
			$this->die_on_login( __( 'Invalid ID token', 'wp-auth0' ), $e->getCode(), false );
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
		$auth_domain        = $this->a0_options->get_auth_domain();
		$client_id          = $this->a0_options->get( 'client_id' );
		$client_secret      = $this->a0_options->get( 'client_secret' );
		$userinfo_resp_code = null;
		$userinfo_resp_body = null;

		// Exchange authorization code for an access token.
		$exchange_resp = WP_Auth0_Api_Client::get_token(
			$auth_domain,
			$client_id,
			$client_secret,
			'authorization_code',
			array(
				'redirect_uri' => $this->a0_options->get_wp_auth0_url(),
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
			throw new WP_Auth0_LoginFlowValidationException( $e_message, $exchange_resp_code );
		}

		$access_token  = $data->access_token;
		$id_token      = $data->id_token;
		$refresh_token = isset( $data->refresh_token ) ? $data->refresh_token : null;

		// Decode the incoming ID token for the Auth0 user.
		$decoded_token = JWT::decode(
			$id_token,
			$this->a0_options->get_client_secret_as_key(),
			array( $this->a0_options->get_client_signing_algorithm() )
		);

		// Attempt to authenticate with the Management API.
		$client_credentials_api   = new WP_Auth0_Api_Client_Credentials( $this->a0_options );
		$client_credentials_token = $client_credentials_api->call();

		if ( $client_credentials_token ) {
			$userinfo_resp      = WP_Auth0_Api_Client::get_user( $domain, $client_credentials_token, $decoded_token->sub );
			$userinfo_resp_code = (int) wp_remote_retrieve_response_code( $userinfo_resp );
			$userinfo_resp_body = wp_remote_retrieve_body( $userinfo_resp );
		}

		// Management API call failed, fallback to ID token.
		if ( 200 === $userinfo_resp_code && ! empty( $userinfo_resp_body ) ) {
			$userinfo = json_decode( $userinfo_resp_body );
		} else {
			$userinfo = $this->clean_id_token( $decoded_token );
		}

		// Populate sub property, if not provided.
		if ( ! isset( $userinfo->sub ) ) {
			$userinfo->sub = $userinfo->user_id;
		}

		if ( $this->login_user( $userinfo, $id_token, $access_token, $refresh_token ) ) {
			$state_decoded = $this->get_state();
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
	 */
	public function implicit_login() {
		if ( empty( $_POST['id_token'] ) && empty( $_POST['token'] ) ) {
			throw new WP_Auth0_LoginFlowValidationException( __( 'No ID token found', 'wp-auth0' ) );
		}

		// Posted from the login page to the callback URL.
		$id_token_param = ! empty( $_POST['id_token'] ) ? $_POST['id_token'] : $_POST['token'];
		$id_token       = sanitize_text_field( wp_unslash( $id_token_param ) );

		$decoded_token = JWT::decode(
			$id_token,
			$this->a0_options->get_client_secret_as_key(),
			array( $this->a0_options->get_client_signing_algorithm() )
		);

		// Validate that this JWT was made for us.
		if ( $this->a0_options->get( 'client_id' ) !== $decoded_token->aud ) {
			throw new WP_Auth0_LoginFlowValidationException(
				__( 'This token is not intended for us', 'wp-auth0' )
			);
		}

		// Validate the nonce if one was included in the request if using auto-login.
		$nonce = isset( $decoded_token->nonce ) ? $decoded_token->nonce : null;
		if ( ! WP_Auth0_Nonce_Handler::get_instance()->validate( $nonce ) ) {
			throw new WP_Auth0_LoginFlowValidationException(
				__( 'Invalid nonce', 'wp-auth0' )
			);
		}

		$decoded_token = $this->clean_id_token( $decoded_token );

		if ( $this->login_user( $decoded_token, $id_token ) ) {

			// Validated above in $this->init_auth0().
			$state_decoded = $this->get_state();

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
	 * @param object      $userinfo - Auth0 profile of the user.
	 * @param null|string $id_token - user's ID token if returned from Auth0.
	 * @param null|string $access_token - user's access token if returned from Auth0.
	 * @param null|string $refresh_token - user's refresh token if returned from Auth0.
	 *
	 * @return bool
	 *
	 * @throws WP_Auth0_LoginFlowValidationException - OAuth login flow errors.
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 */
	public function login_user( $userinfo, $id_token = null, $access_token = null, $refresh_token = null ) {
		$auth0_sub        = $userinfo->sub;
		list( $strategy ) = explode( '|', $auth0_sub );

		// Check that the user has a verified email, if required.
		if (
			// Admin settings enforce verified email.
			$this->a0_options->get( 'requires_verified_email' ) &&
			// Email verification is not ignored (set at class initialization).
			! $this->ignore_unverified_email &&
			// Strategy for the user is not skipped.
			! $this->a0_options->strategy_skips_verified_email( $strategy )
		) {

			// Email address is empty so cannot proceed.
			if ( empty( $userinfo->email ) ) {
				throw new WP_Auth0_LoginFlowValidationException(
					__( 'This account does not have an email associated, as required by your site administrator.', 'wp-auth0' )
				);
			}

			// Die with an action to re-send email verification.
			if ( empty( $userinfo->email_verified ) ) {
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
			$user = $this->users_repo->find_auth0_user( $auth0_sub );
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

				wp_update_user(
					(object) array(
						'ID'          => $user->data->ID,
						'user_email'  => $userinfo->email,
						'description' => $description,
					)
				);
			}

			$this->users_repo->update_auth0_object( $user->data->ID, $userinfo );
			$user = apply_filters( 'auth0_get_wp_user', $user, $userinfo );
			$this->do_login( $user, $userinfo, false, $id_token, $access_token, $refresh_token );
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
				$this->do_login( $user, $userinfo, true, $id_token, $access_token, $refresh_token );
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
	 * @param object      $user - the WP user object, such as returned by get_user_by().
	 * @param object      $userinfo - the Auth0 profile of the user.
	 * @param bool        $is_new - `true` if the user was created in the WordPress database, `false` if not.
	 * @param null|string $id_token - user's ID token if returned from Auth0, otherwise null.
	 * @param null|string $access_token - user's access token if returned from Auth0, otherwise null.
	 * @param null|string $refresh_token - user's refresh token if returned from Auth0, otherwise null.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Errors encountered during the auth0_before_login action.
	 */
	private function do_login( $user, $userinfo, $is_new, $id_token, $access_token, $refresh_token ) {
		$remember_users_session = $this->a0_options->get( 'remember_users_session' );

		try {
			do_action( 'auth0_before_login', $user );
		} catch ( Exception $e ) {
			throw new WP_Auth0_BeforeLoginException( $e->getMessage() );
		}

		$secure_cookie = is_ssl();

		// See wp_signon() for documentation on this filter.
		$secure_cookie = apply_filters(
			'secure_signon_cookie',
			$secure_cookie,
			array(
				'user_login'    => $user->user_login,
				'user_password' => null,
				'remember'      => $remember_users_session,
			)
		);

		wp_set_auth_cookie( $user->ID, $remember_users_session, $secure_cookie );
		do_action( 'wp_login', $user->user_login, $user );
		do_action( 'auth0_user_login', $user->ID, $userinfo, $is_new, $id_token, $access_token, $refresh_token );
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
		$is_sso        = (bool) $this->a0_options->get( 'sso' );
		$is_slo        = (bool) $this->a0_options->get( 'singlelogout' );
		$is_auto_login = (bool) $this->a0_options->get( 'auto_login' );

		// If SSO/SLO is in use, redirect to Auth0 to logout there as well.
		if ( $is_sso || $is_slo ) {
			$return_to    = apply_filters( 'auth0_slo_return_to', home_url() );
			$redirect_url = sprintf(
				'https://%s/v2/logout?returnTo=%s&client_id=%s',
				$this->a0_options->get_auth_domain(),
				rawurlencode( $return_to ),
				$this->a0_options->get( 'client_id' )
			);
			$redirect_url = apply_filters( 'auth0_logout_url', $redirect_url );
			wp_redirect( $redirect_url );
			exit;
		}

		// If auto-login is in use, cannot redirect back to login page.
		if ( $is_auto_login ) {
			wp_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Outputs JS on wp-login.php to log a user in if an Auth0 session is found.
	 * Hooked to `login_message` filter.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.10.0, moved to assets/js/lock-init.js
	 *
	 * @param string $previous_html - HTML passed into the login_message filter.
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function auth0_sso_footer( $previous_html ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		// No need to checkSession if already logged in.
		// URL parameter `skip_sso` is set to skip checkSession.
		if ( is_user_logged_in() || isset( $_GET['skip_sso'] ) || ! $this->a0_options->get( 'sso' ) ) {
			return $previous_html;
		}

		wp_enqueue_script( 'wpa0_auth0js', apply_filters( 'auth0_sso_auth0js_url', WPA0_AUTH0_JS_CDN_URL ) );
		ob_start();
		include WPA0_PLUGIN_DIR . 'templates/auth0-sso-handler-lock10.php';
		return $previous_html . ob_get_clean();
	}

	/**
	 * Outputs JS on all pages to log a user out if no Auth0 session is found.
	 * Hooked to `wp_footer` action.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.10.0, removed.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function auth0_singlelogout_footer() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$tpl_path = WPA0_PLUGIN_DIR . 'templates/auth0-singlelogout-handler.php';
		if ( is_user_logged_in() && $this->a0_options->get( 'singlelogout' ) && file_exists( $tpl_path ) ) {
			include $tpl_path;
		}
	}

	/**
	 * Get and filter the scope used for access and ID tokens.
	 *
	 * @param string $context - how the scopes are being used.
	 *
	 * @return string
	 */
	public static function get_userinfo_scope( $context = '' ) {
		$default_scope  = array( 'openid', 'email', 'profile' );
		$filtered_scope = apply_filters( 'auth0_auth_scope', $default_scope, $context );
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
		$params       = array();
		$options      = WP_Auth0_Options::Instance();
		$lock_options = new WP_Auth0_Lock10_Options();
		$is_implicit  = (bool) $options->get( 'auth0_implicit_workflow', false );
		$nonce        = WP_Auth0_Nonce_Handler::get_instance()->get_unique();

		$params['client_id']     = $options->get( 'client_id' );
		$params['scope']         = self::get_userinfo_scope( 'authorize_url' );
		$params['response_type'] = $is_implicit ? 'id_token' : 'code';
		$params['redirect_uri']  = $is_implicit
			? $lock_options->get_implicit_callback_url()
			: $options->get_wp_auth0_url();

		if ( $is_implicit ) {
			$params['nonce']         = $nonce;
			$params['response_mode'] = 'form_post';
		}

		if ( ! empty( $connection ) ) {
			$params['connection'] = $connection;
		}

		// Where should the user be redirected after logging in?
		if ( empty( $redirect_to ) && ! empty( $_GET['redirect_to'] ) ) {
			$redirect_to = $_GET['redirect_to'];
		} elseif ( empty( $redirect_to ) ) {
			$redirect_to = $options->get( 'default_login_redirection' );
		}

		// State parameter, checked during login callback.
		$params['state'] = base64_encode(
			json_encode(
				array(
					'interim'     => false,
					'nonce'       => $nonce,
					'redirect_to' => filter_var( $redirect_to, FILTER_SANITIZE_URL ),
				)
			)
		);

		return apply_filters( 'auth0_authorize_url_params', $params, $connection, $redirect_to );
	}

	/**
	 * Build a link to the tenant's authorize page.
	 *
	 * @param array $params - URL parameters to append.
	 *
	 * @return string
	 */
	public static function build_authorize_url( array $params = array() ) {
		$auth_url = 'https://' . WP_Auth0_Options::Instance()->get_auth_domain() . '/authorize';
		$auth_url = add_query_arg( array_map( 'rawurlencode', $params ), $auth_url );
		return apply_filters( 'auth0_authorize_url', $auth_url, $params );
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
	 * @return string|object|null
	 */
	protected function get_state() {
		$state_val = rawurldecode( $_REQUEST['state'] );
		$state_val = base64_decode( $state_val );
		$state_val = json_decode( $state_val );
		return $state_val;
	}

	/**
	 * Die during login process with a message
	 *
	 * @param string     $msg - translated error message to display.
	 * @param string|int $code - error code, if given.
	 * @param bool       $login_link - TRUE for login link, FALSE for logout link.
	 */
	protected function die_on_login( $msg = '', $code = 0, $login_link = true ) {
		$html = sprintf(
			'%s: %s [%s: %s]<br><br><a href="%s">%s</a>',
			$login_link
				? __( 'There was a problem with your log in', 'wp-auth0' )
				: __( 'You have logged in successfully, but there is a problem accessing this site', 'wp-auth0' ),
			! empty( $msg )
				? sanitize_text_field( $msg )
				: __( 'Please see the site administrator', 'wp-auth0' ),
			__( 'error code', 'wp-auth0' ),
			$code ? sanitize_text_field( $code ) : __( 'unknown', 'wp-auth0' ),
			$login_link ? add_query_arg( 'skip_sso', '', wp_login_url() ) : wp_logout_url(),
			$login_link
				? __( '← Login', 'wp-auth0' )
				: __( '← Logout', 'wp-auth0' )
		);

		$html = apply_filters( 'auth0_die_on_login_output', $html, $msg, $code, $login_link );
		wp_die( $html );
	}

	/**
	 * Remove unnecessary ID token properties.
	 *
	 * @param stdClass $id_token_obj - ID token object to clean.
	 *
	 * @return stdClass
	 *
	 * @codeCoverageIgnore - Private method
	 */
	private function clean_id_token( $id_token_obj ) {
		foreach ( array( 'iss', 'aud', 'iat', 'exp', 'nonce' ) as $attr ) {
			unset( $id_token_obj->$attr );
		}
		if ( ! isset( $id_token_obj->user_id ) && isset( $id_token_obj->sub ) ) {
			$id_token_obj->user_id = $id_token_obj->sub;
		} elseif ( ! isset( $id_token_obj->sub ) && isset( $id_token_obj->user_id ) ) {
			$id_token_obj->sub = $id_token_obj->user_id;
		}
		return $id_token_obj;
	}

	/*
	 *
	 * DEPRECATED
	 *
	 */

	/**
	 * End the PHP session.
	 *
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function end_session() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( session_id() ) {
			session_destroy();
		}
	}

	/**
	 * Login using oauth/ro endpoint
	 *
	 * @deprecated - 3.6.0, not used and no replacement provided.
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
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function login_with_credentials( $username, $password, $connection = 'Username-Password-Authentication' ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$domain    = $this->a0_options->get( 'domain' );
		$client_id = $this->a0_options->get( 'client_id' );
		$secret    = $this->a0_options->get_client_secret_as_key();
		$response  = WP_Auth0_Api_Client::ro(
			$domain,
			$client_id,
			$username,
			$password,
			$connection,
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
	 * @deprecated - 3.5.0, use WP_Auth0_Email_Verification::render_die().
	 *
	 * @param object $userinfo - Auth0 profile.
	 * @param string $id_token - ID token.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	// phpcs:ignore
	private function dieWithVerifyEmail( $userinfo, $id_token = '' ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		WP_Auth0_Email_Verification::render_die( $userinfo );
	}
}
