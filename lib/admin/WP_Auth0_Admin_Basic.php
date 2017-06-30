<?php

class WP_Auth0_Admin_Basic extends WP_Auth0_Admin_Generic {

	const BASIC_DESCRIPTION = 'Basic settings related to Auth0 credentials and basic WordPress integration.';

	protected $actions_middlewares = array(
		'basic_validation',
	);

	public function init() {

		/* ------------------------- BASIC ------------------------- */

		$this->init_option_section( '', 'basic', array(

				array( 'id' => 'wpa0_domain', 'name' => 'Domain', 'function' => 'render_domain' ),
				array( 'id' => 'wpa0_client_id', 'name' => 'Client ID', 'function' => 'render_client_id' ),
				array( 'id' => 'wpa0_client_secret', 'name' => 'Client Secret', 'function' => 'render_client_secret' ),
				array( 'id' => 'wpa0_client_secret_b64_encoded', 'name' => 'Client Secret Base64 Encoded', 'function' => 'render_client_secret_b64_encoded' ),
				array( 'id' => 'wpa0_auth0_app_token', 'name' => 'API token', 'function' => 'render_auth0_app_token' ), //we are not going to show the token
				array( 'id' => 'wpa0_login_enabled', 'name' => 'WordPress login enabled', 'function' => 'render_allow_wordpress_login' ),
				array( 'id' => 'wpa0_allow_signup', 'name' => 'Allow signup', 'function' => 'render_allow_signup' ),

			) );
	}


