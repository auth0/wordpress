<?php

class WP_Auth0_UserCreator {

    public function tokenHasRequiredScopes($jwt) {

        return (
            (isset($jwt->email) || isset($jwt->nickname))
            && isset($jwt->identities)
        );

    }

	public function create($userinfo, $token) {

		// If the user doesn't exist we need to either create a new one, or asign him to an existing one
        $isDatabaseUser = false;
        foreach ($userinfo->identities as $identity) {
            if ($identity->provider == "auth0") {
                $isDatabaseUser = true;
            }
        }
        $joinUser = null;
        // If the user has a verified email or is a database user try to see if there is
        // a user to join with. The isDatabase is because we don't want to allow database
        // user creation if there is an existing one with no verified email

        if (isset($userinfo->email) && ((isset($userinfo->email_verified) && $userinfo->email_verified) || $isDatabaseUser)) {
            $joinUser = get_user_by( 'email', $userinfo->email );
        }

        // $auto_provisioning = WP_Auth0_Options::get('auto_provisioning');
        // $allow_signup = WP_Auth0_Options::is_wp_registration_enabled() || $auto_provisioning;
        $allow_signup = WP_Auth0_Options::is_wp_registration_enabled();

        if (!is_null($joinUser) && $joinUser instanceof WP_User) {
            // If we are here, we have a potential join user
            // Don't allow creation or assignation of user if the email is not verified, that would
            // be hijacking
            if (!$userinfo->email_verified) {
                throw new WP_Auth0_EmailNotVerifiedException($userinfo, $token);
            }
            $user_id = $joinUser->ID;
        } elseif ($allow_signup) {
            // If we are here, we need to create the user
            $user_id = WP_Auth0_Users::create_user($userinfo);

            // Check if user was created

            if( is_wp_error($user_id) ) {
                throw new WP_Auth0_CouldNotCreateUserException($user_id->get_error_message());
            }elseif($user_id == -2){
                throw new WP_Auth0_CouldNotCreateUserException('Could not create user. The registration process were rejected. Please verify that your account is whitelisted for this system.');
            }elseif ($user_id <0){
                throw new WP_Auth0_CouldNotCreateUserException();
            }
        } else {
            throw new WP_Auth0_RegistrationNotEnabledException();
        }
        
        // If we are here we should have a valid $user_id with a new user or an existing one
        // log him in, and update the auth0_user table
        self::insertAuth0User($userinfo, $user_id);

        return $user_id;

	}

    public function insertAuth0User($userinfo, $user_id) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->auth0_user,
            array(
                'auth0_id' => $userinfo->user_id,
                'wp_id' => $user_id,
                'auth0_obj' => serialize($userinfo)
            ),
            array(
                '%s',
                '%d',
                '%s'
            )
        );
    }

}