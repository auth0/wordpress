<?php
class WP_Auth0_Users {

	/**
	 * Create a WordPress user with Auth0 data.
	 *
	 * @param object       $userinfo - User profile data from Auth0.
	 * @param null|boolean $role - Set the role as administrator - @deprecated - 3.8.0.
	 *
	 * @return int|WP_Error
	 */
	public static function create_user( $userinfo, $role = null ) {
		$email = null;
		if ( isset( $userinfo->email ) ) {
			$email = $userinfo->email;
		}
		if ( empty( $email ) ) {
			$email = 'change_this_email@' . uniqid() . '.com';
		}

		$valid_user = apply_filters( 'wpa0_should_create_user', true, $userinfo );
		if ( ! $valid_user ) {
			return -2;
		}

		// Generate a random password
		$password = wp_generate_password();

		$firstname = '';
		$lastname  = '';

		if ( isset( $userinfo->name ) ) {
			// Split the name into first- and lastname
			$names = explode( ' ', $userinfo->name );

			if ( count( $names ) == 1 ) {
				$firstname = $userinfo->name;
			} elseif ( count( $names ) == 2 ) {
				$firstname = $names[0];
				$lastname  = $names[1];
			} else {
				$lastname  = array_pop( $names );
				$firstname = implode( ' ', $names );
			}
		}

		$username = '';
		if ( isset( $userinfo->username ) ) {
			$username = $userinfo->username;
		} elseif ( isset( $userinfo->nickname ) ) {
			$username = $userinfo->nickname;
		}
		if ( empty( $username ) ) {
			$username = $email;
		}
		while ( username_exists( $username ) ) {
			$username = $username . rand( 0, 9 );
		}

		$description = '';

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
		// Create the user data array for updating first- and lastname
		$user_data = [
			'user_email'   => $email,
			'user_login'   => $username,
			'user_pass'    => $password,
			'first_name'   => $firstname,
			'last_name'    => $lastname,
			'display_name' => $username,
			'description'  => $description,
		];

		$user_data = apply_filters( 'wpa0_user_data', $user_data, $userinfo, $firstname, $lastname );

		if ( $role ) {
			// phpcs:ignore
			@trigger_error( sprintf( __( '$role parameter is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
			$user_data['role'] = 'administrator';
		}

		// Update the user
		$user_id = wp_insert_user( $user_data );

		if ( ! is_numeric( $user_id ) ) {
			return $user_id;
		}

		do_action( 'wpa0_user_created', $user_id, $email, $password, $firstname, $lastname );

		// Return the user ID
		return $user_id;
	}

	/**
	 * Get the strategy from an Auth0 user ID.
	 *
	 * @param string $auth0_id - Auth0 user ID.
	 *
	 * @return string
	 */
	public static function get_strategy( $auth0_id ) {
		if ( false === strpos( $auth0_id, '|' ) ) {
			return '';
		}
		$auth0_id_parts = explode( '|', $auth0_id );
		return $auth0_id_parts[0];
	}
}
