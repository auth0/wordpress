<?php
/**
 * Contains class WP_Auth0_Profile_Change_Password.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Profile_Change_Password.
 */
class WP_Auth0_Profile_Change_Password {

	/**
	 * WP_Auth0_Api_Change_Password instance.
	 *
	 * @var WP_Auth0_Api_Change_Password
	 */
	protected $api_change_password;

	/**
	 * WP_Auth0_Profile_Change_Password constructor.
	 *
	 * @param WP_Auth0_Api_Change_Password $api_change_password - WP_Auth0_Api_Change_Password instance.
	 */
	public function __construct( WP_Auth0_Api_Change_Password $api_change_password ) {
		$this->api_change_password = $api_change_password;
	}

	/**
	 * Update the user's password at Auth0
	 * Hooked to: user_profile_update_errors, validate_password_reset, woocommerce_save_account_details_errors
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param WP_Error         $errors - WP_Error object to use if validation fails.
	 * @param boolean|stdClass $user - Boolean update or WP_User instance, depending on action.
	 *
	 * @return boolean
	 */
	public function validate_new_password( $errors, $user ) {

		// Exit if we're not changing the password.
		// The pass1 key is for core WP, password_1 is WooCommerce.
		if ( empty( $_POST['pass1'] ) && empty( $_POST['password_1'] ) ) {
			return false;
		}

		$field_name   = ! empty( $_POST['pass1'] ) ? 'pass1' : 'password_1';
		$new_password = wp_unslash( $_POST[ $field_name ] );
		$wp_user_id   = null;

		// User object passed in from an action.
		if ( is_object( $user ) && ! empty( $user->ID ) ) {
			$wp_user_id = absint( $user->ID );
		}

		// Input field from user edit or profile update.
		if ( ! $wp_user_id && isset( $_POST['user_id'] ) ) {
			$user_editing_allowed = ( current_user_can( 'edit_users' ) || $_POST['user_id'] == get_current_user_id() );

			if ( $user_editing_allowed ) {
				$wp_user_id = absint( $_POST['user_id'] );
			}
		}

		if ( ! $wp_user_id ) {
			return false;
		}

		// Exit if this is not an Auth0 user.
		$auth0_id = WP_Auth0_UsersRepo::get_meta( $wp_user_id, 'auth0_id' );
		if ( empty( $auth0_id ) ) {
			return false;
		}
		$strategy = WP_Auth0_Users::get_strategy( $auth0_id );

		// Exit if this is not a database strategy user.
		if ( 'auth0' !== $strategy ) {
			return false;
		}

		$result = $this->api_change_password->call( $auth0_id, $new_password );

		// Password change was successful, nothing else to do.
		if ( true === $result ) {
			return true;
		}

		// Password change was unsuccessful so don't change WP user account.
		unset( $_POST['pass1'] );
		unset( $_POST['pass1-text'] );
		unset( $_POST['pass2'] );

		// Add an error message to appear at the top of the page.
		$error_msg = is_string( $result ) ? $result : __( 'Password could not be updated.', 'wp-auth0' );
		$errors->add( 'auth0_password', $error_msg, [ 'form-field' => $field_name ] );
		return false;
	}
}
