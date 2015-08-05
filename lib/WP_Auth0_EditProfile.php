<?php

class WP_Auth0_EditProfile {

    public static function init() {
        add_action('personal_options_update', array( __CLASS__,'override_email_update'), 1);
    }

    public static function override_email_update() {
        global $wpdb;

        $current_user = wp_get_current_user();

        if ( $current_user->ID != $_POST['user_id'] ) {
            return false;
        }

    	if ( $current_user->user_email != $_POST['email'] ) {
            if ( !is_email( $_POST['email'] ) ) {
    			$errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address isn&#8217;t correct." ), array( 'form-field' => 'email' ) );
    			return;
    		}

    		if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE user_email=%s", $_POST['email'] ) ) ) {
    			$errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address is already used." ), array( 'form-field' => 'email' ) );
    			delete_option( $current_user->ID . '_new_email' );
    			return;
    		}

            $options = WP_Auth0_Options::Instance();
            $user_profiles = WP_Auth0_DBManager::get_current_user_profiles();
            $user_profile = $user_profiles[0];

            $connection = $user_profile->identities[0]->connection;
            $user_id = $user_profile->user_id;
            $client_id = $options->get('client_id');
            $domain = $options->get('domain');
            $requires_verified_email = $options->get('requires_verified_email');
            $app_token = self::get_token();

            $response = WP_Auth0_Api_Client::update_user($domain, $app_token, $user_id, array(
                'connection' => $connection,
                'email' => $user_email,
                'client_id' => $client_id,
                'verify_email' => ($requires_verified_email == 1)
            ));

            if ($response !== false) {

                $user_email = esc_html( trim( $_POST['email'] ) );
        		if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $current_user->user_login ) ) ) {
        			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST['email'], $current_user->user_login ) );
                }
                wp_update_user( array(
                    'ID' => $current_user->ID,
                    'user_email' => $user_email,
                ) );

                if ($requires_verified_email) {
                    wp_logout();
                }

            }
        }
    }

    protected static function get_token() {
		$user = get_currentauth0user();
		self::$token = $user->access_token;
		return self::$token;
	}

}
