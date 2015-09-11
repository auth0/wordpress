<?php

class WP_Auth0_Admin {

	const BASIC_DESCRIPTION = 'Basic settings related to auth0 credentials and basic WordPress integration.';
	const FEATURES_DESCRIPTION = 'Settings related to specific features provided by the plugin.';
	const APPEARANCE_DESCRIPTION = 'Settings related to the way the login widget is shown.';
	const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

	protected $a0_options;
	protected $router;

	public function __construct(WP_Auth0_Options $a0_options, WP_Auth0_Routes $router) {
		$this->a0_options = $a0_options;
		$this->router = $router;
	}

	public function init() {
		add_action( 'admin_init', array( $this, 'init_admin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}

	public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0' !== $_REQUEST['page'] ) {
			return;
		}

		$client_id = $this->a0_options->get('client_id');
		$secret = $this->a0_options->get('client_secret');
		$domain = $this->a0_options->get('domain');

		if ( empty($client_id) || empty($secret) || empty($domain) ) {
				add_action( 'admin_notices', array( $this, 'create_account_message' ) );
		}

		$this->validate_required_api_scopes();

		wp_enqueue_media();
		wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_style( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/css/settings.css' );
		wp_enqueue_style( 'media' );

		wp_localize_script( 'wpa0_admin', 'wpa0', array(
			'media_title' => __( 'Choose your icon', WPA0_LANG ),
			'media_button' => __( 'Choose icon', WPA0_LANG ),
		) );
	}

	protected function validate_required_api_scopes() {
		$app_token = $this->get_token();
		if ( ! WP_Auth0_Api_Client::validate_user_token($app_token) ) {
			add_action( 'admin_notices', array( $this, 'cant_connect_to_auth0' ) );
		}
	}

	public function cant_connect_to_auth0(){
		?>
		<div id="message" class="error">
			<p>
				<strong>
					<?php echo __( 'The current user is not authorized to manage the Auth0 account. You must be both a WordPress site administrator and a user known to Auth0 to control Auth0 from this settings page. Please see the', WPA0_LANG ); ?>
					<a href="https://auth0.com/docs/wordpress"><?php echo __( 'documentation', WPA0_LANG ); ?></a>
					<?php echo __( 'for more information.', WPA0_LANG ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	protected function init_option_section($sectionName, $id, $settings) {
		$options_name = $this->a0_options->get_options_name();

		add_settings_section(
			"wp_auth0_{$id}_settings_section",
			__( $sectionName, WPA0_LANG ),
			array( $this, "render_{$id}_description" ),
			$options_name
		);

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['id'],
				__( $setting['name'], WPA0_LANG ),
				array( $this, $setting['function'] ),
				$options_name,
				"wp_auth0_{$id}_settings_section",
				array( 'label_for' => $setting['id'] )
			);
		}
	}

	public function init_admin() {

		/* ------------------------- BASIC ------------------------- */

		$this->init_option_section( $this->build_section_title( 'Basic', self::BASIC_DESCRIPTION ), 'basic', array(

			array( 'id' => 'wpa0_domain', 'name' => 'Domain', 'function' => 'render_domain' ),
			array( 'id' => 'wpa0_client_id', 'name' => 'Client ID', 'function' => 'render_client_id' ),
			array( 'id' => 'wpa0_client_secret', 'name' => 'Client Secret', 'function' => 'render_client_secret' ),
			// array( 'id' => 'wpa0_auth0_app_token', 'name' => 'App token', 'function' => 'render_auth0_app_token' ), //we are not going to show the token
			array( 'id' => 'wpa0_login_enabled', 'name' => 'WordPress login enabled', 'function' => 'render_allow_wordpress_login' ),
			array( 'id' => 'wpa0_allow_signup', 'name' => 'Allow signup', 'function' => 'render_allow_signup' ),

		) );

		/* ------------------------- Features ------------------------- */

		$this->init_option_section( $this->build_section_title( 'Features', self::FEATURES_DESCRIPTION ), 'features',array(

			array( 'id' => 'wpa0_brute_force_protection', 'name' => 'Brute Force Protection', 'function' => 'render_brute_force_protection' ),
			array( 'id' => 'wpa0_password_policy', 'name' => 'Password Policy', 'function' => 'render_password_policy' ),
			array( 'id' => 'wpa0_sso', 'name' => 'Single Sign On (SSO)', 'function' => 'render_sso' ),
			array( 'id' => 'wpa0_singlelogout', 'name' => 'Single Logout', 'function' => 'render_singlelogout' ),
			array( 'id' => 'wpa0_mfa', 'name' => 'Multifactor Authentication (MFA)', 'function' => 'render_mfa' ),
			array( 'id' => 'wpa0_fullcontact', 'name' => 'FullContact integration', 'function' => 'render_fullcontact' ),
			array( 'id' => 'wpa0_geo', 'name' => 'Store geolocation', 'function' => 'render_geo' ),
			array( 'id' => 'wpa0_income', 'name' => 'Store zipcode income', 'function' => 'render_income' ),
			array( 'id' => 'wpa0_social_facebook', 'name' => 'Login with Facebook', 'function' => 'render_social_facebook' ),
			array( 'id' => 'wpa0_social_twitter', 'name' => 'Login with Twitter', 'function' => 'render_social_twitter' ),
			array( 'id' => 'wpa0_social_google_oauth2', 'name' => 'Login with Google +', 'function' => 'render_social_google_oauth2' ),
			array( 'id' => 'wpa0_social_other', 'name' => '', 'function' => 'render_social_other' ),

		) );

		/* ------------------------- Appearance ------------------------- */

		$this->init_option_section( $this->build_section_title( 'Appearance', self::APPEARANCE_DESCRIPTION ), 'appearance', array(

			array( 'id' => 'wpa0_form_title', 'name' => 'Form Title', 'function' => 'render_form_title' ),
			array( 'id' => 'wpa0_social_big_buttons', 'name' => 'Show big social buttons', 'function' => 'render_social_big_buttons' ),
			array( 'id' => 'wpa0_icon_url', 'name' => 'Icon URL', 'function' => 'render_icon_url' ),
			array( 'id' => 'wpa0_gravatar', 'name' => 'Enable Gravatar integration', 'function' => 'render_gravatar' ),
			array( 'id' => 'wpa0_custom_css', 'name' => 'Customize the Login Widget CSS', 'function' => 'render_custom_css' ),
			array( 'id' => 'wpa0_custom_js', 'name' => 'Customize the Login Widget with custom JS', 'function' => 'render_custom_js' ),
			array( 'id' => 'wpa0_username_style', 'name' => 'Username style', 'function' => 'render_username_style' ),
			array( 'id' => 'wpa0_remember_last_login', 'name' => 'Remember last login', 'function' => 'render_remember_last_login' ),

		) );

		/* ------------------------- ADVANCED ------------------------- */

		$advancedOptions = array(

			array( 'id' => 'wpa0_link_auth0_users', 'name' => 'Link users with same email', 'function' => 'render_link_auth0_users' ),
			array( 'id' => 'wpa0_migration_ws', 'name' => 'Users Migration', 'function' => 'render_migration_ws' ),
			array( 'id' => 'wpa0_dict', 'name' => 'Translation', 'function' => 'render_dict' ),
			array( 'id' => 'wpa0_default_login_redirection', 'name' => 'Login redirection URL', 'function' => 'render_default_login_redirection' ),
			array( 'id' => 'wpa0_758ied_email', 'name' => 'Requires verified email', 'function' => 'render_verified_email' ),
			array( 'id' => 'wpa0_auth0_implicit_workflow', 'name' => 'Auth0 Implicit flow', 'function' => 'render_auth0_implicit_workflow' ),
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

		$this->init_option_section( $this->build_section_title( 'Advanced', self::ADVANCED_DESCRIPTION ), 'advanced', $advancedOptions );

		$options_name = $this->a0_options->get_options_name();
		register_setting( $options_name, $options_name, array( $this, 'input_validator' ) );
	}

	public function render_remember_last_login() {
		$v = absint( $this->a0_options->get( 'remember_last_login' ) );
	?>
		<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[remember_last_login]" id="wpa0_remember_last_login" value="1" <?php echo checked( $v, 1, false ); ?> />
		<div class="subelement">
			<span class="description">
				<?php echo __( 'Request for SSO data and enable "Last time you signed in with[...]" message.', WPA0_LANG ); ?>
				<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#rememberlastlogin-boolean"><?php echo __( 'More info', WPA0_LANG ); ?></a>
			</span>
		</div>
	<?php
	}

	public function render_jwt_auth_integration() {
		$v = absint( $this->a0_options->get( 'jwt_auth_integration' ) );
	?>
		<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[jwt_auth_integration]" id="wpa0_jwt_auth_integration" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'This will enable the JWT Auth\'s Users Repository override.', WPA0_LANG ); ?></span>
		</div>
	<?php
	}

	public function create_account_message() {
		?>
		<div id="message" class="updated">
			<p>
				<strong>
					<?php echo __( 'In order to use this plugin, you need to first', WPA0_LANG ); ?>
					<a target="_blank" href="https://manage.auth0.com/#/applications"><?php echo __( 'create an application', WPA0_LANG ); ?></a>
					<?php echo __( ' on Auth0 and copy the information here.', WPA0_LANG ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	public function render_client_id() {
		$v = $this->a0_options->get( 'client_id' );
		?>
			<input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[client_id]" id="wpa0_client_id" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Application ID, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_auth0_app_token() {
		$v = $this->a0_options->get( 'auth0_app_token' );
		?>
			<input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[auth0_app_token]" id="wpa0_auth0_app_token" value="<?php echo esc_attr( $v ); ?>"/>
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
		$v = $this->a0_options->get( 'client_secret' );
		?>
			<input type="text" autocomplete="off" name="<?php echo $this->a0_options->get_options_name(); ?>[client_secret]" id="wpa0_client_secret" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Application secret, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_domain() {
		$v = $this->a0_options->get( 'domain' );
		?>
			<input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[domain]" id="wpa0_domain" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Your Auth0 domain, you can see it in the dashboard. Example: foo.auth0.com', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_form_title() {
		$v = $this->a0_options->get( 'form_title' );
		?>
			<input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[form_title]" id="wpa0_form_title" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'This is the title for the login widget', WPA0_LANG ); ?></span>
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

		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[link_auth0_users]" id="wpa0_link_auth0_users" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'To enable the link of accounts with the same email. It will only occur if the email was verified before.', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_migration_ws() {
		$v = $this->a0_options->get( 'migration_ws' );
		$token = $this->a0_options->get( 'migration_token' );

		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[migration_ws]" id="wpa0_auth0_migration_ws" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this to expose a WS in order to easy the users migration process.', WPA0_LANG ); ?></span>
			<?php if( ! empty($token) ) {?>
				<span class="description"><?php echo __( 'Security token:', WPA0_LANG ); ?><code><?php echo $token; ?></code></span>
			<?php } ?>
			</div>
		<?php
	}

	public function render_dict() {
		$v = $this->a0_options->get( 'dict' );
		?>
			<textarea name="<?php echo $this->a0_options->get_options_name(); ?>[dict]" id="wpa0_dict"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This is the widget\'s dict param.', WPA0_LANG ); ?><a target="_blank" href="https://auth0.com/docs/libraries/lock/customization#4"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public function render_custom_css() {
		$v = $this->a0_options->get( 'custom_css' );
		?>
			<textarea name="<?php echo $this->a0_options->get_options_name(); ?>[custom_css]" id="wpa0_custom_css"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This should be a valid CSS to customize the Auth0 login widget. ', WPA0_LANG ); ?><a target="_blank" href="https://github.com/auth0/wp-auth0#can-i-customize-the-login-widget"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public function render_custom_js() {
		$v = $this->a0_options->get( 'custom_js' );
		?>
			<textarea name="<?php echo $this->a0_options->get_options_name(); ?>[custom_js]" id="wpa0_custom_js"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This should be a valid JS to customize the Auth0 login widget to, for example, add custom buttons. ', WPA0_LANG ); ?><a target="_blank" href="https://auth0.com/docs/hrd#3"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public function render_username_style() {
		$v = $this->a0_options->get( 'username_style' );
		?>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[username_style]" id="wpa0_username_style_email" value="email" <?php echo (esc_attr( $v ) == 'email' ? 'checked="true"' : '' ); ?> />
			<label for="wpa0_username_style_email"><?php echo __( 'Email', WPA0_LANG ); ?></label>

			&nbsp;

			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[username_style]" id="wpa0_username_style_username" value="username" <?php echo (esc_attr( $v ) == 'username' ? 'checked="true"' : '' ); ?> />
			<label for="wpa0_username_style_username"><?php echo __( 'Username', WPA0_LANG ); ?></label>

			<div class="subelement">
				<span class="description">
					<?php echo __( 'If you don\'t want to validate that the user enters an email, just set this to username.', WPA0_LANG ); ?>
					<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#usernamestyle-string"><?php echo __( 'More info', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}

	public function render_auth0_implicit_workflow() {
		$v = absint( $this->a0_options->get( 'auth0_implicit_workflow' ) );
		?>
		<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[auth0_implicit_workflow]" id="wpa0_auth0_implicit_workflow" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to change the login workflow to allow the plugin work when the server does not have internet access)', WPA0_LANG ); ?></span>
		</div>
		<?php
	}

	public function render_auto_login() {
		$v = absint( $this->a0_options->get( 'auto_login' ) );
		?>
		<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[auto_login]" id="wpa0_auto_login" value="1" <?php echo checked( $v, 1, false ); ?>/>
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
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[ip_range_check]" id="wpa0_ip_range_check" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<?php
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

	public function render_social_big_buttons() {
		$v = absint( $this->a0_options->get( 'social_big_buttons' ) );
		?>
		<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[social_big_buttons]" id="wpa0_social_big_buttons" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<?php
	}

	public function render_gravatar() {
		$v = absint( $this->a0_options->get( 'gravatar' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[gravatar]" id="wpa0_gravatar" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Read more about the gravatar integration ', WPA0_LANG ); ?>
					<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#gravatar-boolean"><?php echo __( 'HERE', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public function render_icon_url() {
		$v = $this->a0_options->get( 'icon_url' );
		?>
			<input type="text" name="<?php echo $this->a0_options->get_options_name(); ?>[icon_url]" id="wpa0_icon_url" value="<?php echo esc_attr( $v ); ?>"/>
			<a target="_blank" href="javascript:void(0);" id="wpa0_choose_icon" class="button-secondary"><?php echo __( 'Choose Icon', WPA0_LANG ); ?></a>
			<div class="subelement">
				<span class="description"><?php echo __( 'The icon should be 32x32 pixels!', WPA0_LANG ); ?></span>
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

	public function render_brute_force_protection() {
		$v = absint( $this->a0_options->get( 'brute_force_protection' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[brute_force_protection]" id="wpa0_brute_force_protection" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to enable Brute Force Protection.', WPA0_LANG ); ?>
				</span>
			</div>
		<?php
	}

	public function render_password_policy() {
		$v = $this->a0_options->get( 'password_policy' );
		?>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_none" value="" <?php echo checked( $v, null, false ); ?>/><label for="wpa0_password_policy_none">None</label>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_low" value="low" <?php echo checked( $v, 'low', false ); ?>/><label for="wpa0_password_policy_low">Low</label>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_fair" value="fair" <?php echo checked( $v, 'fair', false ); ?>/><label for="wpa0_password_policy_fair">Fair</label>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_good" value="good" <?php echo checked( $v, 'good', false ); ?>/><label for="wpa0_password_policy_good">Good</label>
			<input type="radio" name="<?php echo $this->a0_options->get_options_name(); ?>[password_policy]" id="wpa0_password_policy_excellent" value="excellent" <?php echo checked( $v, 'excellent', false ); ?>/><label for="wpa0_password_policy_excellent">Excellent</label>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'For more info about the password policies check ', WPA0_LANG ); ?>
					<a target="_blank" href="https://auth0.com/docs/password-strength"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}

	public function render_sso() {
		$v = absint( $this->a0_options->get( 'sso' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[sso]" id="wpa0_sso" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to enable SSO. More info ', WPA0_LANG ); ?>
					<a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}
	public function render_metrics() {
		$v = absint( $this->a0_options->get( 'metrics' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[metrics]" id="wpa0_metrics" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'This plugin tracks anonymous usage data. Click to disable.', WPA0_LANG ); ?>
				</span>
			</div>
		<?php
	}

	public function render_singlelogout() {
		$v = absint( $this->a0_options->get( 'singlelogout' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[singlelogout]" id="wpa0_singlelogout" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to enable Single Logout. More info ', WPA0_LANG ); ?>
					<a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}

	public function render_mfa() {
		$v = $this->a0_options->get( 'mfa' );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[mfa]" id="wpa0_mfa" value="1" <?php echo (empty($v) ? '' : 'checked'); ?>/>
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
		$v = $this->a0_options->get( 'geo_rule' );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[geo_rule]" id="wpa0_geo_rule" value="1" <?php echo (is_null($v) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to store geo location information based on your users IP in the user_metadata', WPA0_LANG );?>
				</span>
			</div>
		<?php
	}

	public function render_income() {
		$v = $this->a0_options->get( 'income_rule' );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[income_rule]" id="wpa0_income_rule" value="1" <?php echo (is_null($v) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you want to store income data based on the zipcode (calculated using the users IP).', WPA0_LANG ); ?></span>
			</div>
			<div class="subelement">
				<span class="description"><?php echo __( 'Represents the median income of the users zipcode, based on last US census data.', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_fullcontact() {
		$v = $this->a0_options->get( 'fullcontact' );
		$apikey = $this->a0_options->get( 'fullcontact_apikey' );
		?>
			<input type="checkbox" id="wpa0_fullcontact" name="<?php echo $this->a0_options->get_options_name(); ?>[fullcontact]" value="1" <?php echo (empty($v) ? '' : 'checked'); ?> />

			<div class="subelement fullcontact <?php echo (empty($v) ? 'hidden' : ''); ?>">
				<label for="wpa0_fullcontact_key" id="wpa0_fullcontact_key_label">Enter your FullContact api key:</label>
				<input type="text" id="wpa0_fullcontact_key" name="<?php echo $this->a0_options->get_options_name(); ?>[fullcontact_apikey]" value="<?php echo $apikey; ?>" />
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

	public function render_social_facebook() {
		$social_facebook = $this->a0_options->get( 'social_facebook' );
		$social_facebook_key = $this->a0_options->get( 'social_facebook_key' );
		$social_facebook_secret = $this->a0_options->get( 'social_facebook_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook]" id="wpa0_social_facebook" value="1" <?php echo checked( $social_facebook, 1, false ); ?>/>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_key" id="wpa0_social_facebook_key_label">Api key:</label>
				<input type="text" id="wpa0_social_facebook_key" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook_key]" value="<?php echo $social_facebook_key; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_secret" id="wpa0_social_facebook_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_facebook_secret" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook_secret]" value="<?php echo $social_facebook_secret; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_social_twitter() {
		$social_twitter = $this->a0_options->get( 'social_twitter' );
		$social_twitter_key = $this->a0_options->get( 'social_twitter_key' );
		$social_twitter_secret = $this->a0_options->get( 'social_twitter_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[social_twitter]" id="wpa0_social_twitter" value="1" <?php echo checked( $social_twitter, 1, false ); ?>/>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<label for="wpa0_social_twitter_key" id="wpa0_social_twitter_key_label">Api key:</label>
				<input type="text" id="wpa0_social_twitter_key" name="<?php echo $this->a0_options->get_options_name(); ?>[social_twitter_key]" value="<?php echo $social_twitter_key; ?>" />
			</div>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<label for="wpa0_social_twitter_secret" id="wpa0_social_twitter_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_twitter_secret" name="<?php echo $this->a0_options->get_options_name(); ?>[social_twitter_secret]" value="<?php echo $social_twitter_secret; ?>" />
			</div>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_social_google_oauth2() {
		$social_google_oauth2 = $this->a0_options->get( 'social_google-oauth2' );
		$social_google_oauth2_key = $this->a0_options->get( 'social_google-oauth2_key' );
		$social_google_oauth2_secret = $this->a0_options->get( 'social_google-oauth2_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[social_google-oauth2]" id="wpa0_social_google_oauth2" value="1" <?php echo checked( $social_google_oauth2, 1, false ); ?>/>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<label for="wpa0_social_google_oauth2_key" id="wpa0_social_google_oauth2_key_label">Api key:</label>
				<input type="text" id="wpa0_social_google_oauth2_key" name="<?php echo $this->a0_options->get_options_name(); ?>[social_google-oauth2_key]" value="<?php echo $social_google_oauth2_key; ?>" />
			</div>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<label for="wpa0_social_google_oauth2_secret" id="wpa0_social_google_oauth2_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_google_oauth2_secret" name="<?php echo $this->a0_options->get_options_name(); ?>[social_google-oauth2_secret]" value="<?php echo $social_google_oauth2_secret; ?>" />
			</div>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_social_other() {
		?>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Auth0 supports more than 30 different social connections (like Github, LinkedIn, Fitbit and more), enterprise connections (like google apps, ADFS, office 365 and more) and also custom Oauth2 connections. You can enable and configure them using the ', WPA0_LANG ); ?>
					<a href="https://manage.auth0.com/#/connections/social" target="_blank">Auth0 dashboard</a>
				</span>
			</div>
		<?php
	}

	public function render_verified_email() {
		$v = absint( $this->a0_options->get( 'requires_verified_email' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[requires_verified_email]" id="wpa0_verified_email" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you require the user to have a verified email to login', WPA0_LANG ); ?></span>
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
		$allow_signup = $this->a0_options->is_wp_registration_enabled();
		?>
			<span class="description">
				<?php echo __( 'Signup will be ', WPA0_LANG ); ?>

				<?php if ( ! $allow_signup ) { ?>
					<b><?php echo __( 'disabled', WPA0_LANG ); ?></b>
					<?php echo __( ' because it is enabled by the setting "Allow new registrations" in the Network Admin.', WPA0_LANG ); ?><br>
				<?php } else { ?>
					<b><?php echo __( 'enabled', WPA0_LANG ); ?></b>
					<?php echo __( ' because it is enabled by the setting "Allow new registrations" nn the Network Admin.', WPA0_LANG ); ?><br>
				<?php } ?>

				<?php echo __( 'You can manage this setting on Network Admin > Settings > Network Settings > Allow new registrations (you need to set it up to <b>User accounts may be registered</b> or <b>Both sites and user accounts can be registered</b> depending on your preferences).', WPA0_LANG ); ?>
			</span>

		<?php
	}

	public function render_allow_signup_regular() {
		$allow_signup = $this->a0_options->is_wp_registration_enabled();
		?>
			<span class="description">
				<?php echo __( 'Signup will be ', WPA0_LANG ); ?>

				<?php if ( ! $allow_signup ) { ?>
					<b><?php echo __( 'disabled', WPA0_LANG ); ?></b>
					<?php echo __( ' because you have turned on the setting " Anyone can register" off WordPress', WPA0_LANG ); ?><br>
				<?php } else { ?>
					<b><?php echo __( 'enabled', WPA0_LANG ); ?></b>
					<?php echo __( ' because you have turned on the setting " Anyone can register" on WordPress', WPA0_LANG ); ?><br>
				<?php } ?>

				<?php echo __( 'You can manage this setting on Settings > General > Membership, Anyone can register', WPA0_LANG ); ?>
			</span>

		<?php
	}

	public function render_allow_wordpress_login () {
		$v = absint( $this->a0_options->get( 'wordpress_login_enabled' ) );
		?>
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[wordpress_login_enabled]" id="wpa0_wp_login_enabled" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you want to enable the regular WordPress login', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public function render_basic_description() {

	}

	public function render_appearance_description() {

	}

	public function render_features_description() {

	}

	public function render_advanced_description() {

	}

	public function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}

	protected function add_validation_error( $error ) {
		add_settings_error(
			$this->a0_options->get_options_name(),
			$this->a0_options->get_options_name(),
			$error,
			'error'
		);
	}

	public function basic_validation( $old_options, $input ) {
		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['form_title'] = sanitize_text_field( $input['form_title'] );
		$input['icon_url'] = esc_url( $input['icon_url'], array( 'http', 'https' ) );
		$input['requires_verified_email'] = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
		$input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] ) ? $input['wordpress_login_enabled'] : 0 );
		$input['link_auth0_users'] = ( isset( $input['link_auth0_users'] ) ? $input['link_auth0_users'] : 0 );
		$input['jwt_auth_integration'] = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
		$input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );
		$input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
		$input['social_big_buttons'] = ( isset( $input['social_big_buttons'] ) ? $input['social_big_buttons'] : 0 );
		$input['gravatar'] = ( isset( $input['gravatar'] ) ? $input['gravatar'] : 0 );
		$input['remember_last_login'] = ( isset( $input['remember_last_login'] ) ? $input['remember_last_login'] : 0 );
		$input['singlelogout'] = ( isset( $input['singlelogout'] ) ? $input['singlelogout'] : 0 );
		$input['metrics'] = ( isset( $input['metrics'] ) ? $input['metrics'] : 0 );
		$input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );
		$input['auth0_app_token'] = $old_options['auth0_app_token'];
		$input['auth0_app_token'] = $old_options['auth0_app_token'];

		if ( trim( $input['dict'] ) !== '' ) {
			if ( strpos( $input['dict'], '{' ) !== false && json_decode( $input['dict'] ) === null ) {
				$error = __( 'The Translation parameter should be a valid json object.', WPA0_LANG );
				$this->add_validation_error( $error );
			}
		}

		if ( trim( $input['extra_conf'] ) !== '' ) {
			if ( json_decode( $input['extra_conf'] ) === null ) {
				$error = __( 'The Extra settings parameter should be a valid json object.', WPA0_LANG );
				$this->add_validation_error( $error );
			}
		}

		return $input;
	}

	public function sso_validation( $old_options, $input ) {
		$input['sso'] = ( isset( $input['sso'] ) ? $input['sso'] : 0 );
		if ($old_options['sso'] != $input['sso'] && 1 == $input['sso']) {
			if ( false === WP_Auth0_Api_Client::update_client($input['domain'], $this->get_token(), $input['client_id'],$input['sso'] == 1) ) {

				$error = __( 'There was an error updating your Auth0 App to enable SSO. To do it manually, turn it on ', WPA0_LANG );
				$error .= '<a href="https://auth0.com/docs/sso/single-sign-on#1">HERE</a>.';
				$this->add_validation_error( $error );

			}
		}
		return $input;
	}

	public function security_validation( $old_options, $input ) {

		$input['brute_force_protection'] = ( isset( $input['brute_force_protection'] ) ? $input['brute_force_protection'] : 0 );
		$input['password_policy'] = ( isset( $input['password_policy'] ) && $input['password_policy'] != "" ? $input['password_policy'] : null );

		if ($old_options['brute_force_protection'] != $input['brute_force_protection'] ||
				$old_options['password_policy'] != $input['password_policy']) {

			$connections = WP_Auth0_Api_Client::search_connection($input['domain'], $this->get_token(), 'auth0');

			foreach ($connections as $connection) {

				if ( in_array($input['client_id'], $connection->enabled_clients) ) {
					if ( false === WP_Auth0_Api_Client::update_connection($input['domain'], $this->get_token(), $connection->id, array(
						'options' => array(
							'brute_force_protection' => $input['brute_force_protection'],
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

	public function migration_ws_validation( $old_options, $input ) {
		$input['migration_ws'] = ( isset( $input['migration_ws'] ) ? $input['migration_ws'] : 0 );

		if ( $old_options['migration_ws'] != $input['migration_ws'] ) {

			if ( 1 == $input['migration_ws'] ) {
				$secret = $this->a0_options->get( 'client_secret' );
				$token_id = uniqid();
				$input['migration_token'] = JWT::encode(array('scope' => 'migration_ws', 'jti' => $token_id), JWT::urlsafeB64Decode( $secret ));
				$input['migration_token_id'] = $token_id;

				$operations = new WP_Auth0_Api_Operations($this->a0_options);
				$response = $operations->enable_users_migration($this->get_token(), $input['migration_token']);

				if ($response === false) {
					$error = __( 'There was an error enabling your custom database. Check how to do it manually ', WPA0_LANG );
					$error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
					$this->add_validation_error( $error );
				}

			} else {
				$input['migration_token'] = null;
				$input['migration_token_id'] = null;

				$response = WP_Auth0_Api_Client::update_connection($input['domain'], $this->get_token(), $old_options['migration_connection_id'], array(
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

	public function fullcontact_validation( $old_options, $input ) {
		$fullcontact_script = WP_Auth0_RulesLib::$fullcontact['script'];
		$fullcontact_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['fullcontact_apikey'], $fullcontact_script);
		return $this->rule_validation($old_options, $input, 'fullcontact', WP_Auth0_RulesLib::$fullcontact['name'], $fullcontact_script);
	}

	public function rule_validation( $old_options, $input, $key, $rule_name, $rule_script ) {
		$input[$key] = ( isset( $input[$key] ) ? $input[$key] : null );

		if ($input[$key] !== null && $old_options[$key] === null || $input[$key] === null && $old_options[$key] !== null) {
			try {

				$operations = new WP_Auth0_Api_Operations($this->a0_options);
				$input[$key] = $operations->toggle_rule ( $this->get_token(), (is_null($input[$key]) ? $old_options[$key] : null), $rule_name, $rule_script );

			} catch (Exception $e) {
				$this->add_validation_error( $e->getMessage() );
				$input[$key] = null;
			}
		}

		return $input;
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

	public function socialfacebook_validation( $old_options, $input ) {
		try {
			$operations = new WP_Auth0_Api_Operations($this->a0_options);
			return $operations->social_validation( $this->get_token(), $old_options, $input, 'facebook', array(
				"public_profile" => true,
				"email" => true,
				"user_birthday" => true,
				"publish_actions" => true,
			) );
		} catch (Exception $e) {
			$this->add_validation_error($e->getMessage());
		}
 	}

	public function socialtwitter_validation( $old_options, $input ) {
		try{
			$operations = new WP_Auth0_Api_Operations($this->a0_options);
			return $operations->social_validation( $this->get_token(), $old_options, $input, 'twitter', array(
				"profile" => true,
			) );
		} catch (Exception $e) {
			$this->add_validation_error($e->getMessage());
		}
 	}

	public function socialgoogle_validation( $old_options, $input ) {
		try {
			$operations = new WP_Auth0_Api_Operations($this->a0_options);
			return $operations->social_validation( $this->get_token(), $old_options, $input, 'google-oauth2', array(
				"google_plus" => true,
				"email" => true,
      		"profile" => true,
			) );
		} catch (Exception $e) {
			$this->add_validation_error($e->getMessage());
		}
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

	public function input_validator( $input ){
		$old_options = $this->a0_options->get_options();

		$actions_middlewares = array(
			'basic_validation',
			'sso_validation',
			'security_validation',
			'migration_ws_validation',
			'fullcontact_validation',
			'mfa_validation',
			'georule_validation',
			'incomerule_validation',
			'loginredirection_validation',
			'basicdata_validation',
			'socialfacebook_validation',
			'socialtwitter_validation',
			'socialgoogle_validation',
		);

		foreach ($actions_middlewares as $action) {
			$input = $this->$action($old_options, $input);
		}

		return $input;
	}

	protected function build_section_title($title, $description) {
		return "<span class=\"title\">$title</span><span class=\"description\" title=\"$description\">$description</span>";
	}

	protected $token = null;
	protected function get_token() {
		if ( $this->token === null ) {
			$user = get_currentauth0user();
			if ($user && isset($user->access_token)) {
				$this->token = $user->access_token;
			}
		}
		return $this->token;
	}
}
