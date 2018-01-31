<?php

class WP_Auth0_LoginManager {

  protected $a0_options;
  protected $default_role;
  protected $ignore_unverified_email;
  protected $users_repo;

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

    $current_user = get_currentauth0user();
    $user_profile = $current_user->auth0_obj;

    if ( empty( $user_profile ) ) {
      return;
    }

    $lock_options = new WP_Auth0_Lock10_Options();
    $cdn = $this->a0_options->get('auth0js-cdn');
    $client_id = $this->a0_options->get( 'client_id' );
    $domain = $this->a0_options->get( 'domain' );
    $logout_url = wp_logout_url( get_permalink() ) . '&SLO=1';

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

      $stateObj = array( 'interim' => false, 'uuid' => uniqid() );
      if ( isset( $_GET['redirect_to'] ) ) {
        $stateObj['redirect_to'] = $_GET['redirect_to'];
      }
      $state = base64_encode( json_encode( $stateObj ) );

      $connection = apply_filters( 'auth0_get_auto_login_connection', $this->a0_options->get( 'auto_login_method' ) );

      $response_type = $lock_options->get_auth0_implicit_workflow() ? 'id_token' : 'code';

      // Create the link to log in.
      $login_url = "https://". $this->a0_options->get( 'domain' ) .
        "/authorize?".
        "scope=".urlencode($options["auth"]["params"]["scope"]) .
        "&response_type=" . $response_type .
        "&client_id=".$this->a0_options->get( 'client_id' ) .
        "&redirect_uri=" . $options["auth"]["redirectUrl"] .
        "&state=" . urlencode( $state ).
        "&nonce=nonce" . // TODO add full nonce support
        "&connection=". trim($connection) .
        "&auth0Client=" . WP_Auth0_Api_Client::get_info_headers();