	public function render_client_id() {
		$v = $this->options->get( 'client_id' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[client_id]" id="wpa0_client_id" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'Application ID, copy from your application\'s settings in the', 'wp-auth0' ); ?> <a href="https://manage.auth0.com/#/applications" target="_blank">Auth0 dashboard</a>.</span>
      </div>
    <?php
	}

	public function render_auth0_app_token() {

		$scopes = WP_Auth0_Api_Client::GetConsentScopestoShow();
		$v = $this->options->get( 'auth0_app_token' );

?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[auth0_app_token]" id="wpa0_auth0_app_token" autocomplete="off" <?php if ( !empty( $v ) ) {?>placeholder="Not visible"<?php } ?> />
      <div class="subelement">
        <span class="description">
          <?php echo __( 'The token should be generated via the ', 'wp-auth0' ); ?>
          <a href="https://auth0.com/docs/api/v2" target="_blank"><?php echo __( 'token generator', 'wp-auth0' ); ?></a>
          <?php echo __( ' with the following scopes:', 'wp-auth0' ); ?>
          <i>
          <?php $a = 0; foreach ( $scopes as $resource => $actions ) { $a++;?>
            <b><?php echo $resource ?></b> (<?php echo $actions ?>)<?php
			if ( $a < count( $scopes ) - 1 ) {
				echo ", ";
			} else if ( $a === count( $scopes ) - 1 ) {
					echo " and ";
				}
?>
          <?php } ?>.
          </i>
        </span>
      </div>
    <?php
	}

	public function render_client_secret() {
		$v = $this->options->get( 'client_secret' );
?>
      <input type="text" autocomplete="off" name="<?php echo $this->options->get_options_name(); ?>[client_secret]" id="wpa0_client_secret"  <?php if ( !empty( $v ) ) {?>placeholder="Not visible"<?php } ?> />
      <div class="subelement">
        <span class="description"><?php echo __( 'Application secret, copy from your application\'s settings in the', 'wp-auth0' ); ?> <a href="https://manage.auth0.com/#/applications" target="_blank">Auth0 dashboard</a>.</span>
      </div>
    <?php
	}

	public function render_client_secret_b64_encoded() {
		$v = absint( $this->options->get( 'client_secret_b64_encoded' ) );

		echo $this->render_a0_switch( "wpa_client_secret_b64_encoded", "client_secret_b64_encoded", 1, 1 == $v );
	?>
				<div class="subelement">
					<span class="description"><?php echo __( 'Enable if your client secret is base64 enabled.  If you are not sure, check your clients page in Auth0.  Displayed below the client secret on that page is the text "The Client Secret is not base64 encoded.
	" when this is not encoded.', 'wp-auth0' ); ?></span>
				</div>
			<?php
	}

	public function render_domain() {
		$v = $this->options->get( 'domain' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[domain]" id="wpa0_domain" value="<?php echo esc_attr( $v ); ?>" />
      <div class="subelement">
        <span class="description"><?php echo __( 'Your Auth0 domain, you can see it in the', 'wp-auth0' ); ?> <a href="https://manage.auth0.com/#/applications" target="_blank">Auth0 dashboard</a><?php echo __( '. Example: foo.auth0.com', 'wp-auth0' ); ?></span>
      </div>
    <?php
	}


	public function render_allow_signup() {
		if ( is_multisite() ) {
			$this->render_allow_signup_regular_multisite();
		} else {
			$this->render_allow_signup_regular();
		}
	}

	public function render_allow_signup_regular_multisite() {
		$allow_signup = $this->options->is_wp_registration_enabled();
?>
      <span class="description">
        <?php echo __( 'Signup will be', 'wp-auth0' ); ?>

        <?php if ( ! $allow_signup ) { ?>
          <b><?php echo __( 'disabled', 'wp-auth0' ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Allow new registrations" in the Network Admin.', 'wp-auth0' ); ?>
        <?php } else { ?>
          <b><?php echo __( 'enabled', 'wp-auth0' ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Allow new registrations" in the Network Admin.', 'wp-auth0' ); ?>
        <?php } ?>

        <?php echo __( 'You can manage this setting on <code>Network Admin > Settings > Network Settings > Allow new registrations</code> (you need to set it up to <b>User accounts may be registered</b> or <b>Both sites and user accounts can be registered</b> depending on your preferences).', 'wp-auth0' ); ?>
      </span>

    <?php
	}

	public function render_allow_signup_regular() {
		$allow_signup = $this->options->is_wp_registration_enabled();
?>
      <span class="description">
        <?php echo __( 'Signup will be', 'wp-auth0' ); ?>

        <?php if ( ! $allow_signup ) { ?>
          <b><?php echo __( 'disabled', 'wp-auth0' ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Anyone can register" in the WordPress General Settings.', 'wp-auth0' ); ?>
        <?php } else { ?>
          <b><?php echo __( 'enabled', 'wp-auth0' ); ?></b>
          <?php echo __( ' because it is enabled by the setting "Anyone can register" in the WordPress General Settings.', 'wp-auth0' ); ?>
        <?php } ?>

        <?php echo __( 'You can manage this setting on <code>Settings > General > Membership</code>, Anyone can register', 'wp-auth0' ); ?>
      </span>

    <?php
	}

	public function render_allow_wordpress_login() {
		$v = absint( $this->options->get( 'wordpress_login_enabled' ) );

		echo $this->render_a0_switch( "wpa0_wp_login_enabled", "wordpress_login_enabled", 1, 1 == $v );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Enable to allow existing and new WordPress logins to work. If this site already had users before you installed Auth0, and you want them to still be able to use those logins, enable this.', 'wp-auth0' ); ?></span>
      </div>
    <?php
	}

	public function render_basic_description() {
?>

    <p class=\"a0-step-text\"><?php echo self::BASIC_DESCRIPTION; ?></p>

    <?php
	}

	public function basic_validation( $old_options, $input ) {

    // $input['registration_enabled'] = $old_options['registration_enabled'];

		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] ) ? $input['wordpress_login_enabled'] : 0 );
		$input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );

		// Only replace the secret or token if a new value was set. If not, we will keep the last one entered.
		$input['client_secret'] = ( !empty( $input['client_secret'] ) ? $input['client_secret'] : $old_options['client_secret'] );
		$input['client_secret_b64_encoded'] = ( isset( $input['client_secret_b64_encoded'] ) ? $input['client_secret_b64_encoded'] == 1 : false );
		$input['auth0_app_token'] = ( !empty( $input['auth0_app_token'] ) ? $input['auth0_app_token'] : $old_options['auth0_app_token'] );

		$error = '';
		$completeBasicData = true;
		if ( empty( $input['domain'] ) ) {
			$error = __( 'You need to specify domain', 'wp-auth0' );
			$this->add_validation_error( $error );
			$completeBasicData = false;
		}

		if ( empty( $input['client_id'] ) ) {
			$error = __( 'You need to specify a client id', 'wp-auth0' );
			$this->add_validation_error( $error );
			$completeBasicData = false;
		}
		if ( empty( $input['client_secret'] ) && empty( $old_options['client_secret'] ) ) {
			$error = __( 'You need to specify a client secret', 'wp-auth0' );
			$this->add_validation_error( $error );
			$completeBasicData = false;
		}
		
		return $input;
	}


}
