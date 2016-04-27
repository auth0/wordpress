<?php

class WP_Auth0_LoginManager {

	protected $a0_options;
	protected $default_role;
	protected $ignore_unverified_email;

	public function __construct($a0_options = null, $default_role = null, $ignore_unverified_email = false) {

		$this->default_role = $default_role;
		$this->ignore_unverified_email = $ignore_unverified_email;

		if ($a0_options instanceof WP_Auth0_Options) {
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
		add_action( 'wp_footer', array( $this, 'auth0_sso_footer') );
		add_action( 'wp_footer', array( $this, 'auth0_singlelogout_footer') );
		add_filter( 'login_message', array( $this, 'auth0_sso_footer' ) );
	}

	public function auth0_sso_footer($previous_html) {

		echo $previous_html;

		if (is_user_logged_in()) {
			return;
		}

		$lock_options = new WP_Auth0_Lock_Options();

		$sso = $lock_options->get_sso();

		if ( $sso ) {
			$cdn = $lock_options->get_cdn_url();
			$client_id = $lock_options->get_client_id();
			$domain = $lock_options->get_domain();

			wp_enqueue_script( 'wpa0_lock', $cdn, 'jquery' );
			include WPA0_PLUGIN_DIR . 'templates/auth0-sso-handler.php';
		}
	}
	public function auth0_singlelogout_footer($previous_html) {
		
		echo $previous_html;

		if (!is_user_logged_in()) {
			return;
		}

		$singlelogout = $this->a0_options->get('singlelogout');

		if ( ! $singlelogout ) { 
			return;
		}

		$db_manager = new WP_Auth0_DBManager();

		$profiles = $db_manager->get_current_user_profiles();

		if ( empty($profiles) ) { 
			return;
		}

		$ids = array();

		foreach($profiles as $profile) {
			$ids[] = $profile->user_id;
		}

		$cdn = $this->a0_options->get('cdn_url');
		$client_id = $this->a0_options->get('client_id');
		$domain = $this->a0_options->get('domain');
		$logout_url = wp_logout_url(get_permalink()) . '&SLO=1';
		
		wp_enqueue_script( 'wpa0_lock', $cdn, 'jquery' );
		include WPA0_PLUGIN_DIR . 'templates/auth0-singlelogout-handler.php';
	}

	public function logout() {
		$this->end_session();

		$sso = $this->a0_options->get( 'sso' );
		$slo = $this->a0_options->get( 'singlelogout' );
		$client_id = $this->a0_options->get( 'client_id' );
		$auto_login = absint( $this->a0_options->get( 'auto_login' ) );

		if ($slo && isset($_REQUEST['SLO'])) {
			$redirect_to = $_REQUEST['redirect_to'];
			wp_redirect($redirect_to);
			die();
		}

		if ( $sso ) {
			$redirect_to = home_url();
			wp_redirect( 'https://' . $this->a0_options->get( 'domain' ) . '/v2/logout?returnTo=' . urlencode( $redirect_to ) . '&client_id='.$client_id.'&auth0Client=' . base64_encode(json_encode(WP_Auth0_Api_Client::get_info_headers())) );
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

			if (strtolower($_SERVER['REQUEST_METHOD']) !== 'get') {
				return;
			}


			$stateObj = array( 'interim' => false, 'uuid' => uniqid() );
			if ( isset( $_GET['redirect_to'] ) ) {
				$stateObj['redirect_to'] = $_GET['redirect_to'];
			}
			$state = wp_json_encode( $stateObj );

			// Create the link to log in.
			$login_url = "https://". $this->a0_options->get( 'domain' ) .
						 "/authorize?response_type=code&scope=openid%20profile".
						 "&client_id=".$this->a0_options->get( 'client_id' ) .
						 "&redirect_uri=".home_url( '/index.php?auth0=1' ) .
						 "&state=".urlencode( $state ).
						 "&connection=".$this->a0_options->get( 'auto_login_method' ).
						 "&auth0Client=" . WP_Auth0_Api_Client::get_info_headers();

			wp_redirect( $login_url );
			die();
		}
	}

	public function init_auth0() {
		global $wp_query;

		// WP_Auth0_Seeder::get_me(100);
		// exit;

		if ( $this->query_vars('auth0') === null ) {
			return;
		}

		if ( $this->query_vars('auth0') === 'implicit' ) {
			$this->implicit_login();
		} else {
			$this->redirect_login();
		}
	}

	public function redirect_login() {
		global $wp_query;

		if ( $this->query_vars('auth0') === null ) {
			return;
		}

		if ( $this->query_vars('error_description') !== null && $this->query_vars('error_description') !== '' ) {
			$msg = __( 'There was a problem with your log in:', WPA0_LANG );
			$msg .= ' '.$this->query_vars('error_description');
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		if ( $this->query_vars('error') !== null && trim( $this->query_vars('error') ) !== '' ) {
			$msg = __( 'There was a problem with your log in:', WPA0_LANG );
			$msg .= ' '.$this->query_vars('error');
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		$code = $this->query_vars('code');
		$state = $this->query_vars('state');

		$stateFromGet = json_decode( stripcslashes( $state ) );

		$domain = $this->a0_options->get( 'domain' );

		$client_id = $this->a0_options->get( 'client_id' );
		$client_secret = $this->a0_options->get( 'client_secret' );

		if ( empty( $client_id ) ) {
			wp_die( __( 'Error: Your Auth0 Client ID has not been entered in the Auth0 SSO plugin settings.', WPA0_LANG ) );
		}
		if ( empty( $client_secret ) ) {
			wp_die( __( 'Error: Your Auth0 Client Secret has not been entered in the Auth0 SSO plugin settings.', WPA0_LANG ) );
		}
		if ( empty( $domain ) ) {
			wp_die( __( 'Error: No Domain defined in Wordpress Administration!', WPA0_LANG ) );
		}

		$response = WP_Auth0_Api_Client::get_token( $domain, $client_id, $client_secret, 'authorization_code', array(
				'redirect_uri' => home_url(),
				'code' => $code,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'init_auth0_oauth/token',$response );

			error_log( $response->get_error_message() );
			$msg = __( 'Sorry. There was a problem logging you in.', WPA0_LANG );
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		$data = json_decode( $response['body'] );

		if ( isset( $data->access_token ) || isset( $data->id_token ) ) {
			// Get the user information

			if ( isset( $data->id_token ) ) { 
				$response = WP_Auth0_Api_Client::get_current_user( $domain, $data->id_token );
			} else {
				$data->id_token = null;
				$response = WP_Auth0_Api_Client::get_user_info($domain, $data->access_token);
			}

			if ( $response instanceof WP_Error ) {
				WP_Auth0_ErrorManager::insert_auth0_error( 'init_auth0_userinfo', $response );

				error_log( $response->get_error_message() );
				$msg = __( 'There was a problem with your log in.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
				wp_die( $msg );
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

			WP_Auth0_ErrorManager::insert_auth0_error( 'init_auth0_oauth/token', $error );

			$msg = __( 'Error: the Client Secret configured on the Auth0 plugin is wrong. Make sure to copy the right one from the Auth0 dashboard.', WPA0_LANG );
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
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
				WP_Auth0_ErrorManager::insert_auth0_error( 'init_auth0_oauth/token', $error );
			}
			// Login failed!
			wp_redirect( home_url() . '?message=' . $data->error_description );
			//echo "Error logging in! Description received was:<br/>" . $data->error_description;
		}
		exit();
	}

	public function implicit_login() {

		$token = $_POST['token'];
		$stateFromGet = json_decode( stripcslashes( $_POST['state'] ) );

		$secret = $this->a0_options->get( 'client_secret' );

		$secret = JWT::urlsafeB64Decode( $secret );

		try {
			// Decode the user
			$decodedToken = JWT::decode( $token, $secret, array( 'HS256' ) );

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

			WP_Auth0_ErrorManager::insert_auth0_error( 'implicit_login', $e );

			error_log( $e->getMessage() );
			$msg = __( 'Sorry. There was a problem logging you in.', WPA0_LANG );
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}
	}

	public function login_user( $userinfo, $id_token, $access_token ) {
		// If the userinfo has no email or an unverified email, and in the options we require a verified email
		// notify the user he cant login until he does so.
		$requires_verified_email = $this->a0_options->get( 'requires_verified_email' );
		$remember_users_session = $this->a0_options->get( 'remember_users_session' );

		if ( ! $this->ignore_unverified_email &&  1 == $requires_verified_email ) {
			if ( empty( $userinfo->email ) ) {
				$msg = __( 'This account does not have an email associated, as required by your site administrator.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . home_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';

				wp_die( $msg );
			}

			if ( ! $userinfo->email_verified ) {
				$this->dieWithVerifyEmail( $userinfo, $id_token );
			}

		}

		// See if there is a user in the auth0_user table with the user info client id
		$user = WP_Auth0_Users::find_auth0_user( $userinfo->user_id );

		if ( ! is_null( $user ) ) {
			// User exists! Log in
			if (isset($userinfo->email) && $user->data->user_email !== $userinfo->email) {
				$user_id = wp_update_user( array( 'ID' => $user->data->ID, 'user_email' => $userinfo->email ) );
			}

			WP_Auth0_Users::update_auth0_object($userinfo);

			wp_set_current_user( $user->ID, $user->user_login );
	    wp_set_auth_cookie( $user->ID, $remember_users_session );
	    do_action( 'wp_login', $user->user_login, $user );

			do_action( 'auth0_user_login' , $user->ID, $userinfo, false, $id_token, $access_token );

			return true;

		} else {
			try {

				$creator = new WP_Auth0_UserCreator($this->a0_options);
				$user_id = $creator->create( $userinfo, $id_token, $access_token, $this->default_role, $this->ignore_unverified_email );

				$user = get_user_by( 'id', $user_id ); 

				wp_set_current_user( $user->ID, $user->user_login );
		    wp_set_auth_cookie( $user->ID, $remember_users_session );
		    do_action( 'wp_login', $user->user_login );


				do_action( 'auth0_user_login' , $user_id, $userinfo, true, $id_token, $access_token );
			}
			catch ( WP_Auth0_CouldNotCreateUserException $e ) {
				$msg = __( 'Error: Could not create user.', WPA0_LANG );
				$msg = ' ' . $e->getMessage();
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . home_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';
				wp_die( $msg );
			} catch ( WP_Auth0_RegistrationNotEnabledException $e ) {
				$msg = __( 'Error: Could not create user. The registration process is not available. Please contact your site’s administrator.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . home_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';
				wp_die( $msg );
			} catch ( WP_Auth0_EmailNotVerifiedException $e ) {
				$this->dieWithVerifyEmail( $e->userinfo, $e->id_token );
			}
			// catch ( Exception $e ) {
			// 	echo $e;exit;
			// }

			return true;
		}
	}

	private function dieWithVerifyEmail($userinfo, $id_token) {
		
		$html = apply_filters( 'auth0_verify_email_page' , '', $userinfo, $id_token );
		wp_die( $html );
	}

	public function login_with_credentials($username, $password, $connection="Username-Password-Authentication") {

		$domain = $this->a0_options->get( 'domain' );
		$client_id = $this->a0_options->get( 'client_id' );

		$response = WP_Auth0_Api_Client::ro($domain, $client_id, $username, $password, $connection, 'openid name email nickname email_verified identities');

		$secret = $this->a0_options->get( 'client_secret' );

		$secret = JWT::urlsafeB64Decode( $secret );

		try {
			// Decode the user
			$decodedToken = JWT::decode( $response->id_token, $secret, array( 'HS256' ) );

			// validate that this JWT was made for us
			if ( $this->a0_options->get( 'client_id' ) !== $decodedToken->aud ) {
				throw new Exception( 'This token is not intended for us.' );
			}

			$decodedToken->user_id = $decodedToken->sub;

			if ( $this->login_user( $userinfo, $response->id_token, $response->access_token ) ) {
					return false;
			}

		} catch( UnexpectedValueException $e ) {

			WP_Auth0_ErrorManager::insert_auth0_error( 'login_with_credentials', $e );

			error_log( $e->getMessage() );
		}
		return false;

	}

	protected function query_vars($key) {
		global $wp_query;
		if (isset($wp_query->query_vars[$key])) return $wp_query->query_vars[$key];
		if (isset($_REQUEST[$key])) return $_REQUEST[$key];
		return null;
	}

}
