<?php

class WP_Auth0_UsersRepo {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		if ( $this->a0_options->get( 'jwt_auth_integration' ) == 1 ) {
			add_filter( 'wp_jwt_auth_get_user', array( $this, 'getUser' ), 0, 2 );
		}
	}

	public function getUser( $jwt, $encodedJWT ) {

		$userRow = $this->find_auth0_user( $jwt->sub );

		$domain = $this->a0_options->get( 'domain' );

		$response = WP_Auth0_Api_Client::get_user( $domain, $encodedJWT, $jwt->sub );

		if ( $response['response']['code'] != 200 ) return null;

		if ( is_null( $userRow ) ) {

			if ( $this->tokenHasRequiredScopes( $jwt ) ) {
				$auth0User = $jwt;
			}
			else {
				$auth0User = json_decode( $response['body'] );
			}

			try {
				$user_id = $this->create( $auth0User, $encodedJWT );

				do_action( 'auth0_user_login' , $user_id, $response, true, $encodedJWT, null );

				return new WP_User( $user_id );
			}
			catch ( WP_Auth0_CouldNotCreateUserException $e ) {
				return null;
			}
			catch ( WP_Auth0_RegistrationNotEnabledException $e ) {
				return null;
			}

			return null;
		}elseif ( $userRow instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User', $userRow );
			return null;
		}else {

			do_action( 'auth0_user_login' , $userRow->ID, $response, false, $encodedJWT, null );

			return $userRow;
		}


	}

	public function tokenHasRequiredScopes( $jwt ) {

		return (
			( isset( $jwt->email ) || isset( $jwt->nickname ) )
			&& isset( $jwt->identities )
		);

	}

	public function create( $userinfo, $token, $access_token = null, $role = null, $ignore_unverified_email = false ) {

		// If the user doesn't exist we need to either create a new one, or asign him to an existing one
		$isDatabaseUser = false;
		foreach ( $userinfo->identities as $identity ) {
			if ( $identity->provider == "auth0" ) {
				$isDatabaseUser = true;
			}
		}
		$joinUser = null;
		// If the user has a verified email or is a database user try to see if there is
		// a user to join with. The isDatabase is because we don't want to allow database
		// user creation if there is an existing one with no verified email

		if ( isset( $userinfo->email )
			&& ( ( $ignore_unverified_email || ( isset( $userinfo->email_verified ) && $userinfo->email_verified ) )
				|| !$isDatabaseUser
			)
		) { //TODO: check this
			$joinUser = get_user_by( 'email', $userinfo->email );
		}

		// $auto_provisioning = WP_Auth0_Options::get('auto_provisioning');
		// $allow_signup = WP_Auth0_Options::Instance()->is_wp_registration_enabled() || $auto_provisioning;
		$allow_signup = $this->a0_options->is_wp_registration_enabled();

		$user_id = null;

		if ( !is_null( $joinUser ) && $joinUser instanceof WP_User ) {
			// If we are here, we have a potential join user
			// Don't allow creation or assignation of user if the email is not verified, that would
			// be hijacking

			if ( $ignore_unverified_email || $userinfo->email_verified ) {
				$user_id = $joinUser->ID;
			} else {
				throw new WP_Auth0_EmailNotVerifiedException( $userinfo, $token );
			}

		} elseif ( $allow_signup ) {
			// If we are here, we need to create the user
			$user_id = WP_Auth0_Users::create_user( $userinfo, $role );

			// Check if user was created

			if ( is_wp_error( $user_id ) ) {
				throw new WP_Auth0_CouldNotCreateUserException( $user_id->get_error_message() );
			}elseif ( $user_id == -2 ) {
				throw new WP_Auth0_CouldNotCreateUserException( 'Could not create user. The registration process were rejected. Please verify that your account is whitelisted for this system. Please contact your site’s administrator.' );
			}elseif ( $user_id <0 ) {
				throw new WP_Auth0_CouldNotCreateUserException();
			}
		} elseif ( ! $allow_signup ) {
			throw new WP_Auth0_RegistrationNotEnabledException();
		}

		// If we are here we should have a valid $user_id with a new user or an existing one
		// log him in, and update the auth0_user table
		$this->update_auth0_object( $user_id, $userinfo );

		return $user_id;
	}

	public function find_auth0_user( $id ) {

		$users = get_users( array( 'meta_key' => 'auth0_id', 'meta_value' => $id) ); 

		if ( $userRow instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( '_find_auth0_user', $userRow );
			return null;
		}

		if (!empty($users)) {
			return $users[0];
		}

		return null;
	}

	public function update_auth0_object( $user_id, $userinfo ) {
		update_user_meta( $user_id, 'auth0_id', ( isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub )); 
		update_user_meta( $user_id, 'auth0_obj', WP_Auth0_Serializer::serialize( $userinfo )); 
		update_user_meta( $user_id, 'last_update', date( 'c' ) ); 
	}

}
