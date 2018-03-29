<?php

class WP_Auth0_LoginManager {

  protected $a0_options;
  protected $default_role;
  protected $ignore_unverified_email;
  protected $users_repo;
  protected $state;
  protected $state_decoded;

  public function __construct( WP_Auth0_UsersRepo $users_repo, $a0_options = null, $default_role = null, $ignore_unverified_email = false ) {

    $this->default_role = $default_role;
    $this->ignore_unverified_email = $ignore_unverified_email;
    $this->users_repo = $users_repo;

    if ( $a0_options instanceof WP_Auth0_Options ) {
      $this->a0_options = $a0_options;
    } else {
      $this->a0_options = WP_Auth0_Options::Instance();
    }

  }

  public function init() {
    add_action( 'wp_logout', array( $this, 'logout' ) );
    add_action( 'wp_login', array( $this, 'end_session' ) );
    add_action( 'login_init', array( $this, 'login_auto' ) );
    add_action( 'template_redirect', array( $this, 'init_auth0' ), 1 );
    //add_action( 'wp_footer', array( $this, 'auth0_sso_footer' ) );
    add_action( 'wp_footer', array( $this, 'auth0_singlelogout_footer' ) );
    add_filter( 'login_message', array( $this, 'auth0_sso_footer' ) );
  }

  public function auth0_sso_footer( $previous_html ) {

    echo $previous_html;

    if ( is_user_logged_in() ) {
      return;
    }

    $lock_options = new WP_Auth0_Lock10_Options();

    $sso = $lock_options->get_sso();

    if ( $sso ) {
      $client_id = $lock_options->get_client_id();
      $domain = $lock_options->get_domain();
      $cdn = $this->a0_options->get('auth0js-cdn');

      wp_enqueue_script( 'wpa0_auth0js', $cdn );
      include WPA0_PLUGIN_DIR . 'templates/auth0-sso-handler-lock10.php';
    }
  }
  public function auth0_singlelogout_footer( $previous_html ) {

    echo $previous_html;

    if ( !is_user_logged_in() ) {
      return;
    }

    $singlelogout = $this->a0_options->get( 'singlelogout' );

    if ( ! $singlelogout ) {
      return;
    }

    include WPA0_PLUGIN_DIR . 'templates/auth0-singlelogout-handler.php';
  }

  public function logout() {
    $this->end_session();

    $sso = $this->a0_options->get( 'sso' );
    $slo = $this->a0_options->get( 'singlelogout' );
    $client_id = $this->a0_options->get( 'client_id' );
    $auto_login = absint( $this->a0_options->get( 'auto_login' ) );

    if ( $slo && isset( $_REQUEST['SLO'] ) ) {
      wp_redirect( $_REQUEST['redirect_to'] );
      die();
    }

    if ( $sso ) {
      wp_redirect( 'https://' . $this->a0_options->get( 'domain' ) . '/v2/logout?federated&returnTo=' . urlencode( home_url() ) . '&client_id='.$client_id.'&auth0Client=' . base64_encode( json_encode( WP_Auth0_Api_Client::get_info_headers() ) ) );
      die();
    }

    if ( $auto_login ) {
      wp_redirect( home_url() );
      die();
    }
  }

  public function end_session() {
    if ( session_id() ) {
      session_destroy();
    }
  }

  public function login_auto() {
    $auto_login = absint( $this->a0_options->get( 'auto_login' ) );

    if ( $auto_login && ( ! isset( $_GET['action'] ) || 'logout' !== $_GET['action'] ) && ! isset( $_GET['wle'] ) ) {

      if ( strtolower( $_SERVER['REQUEST_METHOD'] ) !== 'get' ) {
        return;
      }

      if ( $this->query_vars( 'auth0' ) !== null ) {
        return;
      }

      $lock_options = new WP_Auth0_Lock10_Options();
      $options = $lock_options->get_lock_options();

      $connection = apply_filters( 'auth0_get_auto_login_connection', $this->a0_options->get( 'auto_login_method' ) );

      $response_type = $lock_options->get_auth0_implicit_workflow() ? 'id_token' : 'code';

      $state = $lock_options->get_state_obj();

      // Create the link to log in.
      $login_url = "https://". $this->a0_options->get( 'domain' ) .
        "/authorize?".
        "scope=".urlencode($options["auth"]["params"]["scope"]) .
        "&response_type=" . $response_type .
        "&client_id=".$this->a0_options->get( 'client_id' ) .
        "&redirect_uri=" . $options["auth"]["redirectUrl"] .
        "&state=" . urlencode( $state ) .
        "&nonce=" . WP_Auth0_Nonce_Handler::getInstance()->get() .
        "&connection=". trim($connection) .
        "&auth0Client=" . WP_Auth0_Api_Client::get_info_headers();

      setcookie( WPA0_STATE_COOKIE_NAME, $state, time() + WP_Auth0_Nonce_Handler::COOKIE_EXPIRES, '/' );
      wp_redirect( $login_url );
      die();
    }
  }

