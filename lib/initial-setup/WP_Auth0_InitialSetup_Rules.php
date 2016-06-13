<?php

class WP_Auth0_InitialSetup_Rules {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {

		$mfa = $this->a0_options->get( 'mfa' );
		$geo = $this->a0_options->get( 'geo_rule' );
		$income = $this->a0_options->get( 'income_rule' );
		$fullcontact = $this->a0_options->get( 'fullcontact' );
		$fullcontact_apikey = $this->a0_options->get( 'fullcontact_apikey' );

		include WPA0_PLUGIN_DIR . 'templates/initial-setup/rules.php';
	}

	public function callback() {

		$client_id = $this->a0_options->get( 'client_id' );

		$keys = array(
			'mfa',
			'geo_rule',
			'income_rule',
			'fullcontact',
			'fullcontact_apikey',
		);

		$input = array();
		$old_options = array();

		foreach ( $keys as $key ) {
			if ( isset( $_REQUEST[$key] ) ) {
				$input[$key] = $_REQUEST[$key];
			}
			$old_options[$key] = $this->a0_options->get( $key );
		}

		$mfa_script = WP_Auth0_RulesLib::$google_MFA['script'];
		$mfa_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $client_id, $mfa_script );
		$input = $this->rule_validation( $old_options, $input, 'mfa', WP_Auth0_RulesLib::$google_MFA['name'] . '-' . get_bloginfo('name'), $mfa_script );

		$input = $this->rule_validation( $old_options, $input, 'geo_rule', WP_Auth0_RulesLib::$geo['name'] . '-' . get_bloginfo('name'), WP_Auth0_RulesLib::$geo['script'] );

		$input = $this->rule_validation( $old_options, $input, 'income_rule', WP_Auth0_RulesLib::$income['name'] . '-' . get_bloginfo('name'), WP_Auth0_RulesLib::$income['script'] );

		$fullcontact_script = WP_Auth0_RulesLib::$fullcontact['script'];
		$fullcontact_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['fullcontact_apikey'], $fullcontact_script );
		$input = $this->rule_validation( $old_options, $input, 'fullcontact', WP_Auth0_RulesLib::$fullcontact['name'] . '-' . get_bloginfo('name'), $fullcontact_script );

		$this->a0_options->set( 'fullcontact_apikey', $input['fullcontact_apikey'] );

		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=6' ) );
	}

	public function rule_validation( $old_options, $input, $key, $rule_name, $rule_script ) {
		$input[$key] = ( isset( $input[$key] ) ? $input[$key] : null );

		if ( ( !empty( $input[$key] ) &&  empty( $old_options[$key] ) ) || ( empty( $input[$key] ) && !empty( $old_options[$key] ) ) ) {
			try {

				$operations = new WP_Auth0_Api_Operations( $this->a0_options );
				$input[$key] = $operations->toggle_rule ( $this->a0_options->get( 'auth0_app_token' ), ( is_null( $input[$key] ) ? $old_options[$key] : null ), $rule_name, $rule_script );

				$this->a0_options->set( $key, $input[$key] );

			} catch ( Exception $e ) {
				$this->add_validation_error( $e->getMessage() );
				$input[$key] = null;
			}
		}

		return $input;
	}

	public function add_validation_error( $error ) {

		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=6&error=' . urlencode( 'There was an error setting up your rules.' ) ) );
		exit;

	}

}
