<?php

class WP_Auth0_Admin_Basic extends WP_Auth0_Admin_Generic {

	// TODO: Deprecate
	const BASIC_DESCRIPTION = 'Basic settings related to Auth0 credentials and basic WordPress integration.';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
	);

	/**
	 * WP_Auth0_Admin_Basic constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 */
	public function __construct( WP_Auth0_Options_Generic $options ) {
		parent::__construct( $options );
		$this->_description = __( 'Basic settings related to the Auth0 integration.', 'wp-auth0' );
	}

	public function init() {

		/* ------------------------- BASIC ------------------------- */
		add_action( 'wp_ajax_auth0_delete_cache_transient', array( $this, 'auth0_delete_cache_transient' ) );

		$this->init_option_section( '', 'basic', array(

				array( 'id' => 'wpa0_domain', 'name' => 'Domain', 'function' => 'render_domain' ),
				array( 'id' => 'wpa0_client_id', 'name' => 'Client ID', 'function' => 'render_client_id' ),
				array( 'id' => 'wpa0_client_secret', 'name' => 'Client Secret', 'function' => 'render_client_secret' ),
				array( 'id' => 'wpa0_client_secret_b64_encoded', 'name' => 'Client Secret Base64 Encoded', 'function' => 'render_client_secret_b64_encoded' ),
				array( 'id' => 'wpa0_client_signing_algorithm', 'name' => 'Client Signing Algorithm', 'function' => 'render_client_signing_algorithm' ),
				array( 'id' => 'wpa0_cache_expiration', 'name' => 'Cache Time (minutes)', 'function' => 'render_cache_expiration' ),
				array( 'id' => 'wpa0_auth0_app_token', 'name' => 'API token', 'function' => 'render_auth0_app_token' ),
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
          <a href="https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually" target="_blank"><?php echo __( 'token generator', 'wp-auth0' ); ?></a>
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

		$this->render_a0_switch( "wpa_client_secret_b64_encoded", "client_secret_b64_encoded", 1, 1 == $v );
	?>
				<div class="subelement">
					<span class="description"><?php echo __( 'Enable if your client secret is base64 enabled.  If you are not sure, check your clients page in Auth0.  Displayed below the client secret on that page is the text "The Client Secret is not base64 encoded.
	" when this is not encoded.', 'wp-auth0' ); ?></span>
				</div>
			<?php
	}

  public function render_client_signing_algorithm(){
		$v = $this->options->get( 'client_signing_algorithm' );
	?>

    <select id="wpa0_client_signing_algorithm" name="<?php echo $this->options->get_options_name() ?>[client_signing_algorithm]">
    	<option value="HS256" <?php echo ($v == "HS256" ? 'selected' : '') ?>>HS256</option>
    	<option value="RS256" <?php echo ($v == "RS256" ? 'selected' : '') ?>>RS256</option>
    </select>
    <div class="subelement">
			<span class="description"><?php echo __( 'If you use the default client secret to sign tokens, select HS256. See your clients page in Auth0. Advanced > OAuth > JsonWebToken Signature Algorithm', 'wp-auth0' ); ?></span>
		</div>
  <?php  
 	}

 public function render_cache_expiration() {
 		$v = $this->options->get( 'cache_expiration' );
 	?>
 	   <script>
	    function DeleteCacheTransient(event) {
	      event.preventDefault();

	      var data = {
	        'action': 'auth0_delete_cache_transient',
	      };

	      jQuery('#auth0_delete_cache_transient').attr('disabled', 'true');

	      jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

	        jQuery('#auth0_delete_cache_transient').val('Done!').attr('disabled', 'true');

	      }, 'json');

	    }
    </script>

     <input type="number" name="<?php echo $this->options->get_options_name(); ?>[cache_expiration]" id="wpa0_cache_expiration" value="<?php echo esc_attr( $v ); ?>" />
     
     <input type="button" onclick="DeleteCacheTransient(event);" name="auth0_delete_cache_transient" id="auth0_delete_cache_transient" value="Delete Cache" class="button button-secondary" />

  		<div class="subelement">
				<span class="description"><?php echo __( 'JWKS cache expiration in minutes (0 = no caching)', WPA0_LANG ); ?></span>
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
	       <a href="<?php echo admin_url( 'options-general.php' ) ?>" target="_blank"><?php _e( 'here', 'wp-auth0' ) ?></a>.
      </span>

    <?php
	}

	public function render_allow_wordpress_login() {
		$v = absint( $this->options->get( 'wordpress_login_enabled' ) );

		$this->render_a0_switch( "wpa0_wp_login_enabled", "wordpress_login_enabled", 1, 1 == $v );
?>
      <div class="subelement">
        <span class="description"><?php echo __( 'Enable to allow existing and new WordPress logins to work. If this site already had users before you installed Auth0, and you want them to still be able to use those logins, enable this.', 'wp-auth0' ); ?></span>
      </div>
    <?php
	}

	// TODO: Deprecate
	public function render_basic_description() {
?>

    <p class=\"a0-step-text\"><?php echo self::BASIC_DESCRIPTION; ?></p>

    <?php
	}
	public function auth0_delete_cache_transient() {
		if ( ! is_admin() ) return;

		WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, 'deleting cache transient' );

		delete_transient('WP_Auth0_JWKS_cache');

	}

	public function basic_validation( $old_options, $input ) {

		if ( wp_cache_get( 'doing_db_update', WPA0_CACHE_GROUP ) ) {
			return $input;
		}

		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['cache_expiration'] = absint( $input['cache_expiration'] );

		$input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] )
			? $input['wordpress_login_enabled']
			: 0 );

		$input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );

		// Only replace the secret or token if a new value was set. If not, we will keep the last one entered.
		$input['client_secret'] = ( ! empty( $input['client_secret'] )
			? $input['client_secret']
			: $old_options['client_secret'] );

		$input['client_secret_b64_encoded'] = ( isset( $input['client_secret_b64_encoded'] )
			? $input['client_secret_b64_encoded'] == 1
			: false );

		$input['auth0_app_token'] = ( ! empty( $input['auth0_app_token'] )
			? $input['auth0_app_token']
			: $old_options['auth0_app_token'] );

		// If we have an app token, get and store the audience
		if ( ! empty( $input['auth0_app_token'] ) ) {
			$db_manager = new WP_Auth0_DBManager( WP_Auth0_Options::Instance() );

			if ( get_option( 'wp_auth0_client_grant_failed' ) ) {
				$db_manager->install_db( 16, $input['auth0_app_token'] );
			}

			if ( get_option( 'wp_auth0_grant_types_failed' ) ) {
				$db_manager->install_db( 17, $input['auth0_app_token'] );
			}
		}

		if ( empty( $input['domain'] ) ) {
			$this->add_validation_error( __( 'You need to specify domain', 'wp-auth0' ) );
		}

		if ( empty( $input['client_id'] ) ) {
			$this->add_validation_error( __( 'You need to specify a client id', 'wp-auth0' ) );
		}

		if ( empty( $input['client_secret'] ) && empty( $old_options['client_secret'] ) ) {
			$this->add_validation_error( __( 'You need to specify a client secret', 'wp-auth0' ) );
		}

		return $input;
	}
}