  public function init_auth0() {

    // Not an Auth0 login process or settings are not configured to allow logins
    if ( ! $this->query_vars( 'auth0' ) || ! WP_Auth0::ready() ) {
      return;
    }

    // Catch any incoming errors and stop the login process
    // See https://auth0.com/docs/libraries/error-messages
    if ( $this->query_vars( 'error' ) || $this->query_vars( 'error_description' ) ) {
      $error_msg = sanitize_text_field( $this->query_vars( 'error_description' ) );
      $error_code = sanitize_text_field( $this->query_vars( 'error' ) );
      $this->die_on_login( $error_msg, $error_code );
    }

    // Check for valid state nonce, set in WP_Auth0_Lock10_Options::get_state_obj()
    // See https://auth0.com/docs/protocols/oauth2/oauth-state
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

      // Errors during the OAuth login flow
      $this->die_on_login( $e->getMessage(), $e->getCode() );

    } catch ( WP_Auth0_BeforeLoginException $e ) {

      // Errors during the WordPress login flow
      $this->die_on_login( $e->getMessage(), $e->getCode(), FALSE );
    }
  }

  /**
   * Main login flow, Authorization Code Grant
   *
   * @throws WP_Auth0_BeforeLoginException
   * @throws WP_Auth0_LoginFlowValidationException
   *
   * @see https://auth0.com/docs/api-auth/tutorials/authorization-code-grant
   */
  public function redirect_login() {

    $domain = $this->a0_options->get( 'domain' );
    $client_id = $this->a0_options->get( 'client_id' );
    $client_secret = $this->a0_options->get( 'client_secret' );

    // Exchange authorization code for token
    $exchange_resp = WP_Auth0_Api_Client::get_token( $domain, $client_id, $client_secret, 'authorization_code', array(
        'redirect_uri' => home_url(),
        'code' => $this->query_vars( 'code' ),
      ) );

    $exchange_resp_code = (int) wp_remote_retrieve_response_code( $exchange_resp );
    $exchange_resp_body = wp_remote_retrieve_body( $exchange_resp );

    if ( 401 === $exchange_resp_code ) {

      // Not authorized
      WP_Auth0_ErrorManager::insert_auth0_error(
        __METHOD__ . ' L:' . __LINE__,
        __( 'An /oauth/token call triggered a 401 response from Auth0. ', 'wp-auth0' ) .
          __( 'Please check the Client Secret saved in the Auth0 plugin settings. ', 'wp-auth0' )
      );
      throw new WP_Auth0_LoginFlowValidationException( __( 'Not Authorized', 'wp-auth0' ), 401 );

    } else if ( empty( $exchange_resp_body ) ) {

      // Unsuccessful for another reason
      if ( $exchange_resp instanceof WP_Error ) {
        WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' L:' . __LINE__, $exchange_resp );
      }

      throw new WP_Auth0_LoginFlowValidationException( __( 'Unknown error', 'wp-auth0' ), $exchange_resp_code );
    }

    $data = json_decode( $exchange_resp_body );

    if ( empty( $data->access_token ) ) {

      // Look for clues as to what went wrong
      $e_message = ! empty( $data->error_description ) ? $data->error_description : __( 'Unknown error', 'wp-auth0' );
      $e_code = ! empty( $data->error ) ? $data->error : $exchange_resp_code;
      throw new WP_Auth0_LoginFlowValidationException( $e_message, $e_code );
    }

    // Decode our incoming ID token for the Auth0 user_id
    $decoded_token = JWT::decode(
      $data->id_token,
      $this->a0_options->get_client_secret_as_key(),
      array( $this->a0_options->get_client_signing_algorithm() )
    );

    // Attempt to authenticate with the Management API
    $client_credentials_token = WP_Auth0_Api_Client::get_client_token();
    $userinfo_resp_code = $userinfo_resp_body = null;

    if ( $client_credentials_token ) {
      $userinfo_resp = WP_Auth0_Api_Client::get_user( $domain, $client_credentials_token, $decoded_token->sub );
      $userinfo_resp_code = (int) wp_remote_retrieve_response_code( $userinfo_resp );
      $userinfo_resp_body = wp_remote_retrieve_body( $userinfo_resp );
    }

    // Management API call failed, fallback to userinfo
    if ( 200 !== $userinfo_resp_code || empty( $userinfo_resp_body ) ) {

      $userinfo_resp = WP_Auth0_Api_Client::get_user_info( $domain, $data->access_token );
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
      $state_decoded = $this->get_state( TRUE );
      if ( ! empty( $state_decoded->interim ) ) {
        include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
      } else {
        if ( ! empty( $state_decoded->redirect_to ) && wp_login_url() !== $state_decoded->redirect_to ) {
          $redirectURL = $state_decoded->redirect_to;
        } else {
          $redirectURL = $this->a0_options->get( 'default_login_redirection' );
        }
        wp_safe_redirect( $redirectURL );
      }
      exit();
    }
  }

  /**
   * Secondary login flow, Implicit Grant
   * Client should be of type "Single Page App" for this flow
   *
   * @throws WP_Auth0_BeforeLoginException
   * @throws WP_Auth0_LoginFlowValidationException
   *
   * @see https://auth0.com/docs/api-auth/tutorials/implicit-grant
   * @see /wp-content/plugins/auth0/assets/js/implicit-login.js
   */
  public function implicit_login() {

    // Posted from the login page here
    $token = $_POST['token'];

    try {
      $decodedToken = JWT::decode(
        $token,
        $this->a0_options->get_client_secret_as_key(),
        array(  $this->a0_options->get_client_signing_algorithm() )
      );
    } catch( UnexpectedValueException $e ) {
      WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );
      error_log( $e->getMessage() );
      throw new WP_Auth0_LoginFlowValidationException();
    }

    // Validate that this JWT was made for us
    if ( $this->a0_options->get( 'client_id' ) !== $decodedToken->aud ) {
      throw new WP_Auth0_LoginFlowValidationException(
        __( 'This token is not intended for us', 'wp-auth0' )
      );
    }

    // Validate the nonce if one was included in the request (auto-login)
    if ( $this->a0_options->get( 'auto_login' ) ) {
      $nonce = isset( $decodedToken->nonce ) ? $decodedToken->nonce : null;
      if ( ! WP_Auth0_Nonce_Handler::getInstance()->validate( $nonce ) ) {
        throw new WP_Auth0_LoginFlowValidationException(
          __( 'Invalid nonce', 'wp-auth0' )
        );
      }
    }

    // Legacy userinfo property
    $decodedToken->user_id = $decodedToken->sub;

    if ( $this->login_user( $decodedToken, $token, null ) ) {

      // Validated in $this->init_auth0()
      $state_decoded = $this->get_state( TRUE );

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
   * Does all actions required to log the user in to wordpress, invoking hooks as necessary
   *
   * @param object $user - the WP user object, such as returned by get_user_by()
   * @param object $userinfo - the Auth0 profile of the user
   * @param bool $is_new - `true` if the user was created in the WordPress database, `false` if not
   * @param string $id_token - user's ID token returned from Auth0
   * @param string $access_token - user's access token returned from Auth0; not provided when using implicit_login()
   *
   * @throws WP_Auth0_BeforeLoginException
   */
  private function do_login( $user, $userinfo, $is_new, $id_token, $access_token ) {
    $remember_users_session = $this->a0_options->get( 'remember_users_session' );

    // allow other hooks to run prior to login
    // if something goes wrong with the login, they should throw an exception.
    try {
      do_action( 'auth0_before_login', $user );
    }
    catch ( Exception $e ) {
      throw new WP_Auth0_BeforeLoginException( $e->getMessage() );
    }

    $secure_cookie = is_ssl();

    // See wp_signon() for documentation on this filter
    $secure_cookie = apply_filters( 'secure_signon_cookie', $secure_cookie, array(
      "user_login" => $user->user_login,
      "user_password" => null,
      "remember" => $remember_users_session
      )
    );

    wp_set_auth_cookie( $user->ID, $remember_users_session, $secure_cookie);
    do_action( 'wp_login', $user->user_login, $user );
    do_action( 'auth0_user_login' , $user->ID, $userinfo, $is_new, $id_token, $access_token );
  }

  /**
   * @param object $userinfo - the Auth0 profile of the user
   * @param string $id_token - user's ID token returned from Auth0
   * @param string $access_token - user's access token returned from Auth0; not provided when using implicit_login()
   *
   * @return bool
   *
   * @throws WP_Auth0_BeforeLoginException
   * @throws WP_Auth0_LoginFlowValidationException
   */
  public function login_user( $userinfo, $id_token, $access_token ) {
    // If the userinfo has no email or an unverified email, and in the options we require a verified email
    // notify the user he cant login until he does so.
    $requires_verified_email = $this->a0_options->get( 'requires_verified_email' );


    if ( ! $this->ignore_unverified_email &&  1 == $requires_verified_email ) {
      if ( empty( $userinfo->email ) ) {
        $msg = __( 'This account does not have an email associated, as required by your site administrator.', 'wp-auth0' );

        throw new WP_Auth0_LoginFlowValidationException( $msg );
      }

      if ( ! $userinfo->email_verified ) {
        WP_Auth0_Email_Verification::render_die( $userinfo );
      }

    }

    // See if there is a user linked to the same auth0 user_id
    if (isset($userinfo->identities)) {
      foreach ($userinfo->identities as $identity) {
        $user = $this->users_repo->find_auth0_user( "{$identity->provider}|{$identity->user_id}" );
        if ($user) {
          break;
        }
      }
    } else {
      $user = $this->users_repo->find_auth0_user( $userinfo->sub );
    }

    $user = apply_filters( 'auth0_get_wp_user', $user, $userinfo );

    if ( ! is_null( $user ) ) {
      // User exists! Log in
      if ( isset( $userinfo->email ) && $user->data->user_email !== $userinfo->email ) {

        $description = $user->data->description;

        if (empty($description)){
          if (isset($userinfo->headline)) {
            $description = $userinfo->headline;
          }
          if (isset($userinfo->description)) {
            $description = $userinfo->description;
          }
          if (isset($userinfo->bio)) {
            $description = $userinfo->bio;
          }
          if (isset($userinfo->about)) {
            $description = $userinfo->about;
          }
        }

        $user_id = wp_update_user( array(
          'ID' => $user->data->ID,
          'user_email' => $userinfo->email,
          'description' => $description,
        ) );
      }

      $this->users_repo->update_auth0_object( $user->data->ID, $userinfo );

      $user = apply_filters( 'auth0_get_wp_user' , $user, $userinfo );

      $this->do_login( $user, $userinfo, false, $id_token, $access_token );

      return true;

    } else {
      try {

        $creator = new WP_Auth0_UsersRepo( $this->a0_options );
        $user_id = $creator->create( $userinfo, $id_token, $access_token, $this->default_role, $this->ignore_unverified_email );

        $user = get_user_by( 'id', $user_id );

        $this->do_login( $user, $userinfo, true, $id_token, $access_token );
      }
      catch ( WP_Auth0_CouldNotCreateUserException $e ) {
        throw new WP_Auth0_LoginFlowValidationException( $e->getMessage() );
      } catch ( WP_Auth0_RegistrationNotEnabledException $e ) {
        $msg = __( 'Could not create user. The registration process is not available. Please contact your site’s administrator.', 'wp-auth0' );

        throw new WP_Auth0_LoginFlowValidationException( $msg );
      } catch ( WP_Auth0_EmailNotVerifiedException $e ) {
        WP_Auth0_Email_Verification::render_die( $e->userinfo );
      }
      return true;
    }
  }

  /**
   * Deprecated to conform to OIDC standards
   *
   * @see https://auth0.com/docs/api-auth/intro#other-authentication-api-endpoints
   *
   * @deprecated 3.6.0
   *
   * @param string $username
   * @param string $password
   * @param string $connection
   *
   * @return bool
   *
   * @throws Exception
   * @throws WP_Auth0_BeforeLoginException
   * @throws WP_Auth0_LoginFlowValidationException
   */
  public function login_with_credentials( $username, $password, $connection="Username-Password-Authentication" ) {
    trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), E_USER_DEPRECATED ) );

    $domain = $this->a0_options->get( 'domain' );
    $client_id = $this->a0_options->get( 'client_id' );

    $response = WP_Auth0_Api_Client::ro( $domain, $client_id, $username, $password, $connection, 'openid name email nickname email_verified identities' );

    $secret = $this->a0_options->get_client_secret_as_key();

    try {
      // Decode the user
      $decodedToken = JWT::decode( $response->id_token, $secret, array(  $this->a0_options->get_client_signing_algorithm() ) );

      // validate that this JWT was made for us
      if ( $this->a0_options->get( 'client_id' ) !== $decodedToken->aud ) {
        throw new Exception( 'This token is not intended for us.' );
      }

      $decodedToken->user_id = $decodedToken->sub;

      if ( $this->login_user( $decodedToken, $response->id_token, $response->access_token ) ) {
        return false;
      }

    } catch( UnexpectedValueException $e ) {

      WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );

      error_log( $e->getMessage() );
    }
    return false;

  }

  protected function query_vars( $key ) {
    global $wp_query;
    if ( isset( $wp_query->query_vars[$key] ) ) return $wp_query->query_vars[$key];
    if ( isset( $_REQUEST[$key] ) ) return $_REQUEST[$key];
    return null;
  }

  /**
   * Get and store state returned from Auth0
   *
   * @param bool $decoded - `true` to return decoded state
   *
   * @return string|object|null
   */
  protected function get_state( $decoded = FALSE ) {

    if ( empty( $this->state ) ) {
      // Get and store base64 encoded state
      $state_val = isset( $_REQUEST[ 'state' ] ) ? $_REQUEST[ 'state' ] : '';
      $state_val = urldecode( $state_val );
      $this->state = $state_val;

      // Decode and store
      $state_val = base64_decode( $state_val );
      $this->state_decoded = json_decode( $state_val );
    }

    if ( $decoded ) {
      return is_object( $this->state_decoded ) ? $this->state_decoded : null;
    } else {
      return $this->state;
    }
  }

  /**
   * Check the state send back from Auth0 with the one stored in the user's browser
   *
   * @return bool
   */
  protected function validate_state() {
    $valid = isset( $_COOKIE[ WPA0_STATE_COOKIE_NAME ] )
      ? $_COOKIE[ WPA0_STATE_COOKIE_NAME ] === $this->get_state()
      : FALSE;
    setcookie( WPA0_STATE_COOKIE_NAME, '', 0, '/' );
    return $valid;
  }

  /**
   * Die during login process with a message
   *
   * @param string $msg - translated error message to display
   * @param string|int $code - error code, if given
   * @param bool $login_link - TRUE for login link, FALSE for logout link
   */
  protected function die_on_login( $msg = '', $code = 0, $login_link = TRUE ) {

    wp_die( sprintf(
      '%s: %s [%s: %s]<br><br><a href="%s">%s</a>',
      $login_link
        ? __( 'There was a problem with your log in', 'wp-auth0' )
        : __( 'You have logged in successfully, but there is a problem accessing this site', 'wp-auth0' ),
      ! empty( $msg )
        ? sanitize_text_field( $msg )
        : __( 'Please see the site administrator', 'wp-auth0' ),
      __( 'error code', 'wp-auth0' ),
      $code ? sanitize_text_field( $code ) : __( 'unknown', 'wp-auth0' ),
      $login_link ? wp_login_url() : wp_logout_url(),
      $login_link
        ? __( '← Login', 'wp-auth0' )
        : __( '← Logout', 'wp-auth0' )
    ) );
  }

	/**
	 * Deprecated to improve the functionality and move to a new class
	 *
	 * @see \WP_Auth0_Email_Verification::render_die()
	 *
	 * @deprecated 3.5.0
	 *
	 * @param $userinfo
	 * @param $id_token
	 */
	private function dieWithVerifyEmail( $userinfo, $id_token = '' ) {
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		WP_Auth0_Email_Verification::render_die( $userinfo );
	}
}