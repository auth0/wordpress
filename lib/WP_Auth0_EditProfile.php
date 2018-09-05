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
		$this->a0_options = $a0_options;
		$this->users_repo = $users_repo;
		$this->db_manager = $db_manager;
	}

	/**
	 * Add actions and filters for the profile page.
	 */
	public function init() {
		global $pagenow;

		add_action( 'personal_options_update', array( $this, 'override_email_update' ), 1 );

		add_action( 'edit_user_profile', array( $this, 'show_delete_identity' ) );
		add_action( 'show_user_profile', array( $this, 'show_delete_identity' ) );
		add_action( 'edit_user_profile', array( $this, 'show_delete_mfa' ) );
		add_action( 'show_user_profile', array( $this, 'show_delete_mfa' ) );

		add_action( 'wp_ajax_auth0_delete_data', array( $this, 'delete_user_data' ) );
		add_action( 'wp_ajax_auth0_delete_mfa', array( $this, 'delete_mfa' ) );

		add_action( 'user_profile_update_errors', array( $this, 'validate_new_password' ), 10, 2 );
		add_action( 'validate_password_reset', array( $this, 'validate_new_password' ), 10, 2 );

		if ( $pagenow == 'profile.php' || $pagenow == 'user-edit.php' ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
	}

	/**
	 * Enqueue styles and scripts for the user profile edit screen.
	 * Hooked to: admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		global $user_id;

		wp_enqueue_script(
			'wpa0_user_profile',
			WPA0_PLUGIN_JS_URL . 'edit-user-profile.js',
			array( 'jquery' ),
			WPA0_VERSION
		);

		$profile  = get_auth0userinfo( $user_id );
		$strategy = isset( $profile->sub ) ? $this->get_auth0_strategy( $profile->sub ) : '';

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
	 * Update the user's password at Auth0
	 * Hooked to: user_profile_update_errors, validate_password_reset
	 *
	 * @param WP_Error        $errors - WP_Error object to use if validation fails.
	 * @param boolean|WP_User $user - Boolean update or WP_User instance, depending on action.
	 */
	public function validate_new_password( $errors, $user ) {
		global $wpdb;

		// Exit if we're not changing the password.
		if ( empty( $_POST['pass1'] ) ) {
			return;
		}
		$new_password = $_POST['pass1'];

		if ( isset( $_POST['user_id'] ) ) {
			$wp_user_id = absint( $_POST['user_id'] );
		} elseif ( is_object( $user ) && $user instanceof WP_User ) {
			$wp_user_id = absint( $user->ID );
		} else {
			return;
		}

		// Exit if this is not an Auth0 user.
		// TODO: Replace the call below with WP_Auth0_UsersRepo::get_meta() when rebased.
		$auth0_id = get_user_meta( $wp_user_id, $wpdb->prefix . 'auth0_id', true );
		if ( empty( $auth0_id ) ) {
			return;
		}
		$strategy = $this->get_auth0_strategy( $auth0_id );

		// Exit if this is not a database strategy user.
		if ( 'auth0' !== $strategy ) {
			return;
		}

		$change_password = new WP_Auth0_Api_Change_Password( $this->a0_options, $auth0_id );
		$result          = $change_password->call( array( 'password' => $new_password ) );

		// Password change was successful, nothing else to do.
		if ( true === $result ) {
			return;
		}

		// Password change was unsuccessful so don't change WP user account.
		unset( $_POST['pass1'] );
		unset( $_POST['pass1-text'] );
		unset( $_POST['pass2'] );

		// Add an error message to appear at the top of the page.
		$error_msg = is_string( $result ) ? $result : __( 'Password could not be updated.', 'wp-auth0' );
		$errors->add( 'auth0_password', $error_msg, array( 'form-field' => 'pass1' ) );
	}

	/**
	 * TODO: Deprecate, moved to WP_Auth0_EditProfile::validate_new_password()
	 */
	public function update_change_password() {
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
	 * AJAX function to delete Auth0 data in the usermeta table.
	 * Hooked to: wp_ajax_auth0_delete_data
	 */
	public function delete_user_data() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'delete_auth0_identity' ) ) {
			exit( '0' );
		}

		if ( empty( $_POST['user_id'] ) ) {
			exit( '0' );
		}

		$user_id = $_POST['user_id'];

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			exit( '0' );
		}

		$this->users_repo->delete_auth0_object( $user_id );
		exit( '1' );
	}

	/**
	 * AJAX function to delete the MFA provider at Auth0.
	 * Hooked to: wp_ajax_auth0_delete_data
	 */
	public function delete_mfa() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'delete_auth0_mfa' ) ) {
			exit( '0' );
		}

		if ( empty( $_POST['user_id'] ) ) {
			exit( '0' );
		}

		$user_id = $_POST['user_id'];

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			exit( '0' );
		}

		$profile         = get_auth0userinfo( $user_id );
		$delete_user_mfa = new WP_Auth0_Api_Delete_User_Mfa( $this->a0_options, $profile->sub );
		echo intval( $delete_user_mfa->call() );
		exit;
	}

	/**
	 * Show the delete Auth0 user data button.
	 * Hooked to: edit_user_profile, show_user_profile
	 */
	public function show_delete_identity() {
		global $user_id;

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			return;
		}

		if ( ! get_auth0userinfo( $user_id ) ) {
			return;
		}

		?>
		<table class="form-table">
			<tr>
				<th>
					<label><?php _e( 'Delete Auth0 data' ); ?></label>
				</th>
				<td>
					<input type="button" id="auth0_delete_data" class="button button-secondary"
								 value="<?php _e( 'Delete Auth0 Data', 'wp-auth0' ); ?>" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Show the delete Auth0 MFA data button.
	 * Hooked to: edit_user_profile, show_user_profile
	 */
	public function show_delete_mfa() {
		global $user_id;
		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			return;
		}

		if ( ! $this->a0_options->get( 'mfa' ) ) {
			return;
		}

		if ( ! get_auth0userinfo( $user_id ) ) {
			return;
		}
		?>
		<table class="form-table">
			<tr>
				<th>
					<label><?php _e( 'Delete MFA Provider' ); ?></label>
				</th>
				<td>
					<input type="button" id="auth0_delete_mfa" class="button button-secondary"
								 value="<?php _e( 'Delete MFA' ); ?>" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * TODO: Deprecate, moved to edit-user-profile.js
	 */
	public function show_change_password() {
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
	 * TODO: Deprecate, moved to edit-user-profile.js
	 */
	public function disable_email_field() {
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
			return;
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
				return;
			}

			if ( ! is_email( $_POST['email'] ) ) {
				$errors->add( 'user_email', __( '<strong>ERROR</strong>: The email address is not correct.', 'wp-auth0' ), array( 'form-field' => 'email' ) );
				return;
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
	 * Get the strategy from an Auth0 user ID.
	 *
	 * @param string $auth0_id - Auth0 user ID.
	 *
	 * @return string
	 */
	private function get_auth0_strategy( $auth0_id ) {
		if ( false === strpos( $auth0_id, '|' ) ) {
			return '';
		}
		$auth0_id_parts = explode( '|', $auth0_id );
		return $auth0_id_parts[0];
	}
}
