<?php

class WP_Auth0_EditProfile {

  protected $a0_options;
  protected $db_manager;

  public function __construct(WP_Auth0_DBManager $db_manager,WP_Auth0_Options $a0_options) {
    $this->a0_options = $a0_options;
    $this->db_manager = $db_manager;
  }

  public function init() {
    global $pagenow;

    add_action( 'personal_options_update', array( $this, 'override_email_update' ), 1 );

    add_action( 'show_user_profile', array( $this, 'show_change_password' ));
    add_action( 'personal_options_update', array( $this, 'update_change_password' ) );
    add_filter( 'user_profile_update_errors', array( $this, 'validate_new_password' ), 10, 3);

    if ( $pagenow == 'profile.php' ) {
      add_action( 'admin_footer', array( $this, 'disable_email_field' ) );
    }
  }


  public function validate_new_password($errors, $update, $user){
    $auth0_password = $_POST['auth0_password'];
    $auth0_repeat_password = $_POST['auth0_repeat_password'];

    if (empty($auth0_password)) {
      $errors->add( 'auth0_password', __('<strong>ERROR</strong>: The password can not be empty'), array( 'form-field' => 'auth0_password' ) );
    }
    if ($auth0_password != $auth0_repeat_password) {
      $errors->add( 'auth0_password', __('<strong>ERROR</strong>: The password does not match'), array( 'form-field' => 'auth0_password' ) );
    }
  }


  public function update_change_password() { 
    $user_profiles = $this->db_manager->get_current_user_profiles();

    if (empty($user_profiles)) return;

    $auth0_password = $_POST['auth0_password'];
    $auth0_repeat_password = $_POST['auth0_repeat_password'];

    if (empty($auth0_password) || $auth0_password == $auth0_repeat_password) {
      $domain = $this->a0_options->get('domain');
      $client_id = $this->a0_options->get('client_id');

      $user_profile = $user_profiles[0];
      $connection = null;
      $email = null;

      foreach ($user_profile->identities as $identity) {
        if ($identity->provider === 'auth0') {
          $connection = $identity->connection;
          $email = $identity->email;
        }
      }

      WP_Auth0_Api_Client::change_password($domain, array(
        'client_id' => $client_id,
        'email' => $user_profile->email,
        'password' => $auth0_password,
        'connection' => $connection
      ));
    }
  }

  public function show_change_password() { 
    $user_profiles = $this->db_manager->get_current_user_profiles();

    if (empty($user_profiles)) return;

    $user_profile = $user_profiles[0];
    $connection = null;

    foreach ($user_profile->identities as $identity) {
      if ($identity->provider === 'auth0') {
        $connection = $identity->connection;
      }
    }

    if ($connection === null) return;
  ?>
    <script>
      jQuery('.wp-pwd').parent().parent().hide();
    </script>
    <table class="form-table">
    <tr>
      <th>
        <label for="auth0_password"><?php _e('New Password'); ?></label>
      </th>
      <td>
        <input type="password" name="auth0_password" id="auth0_password" value="" class="regular-text" />
      </td>
    </tr>
     <tr>
      <th>
        <label for="auth0_repeat_password"><?php _e('Repeat Password'); ?></label>
      </th>
      <td>
        <input type="password" name="auth0_repeat_password" id="auth0_repeat_password" value="" class="regular-text" />
      </td>
    </tr>

    </table>
  <?php
  }  

  public function disable_email_field() {

    $user_profiles = $this->db_manager->get_current_user_profiles();

    if (!empty($user_profiles)) {
      $user_profile = $user_profiles[0];
      $connection = null;

      foreach ($user_profile->identities as $identity) {
        if ($identity->provider === 'auth0') {
          $connection = $identity->connection;
        }
      }

      if ( $connection === null){
        ?>
        <script>
          jQuery(document).ready( function($) {
            if ( $('input[name=email]').length ) {
              $('input[name=email]').attr("disabled", "disabled");
              $('<span class="description">You can\'t change your email here if you logged in using a social connection.</span>').insertAfter($('input[name=email]'));
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

    if ( ! is_object($errors) ) {
      $errors = new WP_Error();
    }

    $current_user = wp_get_current_user();
    $app_token = $this->a0_options->get( 'auth0_app_token' );;

    if (!$app_token) {
      return;
    }

    if ( $current_user->ID != $_POST['user_id'] ) {
      return false;
    }

    $user_profiles = $this->db_manager->get_current_user_profiles();

    if (empty($user_profiles)) {
      return;
    }

    $user_profile = $user_profiles[0];

    if ( $current_user->user_email != $_POST['email'] ) {

      $connection = null;

      foreach ($user_profile->identities as $identity) {
        if ($identity->provider === 'auth0') {
          $connection = $identity->connection;
        }
      }

      if ( $connection === null ) {
        $errors->add( 'user_email', __( "<strong>ERROR</strong>: You can't change your email if you are using a social connection." ), array( 'form-field' => 'email' ) );
        return false;
      }

      if ( ! is_email( $_POST['email'] ) ) {
        $errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address isn&#8217;t correct." ), array( 'form-field' => 'email' ) );
        return false;
      }

      if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE user_email=%s", $_POST['email'] ) ) ) {
        $errors->add( 'user_email', __( "<strong>ERROR</strong>: The email address is already used." ), array( 'form-field' => 'email' ) );
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
          'user_email' => $user_email,
        ) );

        if ($requires_verified_email) {
          wp_logout();
        }
      }
    }
  }

}
