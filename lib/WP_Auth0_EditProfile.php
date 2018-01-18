<?php

class WP_Auth0_EditProfile {

	protected $a0_options;
  protected $db_manager;
	protected $users_repo;

	public function __construct( WP_Auth0_DBManager $db_manager, WP_Auth0_UsersRepo $users_repo, WP_Auth0_Options $a0_options ) {
    $this->a0_options = $a0_options;
		$this->users_repo = $users_repo;
		$this->db_manager = $db_manager;
	}

	public function init() {
		global $pagenow;

		add_action( 'personal_options_update', array( $this, 'override_email_update' ), 1 );

    add_action( 'edit_user_profile', array( $this, 'show_delete_identity' ) );
		add_action( 'edit_user_profile', array( $this, 'show_delete_mfa' ) );
    add_action( 'show_user_profile', array( $this, 'show_delete_mfa' ) );

    add_action( 'wp_ajax_auth0_delete_mfa', array( $this, 'delete_mfa' ) );
		add_action( 'wp_ajax_auth0_delete_data', array( $this, 'delete_user_data' ) );

		add_action( 'show_user_profile', array( $this, 'show_change_password' ) );
		add_action( 'personal_options_update', array( $this, 'update_change_password' ) );
		add_filter( 'user_profile_update_errors', array( $this, 'validate_new_password' ), 10, 3 );

		if ( $pagenow == 'profile.php' || $pagenow == 'user-edit.php' ) {
			add_action( 'admin_footer', array( $this, 'disable_email_field' ) );
		}
	}

	public function validate_new_password( $errors, $update, $user ) {
		$auth0_password = isset( $_POST['auth0_password'] ) ? $_POST['auth0_password'] : null;
		$auth0_repeat_password = isset( $_POST['auth0_repeat_password'] ) ? $_POST['auth0_repeat_password'] : null;

		if ( $auth0_password != $auth0_repeat_password ) {
			$errors->add( 'auth0_password', __( '<strong>ERROR</strong>: The password does not match', 'wp-auth0' ), array( 'form-field' => 'auth0_password' ) );
		}
	}


