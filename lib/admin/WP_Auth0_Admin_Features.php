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
  );

  public function init() {

    $this->init_option_section( '', 'features', array(

        array( 'id' => 'wpa0_password_policy', 'name' => 'Password Policy', 'function' => 'render_password_policy' ),
        array( 'id' => 'wpa0_sso', 'name' => 'Single Sign On (SSO)', 'function' => 'render_sso' ),
        array( 'id' => 'wpa0_singlelogout', 'name' => 'Single Logout', 'function' => 'render_singlelogout' ),
        array( 'id' => 'wpa0_mfa', 'name' => 'Multifactor Authentication (MFA)', 'function' => 'render_mfa' ),
        array( 'id' => 'wpa0_fullcontact', 'name' => 'FullContact integration', 'function' => 'render_fullcontact' ),
        array( 'id' => 'wpa0_geo', 'name' => 'Store geolocation', 'function' => 'render_geo' ),
        array( 'id' => 'wpa0_income', 'name' => 'Store zipcode income', 'function' => 'render_income' ),
        array( 'id' => 'wpa0_override_wp_avatars', 'name' => 'Override WordPress avatars', 'function' => 'render_override_wp_avatars' ),

      ) );
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
          <?php echo __( 'The difficulty of the user password where \'none\' requires a single character, \'low\' requires six characters and so on. For more details see our', 'wp-auth0' ); ?> <a target="_blank" href="https://auth0.com/docs/password-strength"><?php echo __( 'help page', 'wp-auth0' ); ?></a> <?php echo __( 'on password difficulty.', 'wp-auth0' ); ?>
        </span>
      </div>
    <?php
  }

  public function render_sso() {
    $v = absint( $this->options->get( 'sso' ) );

    echo $this->render_a0_switch( "wpa0_sso", "sso", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Single Sign On (SSO) allows users to sign in once to multiple services. For more details, see our ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'help page on SSO', 'wp-auth0' ); ?></a>.
        </span>
      </div>
    <?php
  }

  public function render_singlelogout() {
    $v = absint( $this->options->get( 'singlelogout' ) );

    echo $this->render_a0_switch( "wpa0_singlelogout", "singlelogout", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Single Logout is the opposite of the above SSO, it logs users out of everything at once. For more details, see our', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'help page on SSO', 'wp-auth0' ); ?></a>.
        </span>
      </div>
    <?php
  }

  public function render_mfa() {
    $v = $this->options->get( 'mfa' );

    echo $this->render_a0_switch( "wpa0_mfa", "mfa", 1, !empty( $v ) );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to enable multifactor authentication with Auth0 Guardian. For more information, see ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/mfa"><?php echo __( 'our help page on MFA', 'wp-auth0' ); ?></a>.
          <?php echo __( 'You can enable other MFA providers from the ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://manage.auth0.com/#/multifactor"><?php echo __( 'Auth0 dashboard', 'wp-auth0' ); ?></a>.
          <?php echo __( 'You can reset your users MFA provider data, by going to the user and clicking on "Delete MFA Provider" button.', 'wp-auth0' ); ?>
        </span>
      </div>
    <?php
  }

  public function render_geo() {
    $v = $this->options->get( 'geo_rule' );

    echo $this->render_a0_switch( "wpa0_geo_rule", "geo_rule", 1, !empty( $v ) );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to store geo location information based on your users IP in the user_metadata', 'wp-auth0' );?>
        </span>
      </div>
    <?php
  }

  public function render_income() {
    $v = $this->options->get( 'income_rule' );

    echo $this->render_a0_switch( "wpa0_income_rule", "income_rule", 1, !empty( $v ) );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you want to store income data based on the zipcode (calculated using the users IP).', 'wp-auth0' ); ?></span>
      </div>
      <div class="subelement">
        <span class="description"><?php echo __( 'Represents the median income of the users zipcode, based on last US census data.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_override_wp_avatars() {
    $v = $this->options->get( 'override_wp_avatars' );

    echo $this->render_a0_switch( "wpa0_override_wp_avatars", "override_wp_avatars", 1, !empty( $v ) );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Mark this if you want to override the WordPress avatar with the user\'s Auth0 profile avatar.', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_fullcontact() {
    $v = $this->options->get( 'fullcontact' );
    $apikey = $this->options->get( 'fullcontact_apikey' );

    echo $this->render_a0_switch( "wpa0_fullcontact", "fullcontact", 1, !empty( $v ) );

?>

      <div class="subelement fullcontact <?php echo empty( $v ) ? 'hidden' : ''; ?>">
        <label for="wpa0_fullcontact_key" id="wpa0_fullcontact_key_label">Enter your FullContact api key:</label>
        <input type="text" id="wpa0_fullcontact_key" name="<?php echo $this->options->get_options_name(); ?>[fullcontact_apikey]" value="<?php echo $apikey; ?>" />
      </div>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Mark this if you want to enrich your users\' profiles with the data provided by FullContact. A valid api key is required. ', 'wp-auth0' ); ?>
          <?php echo __( 'For more information, see our ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/scenarios/mixpanel-fullcontact-salesforce#2-augment-user-profile-with-fullcontact-"><?php echo __( 'help page on FullContact integration with Auth0', 'wp-auth0' );?></a>
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
    $input['override_wp_avatars'] = ( isset( $input['override_wp_avatars'] ) ? $input['override_wp_avatars'] : 0 );

    return $input;
  }

  public function sso_validation( $old_options, $input ) {
    $input['sso'] = ( isset( $input['sso'] ) ? $input['sso'] : 0 );

    if ( $old_options['sso'] != $input['sso'] && 1 == $input['sso'] ) {
      if ( false === WP_Auth0_Api_Client::update_client( $input['domain'], $input['auth0_app_token'], $input['client_id'], $input['sso'] == 1 ) ) {

        $error = __( 'There was an error updating your Auth0 App to enable SSO. To do it manually, turn it ', 'wp-auth0' );
        $error .= '<a href="https://auth0.com/docs/sso/single-sign-on#1">HERE</a>.';
        $this->add_validation_error( $error );

      }
    }
    return $input;
  }

  public function security_validation( $old_options, $input ) {

    $input['password_policy'] = ( isset( $input['password_policy'] ) && $input['password_policy'] != "" ? $input['password_policy'] : null );

    if ( $old_options['password_policy'] != $input['password_policy'] ) {

      $connections = WP_Auth0_Api_Client::search_connection( $input['domain'], $input['auth0_app_token'], 'auth0' );

      foreach ( $connections as $connection ) {

        if ( in_array( $input['client_id'], $connection->enabled_clients ) ) {

          $connection->options->passwordPolicy = $input['password_policy'];
          $connection_id = $connection->id;
 
          unset($connection->name);
          unset($connection->strategy);
          unset($connection->id);
 
          if ( false === WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $connection_id, $connection ) ) {

            $error = __( 'There was an error updating your Auth0 DB Connection. To do it manually, change it ', 'wp-auth0' );
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
    $fullcontact_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $fullcontact_script );
    $fullcontact_script = str_replace( 'REPLACE_WITH_YOUR_FULLCONTACT_API_KEY', $input['fullcontact_apikey'], $fullcontact_script );
    return $this->rule_validation( $old_options, $input, 'fullcontact', WP_Auth0_RulesLib::$fullcontact['name']. '-' . get_auth0_curatedBlogName(), $fullcontact_script );
  }

  public function mfa_validation( $old_options, $input ) {

    if (!isset($input['mfa'])) {
      $input['mfa'] = null;
    }
    if (!isset($old_options['mfa'])) {
      $old_options['mfa'] = null;
    }

    if ($old_options['mfa'] != $input['mfa'] && $input['mfa'] !== null) {
      WP_Auth0_Api_Client::update_guardian($input['domain'], $input['auth0_app_token'], 'push-notification', true);
    }
    
    $mfa_script = WP_Auth0_RulesLib::$guardian_MFA['script'];
    $mfa_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $mfa_script );
    return $this->rule_validation( $old_options, $input, 'mfa', WP_Auth0_RulesLib::$guardian_MFA['name'] . '-' . get_auth0_curatedBlogName(), $mfa_script );
  }


  public function georule_validation( $old_options, $input ) {
    $geo_script = WP_Auth0_RulesLib::$geo['script'];
    $geo_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $geo_script );
    return $this->rule_validation( $old_options, $input, 'geo_rule', WP_Auth0_RulesLib::$geo['name'] . '-' . get_auth0_curatedBlogName(), $geo_script );
  }

  public function incomerule_validation( $old_options, $input ) {
    $income_script = WP_Auth0_RulesLib::$income['script'];
    $income_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $income_script );
    return $this->rule_validation( $old_options, $input, 'income_rule', WP_Auth0_RulesLib::$income['name'] . '-' . get_auth0_curatedBlogName(), $income_script );
  }

}