<?php
/**
 * Contains class WP_Auth0_UsersRepo.
 *
 * @package WP-Auth0
 *
 * @since 1.2.0
 */

/**
 * Class WP_Auth0_UsersRepo.
 */
class WP_Auth0_UsersRepo {

	/**
	 * Options instance used in this class.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * WP_Auth0_UsersRepo constructor.
	 *
	 * @param WP_Auth0_Options $a0_options - Options instance used in this class.
	 */
	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	/**
	 * Create or join a WP user with an incoming Auth0 one or reject with an exception.
	 *
	 * @param object $userinfo - Profile object from Auth0.
	 * @param string $token - ID token from Auth0.
	 *
	 * @return int|null|WP_Error
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - When the user could not be created.
	 * @throws WP_Auth0_EmailNotVerifiedException - When a users's email is not verified but the site requires it.
	 * @throws WP_Auth0_RegistrationNotEnabledException - When registration is not turned on for this site.
	 */
	public function create( $userinfo, $token ) {

		$auth0_sub      = $userinfo->sub;
		list($strategy) = explode( '|', $auth0_sub );
		$wp_user        = null;
		$user_id        = null;

		// Check legacy identities profile object for a DB connection.
		$is_db_connection = 'auth0' === $strategy;
		if ( ! $is_db_connection && ! empty( $userinfo->identities ) ) {
			foreach ( $userinfo->identities as $identity ) {
				if ( 'auth0' === $identity->provider ) {
					$is_db_connection = true;
					break;
				}
			}
		}

		// Email is considered verified if flagged as such, if we ignore the requirement, or if the strategy is skipped.
		$email_verified = ! empty( $userinfo->email_verified )
			|| $this->a0_options->strategy_skips_verified_email( $strategy );

		// WP user to join with incoming Auth0 user.
		if ( ! empty( $userinfo->email ) ) {
			$wp_user = get_user_by( 'email', $userinfo->email );
		}

		if ( is_object( $wp_user ) && $wp_user instanceof WP_User ) {
			// WP user exists, check if we can join.
			$user_id = $wp_user->ID;

			// Cannot join a DB connection user without a verified email.
			if ( $is_db_connection && ! $email_verified ) {
				throw new WP_Auth0_EmailNotVerifiedException( $userinfo, $token );
			}

			// If the user has a different Auth0 ID, we cannot join it.
			$current_auth0_id = self::get_meta( $user_id, 'auth0_id' );
			if ( ! empty( $current_auth0_id ) && $auth0_sub !== $current_auth0_id ) {
				throw new WP_Auth0_CouldNotCreateUserException( __( 'There is a user with the same email.', 'wp-auth0' ) );
			}
		} elseif ( $this->a0_options->is_wp_registration_enabled() || $this->a0_options->get( 'auto_provisioning' ) ) {
			// WP user does not exist and registration is allowed.
			$user_id = WP_Auth0_Users::create_user( $userinfo );

			// Check if user was created.
			if ( is_wp_error( $user_id ) ) {
				throw new WP_Auth0_CouldNotCreateUserException( $user_id->get_error_message() );
			} elseif ( -2 === $user_id ) {
				// Registration rejected by wpa0_should_create_user filter in WP_Auth0_Users::create_user().
				throw new WP_Auth0_CouldNotCreateUserException( __( 'Registration rejected.', 'wp-auth0' ) );
			} elseif ( $user_id < 0 ) {
				// Registration failed for another reason.
				throw new WP_Auth0_CouldNotCreateUserException();
			}
		} else {
			// Signup is not allowed.
			throw new WP_Auth0_RegistrationNotEnabledException();
		}

		$this->update_auth0_object( $user_id, $userinfo );
		return $user_id;
	}

	/**
	 * Look for and return a user with an Auth0 ID
	 *
	 * @param string $id - An Auth0 user ID, like "provider|id".
	 *
	 * @return null|WP_User
	 */
	public function find_auth0_user( $id ) {
		global $wpdb;

		if ( empty( $id ) ) {
			WP_Auth0_ErrorLog::insert_error( __METHOD__, __( 'Empty user id', 'wp-auth0' ) );

			return null;
		}

		$query = [
			// Limiting the returned number and this happens on login so some delay is acceptable.
			// phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_key'   => $wpdb->prefix . 'auth0_id',
			// phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => $id,
			'number'     => 1,
			'blog_id'    => 0,
		];

		$users = get_users( $query );

		if ( $users instanceof WP_Error ) {
			WP_Auth0_ErrorLog::insert_error( __METHOD__ . ' => get_users() ', $users->get_error_message() );

			return null;
		}

		return ! empty( $users[0] ) ? $users[0] : null;
	}

	/**
	 * Update all Auth0 meta fields for a WordPress user.
	 *
	 * @param int      $user_id - WordPress user ID.
	 * @param stdClass $userinfo - User profile object from Auth0.
	 */
	public function update_auth0_object( $user_id, $userinfo ) {
		$auth0_user_id = isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub;
		self::update_meta( $user_id, 'auth0_id', $auth0_user_id );

		$userinfo_encoded = WP_Auth0_Serializer::serialize( $userinfo );
		$userinfo_encoded = wp_slash( $userinfo_encoded );
		self::update_meta( $user_id, 'auth0_obj', $userinfo_encoded );

		self::update_meta( $user_id, 'last_update', date( 'c' ) );
	}

	/**
	 * Get a user's Auth0 meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to get.
	 *
	 * @return mixed
	 *
	 * @since 3.8.0
	 */
	public static function get_meta( $user_id, $key ) {
		global $wpdb;
		return get_user_meta( $user_id, $wpdb->prefix . $key, true );
	}

	/**
	 * Update a user's Auth0 meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to update.
	 * @param mixed   $value - Usermeta value to use.
	 *
	 * @return int|bool
	 *
	 * @since 3.11.0
	 */
	public static function update_meta( $user_id, $key, $value ) {
		global $wpdb;
		return update_user_meta( $user_id, $wpdb->prefix . $key, $value );
	}

	/**
	 * Delete a user's Auth0 meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to delete.
	 *
	 * @return bool
	 *
	 * @since 3.11.0
	 */
	public static function delete_meta( $user_id, $key ) {
		global $wpdb;
		return delete_user_meta( $user_id, $wpdb->prefix . $key );
	}
}
