<?php

class WP_Auth0_Admin_Features extends WP_Auth0_Admin_Generic {

  const FEATURES_DESCRIPTION = 'Settings related to specific features provided by the plugin.';

  protected $actions_middlewares = array(
    'basic_validation',
    'georule_validation',
    'sso_validation',
    'security_validation',
    'incomerule_validation',
    'fullcontact_validation',
    'mfa_validation',
    'socialfacebook_validation',
    'socialtwitter_validation',
    'socialgoogle_validation',
  );

  public function init() {

    $this->init_option_section( '', 'features',array(

      array( 'id' => 'wpa0_password_policy', 'name' => 'Password Policy', 'function' => 'render_password_policy' ),
      array( 'id' => 'wpa0_sso', 'name' => 'Single Sign On (SSO)', 'function' => 'render_sso' ),
      array( 'id' => 'wpa0_singlelogout', 'name' => 'Single Logout', 'function' => 'render_singlelogout' ),
      array( 'id' => 'wpa0_mfa', 'name' => 'Multifactor Authentication (MFA)', 'function' => 'render_mfa' ),
      array( 'id' => 'wpa0_fullcontact', 'name' => 'FullContact integration', 'function' => 'render_fullcontact' ),
      array( 'id' => 'wpa0_geo', 'name' => 'Store geolocation', 'function' => 'render_geo' ),
      array( 'id' => 'wpa0_income', 'name' => 'Store zipcode income', 'function' => 'render_income' ),
      
    ) );

    $options_name = $this->options->get_options_name();
    register_setting( $options_name . '_features', $options_name, array( $this, 'input_validator' ) );
  }

  public function render_password_policy() {
    $v = $this->options->get( 'password_policy' );
    ?>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_none" value="" <?php echo checked( $v, null, false ); ?>/><label for="wpa0_password_policy_none">None</label>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_low" value="low" <?php echo checked( $v, 'low', false ); ?>/><label for="wpa0_password_policy_low">Low</label>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_fair" value="fair" <?php echo checked( $v, 'fair', false ); ?>/><label for="wpa0_password_policy_fair">Fair</label>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_good" value="good" <?php echo checked( $v, 'good', false ); ?>/><label for="wpa0_password_policy_good">Good</label>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_excellent" value="excellent" <?php echo checked( $v, 'excellent', false ); ?>/><label for="wpa0_password_policy_excellent">Excellent</label>
      <div class="subelement">
        <span class="description">
          <?php echo __( 'For more info about the password policies check ', WPA0_LANG ); ?>
          <a target="_blank" href="https://auth0.com/docs/password-strength"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
        </span>
      </div>
    <?php
  }

  public function render_sso() {
    $v = absint( $this->options->get( 'sso' ) );

    echo $this->render_a0_switch("wpa0_sso", "sso", 1, 1 == $v);
    ?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to enable SSO. More info ', WPA0_LANG ); ?>
          <a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
        </span>
      </div>
    <?php
  }

  public function render_singlelogout() {
    $v = absint( $this->options->get( 'singlelogout' ) );

    echo $this->render_a0_switch("wpa0_singlelogout", "singlelogout", 1, 1 == $v);
    ?>
      
      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to enable Single Logout. More info ', WPA0_LANG ); ?>
          <a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
        </span>
      </div>
    <?php
  }

  public function render_mfa() {
    $v = $this->options->get( 'mfa' );

    echo $this->render_a0_switch("wpa0_mfa", "mfa", 1, !empty($v));
    ?>
      
      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to enable multifactor authentication with Google Authenticator. More info ', WPA0_LANG ); ?>
          <a target="_blank" href="https://auth0.com/docs/mfa"><?php echo __( 'HERE', WPA0_LANG ); ?></a>.
          <?php echo __( 'You can enable other MFA providers from the ', WPA0_LANG ); ?>
          <a target="_blank" href="https://manage.auth0.com/#/multifactor"><?php echo __( 'Auth0 dashboard', WPA0_LANG ); ?></a>.
        </span>
      </div>
    <?php
  }

  public function render_geo() {
    $v = $this->options->get( 'geo_rule' );

    echo $this->render_a0_switch("wpa0_geo_rule", "geo_rule", 1, !empty($v));
    ?>
      
      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to store geo location information based on your users IP in the user_metadata', WPA0_LANG );?>
        </span>
      </div>
    <?php
  }

