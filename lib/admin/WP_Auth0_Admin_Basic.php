<?php

class WP_Auth0_Admin_Basic extends WP_Auth0_Admin_Generic {

  const BASIC_DESCRIPTION = 'Basic settings related to auth0 credentials and basic WordPress integration.';

  protected $actions_middlewares = array(
    'basic_validation',
    'basicdata_validation',
  );

  public function init() {

    /* ------------------------- BASIC ------------------------- */

    $this->init_option_section( '', 'basic', array(

      array( 'id' => 'wpa0_domain', 'name' => 'Domain', 'function' => 'render_domain' ),
      array( 'id' => 'wpa0_client_id', 'name' => 'Client ID', 'function' => 'render_client_id' ),
      array( 'id' => 'wpa0_client_secret', 'name' => 'Client Secret', 'function' => 'render_client_secret' ),
      // array( 'id' => 'wpa0_auth0_app_token', 'name' => 'App token', 'function' => 'render_auth0_app_token' ), //we are not going to show the token
      array( 'id' => 'wpa0_login_enabled', 'name' => 'WordPress login enabled', 'function' => 'render_allow_wordpress_login' ),
      array( 'id' => 'wpa0_allow_signup', 'name' => 'Allow signup', 'function' => 'render_allow_signup' ),

    ) );

    $options_name = $this->options->get_options_name();
    register_setting( $options_name . '_basic', $options_name, array( $this, 'input_validator' ) );
  }


  public function render_client_id() {
    $v = $this->options->get( 'client_id' );
    ?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[client_id]" id="wpa0_client_id" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Application ID, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_auth0_app_token() {
    $v = $this->options->get( 'auth0_app_token' );
    ?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[auth0_app_token]" id="wpa0_auth0_app_token" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description">
          <?php echo __( 'The token should be generated via the ', WPA0_LANG ); ?>
          <a href="https://auth0.com/docs/api/v2" target="_blank"><?php echo __( 'token generator', WPA0_LANG ); ?></a>
          <?php echo __( ' with the following scopes:', WPA0_LANG ); ?>
          <code>create:clients</code> <?php echo __( 'and', WPA0_LANG ); ?> <code>read:connection</code>.
        </span>
      </div>
    <?php
  }

  public function render_client_secret() {
    $v = $this->options->get( 'client_secret' );
    ?>
      <input type="text" autocomplete="off" name="<?php echo $this->options->get_options_name(); ?>[client_secret]" id="wpa0_client_secret" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Application secret, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_domain() {
    $v = $this->options->get( 'domain' );
    ?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[domain]" id="wpa0_domain" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Your Auth0 domain, you can see it in the dashboard. Example: foo.auth0.com', WPA0_LANG ); ?></span>
      </div>
    <?php
  }


  public function render_allow_signup() {
    if (is_multisite()) {
      $this->render_allow_signup_regular_multisite();
    } else {
      $this->render_allow_signup_regular();
    }
  }

  public function render_allow_signup_regular_multisite() {
    $allow_signup = $this->options->is_wp_registration_enabled();
    ?>
      <span class="description">
        <?php echo __( 'Signup will be', WPA0_LANG ); ?>

        <?php if ( ! $allow_signup ) { ?>
          <b><?php echo __( 'disabled', WPA0_LANG ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Allow new registrations" in the Network Admin.', WPA0_LANG ); ?>
        <?php } else { ?>
          <b><?php echo __( 'enabled', WPA0_LANG ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Allow new registrations" in the Network Admin.', WPA0_LANG ); ?>
        <?php } ?>

        <?php echo __( 'You can manage this setting on <code>Network Admin > Settings > Network Settings > Allow new registrations</code> (you need to set it up to <b>User accounts may be registered</b> or <b>Both sites and user accounts can be registered</b> depending on your preferences).', WPA0_LANG ); ?>
      </span>

    <?php
  }

  public function render_allow_signup_regular() {
    $allow_signup = $this->options->is_wp_registration_enabled();
    ?>
      <span class="description">
        <?php echo __( 'Signup will be', WPA0_LANG ); ?>

        <?php if ( ! $allow_signup ) { ?>
          <b><?php echo __( 'disabled', WPA0_LANG ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Anyone can register" in the WordPress General Settings.', WPA0_LANG ); ?>
        <?php } else { ?>
          <b><?php echo __( 'enabled', WPA0_LANG ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Anyone can register" in the WordPress General Settings.', WPA0_LANG ); ?>
        <?php } ?>

        <?php echo __( 'You can manage this setting on <code>Settings > General > Membership</code>, Anyone can register', WPA0_LANG ); ?>
      </span>

    <?php
  }

  public function render_allow_wordpress_login () {
    $v = absint( $this->options->get( 'wordpress_login_enabled' ) );

    echo $this->render_a0_switch("wpa0_wp_login_enabled", "wordpress_login_enabled", 1, 1 == $v);
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you want to enable the regular WordPress login', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_basic_description() {
    ?>

    <p class=\"a0-step-text\"><?php echo self::BASIC_DESCRIPTION; ?></p>

    <?php
  }

  public function basic_validation( $old_options, $input ) {
    $input['client_id'] = sanitize_text_field( $input['client_id'] );
    $input['client_secret'] = sanitize_text_field( $input['client_secret'] );
    $input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] ) ? $input['wordpress_login_enabled'] : 0 );
    $input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );
    $input['auth0_app_token'] = (isset($input['auth0_app_token']) ? $input['auth0_app_token'] : $old_options['auth0_app_token']);

    return $input;
  }

  public function basicdata_validation( $old_options, $input ) {
    $error = '';
    $completeBasicData = true;
    if ( empty( $input['domain'] ) ) {
      $error = __( 'You need to specify domain', WPA0_LANG );
      $this->add_validation_error( $error );
      $completeBasicData = false;
    }

    if ( empty( $input['client_id'] ) ) {
      $error = __( 'You need to specify a client id', WPA0_LANG );
      $this->add_validation_error( $error );
      $completeBasicData = false;
    }
    if ( empty( $input['client_secret'] ) ) {
      $error = __( 'You need to specify a client secret', WPA0_LANG );
      $this->add_validation_error( $error );
      $completeBasicData = false;
    }

    if ( $completeBasicData ) {
      $response = WP_Auth0_Api_Client::get_token( $input['domain'], $input['client_id'], $input['client_secret'] );

      if ( $response instanceof WP_Error ) {
        $error = $response->get_error_message();
        $this->add_validation_error( $error );
      } elseif ( 200 !== (int) $response['response']['code'] ) {
        $error = __( 'The client id or secret is not valid.', WPA0_LANG );
        $this->add_validation_error( $error );
      }
    }
    return $input;
  }


}
