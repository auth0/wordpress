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
	
	/**
	 * Join or create a new user in the WordPress database
	 *
	 * @param stdClass $userinfo - Auth0 userinfo object
	 * @param string $token - DEPRECATED
	 * @param string|null $access_token - token used to retrieve Auth0 userinfo object
	 * @param string|null $role - the WP role to use
	 * @param bool $ignore_unverified_email - skip the unverified email check
	 *
	 * @return int|null|WP_Error
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException
	 * @throws WP_Auth0_EmailNotVerifiedException
	 * @throws WP_Auth0_RegistrationNotEnabledException
	 */
	public function create( $userinfo, $token, $access_token = null, $role = null, $ignore_unverified_email = false ) {

		// If the user doesn't exist we need to either create a new one, or assign him to an existing one
		$isDatabaseUser = false;
		
		if ( isset( $userinfo->identities ) ) {
			foreach ( $userinfo->identities as $identity ) {
				if ( $identity->provider == 'auth0' ) {
					$isDatabaseUser = true;
				}
			}
		} else {
			$sub = $userinfo->sub;
			list( $provider, $id ) = explode( '|', $sub );
			if ( $provider == 'auth0' ) {
				$isDatabaseUser = true;
			}
		}

		$joinUser = null;

		// If the user has a verified email or is a database user try to see if there is
		// a user to join with. The isDatabase is because we don't want to allow database
		// user creation if there is an existing one with no verified email
		// if true, we can join the a0 user with the wp one
		$shouldJoinUser = (
			
			// if a0 user has email
			isset( $userinfo->email ) && (
				
				// and it is verified or we should ignore verification
				( $ignore_unverified_email || ( isset( $userinfo->email_verified ) && $userinfo->email_verified ) )
			
			     // or it is not a database user (we can trust the email is valid)
				|| ! $isDatabaseUser )
			
		);

		$joinUser = get_user_by( 'email', $userinfo->email ); 

		$auto_provisioning = WP_Auth0_Options::Instance()->get('auto_provisioning');
		$allow_signup = WP_Auth0_Options::Instance()->is_wp_registration_enabled() || $auto_provisioning;

		$user_id = null;

		global $wpdb;

		// If there is a user with the same email, we should check if the wp user was joined with an auth0 user.
		// If so, we shouldn't allow it again
		
		if (!is_null( $joinUser ) && $joinUser instanceof WP_User ) {
			$auth0_id = get_user_meta( $joinUser->ID, $wpdb->prefix.'auth0_id', true);

			if ($auth0_id) { // if it has an a0 id, we cant join it
				$msg = __( 'There is a user with the same email', 'wp-auth0' );
				
				throw new WP_Auth0_CouldNotCreateUserException( $msg );
			}
		}

		if ( $shouldJoinUser && !is_null( $joinUser ) && $joinUser instanceof WP_User ) {
			// If we are here, we have a potential join user
			// Don't allow creation or assignation of user if the email is not verified, that would
			// be hijacking

			if ( $ignore_unverified_email || $userinfo->email_verified ) {
				$user_id = $joinUser->ID;
			} else {
				throw new WP_Auth0_EmailNotVerifiedException( $userinfo, $token, $access_token );
			}

		} elseif ( $allow_signup ) {

			// If we are here, we need to create the user
			$user_id = WP_Auth0_Users::create_user( $userinfo, $role );

			// Check if user was created

			if ( is_wp_error( $user_id ) ) {
				throw new WP_Auth0_CouldNotCreateUserException( $user_id->get_error_message() );
			}elseif ( $user_id == -2 ) {
				$msg = __( 'Could not create user. The registration process were rejected. Please verify that your account is whitelisted for this system. Please contact your site’s administrator.', 'wp-auth0' );
				
				throw new WP_Auth0_CouldNotCreateUserException( $msg );
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
	
	/**
	 * Find a WP user with an Auth0 id
	 *
	 * @param string $id - Auth0 user_id or sub
	 *
	 * @return null
	 */
	public function find_auth0_user ( $id ) {
		
		if ( empty( $id ) ) {
			
			WP_Auth0_ErrorManager::insert_auth0_error(
				__CLASS__ . '->' . __METHOD__,
				__( 'Empty user id', 'wp-auth0' )
			);
			return null;
		}
		
		global $wpdb;
		
		$users = get_users( array(
			'meta_key'   => $wpdb->prefix . 'auth0_id',
			'meta_value' => $id,
			'blog_id'    => 0,
		) );
		
		if ( $users instanceof WP_Error ) {
			
			WP_Auth0_ErrorManager::insert_auth0_error( __CLASS__ . '->' . __METHOD__, $users->get_error_message() );
			return null;
		}
		
		return ! empty( $users[0] ) ? $users[0] : null;
	}

	public function update_auth0_object( $user_id, $userinfo ) {
		global $wpdb;
		update_user_meta( $user_id, $wpdb->prefix.'auth0_id', ( isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub )); 
		update_user_meta( $user_id, $wpdb->prefix.'auth0_obj', WP_Auth0_Serializer::serialize( $userinfo )); 
		update_user_meta( $user_id, $wpdb->prefix.'last_update', date( 'c' ) ); 
	}

	public function delete_auth0_object( $user_id ) {
		global $wpdb;
		delete_user_meta( $user_id, $wpdb->prefix.'auth0_id' ); 
		delete_user_meta( $user_id, $wpdb->prefix.'auth0_obj' ); 
		delete_user_meta( $user_id, $wpdb->prefix.'last_update' ); 
	}

}
