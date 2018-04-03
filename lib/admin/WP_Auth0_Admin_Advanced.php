<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

  // TODO: Deprecate
  const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

  protected $_description;

  protected $actions_middlewares = array(
    'basic_validation',
    'migration_ws_validation',
    'link_accounts_validation',
    'loginredirection_validation',
  );

  protected $router;

  /**
   * WP_Auth0_Admin_Advanced constructor.
   *
   * @param WP_Auth0_Options_Generic $options
   * @param WP_Auth0_Routes $router
   */
  public function __construct( WP_Auth0_Options_Generic $options, WP_Auth0_Routes $router ) {
    parent::__construct( $options );
    $this->router = $router;
    $this->_description = __( 'Settings related to specific scenarios.', 'wp-auth0' );
  }

  /**
   * All settings in the Advanced tab
   *
   * @see \WP_Auth0_Admin::init_admin
   * @see \WP_Auth0_Admin_Generic::init_option_section
   */
  public function init() {
    $options = array(
      array( 'name' => __( 'Require Verified Email', 'wp-auth0' ), 'opt' => 'requires_verified_email',
        'id' => 'wpa0_verified_email', 'function' => 'render_verified_email' ),
      array( 'name' => __( 'Remember User Session', 'wp-auth0' ), 'opt' => 'remember_users_session',
        'id' => 'wpa0_remember_users_session', 'function' => 'render_remember_users_session' ),
      array( 'name' => __( 'Login Redirection URL', 'wp-auth0' ), 'opt' => 'default_login_redirection',
        'id' => 'wpa0_default_login_redirection', 'function' => 'render_default_login_redirection' ),
      array( 'name' => __( 'Passwordless Login', 'wp-auth0' ), 'opt' => 'passwordless_enabled',
        'id' => 'wpa0_passwordless_enabled', 'function' => 'render_passwordless_enabled' ),
      array( 'name' => __( 'Force HTTPS Callback', 'wp-auth0' ), 'opt' => 'force_https_callback',
        'id' => 'wpa0_force_https_callback', 'function' => 'render_force_https_callback' ),
      array( 'name' => __( 'Lock JS CDN URL', 'wp-auth0' ), 'opt' => 'passwordless_enabled',
        'id' => 'wpa0_cdn_url', 'function' => 'render_cdn_url' ),
      array( 'name' => __( 'Connections to Show', 'wp-auth0' ), 'opt' => 'lock_connections',
        'id' => 'wpa0_connections', 'function' => 'render_connections' ),
      array( 'name' => __( 'Link Users with Same Email', 'wp-auth0' ), 'opt' => 'link_auth0_users',
        'id' => 'wpa0_link_auth0_users', 'function' => 'render_link_auth0_users' ),
      array( 'name' => __( 'Auto Provisioning', 'wp-auth0' ), 'opt' => 'auto_provisioning',
        'id' => 'wpa0_auto_provisioning', 'function' => 'render_auto_provisioning' ),
      array( 'name' => __( 'User Migration', 'wp-auth0' ), 'opt' => 'migration_ws',
        'id' => 'wpa0_migration_ws', 'function' => 'render_migration_ws' ),
      array( 'name' => __( 'Migration IPs Whitelist', 'wp-auth0' ), 'opt' => 'migration_ips_filter',
        'id' => 'wpa0_migration_ws_ips_filter', 'function' => 'render_migration_ws_ips_filter' ),
      array( 'name' => __( 'IP Addresses', 'wp-auth0' ), 'opt' => 'migration_ips_filter',
        'id' => 'wpa0_migration_ws_ips', 'function' => 'render_migration_ws_ips' ),
      array( 'name' => __( 'Auto Login', 'wp-auth0' ), 'opt' => 'auto_login',
        'id' => 'wpa0_auto_login', 'function' => 'render_auto_login' ),
      array( 'name' => __( 'Auto Login Method', 'wp-auth0' ), 'opt' => 'auto_login_method',
        'id' => 'wpa0_auto_login_method', 'function' => 'render_auto_login_method' ),
      array( 'name' => __( 'Implicit Login Flow', 'wp-auth0' ), 'opt' => 'auth0_implicit_workflow',
        'id' => 'wpa0_auth0_implicit_workflow', 'function' => 'render_auth0_implicit_workflow' ),
      array( 'name' => __( 'Enable IP Ranges', 'wp-auth0' ), 'opt' => 'ip_range_check',
        'id' => 'wpa0_ip_range_check', 'function' => 'render_ip_range_check' ),
      array( 'name' => __( 'IP Ranges', 'wp-auth0' ), 'opt' => 'ip_ranges',
        'id' => 'wpa0_ip_ranges', 'function' => 'render_ip_ranges' ),
      array( 'name' => __( 'Valid Proxy IP', 'wp-auth0' ), 'opt' => 'valid_proxy_ip',
        'id' => 'wpa0_valid_proxy_ip', 'function' => 'render_valid_proxy_ip' ),
      array( 'name' => __( 'Custom Signup Fields', 'wp-auth0' ), 'opt' => 'custom_signup_fields',
        'id' => 'wpa0_custom_signup_fields', 'function' => 'render_custom_signup_fields' ),
      array( 'name' => __( 'Extra Settings', 'wp-auth0' ), 'opt' => 'extra_conf',
        'id' => 'wpa0_extra_conf', 'function' => 'render_extra_conf' ),
      array( 'name' => __( 'Twitter Consumer Key', 'wp-auth0' ), 'opt' => 'social_twitter_key',
        'id' => 'wpa0_social_twitter_key', 'function' => 'render_social_twitter_key' ),
      array( 'name' => __( 'Twitter Consumer Secret', 'wp-auth0' ), 'opt' => 'social_twitter_secret',
        'id' => 'wpa0_social_twitter_secret', 'function' => 'render_social_twitter_secret' ),
      array( 'name' => __( 'Facebook App Key', 'wp-auth0' ), 'opt' => 'social_facebook_key',
        'id' => 'wpa0_social_facebook_key', 'function' => 'render_social_facebook_key' ),
      array( 'name' => __( 'Facebook App Secret', 'wp-auth0' ), 'opt' => 'social_facebook_secret',
        'id' => 'wpa0_social_facebook_secret', 'function' => 'render_social_facebook_secret' ),
      array( 'name' => __( 'Auth0 Server Domain', 'wp-auth0' ), 'opt' => 'auth0_server_domain',
        'id' => 'wpa0_auth0_server_domain', 'function' => 'render_auth0_server_domain' ),
      array( 'name' => __( 'Report Anonymous Data', 'wp-auth0' ), 'opt' => 'metrics',
        'id' => 'wpa0_metrics', 'function' => 'render_metrics' ),
    );

    if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
      $options[] = array( 'name' => 'Enable JWT Auth Integration', 'opt' => 'jwt_auth_integration',
        'id' => 'wpa0_jwt_auth_integration', 'function' => 'render_jwt_auth_integration' );
    }

    $this->init_option_section( '', 'advanced', $options );
  }

  // TODO: Deprecate
  public function render_passwordless_method() {
    $v = $this->options->get( 'passwordless_method' );
?>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social" value="social" <?php echo checked( $v, 'social', false ); ?>/><label for="wpa0_passwordless_method_social">Social</label>

      <br>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_sms" value="sms" <?php echo checked( $v, 'sms', false ); ?>/><label for="wpa0_passwordless_method_sms">SMS</label>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_sms" value="socialOrSms" <?php echo checked( $v, 'socialOrSms', false ); ?>/><label for="wpa0_passwordless_method_social_sms">Social or SMS</label>

      <br>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_magiclink" value="magiclink" <?php echo checked( $v, 'magiclink', false ); ?>/><label for="wpa0_passwordless_method_magiclink">Magic Link</label>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_magiclink" value="socialOrMagiclink" <?php echo checked( $v, 'socialOrMagiclink', false ); ?>/><label for="wpa0_passwordless_method_social_magiclink">Social or Magic Link</label>

      <br>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_emailcode" value="emailcode" <?php echo checked( $v, 'emailcode', false ); ?>/><label for="wpa0_passwordless_method_emailcode">Email Code</label>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_emailcode" value="socialOrEmailcode" <?php echo checked( $v, 'socialOrEmailcode', false ); ?>/><label for="wpa0_passwordless_method_social_emailcode">Social or Email Code</label>




      <div class="subelement">
        <span class="description">
          <?php echo __( 'For more info about the password policies check ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/password-strength"><?php echo __( 'HERE', 'wp-auth0' ); ?></a>
        </span>
      </div>
    <?php
  }

  public function render_jwt_auth_integration() {
    $v = absint( $this->options->get( 'jwt_auth_integration' ) );

    echo $this->render_a0_switch( "wpa0_jwt_auth_integration", "jwt_auth_integration", 1, 1 == $v );
?>
    <div class="subelement">
      <span class="description"><?php echo __( 'This will enable the JWT Auth\'s Users Repository override.', 'wp-auth0' ); ?></span>
    </div>
  <?php
  }

  public function render_default_login_redirection() {
    $v = $this->options->get( 'default_login_redirection' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[default_login_redirection]" id="wpa0_default_login_redirection" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the URL that all users will be redirected to by default after login.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_extra_conf() {
    $v = $this->options->get( 'extra_conf' );
?>

    <textarea name="<?php echo $this->options->get_options_name(); ?>[extra_conf]" id="wpa0_extra_conf"><?php echo esc_attr( $v ); ?></textarea>
    <div class="subelement">
      <span class="description">
        <?php echo __( 'This field is the Json that describes the options to call Lock with. It\'ll override any other option set here. See all the possible options ', 'wp-auth0' ); ?>
        <a target="_blank" href="https://auth0.com/docs/libraries/lock/customization"><?php echo __( 'here', 'wp-auth0' ); ?></a>
        <?php echo __( '(For example: {"disableResetAction": true }) ', 'wp-auth0' ); ?>
      </span>
    </div>
    <?php
  }

  public function render_custom_signup_fields() {
    $v = $this->options->get( 'custom_signup_fields' );
?>

    <textarea name="<?php echo $this->options->get_options_name(); ?>[custom_signup_fields]" id="wpa0_custom_signup_fields"><?php echo esc_attr( $v ); ?></textarea>
    <div class="subelement">
      <span class="description">
        <?php echo __( 'This field is the Json that describes the custom signup fields for lock. It should be a valida json and allows the use of functions (for validation). More info', 'wp-auth0' ); ?>
        <a target="_blank" href="https://auth0.com/docs/libraries/lock/v10/new-features#custom-sign-up-fields"><?php echo __( 'here', 'wp-auth0' ); ?></a>

        <code><pre>[
  {
    name: "address",                              // required
    placeholder: "enter your address",            // required
    icon: "https://example.com/address_icon.png", // optional
    prefill: "street 123",                        // optional
    validator: function(value) {                  // optional
      // only accept addresses with more than 10 chars
      return value.length > 10;
    }
  },
  {
    ... // more fields could be specified
  }
]</pre></code>
      </span>
    </div>
    <?php
  }

  public function render_link_auth0_users() {
    $v = $this->options->get( 'link_auth0_users' );

    echo $this->render_a0_switch( "wpa0_link_auth0_users", "link_auth0_users", 1, ! empty( $v ) );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Links accounts with the same e-mail address. It will only occur if both e-mails are previously verified.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_auto_provisioning() {
    $v = $this->options->get( 'auto_provisioning' );

    echo $this->render_a0_switch( "wpa0_auto_provisioning", "auto_provisioning", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description"><?php echo __( 'The plugin will automatically add new users if they do not exist in the WordPress database if the signups are enabled (enabling this setting will enable this behaviour when signups are disabled).', 'wp-auth0' ); ?></span>
        </div>
    <?php
  }

  // TODO: Better description when on - social connections, passwordless setup in the dashboard
  public function render_passwordless_enabled() {
    $v = $this->options->get( 'passwordless_enabled' );

    echo $this->render_a0_switch( "wpa0_passwordless_enabled", "passwordless_enabled", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description"><?php echo __( 'This option will replace the login widget with Lock Passwordless (Username and password login will not be enabled).', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_force_https_callback() {
    $v = $this->options->get( 'force_https_callback' );

    echo $this->render_a0_switch( "wpa0_force_https_callback", "force_https_callback", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description"><?php echo __( 'This option forces the plugin to use HTTPS for the callback URL in those cases where it needs to support mixed HTTP and HTTPS pages. If disabled, it will pick the protocol from the WordPress home URL (configured under Settings > General).', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_remember_users_session() {
    $v = $this->options->get( 'remember_users_session' );

    echo $this->render_a0_switch( "wpa0_remember_users_session", "remember_users_session", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description"><?php echo __( 'Users session by default lives for two days. Enabling this setting will make the sessions be kept for 14 days.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_migration_ws() {
    $v = $this->options->get( 'migration_ws' );
    $token = $this->options->get( 'migration_token' );

    echo $this->render_a0_switch( "wpa0_auth0_migration_ws", "migration_ws", 1, 1 == $v );

    if ( $v ) {
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Users migration is enabled. If you disable this setting, it can not be automatically enabled again, it needs to be done manually in the Auth0 dashboard.', 'wp-auth0' ); ?></span>
        <br>
        <span class="description"><?php echo __( 'Security token:', 'wp-auth0' ); ?></span>
        <textarea class="code" disabled><?php echo $token; ?></textarea>
      </div>
    <?php
    } else {
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Users migration is disabled. Enabling it will expose the migration webservices but the connection needs to be updated manually on the Auth0 dashboard. More info about the migration process ', 'wp-auth0' ); ?><a target="_blank" href="https://auth0.com/docs/connections/database/migrating">HERE</a>.</span>
      </div>
    <?php
    }

  }

  public function render_migration_ws_ips_filter() {
    $v = $this->options->get( 'migration_ips_filter' );
    $this->render_a0_switch( "wpa0_auth0_migration_ips_filter", "migration_ips_filter", 1, 1 == $v );
  }

  public function render_migration_ws_ips() {
	  $list = $this->options->get( 'migration_ips' );
	  ?>
	  <div class="subelement">
		  <textarea name="<?php echo $this->options->get_options_name(); ?>[migration_ips]" id="wpa0_auth0_migration_ips"><?php echo $list; ?></textarea>
		  <span class="description">
	        <?php echo __( 'Only requests from this IPs will be allowed to the migration WS.', 'wp-auth0' ); ?>
	        <?php echo __( 'Separate multiple IPs with commas.', 'wp-auth0' ); ?>
        </span>
	  </div>
	  <?php
  }

  // TODO: Better documentation for when this should be used
  public function render_auth0_implicit_workflow() {
    $v = absint( $this->options->get( 'auth0_implicit_workflow' ) );

    echo $this->render_a0_switch( "wpa0_auth0_implicit_workflow", "auth0_implicit_workflow", 1, 1 == $v );
?>

    <div class="subelement">
      <span class="description"><?php echo __( 'Activate this option to change the login workflow and allow the plugin to work when the server doesn\'t have internet access.', 'wp-auth0' ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login() {
    $v = absint( $this->options->get( 'auto_login' ) );

    echo $this->render_a0_switch( "wpa0_auto_login", "auto_login", 1, 1 == $v );
?>

    <div class="subelement">
      <span class="description"><?php echo __( 'Mark this to avoid the login page (you will have to select a single login provider)', 'wp-auth0' ); ?></span>
    </div>
    <?php
  }

  public function render_auto_login_method() {
    $v = $this->options->get( 'auto_login_method' );
?>
    <input type="text" name="<?php echo $this->options->get_options_name(); ?>[auto_login_method]" id="wpa0_auto_login_method" value="<?php echo esc_attr( $v ); ?>"/>
    <div class="subelement">
      <span class="description"><?php echo __( 'To find the method name, log into Auth0 Dashboard, and navigate to: Connection -> [Connection Type] (eg. Social or Enterprise). Click the "down arrow" to expand the wanted method, and use the value in the "Name"-field. Example: google-oauth2', 'wp-auth0' ); ?></span>
    </div>
    <?php
  }

  public function render_ip_range_check() {
    $v = absint( $this->options->get( 'ip_range_check' ) );

    echo $this->render_a0_switch( "wpa0_ip_range_check", "ip_range_check", 1, 1 == $v );
  }

  public function render_ip_ranges() {
    $v = $this->options->get( 'ip_ranges' );
?>
    <textarea cols="25" name="<?php echo $this->options->get_options_name(); ?>[ip_ranges]" id="wpa0_ip_ranges"><?php echo esc_textarea( $v ); ?></textarea>
    <div class="subelement">
      <span class="description"><?php echo __( 'Only one range per line! Range format should be as follows (spaces will be trimmed):', 'wp-auth0' ); ?></span>
      <code>xx.xx.xx.xx - yy.yy.yy.yy</code>
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
      <span class="description"><?php echo __( ' If you are using a load balancer or a proxy, you will need to whitelist its IP in order to enable IP checks for logins or migration webservices.', 'wp-auth0' ); ?></span>
    </div>
    <?php
  }

  public function render_cdn_url() {
    $cdn_url = $this->options->get( 'cdn_url' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[cdn_url]" id="wpa0_cdn_url" value="<?php echo esc_attr( $cdn_url ); ?>"/>

      <div class="subelement">
        <span class="description"><?php echo __( 'Point this to the latest widget available in the CDN', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_auth0_server_domain() {
    $v = $this->options->get( 'auth0_server_domain' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[auth0_server_domain]" id="wpa0_auth0_server_domain" value="<?php echo esc_attr( $v ); ?>" />

      <div class="subelement">
        <span class="description"><?php echo __( 'The Auth0 domain, it is used by the setup wizard to fetch your account information.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_connections() {
    $v = $this->options->get( 'lock_connections' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[lock_connections]" id="wpa0_connections" value="<?php echo esc_attr( $v ); ?>" />

      <div class="subelement">
        <span class="description"><?php echo __( 'This is used to select which connections should lock show. It is ignored when empty and is mandatory for passwordless with social mode.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_metrics() {
    $v = absint( $this->options->get( 'metrics' ) );

    echo $this->render_a0_switch( "wpa0_metrics", "metrics", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'This plugin tracks anonymous usage data. Click to disable.', 'wp-auth0' ); ?>
        </span>
      </div>
    <?php
  }

  public function render_verified_email() {
    $v = absint( $this->options->get( 'requires_verified_email' ) );

    echo $this->render_a0_switch( "wpa0_verified_email", "requires_verified_email", 1, 1 == $v );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you require the user to have a verified email to login', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }
  // TODO: Deprecate
  public function render_advanced_description() {
?>

    <p class=\"a0-step-text\"><?php echo self::ADVANCED_DESCRIPTION; ?></p>

    <?php
  }

  public function basic_validation( $old_options, $input ) {
    $input['requires_verified_email'] = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
    $input['auto_provisioning'] = ( isset( $input['auto_provisioning'] ) ? $input['auto_provisioning'] : 0 );
    $input['remember_users_session'] = ( isset( $input['remember_users_session'] ) ? $input['remember_users_session'] : 0 ) == 1;
    $input['passwordless_enabled'] = ( isset( $input['passwordless_enabled'] ) ? $input['passwordless_enabled'] : 0 ) == 1;
    $input['jwt_auth_integration'] = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
    $input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
    $input['metrics'] = ( isset( $input['metrics'] ) ? $input['metrics'] : 0 );
    $input['force_https_callback'] = ( isset( $input['force_https_callback'] ) ? $input['force_https_callback'] : 0 );
    $input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );

    if ( isset( $input['connections'] ) ) {
      if ( isset( $input['connections']['social_twitter_key'] ) ) $input['connections']['social_twitter_key'] = sanitize_text_field( $input['connections']['social_twitter_key'] );
      if ( isset( $input['connections']['social_twitter_secret'] ) ) $input['connections']['social_twitter_secret'] = sanitize_text_field( $input['connections']['social_twitter_secret'] );
      if ( isset( $input['connections']['social_facebook_key'] ) ) $input['connections']['social_facebook_key'] = sanitize_text_field( $input['connections']['social_facebook_key'] );
      if ( isset( $input['connections']['social_facebook_secret'] ) ) $input['connections']['social_facebook_secret'] = sanitize_text_field( $input['connections']['social_facebook_secret'] );
    }

    $input['migration_ips_filter'] =  ( ! empty( $input['migration_ips_filter'] ) ? 1 : 0 );
    $input['migration_ips'] = sanitize_text_field( $input['migration_ips'] );

    $input['valid_proxy_ip'] = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );

    $input['lock_connections'] = trim( $input['lock_connections'] );
    $input['custom_signup_fields'] = trim( $input['custom_signup_fields'] );

    if ( trim( $input["extra_conf"] ) != '' ) {
      if ( json_decode( $input["extra_conf"] ) === null ) {
        $error = __( "The Extra settings parameter should be a valid json object", "wp-auth0" );
        self::add_validation_error( $error );
      }
    }

    return $input;
  }

  public function migration_ws_validation( $old_options, $input ) {
    $input['migration_ws'] = ( isset( $input['migration_ws'] ) ? $input['migration_ws'] : 0 );

    if ( $old_options['migration_ws'] != $input['migration_ws'] ) {

      if ( 1 == $input['migration_ws'] ) {

	      $token_id = uniqid();
	      $secret = $input['client_secret'];
	      if ( $input['client_secret_b64_encoded'] ) {
		      $secret = JWT::urlsafeB64Decode( $secret );
	      }

        $input['migration_token'] = JWT::encode( array( 'scope' => 'migration_ws', 'jti' => $token_id ), $secret );
        $input['migration_token_id'] = $token_id;

	      $this->add_validation_error(
		      __( 'User Migration needs to be configured manually. ', 'wp-auth0' )
		      . __( 'Please see Advanced > Users Migration below for your token, instructions are ', 'wp-auth0' )
		      . '<a href="https://auth0.com/docs/users/migrations/automatic">HERE</a>.'
	      );

      } else {
        $input['migration_token'] = null;
        $input['migration_token_id'] = null;

        if (isset($old_options['db_connection_id'])) {


          $connection = WP_Auth0_Api_Client::get_connection($input['domain'], $input['auth0_app_token'], $old_options['db_connection_id']);

          $connection->options->enabledDatabaseCustomization = false;
          $connection->options->import_mode = false;


          unset($connection->name);
          unset($connection->strategy);
          unset($connection->id);

          $response = WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $old_options['db_connection_id'], $connection);
        } else {
          $response = false;
        }

        if ( $response === false ) {
          $error = __( 'There was an error disabling your custom database. Check how to do it manually ', 'wp-auth0' );
          $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
          $this->add_validation_error( $error );
        }
      }

      $this->router->setup_rewrites( $input['migration_ws'] == 1 );
      flush_rewrite_rules();
    } else {
      $input['migration_token'] = $old_options['migration_token'];
      $input['migration_token_id'] = $old_options['migration_token_id'];
    }
    return $input;
  }

  public function link_accounts_validation( $old_options, $input ) {
    $link_script = WP_Auth0_RulesLib::$link_accounts['script'];
    $link_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $link_script );
    $link_script = str_replace( 'REPLACE_WITH_YOUR_DOMAIN', $input['domain'], $link_script );
    $link_script = str_replace( 'REPLACE_WITH_YOUR_API_TOKEN', $input['auth0_app_token'], $link_script );
    return $this->rule_validation($old_options, $input, 'link_auth0_users', WP_Auth0_RulesLib::$link_accounts['name'] . '-' . get_auth0_curatedBlogName(), $link_script);
  }

  // TODO: Deprecate
  public function connections_validation( $old_options, $input ) {

    $check_if_enabled = array();
    $passwordless_connections = array(
      'sms' => 'sms',
      'magiclink' => 'email',
      'emailcode' => 'email');

    if ($input['passwordless_enabled'] && $input['passwordless_enabled'] != $old_options['passwordless_enabled']) {

      foreach ($passwordless_connections as $alias => $name) {
        if (strpos($input['passwordless_method'], $alias) !== false) {
          $check_if_enabled[] = $name;
        }
      }

    } elseif ($input['passwordless_method'] != $old_options['passwordless_method']) {

      foreach ($passwordless_connections as $name) {
        if (strpos($input['passwordless_method'], $name) !== false) {
          $check_if_enabled[] = $name;
        }
      }
    }

    if (!empty($check_if_enabled)) {

      $response = WP_Auth0_Api_Client::search_connection($input['domain'], $input['auth0_app_token']);
      $enabled_connections = array();
      foreach ($response as $connection) {
        if (in_array($input['client_id'], $connection->enabled_clients)) {
          $enabled_connections[] = $connection->strategy;
        }
      }

      $matching = array_intersect($enabled_connections, $check_if_enabled);

      if (array_diff($matching, $check_if_enabled) !== array_diff($check_if_enabled, $matching)) {
        $error = __( 'The passwordless connection is not enabled. Please go to the Auth0 Dashboard and configure it.', 'wp-auth0' );
        $this->add_validation_error( $error );
      }

    }

    return $input;
  }

  public function loginredirection_validation( $old_options, $input ) {
    $home_url = home_url();

    if ( empty( $input['default_login_redirection'] ) ) {
      $input['default_login_redirection'] = $home_url;
    } else {
      if ( strpos( $input['default_login_redirection'], $home_url ) !== 0 ) {
        if ( strpos( $input['default_login_redirection'], 'http' ) === 0 ) {
          $input['default_login_redirection'] = $home_url;
          $error = __( "The 'Login redirect URL' cannot point to a foreign page.", "wp-auth0" );
          $this->add_validation_error( $error );
        }
      }

      if ( strpos( $input['default_login_redirection'], 'action=logout' ) !== false ) {
        $input['default_login_redirection'] = $home_url;

        $error = __( "The 'Login redirect URL' cannot point to the logout page. ", "wp-auth0" );
        $this->add_validation_error( $error );
      }
    }
    return $input;
  }

}