  public function render_income() {
    $v = $this->options->get( 'income_rule' );

    echo $this->render_a0_switch("wpa0_income_rule", "income_rule", 1, !empty($v));
    ?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you want to store income data based on the zipcode (calculated using the users IP).', WPA0_LANG ); ?></span>
      </div>
      <div class="subelement">
        <span class="description"><?php echo __( 'Represents the median income of the users zipcode, based on last US census data.', WPA0_LANG ); ?></span>
      </div>
    <?php
  }

  public function render_fullcontact() {
    $v = $this->options->get( 'fullcontact' );
    $apikey = $this->options->get( 'fullcontact_apikey' );

    echo $this->render_a0_switch("wpa0_fullcontact", "fullcontact", 1, !empty($v));

    ?>

      <div class="subelement fullcontact <?php echo (empty($v) ? 'hidden' : ''); ?>">
        <label for="wpa0_fullcontact_key" id="wpa0_fullcontact_key_label">Enter your FullContact api key:</label>
        <input type="text" id="wpa0_fullcontact_key" name="<?php echo $this->options->get_options_name(); ?>[fullcontact_apikey]" value="<?php echo $apikey; ?>" />
      </div>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to hydrate your users profile with the data provided by FullContact. A valid api key is requiere.', WPA0_LANG ); ?>
          <?php echo __( 'More info ', WPA0_LANG ); ?>
          <a href="https://auth0.com/docs/scenarios/fullcontact"><?php echo __( 'HERE', WPA0_LANG );?></a>
        </span>
      </div>
    <?php
  }

  public function render_features_description() {
    ?>

    <p class=\"a0-step-text\"><?php echo self::FEATURES_DESCRIPTION; ?></p>

    <?php
  }

  public function basic_validation( $old_options, $input ) {
    $input['singlelogout'] = ( isset( $input['singlelogout'] ) ? $input['singlelogout'] : 0 );

    return $input;
  }

  public function sso_validation( $old_options, $input ) {
    $input['sso'] = ( isset( $input['sso'] ) ? $input['sso'] : 0 );
    if ($old_options['sso'] != $input['sso'] && 1 == $input['sso']) {
      if ( false === WP_Auth0_Api_Client::update_client($input['domain'], $this->options->get( 'auth0_app_token' ), $input['client_id'],$input['sso'] == 1) ) {

        $error = __( 'There was an error updating your Auth0 App to enable SSO. To do it manually, turn it on ', WPA0_LANG );
        $error .= '<a href="https://auth0.com/docs/sso/single-sign-on#1">HERE</a>.';
        $this->add_validation_error( $error );

      }
    }
    return $input;
  }

  public function security_validation( $old_options, $input ) {

    $input['password_policy'] = ( isset( $input['password_policy'] ) && $input['password_policy'] != "" ? $input['password_policy'] : null );

    if ($old_options['password_policy'] != $input['password_policy']) {

      $connections = WP_Auth0_Api_Client::search_connection($input['domain'], $this->options->get( 'auth0_app_token' ), 'auth0');

      foreach ($connections as $connection) {

        if ( in_array($input['client_id'], $connection->enabled_clients) ) {
          if ( false === WP_Auth0_Api_Client::update_connection($input['domain'], $this->options->get( 'auth0_app_token' ), $connection->id, array(
            'options' => array(
              'passwordPolicy' => $input['password_policy'],
            )
          ) ) ) {

            $error = __( 'There was an error updating your Auth0 DB Connection. To do it manually, change it ', WPA0_LANG );
            $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
            $this->add_validation_error( $error );

          }
        }

      }

    }
    return $input;
  }

  public function fullcontact_validation( $old_options, $input ) {
    $fullcontact_script = WP_Auth0_RulesLib::$fullcontact['script'];
    $fullcontact_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['fullcontact_apikey'], $fullcontact_script);
    return $this->rule_validation($old_options, $input, 'fullcontact', WP_Auth0_RulesLib::$fullcontact['name'], $fullcontact_script);
  }

  public function mfa_validation( $old_options, $input ) {
    $mfa_script = WP_Auth0_RulesLib::$google_MFA['script'];
    $mfa_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $mfa_script);
    return $this->rule_validation($old_options, $input, 'mfa', WP_Auth0_RulesLib::$google_MFA['name'], $mfa_script);
  }


  public function georule_validation( $old_options, $input ) {
    return $this->rule_validation($old_options, $input, 'geo_rule', WP_Auth0_RulesLib::$geo['name'], WP_Auth0_RulesLib::$geo['script']);
  }

  public function incomerule_validation( $old_options, $input ) {
    return $this->rule_validation($old_options, $input, 'income_rule', WP_Auth0_RulesLib::$income['name'], WP_Auth0_RulesLib::$income['script']);
  }

}
