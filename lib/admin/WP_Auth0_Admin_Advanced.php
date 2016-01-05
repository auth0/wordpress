<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

  const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

  protected $actions_middlewares = array(
    'basic_validation',
    'migration_ws_validation',
    'link_accounts_validation',
    'loginredirection_validation',
  );

  public function init() {

    $advancedOptions = array(

      array( 'id' => 'wpa0_remember_users_session', 'name' => 'Remember users session', 'function' => 'render_remember_users_session' ),
      array( 'id' => 'wpa0_link_auth0_users', 'name' => 'Link users with same email', 'function' => 'render_link_auth0_users' ),
      array( 'id' => 'wpa0_migration_ws', 'name' => 'Users Migration', 'function' => 'render_migration_ws' ),
      array( 'id' => 'wpa0_auth0_implicit_workflow', 'name' => 'Auth0 Implicit flow', 'function' => 'render_auth0_implicit_workflow' ),
      array( 'id' => 'wpa0_default_login_redirection', 'name' => 'Login redirection URL', 'function' => 'render_default_login_redirection' ),
      array( 'id' => 'wpa0_verified_email', 'name' => 'Requires verified email', 'function' => 'render_verified_email' ),
      array( 'id' => 'wpa0_auto_login', 'name' => 'Auto Login (no widget)', 'function' => 'render_auto_login' ),
      array( 'id' => 'wpa0_auto_login_method', 'name' => 'Auto Login Method', 'function' => 'render_auto_login_method' ),
      array( 'id' => 'wpa0_ip_range_check', 'name' => 'Enable on IP Ranges', 'function' => 'render_ip_range_check' ),
      array( 'id' => 'wpa0_ip_ranges', 'name' => 'IP Ranges', 'function' => 'render_ip_ranges' ),
      array( 'id' => 'wpa0_cdn_url', 'name' => 'Widget URL', 'function' => 'render_cdn_url' ),
      array( 'id' => 'wpa0_metrics', 'name' => 'Anonymous data', 'function' => 'render_metrics' ),

    );

    if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
      $advancedOptions[] = array( 'id' => 'wpa0_jwt_auth_integration', 'name' => 'Enable JWT Auth integration', 'function' => 'render_jwt_auth_integration' );
    }

    $this->init_option_section( '', 'advanced', $advancedOptions );

    $options_name = $this->a0_options->get_options_name();
    register_setting( $options_name . '_advanced', $options_name, array( $this, 'input_validator' ) );
  }

  public function render_jwt_auth_integration() {
    $v = absint( $this->a0_options->get( 'jwt_auth_integration' ) );

    echo $this->render_a0_switch("wpa0_jwt_auth_integration", "jwt_auth_integration", 1, 1 == $v);
  ?>
    <div class="subelement">
      <span class="description"><?php echo __( 'This will enable the JWT Auth\'s Users Repository override.', WPA0_LANG ); ?></span>
    </div>
  <?php
  }

  public function render_default_login_redirection() {
    $v = $this->a0_options->get( 'default_login_redirection' );
    ?>
      <input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[default_login_redirection]" id="wpa0_default_login_redirection" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the URL that all users will be redirected by default after login', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_link_auth0_users() {
    $v = $this->a0_options->get( 'link_auth0_users' );

    echo $this->render_a0_switch("wpa0_link_auth0_users", "link_auth0_users", 1, ! empty($v));
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'To enable the link of accounts with the same email. It will only occur if the email was verified before.', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_remember_users_session() {
    $v = $this->a0_options->get( 'remember_users_session' );

    echo $this->render_a0_switch("wpa0_remember_users_session", "remember_users_session", 1, 1 == $v);
    ?>
      
      <div class="subelement">
        <span class="description"><?php echo __( 'Users session by default lives for two days. Enabling this setting will make the sessions live longer.', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_migration_ws() {
    $v = $this->a0_options->get( 'migration_ws' );
    $token = $this->a0_options->get( 'migration_token' );

    echo $this->render_a0_switch("wpa0_auth0_migration_ws", "migration_ws", 1, 1 == $v);

    if ($v) {
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Users migration is enabled. If you disable this setting, it can not be automatically enabled again, it needs to be done manually in the Auth0 dashboard.', WPA0_LANG ); ?></span>
        <br><span class="description"><?php echo __( 'Security token:', WPA0_LANG ); ?><code><?php echo $token; ?></code></span>
      </div>
    <?php
    } else {
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Users migration is disabled. Enabling it will expose the migration webservices but the connection need to be updated manually on the Auth0 dashboard.', WPA0_LANG ); ?></span>
      </div>
    <?php
    }

  }

  public function render_auth0_implicit_workflow() {
    $v = absint( $this->a0_options->get( 'auth0_implicit_workflow' ) );

    echo $this->render_a0_switch("wpa0_auth0_implicit_workflow", "auth0_implicit_workflow", 1, 1 == $v);
    ?>

    <div class="subelement">
      <span class="description"><?php echo __( 'Mark this to change the login workflow to allow the plugin work when the server does not have internet access)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login() {
    $v = absint( $this->a0_options->get( 'auto_login' ) );

    echo $this->render_a0_switch("wpa0_auto_login", "auto_login", 1, 1 == $v);
    ?>
    
    <div class="subelement">
      <span class="description"><?php echo __( 'Mark this to avoid the login page (you will have to select a single login provider)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login_method() {
    $v = $this->a0_options->get( 'auto_login_method' );
    ?>
    <input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[auto_login_method]" id="wpa0_auto_login_method" value="<?php echo esc_attr( $v ); ?>"/>
    <div class="subelement">
      <span class="description"><?php echo __( 'To find the method name, log into Auth0 Dashboard, and navigate to: Connection -> [Connection Type] (eg. Social or Enterprise). Click the "down arrow" to expand the wanted method, and use the value in the "Name"-field. Example: google-oauth2', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_ip_range_check() {
    $v = absint( $this->a0_options->get( 'ip_range_check' ) );

    echo $this->render_a0_switch("wpa0_ip_range_check", "ip_range_check", 1, 1 == $v);
  }

  public function render_ip_ranges() {
    $v = $this->a0_options->get( 'ip_ranges' );
    ?>
    <textarea cols="25" name="<?php echo $this->a0_options->get_options_name(); ?>[ip_ranges]" id="wpa0_ip_ranges"><?php echo esc_textarea( $v ); ?></textarea>
    <div class="subelement">
      <span class="description"><?php echo __( 'Only one range per line! Range format should be as: <code>xx.xx.xx.xx - yy.yy.yy.yy</code> (spaces will be trimmed)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_cdn_url() {
    $v = $this->a0_options->get( 'cdn_url' );
    ?>
      <input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[cdn_url]" id="wpa0_cdn_url" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Point this to the latest widget available in the CDN', WPA0_LANG ); ?></span>
      </div>
    <?php
  }
  
  public function render_metrics() {
    $v = absint( $this->a0_options->get( 'metrics' ) );

    echo $this->render_a0_switch("wpa0_metrics", "metrics", 1, 1 == $v);
    ?>
      
      <div class="subelement">
        <span class="description">
          <?php echo __( 'This plugin tracks anonymous usage data. Click to disable.', WPA0_LANG ); ?>
        </span>
      </div>
    <?php
  }

  public function render_verified_email() {
    $v = absint( $this->a0_options->get( 'requires_verified_email' ) );

    echo $this->render_a0_switch("wpa0_verified_email", "requires_verified_email", 1, 1 == $v);
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you require the user to have a verified email to login', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_advanced_description() {
    ?>

    <p class=\"a0-step-text\"><?php echo self::ADVANCED_DESCRIPTION; ?></p>

    <?php
  }

  public function basic_validation( $old_options, $input ) {
    $input['requires_verified_email'] = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
    $input['remember_users_session'] = ( isset( $input['remember_users_session'] ) ? $input['remember_users_session'] : 0 ) == 1;
    $input['jwt_auth_integration'] = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
    $input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
    $input['metrics'] = ( isset( $input['metrics'] ) ? $input['metrics'] : 0 );
    $input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );

    return $input;
  }

  public function migration_ws_validation( $old_options, $input ) {
    $input['migration_ws'] = ( isset( $input['migration_ws'] ) ? $input['migration_ws'] : 0 );

    if ( $old_options['migration_ws'] != $input['migration_ws'] ) {

      if ( 1 == $input['migration_ws'] ) {
        $secret = $this->a0_options->get( 'client_secret' );
        $token_id = uniqid();
        $input['migration_token'] = JWT::encode(array('scope' => 'migration_ws', 'jti' => $token_id), JWT::urlsafeB64Decode( $secret ));
        $input['migration_token_id'] = $token_id;

        // avoid creating a new connection, it needs to be done manually
        // $operations = new WP_Auth0_Api_Operations($this->a0_options);
        // $response = $operations->enable_users_migration($this->a0_options->get( 'auth0_app_token' ), $input['migration_token']);

        if ($response === false) {
          $error = __( 'There was an error enabling your custom database. Check how to do it manually ', WPA0_LANG );
          $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
          $this->add_validation_error( $error );
        }

      } else {
        $input['migration_token'] = null;
        $input['migration_token_id'] = null;

        $response = WP_Auth0_Api_Client::update_connection($input['domain'], $this->a0_options->get( 'auth0_app_token' ), $old_options['migration_connection_id'], array(
          'options' => array(
            'enabledDatabaseCustomization' => false,
            'import_mode' => false
          )
        ));

        if ($response === null) {
          $error = __( 'There was an error disabling your custom database. Check how to do it manually ', WPA0_LANG );
          $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
          $this->add_validation_error( $error );
        }
      }

      $this->router->setup_rewrites($input['migration_ws'] == 1);
      flush_rewrite_rules();
    }
    return $input;
  }

  public function link_accounts_validation( $old_options, $input ) {
    $link_script = WP_Auth0_RulesLib::$link_accounts['script'];
    $link_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $link_script);
    $link_script = str_replace('REPLACE_WITH_YOUR_DOMAIN', $input['domain'], $link_script);
    $link_script = str_replace('REPLACE_WITH_YOUR_API_TOKEN', $input['auth0_app_token'], $link_script);
    return $this->rule_validation($old_options, $input, 'link_auth0_users', WP_Auth0_RulesLib::$link_accounts['name'], $link_script);
  }

  public function loginredirection_validation( $old_options, $input ) {
    $home_url = home_url();

    if ( empty( $input['default_login_redirection'] ) ) {
      $input['default_login_redirection'] = $home_url;
    } else {
      if ( strpos( $input['default_login_redirection'], $home_url ) !== 0 ) {
        if ( strpos( $input['default_login_redirection'], 'http' ) === 0 ) {
          $input['default_login_redirection'] = $home_url;
          $error = __( "The 'Login redirect URL' cannot point to a foreign page.", WPA0_LANG );
          $this->add_validation_error( $error );
        }
      }

      if ( strpos( $input['default_login_redirection'], 'action=logout' ) !== false ) {
        $input['default_login_redirection'] = $home_url;

        $error = __( "The 'Login redirect URL' cannot point to the logout page. ", WPA0_LANG );
        $this->add_validation_error( $error );
      }
    }
    return $input;
  }

}
