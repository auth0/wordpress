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

	/**
	 * Sets up settings field registration
	 */
  public function init() {
    $this->init_option_section( '', 'features', array(
        array( 'id' => 'wpa0_password_policy', 'name' => 'Password Policy',
               'function' => 'render_password_policy' ),
        array( 'id' => 'wpa0_sso', 'name' => 'Single Sign On (SSO)',
               'function' => 'render_sso' ),
        array( 'id' => 'wpa0_singlelogout', 'name' => 'Single Logout',
               'function' => 'render_singlelogout' ),
        array( 'id' => 'wpa0_mfa', 'name' => 'Multifactor Authentication (MFA)',
               'function' => 'render_mfa' ),
        array( 'id' => 'wpa0_fullcontact', 'name' => 'FullContact integration',
               'function' => 'render_fullcontact' ),
        array( 'id' => 'wpa0_geo', 'name' => 'Store geolocation',
               'function' => 'render_geo' ),
        array( 'id' => 'wpa0_income', 'name' => 'Store zipcode income',
               'function' => 'render_income' ),
        array( 'id' => 'wpa0_override_wp_avatars', 'name' => 'Override WordPress avatars',
               'function' => 'render_override_wp_avatars' ),
      ) );
  }

	/**
	 * Render description at the top of the settings block
	 */
	public function render_features_description() {
		printf( '<p class="a0-step-text">%s</p>', self::FEATURES_DESCRIPTION );
	}

	/**
	 * Render password_policy options
	 */
  public function render_password_policy() {
	  $value = $this->options->get( 'password_policy' );
	  $this->render_radio_button( 'wpa0_password_policy_none', 'password_policy', '', 'None', empty( $value ) );
	  $this->render_radio_button( 'wpa0_password_policy_low', 'password_policy', 'low', '', 'low' === $value );
	  $this->render_radio_button( 'wpa0_password_policy_fair', 'password_policy', 'fair', '', 'fair' === $value );
	  $this->render_radio_button( 'wpa0_password_policy_fair', 'password_policy', 'good', '', 'good' === $value );
	  $this->render_radio_button( 'wpa0_password_policy_ex', 'password_policy', 'excellent', '', 'excellent' === $value );
	  $this->render_field_description(
		  __( 'Password security policy used; for information on the levels, see our ', 'wp-auth0' ) .
		  $this->get_docs_link( 'password-strength', __( 'help page on password strength', 'wp-auth0' ) )
	  );
  }

	/**
	 * Render SSO switch
	 */
	public function render_sso() {
		$value = absint( $this->options->get( 'sso' ) );
		$this->render_a0_switch( 'wpa0_sso', 'sso', 1 == $value );
		$this->render_field_description(
			__( 'Single Sign On (SSO) allows users to sign in once to multiple Clients in the same tenant. ', 'wp-auth0' ) .
			__( 'For more details, see our ', 'wp-auth0' ) .
			$this->get_docs_link( 'sso/single-sign-on', __( 'help page on SSO', 'wp-auth0' ) )
		);
	}

	/**
	 * Render SLO switch
	 */
	public function render_singlelogout() {
		$value = absint( $this->options->get( 'singlelogout' ) );
		$this->render_a0_switch( 'wpa0_singlelogout', 'singlelogout', 1 == $value );
		$this->render_field_description(
			__( 'Single Logout (SLO) allows users to sign out of multiple Clients in the same tenant. ', 'wp-auth0' ) .
			__( 'For more details, see our ', 'wp-auth0' ) .
			$this->get_docs_link( 'sso/single-sign-on', __( 'help page on SSO', 'wp-auth0' ) )
		);
	}

	/**
	 * Render MFA switch
	 */
  public function render_mfa() {
	  $value = absint( $this->options->get( 'mfa' ) );
	  $this->render_a0_switch( 'wpa0_mfa', 'mfa', ! empty( $value ) );
	  $this->render_field_description(
		  __( 'Mark this if you want to enable multifactor authentication with Auth0 Guardian. ', 'wp-auth0' ) .
		  __( 'For more details, see our ', 'wp-auth0' ) .
		  $this->get_docs_link( 'mfa', __( 'help page on MFA', 'wp-auth0' ) ) .
		  __( 'You can enable other MFA providers from the ', 'wp-auth0' ) .
		  $this->get_dashboard_link( 'multifactor' )
	  );
  }

	/**
	 * Render FullContact switch and API key field
	 *
	 * TODO: test me ... remove validation if not working
	 */
	public function render_fullcontact() {
		$fullcontact_on = absint( $this->options->get( 'fullcontact' ) );
		$fullcontact_key = $this->options->get( 'fullcontact_apikey' );
		$this->render_a0_switch( 'wpa0_fullcontact', 'fullcontact', ! empty( $fullcontact_on ) );

		$fullcontact_key_id = 'wpa0_fullcontact_key';
		printf(
			'<div class="subelement fullcontact %s">
				<label for="%s" id="%s_label">%s</label>
				<input type="text" id="%s" name="%s[fullcontact_apikey]" value="%s">
			</div>',
			empty( $fullcontact_on ) ? 'hidden' : '',
			esc_attr( $fullcontact_key_id ),
			esc_attr( $fullcontact_key_id ),
			__( 'Enter your FullContact api key:', 'wp-auth0' ),
			esc_attr( $fullcontact_key_id ),
			esc_attr( $this->option_name ),
			esc_attr( $fullcontact_key )
		);

		$this->render_field_description(
			__( 'Enriches your user profiles with the data provided by FullContact. ', 'wp-auth0' ) .
			__( 'A valid FullContact API key is required. ', 'wp-auth0' ) .
			__( 'For more details, see our ', 'wp-auth0' ) .
			$this->get_docs_link(
				'scenarios/mixpanel-fullcontact-salesforce',
				__( 'help page on FullContact integration with Auth0', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render geolocation switch
	 *
	 * TODO: test me ... remove validation if not working
	 */
  public function render_geo() {
	  $value = absint( $this->options->get( 'geo_rule' ) );
	  $this->render_a0_switch( 'wpa0_geo_rule', 'geo_rule', ! empty( $value ) );
	  $this->render_field_description(
		  __( 'Store geo location information based on your users IP in the user_metadata', 'wp-auth0' )
	  );
  }

	/**
	 * Render zipcode income switch
	 *
	 * TODO: test me ... remove validation if not working
	 */
  public function render_income() {
	  $value = absint( $this->options->get( 'income_rule' ) );
	  $this->render_a0_switch( 'wpa0_income_rule', 'income_rule', ! empty( $value ) );
	  $this->render_field_description(
		  __( 'Mark this if you want to store income data based on the zipcode (based on IP address). ', 'wp-auth0' ).
		  __( 'Represents the median income of the users zipcode, based on last US census data. ', 'wp-auth0' )
	  );
  }

	/**
	 * Render avatar override switch
	 */
  public function render_override_wp_avatars() {
	  $value = absint( $this->options->get( 'override_wp_avatars' ) );
	  $this->render_a0_switch( 'wpa0_override_wp_avatars', 'override_wp_avatars', ! empty( $value ) );
	  $this->render_field_description(
		  __( 'Overrides the WordPress avatar with the Auth0 profile avatar. ', 'wp-auth0' )
	  );
  }

	/**
	 * Validate settings being saved
	 *
	 * @param array $old_options - options array before saving
	 * @param array $input - options array after saving
	 *
	 * @return array
	 */
  public function basic_validation( $old_options, $input ) {
    $input['singlelogout'] = ! empty( $input['singlelogout'] ) ? 1 : 0;
    $input['override_wp_avatars'] = ! empty( $input['override_wp_avatars'] ) ? 1 : 0;
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