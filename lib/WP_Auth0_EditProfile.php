<?php

/**
 * Class WP_Auth0_EditProfile.
 * Provides functionality on the edit profile and edit user page.
 */
class WP_Auth0_EditProfile {

	/**
	 * WP_Auth0_DBManager instance.
	 *
	 * @var WP_Auth0_DBManager
	 */
	protected $db_manager;

	/**
	 * WP_Auth0_UsersRepo instance.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	protected $users_repo;

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * WP_Auth0_EditProfile constructor.
	 *
	 * @param WP_Auth0_DBManager $db_manager - WP_Auth0_DBManager instance.
	 * @param WP_Auth0_UsersRepo $users_repo - WP_Auth0_UsersRepo instance.
	 * @param WP_Auth0_Options   $a0_options - WP_Auth0_Options instance.
	 */
	public function __construct(
		WP_Auth0_DBManager $db_manager,
		WP_Auth0_UsersRepo $users_repo,
		WP_Auth0_Options $a0_options
	) {
		$this->db_manager = $db_manager;
		$this->users_repo = $users_repo;
		$this->a0_options = $a0_options;
	}

	/**
	 * Add actions and filters for the profile page.
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'personal_options_update', array( $this, 'override_email_update' ), 1 );
	}

	/**
	 * Enqueue styles and scripts for the user profile edit screen.
	 * Hooked to: admin_enqueue_scripts
	 *
	 * @codeCoverageIgnore
	 */
	public function admin_enqueue_scripts() {
		global $user_id;
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'profile.php', 'user-edit.php' ) ) ) {
			return;
		}

		wp_enqueue_script(
			'wpa0_user_profile',
			WPA0_PLUGIN_JS_URL . 'edit-user-profile.js',
			array( 'jquery' ),
			WPA0_VERSION
		);

		$profile  = get_auth0userinfo( $user_id );
		$strategy = isset( $profile->sub ) ? WP_Auth0_Users::get_strategy( $profile->sub ) : '';

		wp_localize_script(
			'wpa0_user_profile',
			'wpa0UserProfile',
			array(
				'userId'         => intval( $user_id ),
				'userStrategy'   => sanitize_text_field( $strategy ),
				'deleteIdNonce'  => wp_create_nonce( 'delete_auth0_identity' ),
				'deleteMfaNonce' => wp_create_nonce( 'delete_auth0_mfa' ),
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'i18n'           => array(
					'confirmDeleteId'   => __( 'Are you sure you want to delete the Auth0 user data for this user?', 'wp-auth0' ),
					'confirmDeleteMfa'  => __( 'Are you sure you want to delete the Auth0 MFA data for this user?', 'wp-auth0' ),
					'actionComplete'    => __( 'Deleted', 'wp-auth0' ),
					'actionFailed'      => __( 'Action failed, please see the Auth0 error log for details.', 'wp-auth0' ),
					'cannotChangeEmail' => __( 'Email cannot be changed for non-database connections.', 'wp-auth0' ),
				),
			)
		);
	}

	/**
	 * Process email changes and pass the update to Auth0 if it passes validation.
	 * Hooked to: personal_options_update
	 */
	public function override_email_update() {
		global $wpdb;
		global $errors;

		if ( ! is_object( $errors ) ) {
			$errors = new WP_Error();
		}

		$current_user = wp_get_current_user();
		$user_profile = get_currentauth0userinfo();

		$app_token = $this->a0_options->get( 'auth0_app_token' );

		if ( ! $app_token ) {
			return;
		}

		if ( $current_user->ID != $_POST['user_id'] ) {
			return false;
		}

		if ( empty( $user_profile ) ) {
			return;
		}

		if ( isset( $_POST['email'] ) && $current_user->user_email != $_POST['email'] ) {

			$connection = null;

			foreach ( $user_profile->identities as $identity ) {
				if ( $identity->provider === 'auth0' ) {
					$connection = $identity->connection;
				}
			}

			if ( $connection === null ) {
				$errors->add( 'user_email', __( "<strong>ERROR</strong>: You can't change your email if you are using a social connection.", 'wp-auth0' ), array( 'form-field' => 'email' ) );
				return false;
			}

			if ( ! is_email( $_POST['email'] ) ) {
				$errors->add( 'user_email', __( '<strong>ERROR</strong>: The email address is not correct.', 'wp-auth0' ), array( 'form-field' => 'email' ) );
				return false;
			}

			if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE user_email=%s", $_POST['email'] ) ) ) {
				$errors->add( 'user_email', __( '<strong>ERROR</strong>: The email address is already used.', 'wp-auth0' ), array( 'form-field' => 'email' ) );
				delete_option( $current_user->ID . '_new_email' );
				return;
			}

			$user_email = esc_html( trim( $_POST['email'] ) );

			$user_id                 = $user_profile->user_id;
			$client_id               = $this->a0_options->get( 'client_id' );
			$domain                  = $this->a0_options->get( 'domain' );
			$requires_verified_email = $this->a0_options->get( 'requires_verified_email' );

			$response = WP_Auth0_Api_Client::update_user(
				$domain, $app_token, $user_id, array(
					'connection'   => $connection,
					'email'        => $user_email,
					'client_id'    => $client_id,
					'verify_email' => ( $requires_verified_email == 1 ),
				)
			);

			if ( $response !== false ) {

				if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $current_user->user_login ) ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST['email'], $current_user->user_login ) );
				}
				wp_update_user(
					array(
						'ID'         => $current_user->ID,
						'user_email' => $user_email,
					)
				);

				if ( $requires_verified_email ) {
					wp_logout();
				}
			}
		}
	}

	/**
	 * Validate a new password.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Change_Password::validate_new_password() instead.
	 *
	 * @param WP_Error $errors - Error instance to collect user profile errors.
	 * @param boolean  $update - Update or creation.
	 * @param WP_User  $user - User to validate.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function validate_new_password( $errors, $update, $user ) {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$auth0_password        = isset( $_POST['auth0_password'] ) ? $_POST['auth0_password'] : null;
		$auth0_repeat_password = isset( $_POST['auth0_repeat_password'] ) ? $_POST['auth0_repeat_password'] : null;

		if ( $auth0_password != $auth0_repeat_password ) {
			$errors->add( 'auth0_password', __( '<strong>ERROR</strong>: The password does not match', 'wp-auth0' ), array( 'form-field' => 'auth0_password' ) );
		}
	}

	/**
	 * Delete a user's password on Auth0.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Change_Password::validate_new_password() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function update_change_password() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		if ( empty( $user_profile ) ) {
			return;
		}

		$auth0_password        = isset( $_POST['auth0_password'] ) ? $_POST['auth0_password'] : null;
		$auth0_repeat_password = isset( $_POST['auth0_repeat_password'] ) ? $_POST['auth0_repeat_password'] : null;

		if ( ! empty( $auth0_password ) && $auth0_password == $auth0_repeat_password ) {
			$domain    = $this->a0_options->get( 'domain' );
			$client_id = $this->a0_options->get( 'client_id' );
			$api_token = $this->a0_options->get( 'auth0_app_token' );

			$connection = null;
			$email      = null;

			foreach ( $user_profile->identities as $identity ) {
				if ( $identity->provider === 'auth0' ) {
					$connection = $identity->connection;
					if ( isset( $identity->email ) ) {
						$email = $identity->email;
					} else {
						$email = $user_profile->email;
					}
				}
			}

			if ( $api_token ) {
				WP_Auth0_Api_Client::update_user(
					$domain, $api_token, $user_profile->user_id, array(
						'password'   => $auth0_password,
						'connection' => $connection,
					)
				);
			} else {
				WP_Auth0_Api_Client::change_password(
					$domain, array(
						'client_id'  => $client_id,
						'email'      => $user_profile->email,
						'connection' => $connection,
					)
				);
			}
		}
	}

	/**
	 * Delete a user's Auth0 data in WordPress.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Delete_Data::delete_user_data() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function delete_user_data() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( ! is_admin() ) {
			return;
		}

		$user_id = $_POST['user_id'];

		$this->users_repo->delete_auth0_object( $user_id );
	}

	/**
	 * Delete a user's MFA provider.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Delete_Mfa::delete_mfa() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function delete_mfa() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( ! is_admin() ) {
			return;
		}

		$user_id = $_POST['user_id'];

		$users = $this->db_manager->get_auth0_users( array( $user_id ) );
		if ( empty( $users ) ) {
			return;
		}

		$user_id = $users[0]->auth0_id;

		$provider  = 'google-authenticator';
		$domain    = $this->a0_options->get( 'domain' );
		$app_token = $this->a0_options->get( 'auth0_app_token' );

		WP_Auth0_Api_Client::delete_user_mfa( $domain, $app_token, $user_id, $provider );
	}

	/**
	 * Show controls to delete a user's Auth0 data.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Delete_Data::show_delete_identity() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function show_delete_identity() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( ! is_admin() ) {
			return;
		}
		if ( ! get_auth0userinfo( $_GET['user_id'] ) ) {
			return;
		}
		?>
		<table class="form-table">
			<tr>
				<th>
					<label><?php _e( 'Delete Auth0 Data' ); ?></label>
				</th>
				<td>
					<input type="button" onclick="DeleteAuth0Data(event);" name="auth0_delete_data" id="auth0_delete_data"
						   value="<?php _e( 'Delete Auth0 Data', 'wp-auth0' ); ?>" class="button button-secondary" />
				</td>
			</tr>
		</table>
		<script>
		function DeleteAuth0Data(event) {
			event.preventDefault();

			var data = {
				'action': 'auth0_delete_data',
				'user_id': '<?php echo $_GET['user_id']; ?>'
			};

			var successMsg = "<?php _e( 'Done!', 'wp-auth0' ); ?>";

			jQuery('#auth0_delete_data').attr('disabled', 'true');

			jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

				jQuery('#auth0_delete_data').val(successMsg).attr('disabled', 'true');

			}, 'json');

		}
		</script>
		<?php
	}

	/**
	 * Show controls to delete a user's Auth0 MFA provider.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Profile_Delete_Mfa::show_delete_mfa() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function show_delete_mfa() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( ! is_admin() ) {
			return;
		}
		if ( ! $this->a0_options->get( 'mfa' ) ) {
			return;
		}

		?>
		<table class="form-table">
			<tr>
				<th>
					<label><?php _e( 'Delete MFA Provider' ); ?></label>
				</th>
				<td>
					<input type="button" onclick="DeleteMFA(event);" name="auth0_delete_mfa" id="auth0_delete_mfa"
						   value="<?php _e( 'Delete MFA Provider' ); ?>" class="button button-secondary" />
				</td>
			</tr>
		</table>
		<script>
		function DeleteMFA(event) {
			event.preventDefault();

			var data = {
				'action': 'auth0_delete_mfa',
				'user_id': '<?php echo $_GET['user_id']; ?>'
			};

			var successMsg = "<?php _e( 'Done!', 'wp-auth0' ); ?>";

			jQuery('#auth0_delete_mfa').attr('disabled', 'true');

			jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

				jQuery('#auth0_delete_mfa').val(successMsg).attr('disabled', 'true');

			}, 'json');

		}
		</script>

		<?php
	}

	/**
	 * Replace WP password field with a custom one plus confirm field.
	 *
	 * @deprecated - 3.8.0, handled in WP core by default.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function show_change_password() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		if ( empty( $user_profile ) ) {
			return;
		}

		$connection = null;

		if ( ! empty( $user_profile->identities ) && is_array( $user_profile->identities ) ) {
			foreach ( $user_profile->identities as $identity ) {
				if ( $identity->provider === 'auth0' ) {
					$connection = $identity->connection;
				}
			}
		}

		if ( $connection === null ) {
			return;
		}
		?>
		<script>
		jQuery('.wp-pwd').parent().parent().hide();
		</script>
		<table class="form-table">
			<tr>
				<th>
					<label for="auth0_password"><?php _e( 'New Password' ); ?></label>
				</th>
				<td>
					<input type="password" name="auth0_password" id="auth0_password" value="" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="auth0_repeat_password"><?php _e( 'Repeat Password' ); ?></label>
				</th>
				<td>
					<input type="password" name="auth0_repeat_password" id="auth0_repeat_password" value="" class="regular-text" />
				</td>
			</tr>

		</table>
		<?php
	}

	/**
	 * Disable the email field for certain connections.
	 *
	 * @deprecated - 3.8.0, handled in assets/js/edit-user-profile.js.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function disable_email_field() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		if ( $user_profile && ! empty( $user_profile ) ) {
			$connection = null;
			if ( ! empty( $user_profile->identities ) && is_array( $user_profile->identities ) ) {
				foreach ( $user_profile->identities as $identity ) {
					if ( $identity->provider === 'auth0' ) {
						$connection = $identity->connection;
					}
				}
			}

			if ( $connection === null ) {
				?>
				<script>
			jQuery(document).ready( function($) {
				if ( $('input[name=email]').length ) {
					var emailElement = $('input[name=email]');
					var newEmailElement = emailElement.clone();
					newEmailElement.attr("disabled", "disabled")
						.attr("name", "disabled_name")
						.attr("id", "disabled_name")
						.insertAfter(emailElement);
					emailElement.attr("type", "hidden");

					var errorMsg = "<?php _e( 'You cannot change your email here if you logged in using a social connection.', 'wp-auth0' ); ?>";
					$('<span class="description">' + errorMsg + '</span>').insertAfter(newEmailElement);
				}
			});
				</script>
				<?php
			}
		}
	}
}