      wp_redirect( $login_url );
      die();
    }
  }

  public function init_auth0() {
    global $wp_query;

    if ( $this->query_vars( 'auth0' ) === null ) {
      return;
    }

    try {
      if ( $this->query_vars( 'auth0' ) === 'implicit' ) {
        $this->implicit_login();
      } else {
        $this->redirect_login();
      }
    } catch (WP_Auth0_LoginFlowValidationException $e) {

      $msg = __( 'There was a problem with your log in. ', 'wp-auth0' );
      $msg .= ' '. $e->getMessage();
      $msg .= '<br/><br/>';
      $msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', 'wp-auth0' ) . '</a>';
      wp_die( $msg );

    } catch (WP_Auth0_BeforeLoginException $e) {

      $msg = __( 'You have logged in successfully, but there is a problem accessing this site. ', 'wp-auth0' );
      $msg .= ' '. $e->getMessage();
      $msg .= '<br/><br/>';
      $msg .= '<a href="' . wp_logout_url() . '">' . __( '← Logout', 'wp-auth0' ) . '</a>';
      wp_die( $msg );

    } catch (Exception $e) {

    }

  }

  public function redirect_login() {
    global $wp_query;

    if ( $this->query_vars( 'auth0' ) === null ) {
      return;
    }

    if ( $this->query_vars( 'error_description' ) !== null && $this->query_vars( 'error_description' ) !== '' ) {
      throw new WP_Auth0_LoginFlowValidationException( sanitize_text_field( $this->query_vars( 'error_description' ) ) );
    }

    if ( $this->query_vars( 'error' ) !== null && trim( $this->query_vars( 'error' ) ) !== '' ) {
      throw new WP_Auth0_LoginFlowValidationException( sanitize_text_field( $this->query_vars( 'error' ) ) );
    }

    $code = $this->query_vars( 'code' );
    $state = $this->query_vars( 'state' );

    $stateFromGet = json_decode( base64_decode( $state ) );

    $domain = $this->a0_options->get( 'domain' );

    $client_id = $this->a0_options->get( 'client_id' );
    $client_secret = $this->a0_options->get( 'client_secret' );

    if ( empty( $client_id ) ) {
      throw new WP_Auth0_LoginFlowValidationException( __( 'Error: Your Auth0 Client ID has not been entered in the Auth0 SSO plugin settings.', 'wp-auth0' ) );
    }
    if ( empty( $client_secret ) ) {
      throw new WP_Auth0_LoginFlowValidationException( __( 'Error: Your Auth0 Client Secret has not been entered in the Auth0 SSO plugin settings.', 'wp-auth0' ) );
    }
    if ( empty( $domain ) ) {
      throw new WP_Auth0_LoginFlowValidationException( __( 'Error: No Domain defined in Wordpress Administration!', 'wp-auth0' ) );
    }

    $response = WP_Auth0_Api_Client::get_token( $domain, $client_id, $client_secret, 'authorization_code', array(
        'redirect_uri' => home_url(),
        'code' => $code,
      ) );

    if ( $response instanceof WP_Error ) {
      WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' => WP_Auth0_Api_Client::get_token()', $response );

      error_log( $response->get_error_message() );

      throw new WP_Auth0_LoginFlowValidationException( $response->get_error_message() );
    }

    $data = json_decode( $response['body'] );

    if ( isset( $data->access_token ) || isset( $data->id_token ) ) {

	    $decoded_token = JWT::decode(
		    $data->id_token,
		    $this->a0_options->get_client_secret_as_key(),
		    array( $this->a0_options->get_client_signing_algorithm() )
	    );

	    $data->id_token = null;
	    $response = WP_Auth0_Api_Client::get_user(
		    $this->a0_options->get( 'domain' ),
		    WP_Auth0_Api_Client::get_client_token(),
		    $decoded_token->sub
	    );

      if ( $response instanceof WP_Error ) {
        WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' => WP_Auth0_Api_Client::get_user()', $response );

        error_log( $response->get_error_message() );

        throw new WP_Auth0_LoginFlowValidationException( );
      }

      $userinfo = json_decode( $response['body'] );
      if ( $this->login_user( $userinfo, $data->id_token, $data->access_token ) ) {
        if ( null !== $stateFromGet && isset( $stateFromGet->interim ) && $stateFromGet->interim ) {
          include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
          exit();
        } else {
          if ( null !== $stateFromGet && isset( $stateFromGet->redirect_to ) ) {
            $redirectURL = $stateFromGet->redirect_to;
          } else {
            $redirectURL = $this->a0_options->get( 'default_login_redirection' );
          }

          wp_safe_redirect( $redirectURL );
        }
      }
    } elseif ( is_array( $response['response'] ) &&  401 === (int) $response['response']['code'] ) {

      $error = new WP_Error( '401', 'auth/token response code: 401 Unauthorized' );

      WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' => $this->login_user() = 401', $error );

      $msg = __( 'Error: the Client Secret configured on the Auth0 plugin is wrong. Make sure to copy the right one from the Auth0 dashboard.', 'wp-auth0' );

      throw new WP_Auth0_LoginFlowValidationException( $msg );
    } else {
      $error = '';
      $description = '';

      if ( isset( $data->error ) ) {
        $error = $data->error;
      }
      if ( isset( $data->error_description ) ) {
        $description = $data->error_description;
      }

      if ( ! empty( $error ) || ! empty( $description ) ) {
        $error = new WP_Error( $error, $description );
        WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' => $this->login_user()', $error );
      }
      // Login failed!
      wp_redirect( home_url() . '?message=' . $data->error_description );
    }
    exit();
  }

  public function implicit_login() {

    $token = $_POST['token'];
    $stateFromGet = json_decode( base64_decode( $_POST['state'] ) );

    $secret = $this->a0_options->get_client_secret_as_key();

    try {
      // Decode the user
      $decodedToken = JWT::decode( $token, $secret, array(  $this->a0_options->get_client_signing_algorithm() ) );

      // validate that this JWT was made for us
      if ( $this->a0_options->get( 'client_id' ) !== $decodedToken->aud ) {
        throw new Exception( 'This token is not intended for us.' );
      }

      $decodedToken->user_id = $decodedToken->sub;

      if ( $this->login_user( $decodedToken, $token, null ) ) {
        if ( null !== $stateFromGet && isset( $stateFromGet->interim ) && $stateFromGet->interim ) {
          include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
          exit();
        } else {
          if ( null !== $stateFromGet && isset( $stateFromGet->redirect_to ) ) {
            $redirectURL = $stateFromGet->redirect_to;
          } else {
            $redirectURL = $this->a0_options->get( 'default_login_redirection' );
          }

          wp_safe_redirect( $redirectURL );
          exit;
        }
      }

    } catch( UnexpectedValueException $e ) {
      WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e );

      error_log( $e->getMessage() );

      throw new WP_Auth0_LoginFlowValidationException( );
    }
  }

  // Does all actions required to log the user in to wordpress, invoking hooks as necessary
  // $user (stdClass): the WP user object, such as returned by get_user_by(...)
  // $user_profile (stdClass): the Auth0 profile of the user
  // $is_new (boolean): `true` if the user was created on Wordress, `false` if not.  Don't get confused with Auth0 registrations, this flag will tell you if a new user was created on the WordPress database.
  // $id_token (string): the user's JWT
  // $access_token (string): the user's access token.  It is not provided when using the **Implicit flow**.
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

    /**
     * Filters whether to use a secure sign-on cookie.
     *
     * @since 3.1.0
     *
     * @param bool  $secure_cookie Whether to use a secure sign-on cookie.
     * @param array $credentials {
     *     Array of entered sign-on data.
     *
     *     @type string $user_login    Username.
     *     @type string $user_password Password entered.
     *     @type bool   $remember      Whether to 'remember' the user. Increases the time
     *                                 that the cookie will be kept. Default false.
     * }
     */
    $secure_cookie = apply_filters( 'secure_signon_cookie', $secure_cookie, array(
      "user_login" => $user->user_login,
      "user_password" => null,
      "remember" => $remember_users_session
      )
    );

    //wp_set_current_user( $user->ID, $user->user_login );
    wp_set_auth_cookie( $user->ID, $remember_users_session, $secure_cookie);
    do_action( 'wp_login', $user->user_login, $user );
    do_action( 'auth0_user_login' , $user->ID, $userinfo, $is_new, $id_token, $access_token );
  }

  // return true if login was successful, false otherwise
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
      // catch ( Exception $e ) {
      //  echo $e;exit;
      // }

      return true;
    }
  }

  public function login_with_credentials( $username, $password, $connection="Username-Password-Authentication" ) {

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
	 * DEPRECATED 3.5.0
	 *
	 * @param $userinfo
	 * @param $id_token
	 */
	private function dieWithVerifyEmail( $userinfo, $id_token = '' ) {
		trigger_error( __( 'Method dieWithVerifyEmail is deprecated.', 'wp-auth0' ), E_USER_DEPRECATED);
		WP_Auth0_Email_Verification::render_die( $userinfo );
	}
}