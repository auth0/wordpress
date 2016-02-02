<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

  const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

  protected $actions_middlewares = array(
    'basic_validation',
    'migration_ws_validation',
    'link_accounts_validation',
    'loginredirection_validation',
  );

  protected $router;
  
  public function __construct(WP_Auth0_Options_Generic $options, WP_Auth0_Routes $router) {
    parent::__construct($options);
    $this->router = $router;
  }

  public function init() {

    $advancedOptions = array(

      array( 'id' => 'wpa0_remember_users_session', 'name' => 'Remember users session', 'function' => 'render_remember_users_session' ),
      array( 'id' => 'wpa0_link_auth0_users', 'name' => 'Link users with same email', 'function' => 'render_link_auth0_users' ),
      
      array( 'id' => 'wpa0_social_twitter_key', 'name' => 'Twitter consumer key', 'function' => 'render_social_twitter_key' ),
      array( 'id' => 'wpa0_social_twitter_secret', 'name' => 'Twitter consumer secret', 'function' => 'render_social_twitter_secret' ),
      
      array( 'id' => 'wpa0_social_facebook_key', 'name' => 'Facebook app key', 'function' => 'render_social_facebook_key' ),
      array( 'id' => 'wpa0_social_facebook_secret', 'name' => 'Facebook app secret', 'function' => 'render_social_facebook_secret' ),
      
      array( 'id' => 'wpa0_migration_ws', 'name' => 'Users Migration', 'function' => 'render_migration_ws' ),
      array( 'id' => 'wpa0_migration_ws_ips_filter', 'name' => 'Migration IPs whitelist', 'function' => 'render_migration_ws_ips_filter' ),
      array( 'id' => 'wpa0_auth0_implicit_workflow', 'name' => 'Auth0 Implicit flow', 'function' => 'render_auth0_implicit_workflow' ),
      array( 'id' => 'wpa0_default_login_redirection', 'name' => 'Login redirection URL', 'function' => 'render_default_login_redirection' ),
      array( 'id' => 'wpa0_verified_email', 'name' => 'Requires verified email', 'function' => 'render_verified_email' ),
      array( 'id' => 'wpa0_auto_login', 'name' => 'Auto Login (no widget)', 'function' => 'render_auto_login' ),
      array( 'id' => 'wpa0_auto_login_method', 'name' => 'Auto Login Method', 'function' => 'render_auto_login_method' ),
      array( 'id' => 'wpa0_ip_range_check', 'name' => 'Enable on IP Ranges', 'function' => 'render_ip_range_check' ),
      array( 'id' => 'wpa0_ip_ranges', 'name' => 'IP Ranges', 'function' => 'render_ip_ranges' ),
      array( 'id' => 'wpa0_valid_proxy_ip', 'name' => 'Valid Proxy IP', 'function' => 'render_valid_proxy_ip' ),
      array( 'id' => 'wpa0_extra_conf', 'name' => 'Extra settings', 'function' => 'render_extra_conf' ),
      array( 'id' => 'wpa0_cdn_url', 'name' => 'Widget URL', 'function' => 'render_cdn_url' ),
      array( 'id' => 'wpa0_metrics', 'name' => 'Anonymous data', 'function' => 'render_metrics' ),

    );

    if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
      $advancedOptions[] = array( 'id' => 'wpa0_jwt_auth_integration', 'name' => 'Enable JWT Auth integration', 'function' => 'render_jwt_auth_integration' );
    }

    $this->init_option_section( '', 'advanced', $advancedOptions );
  }

  public function render_jwt_auth_integration() {
    $v = absint( $this->options->get( 'jwt_auth_integration' ) );

    echo $this->render_a0_switch("wpa0_jwt_auth_integration", "jwt_auth_integration", 1, 1 == $v);
  ?>
    <div class="subelement">
      <span class="description"><?php echo __( 'This will enable the JWT Auth\'s Users Repository override.', WPA0_LANG ); ?></span>
    </div>
  <?php
  }

  public function render_default_login_redirection() {
    $v = $this->options->get( 'default_login_redirection' );
    ?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[default_login_redirection]" id="wpa0_default_login_redirection" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the URL that all users will be redirected by default after login', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_extra_conf() {
    $v = $this->options->get( 'extra_conf' );
    ?>

    <textarea name="<?php echo $this->options->get_options_name(); ?>[extra_conf]" id="wpa0_extra_conf"><?php echo esc_attr( $v ); ?></textarea>
    <div class="subelement">
      <span class="description">
        <?php echo __('This field is the Json that describes the options to call Lock with. It\'ll override any other option set here. See all the posible options ', WPA0_LANG); ?>
        <a target="_blank" href="https://auth0.com/docs/libraries/lock/customization"><?php echo __('here', WPA0_LANG); ?></a>
        <?php echo __('(For example: {"disableResetAction": true }) ', WPA0_LANG); ?>
      </span>
    </div>
    <?php
  }

  public function render_link_auth0_users() {
    $v = $this->options->get( 'link_auth0_users' );

    echo $this->render_a0_switch("wpa0_link_auth0_users", "link_auth0_users", 1, ! empty($v));
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'To enable the link of accounts with the same email. It will only occur if the email was verified before.', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_remember_users_session() {
    $v = $this->options->get( 'remember_users_session' );

    echo $this->render_a0_switch("wpa0_remember_users_session", "remember_users_session", 1, 1 == $v);
    ?>
      
      <div class="subelement">
        <span class="description"><?php echo __( 'Users session by default lives for two days. Enabling this setting will make the sessions be kept for 14 days.', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_migration_ws() {
    $v = $this->options->get( 'migration_ws' );
    $token = $this->options->get( 'migration_token' );

    echo $this->render_a0_switch("wpa0_auth0_migration_ws", "migration_ws", 1, 1 == $v);

    if ($v) {
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Users migration is enabled. If you disable this setting, it can not be automatically enabled again, it needs to be done manually in the Auth0 dashboard.', WPA0_LANG ); ?></span>
        <br>
        <span class="description"><?php echo __( 'Security token:', WPA0_LANG ); ?></span>
        <textarea class="code" disabled><?php echo $token; ?></textarea>
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

  public function render_migration_ws_ips_filter() {
    $v = $this->options->get( 'migration_ips_filter' );
    $list = $this->options->get( 'migration_ips' );

    echo $this->render_a0_switch("wpa0_auth0_migration_ips_filter", "migration_ips_filter", 1, 1 == $v);

    ?>
      <div class="subelement">
        <textarea name="migration_ips_filter"><?php echo $list; ?></textarea>
        <span class="description"><?php echo __( 'Only requests from this IPs will be allowed to the migration WS.', WPA0_LANG ); ?></span>
      </div>
    <?php

  }

  public function render_auth0_implicit_workflow() {
    $v = absint( $this->options->get( 'auth0_implicit_workflow' ) );

    echo $this->render_a0_switch("wpa0_auth0_implicit_workflow", "auth0_implicit_workflow", 1, 1 == $v);
    ?>

    <div class="subelement">
      <span class="description"><?php echo __( 'Mark this to change the login workflow to allow the plugin work when the server does not have internet access)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login() {
    $v = absint( $this->options->get( 'auto_login' ) );

    echo $this->render_a0_switch("wpa0_auto_login", "auto_login", 1, 1 == $v);
    ?>
    
    <div class="subelement">
      <span class="description"><?php echo __( 'Mark this to avoid the login page (you will have to select a single login provider)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login_method() {
    $v = $this->options->get( 'auto_login_method' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[auto_login_method]" id="wpa0_auto_login_method" value="<?php echo esc_attr( $v ); ?>"/>
    <div class="subelement">
      <span class="description"><?php echo __( 'To find the method name, log into Auth0 Dashboard, and navigate to: Connection -> [Connection Type] (eg. Social or Enterprise). Click the "down arrow" to expand the wanted method, and use the value in the "Name"-field. Example: google-oauth2', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_ip_range_check() {
    $v = absint( $this->options->get( 'ip_range_check' ) );

    echo $this->render_a0_switch("wpa0_ip_range_check", "ip_range_check", 1, 1 == $v);
  }

  public function render_ip_ranges() {
    $v = $this->options->get( 'ip_ranges' );
    ?>
    <textarea cols="25" name="<?php echo $this->options->get_options_name(); ?>[ip_ranges]" id="wpa0_ip_ranges"><?php echo esc_textarea( $v ); ?></textarea>
    <div class="subelement">
      <span class="description"><?php echo __( 'Only one range per line! Range format should be as: <code>xx.xx.xx.xx - yy.yy.yy.yy</code> (spaces will be trimmed)', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_social_twitter_key() {
    $v = $this->options->get_connection( 'social_twitter_key' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[social_twitter_key]" id="wpa0_social_twitter_key" value="<?php echo esc_attr( $v ); ?>"/>
    <?php
  }
  public function render_social_twitter_secret() {
    $v = $this->options->get_connection( 'social_twitter_secret' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[social_twitter_secret]" id="wpa0_social_twitter_secret" value="<?php echo esc_attr( $v ); ?>"/>
    <?php
  }

  public function render_social_facebook_key() {
    $v = $this->options->get_connection( 'social_facebook_key' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[social_facebook_key]" id="wpa0_social_facebook_key" value="<?php echo esc_attr( $v ); ?>"/>
    <?php
  }
  public function render_social_facebook_secret() {
    $v = $this->options->get_connection( 'social_facebook_secret' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[social_facebook_secret]" id="wpa0_social_facebook_secret" value="<?php echo esc_attr( $v ); ?>"/>
    <?php
  }

  public function render_valid_proxy_ip() {
    $v = $this->options->get( 'valid_proxy_ip' );
    ?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[valid_proxy_ip]" id="wpa0_valid_proxy_ip" value="<?php echo esc_attr( $v ); ?>"/>
    <div class="subelement">
      <span class="description"><?php echo __( 'If you are using a load balancer or a proxy, you will need to whitelist its ip in order to enable ip checks for logins or migration webservices.', WPA0_LANG ); ?></span>
    </div>
    <?php
  }

  public function render_cdn_url() {
    $v = $this->options->get( 'cdn_url' );
    ?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[cdn_url]" id="wpa0_cdn_url" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Point this to the latest widget available in the CDN', WPA0_LANG ); ?></span>
      </div>
    <?php
  }
  
  public function render_metrics() {
    $v = absint( $this->options->get( 'metrics' ) );

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
    $v = absint( $this->options->get( 'requires_verified_email' ) );

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
    $input['connections']['social_twitter_key'] = sanitize_text_field( $input['connections']['social_twitter_key'] );
    $input['connections']['social_twitter_secret'] = sanitize_text_field( $input['connections']['social_twitter_secret'] );
    $input['connections']['social_facebook_key'] = sanitize_text_field( $input['connections']['social_facebook_key'] );
    $input['connections']['social_facebook_secret'] = sanitize_text_field( $input['connections']['social_facebook_secret'] );

    $input['migration_ips_filter'] =  ( isset( $input['migration_ips_filter'] ) ? $input['migration_ips_filter'] : 0 );
    $input['migration_ips'] = sanitize_text_field($old_options['migration_ips']);

    $input['valid_proxy_ip'] = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );

    if (trim($input["extra_conf"]) != '')
    {
      if (json_decode($input["extra_conf"]) === null)
      {
        $error = __("The Extra settings parameter should be a valid json object", WPA0_LANG);
        self::add_validation_error($error);
      }
    }

    return $input;
  }

  public function migration_ws_validation( $old_options, $input ) {
    $input['migration_ws'] = ( isset( $input['migration_ws'] ) ? $input['migration_ws'] : 0 );

    if ( $old_options['migration_ws'] != $input['migration_ws'] ) {

      if ( 1 == $input['migration_ws'] ) {
        $secret = $input['client_secret'];
        $token_id = uniqid();
        $input['migration_token'] = JWT::encode(array('scope' => 'migration_ws', 'jti' => $token_id), JWT::urlsafeB64Decode( $secret ));
        $input['migration_token_id'] = $token_id;

        if ($response === false) {
          $error = __( 'There was an error enabling your custom database. Check how to do it manually ', WPA0_LANG );
          $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
          $this->add_validation_error( $error );
        }

      } else {
        $input['migration_token'] = null;
        $input['migration_token_id'] = null;

        $response = WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $old_options['db_connection_id'], array(
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
    } else {
      $input['migration_token'] = $old_options['migration_token'];
      $input['migration_token_id'] = $old_options['migration_token_id'];
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