	public function update_change_password() {
    $current_user = get_currentauth0user();
    $user_profile = $current_user->auth0_obj;

		if ( empty( $user_profile ) ) return;

		$auth0_password = isset( $_POST['auth0_password'] ) ? $_POST['auth0_password'] : null;
		$auth0_repeat_password = isset( $_POST['auth0_repeat_password'] ) ? $_POST['auth0_repeat_password'] : null;

		if ( !empty( $auth0_password ) && $auth0_password == $auth0_repeat_password ) {
			$domain = $this->a0_options->get( 'domain' );
			$client_id = $this->a0_options->get( 'client_id' );
			$api_token = $this->a0_options->get( 'auth0_app_token' );

			$connection = null;
			$email = null;

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
				WP_Auth0_Api_Client::update_user( $domain, $api_token, $user_profile->user_id, array(
						'password' => $auth0_password,
						'connection' => $connection
					) );
			} else {
				WP_Auth0_Api_Client::change_password( $domain, array(
						'client_id' => $client_id,
						'email' => $user_profile->email,
						'connection' => $connection
					) );
			}
		}
	}

  public function delete_user_data() {
    if ( ! is_admin() ) return;

    $user_id = $_POST["user_id"];

    $this->users_repo->delete_auth0_object( $user_id );
  }

	public function delete_mfa() {
		if ( ! is_admin() ) return;

		$user_id = $_POST["user_id"];

		$users = $this->db_manager->get_auth0_users( array( $user_id ) );
		if ( empty( $users ) ) return;

		$user_id = $users[0]->auth0_id;

		$provider = 'google-authenticator';
		$domain = $this->a0_options->get( 'domain' );
		$app_token = $this->a0_options->get( 'auth0_app_token' );

		WP_Auth0_Api_Client::delete_user_mfa( $domain, $app_token, $user_id, $provider );
	}

  public function show_delete_identity() {
    if ( ! is_admin() ) return;
    if ( ! get_auth0userinfo( $_GET['user_id'] ) ) return;
    ?>
    <table class="form-table">
    <tr>
      <th>
        <label><?php _e( 'Delete Auth0 data' ); ?></label>
      </th>
      <td>
        <input type="button" onclick="DeleteAuth0Data(event);" name="auth0_delete_data" id="auth0_delete_data" value="Delete Auth0 Data" class="button button-secondary" />
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

      jQuery('#auth0_delete_data').attr('disabled', 'true');

      jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

        jQuery('#auth0_delete_data').val('Done!').attr('disabled', 'true');

      }, 'json');

    }
    </script>
  <?php
  }
	public function show_delete_mfa() {
		if ( ! is_admin() ) return;
		if ( ! $this->a0_options->get( 'mfa' ) ) return;

?>
    <table class="form-table">
    <tr>
      <th>
        <label><?php _e( 'Delete MFA Provider' ); ?></label>
      </th>
      <td>
        <input type="button" onclick="DeleteMFA(event);" name="auth0_delete_mfa" id="auth0_delete_mfa" value="Delete MFA" class="button button-secondary" />
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

      jQuery('#auth0_delete_mfa').attr('disabled', 'true');

      jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

        jQuery('#auth0_delete_mfa').val('Done!').attr('disabled', 'true');

      }, 'json');

    }
  </script>

  <?php
	}

	public function show_change_password() {
    $current_user = get_currentauth0user();
    $user_profile = $current_user->auth0_obj;

		if ( empty( $user_profile ) ) return;

		$connection = null;

		foreach ( $user_profile->identities as $identity ) {
			if ( $identity->provider === 'auth0' ) {
				$connection = $identity->connection;
			}
		}

		if ( $connection === null ) return;
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

	public function disable_email_field() {
    $current_user = get_currentauth0user();
    $user_profile = $current_user->auth0_obj;

		if ( $user_profile && !empty( $user_profile ) ) {
			$connection = null;

			foreach ( $user_profile->identities as $identity ) {
				if ( $identity->provider === 'auth0' ) {
					$connection = $identity->connection;
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

              $('<span class="description">You can\'t change your email here if you logged in using a social connection.</span>').insertAfter(newEmailElement);
            }
          });
        </script>
        <?php
			}
		}
	}

	public function override_email_update() {
		global $wpdb;
		global $errors;

		if ( ! is_object( $errors ) ) {
			$errors = new WP_Error();
		}

    $current_user = wp_get_current_user();
    $user_profile =  get_currentauth0userinfo();

		$app_token = $this->a0_options->get( 'auth0_app_token' );;

		if ( !$app_token ) {
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
				if ($identity->provider === 'auth0') {
					$connection = $identity->connection;
				}
			}

			if ( $connection === null ) {
				$errors->add( 'user_email', __( "<strong>ERROR</strong>: You can't change your email if you are using a social connection.", "wp-auth0" ), array( 'form-field' => 'email' ) );
				return false;
			}

			if ( ! is_email( $_POST['email'] ) ) {
				$errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address isn&#8217;t correct.", "wp-auth0" ), array( 'form-field' => 'email' ) );
				return false;
			}

			if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE user_email=%s", $_POST['email'] ) ) ) {
				$errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address is already used.", "wp-auth0" ), array( 'form-field' => 'email' ) );
				delete_option( $current_user->ID . '_new_email' );
				return;
			}

			$user_email = esc_html( trim( $_POST['email'] ) );

			$user_id = $user_profile->user_id;
			$client_id = $this->a0_options->get('client_id');
			$domain = $this->a0_options->get('domain');
			$requires_verified_email = $this->a0_options->get('requires_verified_email');

			$response = WP_Auth0_Api_Client::update_user($domain, $app_token, $user_id, array(
					'connection' => $connection,
					'email' => $user_email,
					'client_id' => $client_id,
					'verify_email' => ($requires_verified_email == 1)
				));

			if ($response !== false) {

				if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $current_user->user_login ) ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST['email'], $current_user->user_login ) );
				}
				wp_update_user( array(
						'ID' => $current_user->ID,
						'user_email' => $user_email
					) );

				if ($requires_verified_email) {
					wp_logout();
				}
			}
		}
	}

}
