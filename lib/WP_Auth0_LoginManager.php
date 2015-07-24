<?php

class WP_Auth0_LoginManager {

	public static function init() {
		add_action( 'wp_logout', array( __CLASS__, 'logout' ) );
		add_action( 'wp_login', array( __CLASS__, 'end_session' ) );
		add_action( 'login_init', array( __CLASS__, 'login_auto' ) );
		add_action( 'template_redirect', array( __CLASS__, 'init_auth0' ), 1 );
	}

	public static function logout() {
		self::end_session();

		$options = WP_Auth0_Options::Instance();

		$sso = $options->get( 'sso' );
		$auto_login = absint( $options->get( 'auto_login' ) );

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = home_url();
		}

		if ( $sso ) {
			wp_redirect( 'https://' . $options->get( 'domain' ) . '/v2/logout?returnTo=' . urlencode( $redirect_to ) . '&auth0Client=' . WP_Auth0_Api_Client::get_info_headers() );
			die();
		}

		if ( $auto_login ) {
			wp_redirect( home_url() );
			die();
		}
	}

	public static function end_session() {
		if ( session_id() ) {
			session_destroy();
		}
	}

	public static function login_auto() {
		$options = WP_Auth0_Options::Instance();

		$auto_login = absint( $options->get( 'auto_login' ) );

		if ( $auto_login && ( ! isset( $_GET['action'] ) || 'logout' !== $_GET['action'] ) && ! isset( $_GET['wle'] ) ) {

			$stateObj = array( 'interim' => false, 'uuid' => uniqid() );
			if ( isset( $_GET['redirect_to'] ) ) {
				$stateObj['redirect_to'] = $_GET['redirect_to'];
			}
			$state = wp_json_encode( $stateObj );

			// Create the link to log in.
			$login_url = "https://". $options->get( 'domain' ) .
						 "/authorize?response_type=code&scope=openid%20profile".
						 "&client_id=".$options->get( 'client_id' ) .
						 "&redirect_uri=".site_url( '/index.php?auth0=1' ) .
						 "&state=".urlencode( $state ).
						 "&connection=".$options->get( 'auto_login_method' ).
						 "&auth0Client=" . WP_Auth0_Api_Client::get_info_headers();

			wp_redirect( $login_url );
			die();
		}
	}

	public static function init_auth0() {
		global $wp_query;

		if ( ! isset( $wp_query->query_vars['auth0'] ) ) {
			return;
		}

		if ( $wp_query->query_vars['auth0'] === 'implicit' ) {
			self::implicit_login();
		} else {
			self::redirect_login();
		}
	}

	public static function redirect_login() {
		global $wp_query;

		if ( '1' !== $wp_query->query_vars['auth0']) {
			return;
		}

		if ( isset( $wp_query->query_vars['error_description'] ) && trim( $wp_query->query_vars['error_description']) !== '' ) {
			$msg = __( 'There was a problem with your log in:', WPA0_LANG );
			$msg .= ' '.$wp_query->query_vars['error_description'];
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		if ( isset( $wp_query->query_vars['error'] ) && trim( $wp_query->query_vars['error'] ) !== '' ) {
			$msg = __( 'There was a problem with your log in:', WPA0_LANG );
			$msg .= ' '.$wp_query->query_vars['error'];
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		$options = WP_Auth0_Options::Instance();

		$code = $wp_query->query_vars['code'];
		$state = (isset($wp_query->query_vars['state']) ? $wp_query->query_vars['state'] : null);
		$stateFromGet = json_decode( stripcslashes( $state ) );

		$domain = $options->get( 'domain' );

		$client_id = $options->get( 'client_id' );
		$client_secret = $options->get( 'client_secret' );

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
			WP_Auth0::insert_auth0_error( 'init_auth0_oauth/token',$response );

			error_log( $response->get_error_message() );
			$msg = __( 'Sorry. There was a problem logging you in.', WPA0_LANG );
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}

		$data = json_decode( $response['body'] );

		if ( isset( $data->access_token ) ) {
			// Get the user information
			$response = WP_Auth0_Api_Client::get_user_info( $domain, $data->access_token );

			if ( $response instanceof WP_Error ) {
				WP_Auth0::insert_auth0_error( 'init_auth0_userinfo', $response );

				error_log( $response->get_error_message() );
				$msg = __( 'There was a problem with your log in.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
				wp_die( $msg );
			}

			$userinfo = json_decode( $response['body'] );
			if ( self::login_user( $userinfo, $data->id_token, $data->access_token ) ) {
				if ( null !== $stateFromGet && isset( $stateFromGet->interim ) && $stateFromGet->interim ) {
					include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
					exit();
				} else {
					if ( null !== $stateFromGet && isset( $stateFromGet->redirect_to ) ) {
						$redirectURL = $stateFromGet->redirect_to;
					} else {
						$redirectURL = $options->get( 'default_login_redirection' );
					}

					wp_safe_redirect( $redirectURL );
				}
			}
		} elseif ( is_array( $response['response'] ) &&  401 === (int) $response['response']['code'] ) {

			$error = new WP_Error( '401', 'auth/token response code: 401 Unauthorized' );

			WP_Auth0::insert_auth0_error( 'init_auth0_oauth/token', $error );

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
				WP_Auth0::insert_auth0_error( 'init_auth0_oauth/token', $error );
			}
			// Login failed!
			wp_redirect( home_url() . '?message=' . $data->error_description );
			//echo "Error logging in! Description received was:<br/>" . $data->error_description;
		}
		exit();
	}

	public static function implicit_login() {

		require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/BeforeValidException.php';
		require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/ExpiredException.php';
		require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/SignatureInvalidException.php';
		require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Authentication/JWT.php';

		$token = $_POST['token'];
		$stateFromGet = json_decode( stripcslashes( $_POST['state'] ) );

		$options = WP_Auth0_Options::Instance();

		$secret = $options->get( 'client_secret' );

		$secret = base64_decode( strtr( $secret, '-_', '+/' ) );

		try {
			// Decode the user
			$decodedToken = JWT::decode( $token, $secret, array( 'HS256' ) );

			// validate that this JWT was made for us
			if ( $options->get( 'client_id' ) !== $decodedToken->aud ) {
				throw new Exception( 'This token is not intended for us.' );
			}

			$decodedToken->user_id = $decodedToken->sub;

			if ( self::login_user( $decodedToken, $token, null ) ) {
				if ( null !== $stateFromGet && isset( $stateFromGet->interim ) && $stateFromGet->interim ) {
					include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
					exit();
				} else {
					if ( null !== $stateFromGet && isset( $stateFromGet->redirect_to ) ) {
						$redirectURL = $stateFromGet->redirect_to;
					} else {
						$redirectURL = $options->get( 'default_login_redirection' );
					}

					wp_safe_redirect( $redirectURL );
				}
			}

		} catch( UnexpectedValueException $e ) {

			WP_Auth0::insert_auth0_error( 'implicit_login', $e );

			error_log( $e->getMessage() );
			$msg = __( 'Sorry. There was a problem logging you in.', WPA0_LANG );
			$msg .= '<br/><br/>';
			$msg .= '<a href="' . wp_login_url() . '">' . __( '← Login', WPA0_LANG ) . '</a>';
			wp_die( $msg );
		}
	}

	public static function login_user( $userinfo, $id_token, $access_token ) {
		// If the userinfo has no email or an unverified email, and in the options we require a verified email
		// notify the user he cant login until he does so.
		$requires_verified_email = WP_Auth0_Options::Instance()->get( 'requires_verified_email' );

		if ( 1 == $requires_verified_email ) {
			if ( empty( $userinfo->email ) ) {
				$msg = __( 'This account does not have an email associated. Please login with a different provider.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . site_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';

				wp_die( $msg );
			}

			if ( ! $userinfo->email_verified ) {
				self::dieWithVerifyEmail( $userinfo, $id_token );
			}

		}

		// See if there is a user in the auth0_user table with the user info client id
		$user = self::_find_auth0_user( $userinfo->user_id );

		if ( ! is_null( $user ) ) {
			// User exists! Log in
			self::_update_auth0_object( $userinfo );

			wp_set_auth_cookie( $user->ID );

			do_action( 'auth0_user_login' , $user->ID, $userinfo, false, $id_token, $access_token );

			return true;

		} else {

			try {
				$creator = new WP_Auth0_UserCreator();
				$user_id = $creator->create( $userinfo, $id_token );

				wp_set_auth_cookie( $user_id );

				do_action( 'auth0_user_login' , $user_id, $userinfo, true, $id_token, $access_token );
			} catch ( WP_Auth0_CouldNotCreateUserException $e ) {
				$msg = __( 'Error: Could not create user.', WPA0_LANG );
				$msg = ' ' . $e->getMessage();
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . site_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';
				wp_die( $msg );
			} catch ( WP_Auth0_RegistrationNotEnabledException $e ) {
				$msg = __( 'Error: Could not create user. The registration process is not available.', WPA0_LANG );
				$msg .= '<br/><br/>';
				$msg .= '<a href="' . site_url() . '">' . __( '← Go back', WPA0_LANG ) . '</a>';
				wp_die( $msg );
			} catch ( WP_Auth0_EmailNotVerifiedException $e ) {
				self::dieWithVerifyEmail( $e->userinfo, $e->id_token );
			}

			return true;
		}
	}

	private static function _find_auth0_user( $id ) {
		global $wpdb;
		$sql = 'SELECT u.*
				FROM ' . $wpdb->auth0_user .' a
				JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
				WHERE a.auth0_id = %s';
		$userRow = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

		if ( is_null( $userRow ) ) {
			return null;
		} elseif ( $userRow instanceof WP_Error ) {
			WP_Auth0::insert_auth0_error( '_find_auth0_user',$userRow );
			return null;
		}
		$user = new WP_User();
		$user->init( $userRow );
		return $user;
	}

	private static function _update_auth0_object($userinfo) {
		global $wpdb;
		$wpdb->update(
			$wpdb->auth0_user,
			array(
				'auth0_obj' => serialize($userinfo),
			),
			array( 'auth0_id' => $userinfo->user_id ),
			array( '%s' ),
			array( '%s' )
		);
	}

	private static function dieWithVerifyEmail($userinfo, $id_token) {
		ob_start();
		$domain = WP_Auth0_Options::Instance()->get( 'domain' );
		$token = $id_token;
		$email = $userinfo->email;
		$connection = $userinfo->identities[0]->connection;
		$userId = $userinfo->user_id;
		include WPA0_PLUGIN_DIR . 'templates/verify-email.php';

		$html = ob_get_clean();
		wp_die( $html );
	}

}
