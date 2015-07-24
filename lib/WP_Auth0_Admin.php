<?php

class WP_Auth0_Admin {

	const BASIC_DESCRIPTION = 'Basic settings related to auth0 credentials and basic WordPress integration.';
	const FEATURES_DESCRIPTION = 'Settings related to specific features provided by the plugin.';
	const APPEARANCE_DESCRIPTION = 'Settings related to the way the login widget is shown.';
	const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'init_admin' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
	}

	public static function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0' !== $_REQUEST['page'] ) {
			return;
		}

		add_action( 'admin_notices', array( __CLASS__, 'create_account_message' ) );

		wp_enqueue_media();
		wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_style( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/css/settings.css' );
		wp_enqueue_style( 'media' );

		wp_localize_script( 'wpa0_admin', 'wpa0', array(
			'media_title' => __( 'Choose your icon', WPA0_LANG ),
			'media_button' => __( 'Choose icon', WPA0_LANG ),
		) );
	}

	protected static function init_option_section($sectionName, $id, $settings) {
		$options_name = WP_Auth0_Options::Instance()->get_options_name();

		add_settings_section(
			"wp_auth0_{$id}_settings_section",
			__( $sectionName, WPA0_LANG ),
			array( __CLASS__, "render_{$id}_description" ),
			$options_name
		);

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['id'],
				__( $setting['name'], WPA0_LANG ),
				array( __CLASS__, $setting['function'] ),
				$options_name,
				"wp_auth0_{$id}_settings_section",
				array( 'label_for' => $setting['id'] )
			);
		}
	}

	public static function init_admin() {

		/* ------------------------- BASIC ------------------------- */

		self::init_option_section( self::build_section_title( 'Basic', self::BASIC_DESCRIPTION ), 'basic', array(

			array( 'id' => 'wpa0_domain', 'name' => 'Domain', 'function' => 'render_domain' ),
			array( 'id' => 'wpa0_client_id', 'name' => 'Client ID', 'function' => 'render_client_id' ),
			array( 'id' => 'wpa0_client_secret', 'name' => 'Client Secret', 'function' => 'render_client_secret' ),
			array( 'id' => 'wpa0_auth0_app_token', 'name' => 'App token', 'function' => 'render_auth0_app_token' ),
			array( 'id' => 'wpa0_login_enabled', 'name' => 'WordPress login enabled', 'function' => 'render_allow_wordpress_login' ),
			array( 'id' => 'wpa0_allow_signup', 'name' => 'Allow signup', 'function' => 'render_allow_signup' ),

		) );

		/* ------------------------- Features ------------------------- */

		self::init_option_section( self::build_section_title( 'Features', self::FEATURES_DESCRIPTION ), 'features',array(

			array( 'id' => 'wpa0_sso', 'name' => 'Single Sign On (SSO)', 'function' => 'render_sso' ),
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

		self::init_option_section( self::build_section_title( 'Appearance', self::APPEARANCE_DESCRIPTION ), 'appearance', array(

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

			array( 'id' => 'wpa0_dict', 'name' => 'Translation', 'function' => 'render_dict' ),
			array( 'id' => 'wpa0_default_login_redirection', 'name' => 'Login redirection URL', 'function' => 'render_default_login_redirection' ),
			array( 'id' => 'wpa0_verified_email', 'name' => 'Requires verified email', 'function' => 'render_verified_email' ),
			array( 'id' => 'wpa0_auth0_implicit_workflow', 'name' => 'Auth0 Implicit flow', 'function' => 'render_auth0_implicit_workflow' ),
			array( 'id' => 'wpa0_auto_login', 'name' => 'Auto Login (no widget)', 'function' => 'render_auto_login' ),
			array( 'id' => 'wpa0_auto_login_method', 'name' => 'Auto Login Method', 'function' => 'render_auto_login_method' ),
			array( 'id' => 'wpa0_ip_range_check', 'name' => 'Enable on IP Ranges', 'function' => 'render_ip_range_check' ),
			array( 'id' => 'wpa0_ip_ranges', 'name' => 'IP Ranges', 'function' => 'render_ip_ranges' ),
			array( 'id' => 'wpa0_extra_conf', 'name' => 'Extra settings', 'function' => 'render_extra_conf' ),
			array( 'id' => 'wpa0_cdn_url', 'name' => 'Widget URL', 'function' => 'render_cdn_url' ),

		);

		if ( WP_Auth0::is_jwt_auth_enabled() ) {
			$advancedOptions[] = array( 'id' => 'wpa0_jwt_auth_integration', 'name' => 'Enable JWT Auth integration', 'function' => 'render_jwt_auth_integration' );
		}

		self::init_option_section( self::build_section_title( 'Advanced', self::ADVANCED_DESCRIPTION ), 'advanced', $advancedOptions );

		$options_name = WP_Auth0_Options::Instance()->get_options_name();
		register_setting( $options_name, $options_name, array( __CLASS__, 'input_validator' ) );
	}

	public static function render_extra_conf() {
		$v = WP_Auth0_Options::Instance()->get( 'extra_conf' );
	?>
		<textarea name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[extra_conf]" id="wpa0_extra_conf"><?php echo esc_attr( $v ); ?></textarea>
		<div class="subelement">
			<span class="description">
				<?php echo __( 'This field is the JSon that describes the options to call Lock with. It\'ll override any other option set here. See all the posible options ', WPA0_LANG ); ?>
				<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization"><?php echo __( 'here', WPA0_LANG ); ?></a>
				(IE: <code>{"disableResetAction": true }</code>)
			</span>
		</div>
	<?php
	}

	public static function render_remember_last_login() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'remember_last_login' ) );
	?>
		<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[remember_last_login]" id="wpa0_remember_last_login" value="1" <?php echo checked( $v, 1, false ); ?> />
		<div class="subelement">
			<span class="description">
				<?php echo __( 'Request for SSO data and enable "Last time you signed in with[...]" message.', WPA0_LANG ); ?>
				<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#rememberlastlogin-boolean"><?php echo __( 'More info', WPA0_LANG ); ?></a>
			</span>
		</div>
	<?php
	}

	public static function render_jwt_auth_integration() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'jwt_auth_integration' ) );
	?>
		<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[jwt_auth_integration]" id="wpa0_jwt_auth_integration" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'This will enable the JWT Auth\'s Users Repository override.', WPA0_LANG ); ?></span>
		</div>
	<?php
	}

	public static function create_account_message() {
		?>
		<div id="message" class="updated">
			<p>
				<strong>
					<?php echo __( 'In order to use this plugin, you need to first', WPA0_LANG ); ?>
					<a target="_blank" href="https://app.auth0.com/#/applications"><?php echo __( 'create an application', WPA0_LANG ); ?></a>
					<?php echo __( ' on Auth0 and copy the information here.', WPA0_LANG ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	public static function render_client_id() {
		$v = WP_Auth0_Options::Instance()->get( 'client_id' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[client_id]" id="wpa0_client_id" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Application ID, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_auth0_app_token() {
		$v = WP_Auth0_Options::Instance()->get( 'auth0_app_token' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[auth0_app_token]" id="wpa0_auth0_app_token" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'The token should be generated via the ', WPA0_LANG ); ?>
					<a href="https://auth0.com/docs/api/v2" target="_blank"><?php echo __( 'token generator', WPA0_LANG ); ?></a>
					<?php echo __( ' with the scopres required to create, read and update your account clients, users, rules and connections', WPA0_LANG ); ?>
				</span>
			</div>
		<?php
	}

	public static function render_client_secret() {
		$v = WP_Auth0_Options::Instance()->get( 'client_secret' );
		?>
			<input type="text" autocomplete="off" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[client_secret]" id="wpa0_client_secret" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Application secret, copy from your application\'s settings in the Auth0 dashboard', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_domain() {
		$v = WP_Auth0_Options::Instance()->get( 'domain' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[domain]" id="wpa0_domain" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Your Auth0 domain, you can see it in the dashboard. Example: foo.auth0.com', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_form_title() {
		$v = WP_Auth0_Options::Instance()->get( 'form_title' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[form_title]" id="wpa0_form_title" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'This is the title for the login widget', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_default_login_redirection() {
		$v = WP_Auth0_Options::Instance()->get( 'default_login_redirection' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[default_login_redirection]" id="wpa0_default_login_redirection" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'This is the URL that all users will be redirected by default after login', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_dict() {
		$v = WP_Auth0_Options::Instance()->get( 'dict' );
		?>
			<textarea name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[dict]" id="wpa0_dict"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This is the widget\'s dict param.', WPA0_LANG ); ?><a target="_blank" href="https://auth0.com/docs/libraries/lock/customization#4"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public static function render_custom_css() {
		$v = WP_Auth0_Options::Instance()->get( 'custom_css' );
		?>
			<textarea name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[custom_css]" id="wpa0_custom_css"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This should be a valid CSS to customize the Auth0 login widget. ', WPA0_LANG ); ?><a target="_blank" href="https://github.com/auth0/wp-auth0#can-i-customize-the-login-widget"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public static function render_custom_js() {
		$v = WP_Auth0_Options::Instance()->get( 'custom_js' );
		?>
			<textarea name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[custom_js]" id="wpa0_custom_js"><?php echo esc_attr( $v ); ?></textarea>
			<div class="subelement">
				<span class="description"><?php echo __( 'This should be a valid JS to customize the Auth0 login widget to, for example, add custom buttons. ', WPA0_LANG ); ?><a target="_blank" href="https://auth0.com/docs/hrd#3"><?php echo __( 'More info', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public static function render_username_style() {
		$v = WP_Auth0_Options::Instance()->get( 'username_style' );
		?>
			<input type="radio" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[username_style]" id="wpa0_username_style_email" value="email" <?php echo (esc_attr( $v ) == 'email' ? 'checked="true"' : '' ); ?> />
			<label for="wpa0_username_style_email"><?php echo __( 'Email', WPA0_LANG ); ?></label>

			&nbsp;

			<input type="radio" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[username_style]" id="wpa0_username_style_username" value="username" <?php echo (esc_attr( $v ) == 'username' ? 'checked="true"' : '' ); ?> />
			<label for="wpa0_username_style_username"><?php echo __( 'Username', WPA0_LANG ); ?></label>

			<div class="subelement">
				<span class="description">
					<?php echo __( 'If you don\'t want to validate that the user enters an email, just set this to username.', WPA0_LANG ); ?>
					<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#usernamestyle-string"><?php echo __( 'More info', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}

	public static function render_auth0_implicit_workflow() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'auth0_implicit_workflow' ) );
		?>
		<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[auth0_implicit_workflow]" id="wpa0_auth0_implicit_workflow" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to change the login workflow to allow the plugin work when the server does not have internet access)', WPA0_LANG ); ?></span>
		</div>
		<?php
	}

	public static function render_auto_login() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'auto_login' ) );
		?>
		<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[auto_login]" id="wpa0_auto_login" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to avoid the login page (you will have to select a single login provider)', WPA0_LANG ); ?></span>
		</div>
		<?php
	}

	public static function render_auto_login_method() {
		$v = WP_Auth0_Options::Instance()->get( 'auto_login_method' );
		?>
		<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[auto_login_method]" id="wpa0_auto_login_method" value="<?php echo esc_attr( $v ); ?>"/>
		<div class="subelement">
			<span class="description"><?php echo __( 'To find the method name, log into Auth0 Dashboard, and navigate to: Connection -> [Connection Type] (eg. Social or Enterprise). Click the "down arrow" to expand the wanted method, and use the value in the "Name"-field. Example: google-oauth2', WPA0_LANG ); ?></span>
		</div>
		<?php
	}

	public static function render_ip_range_check() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'ip_range_check' ) );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[ip_range_check]" id="wpa0_ip_range_check" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<?php
	}

	public static function render_ip_ranges() {
		$v = WP_Auth0_Options::Instance()->get( 'ip_ranges' );
		?>
		<textarea cols="25" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[ip_ranges]" id="wpa0_ip_ranges"><?php echo esc_textarea( $v ); ?></textarea>
		<div class="subelement">
			<span class="description"><?php echo __( 'Only one range per line! Range format should be as: <code>xx.xx.xx.xx - yy.yy.yy.yy</code> (spaces will be trimmed)', WPA0_LANG ); ?></span>
		</div>
		<?php
	}

	public static function render_social_big_buttons() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'social_big_buttons' ) );
		?>
		<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_big_buttons]" id="wpa0_social_big_buttons" value="1" <?php echo checked( $v, 1, false ); ?>/>
		<?php
	}

	public static function render_gravatar() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'gravatar' ) );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[gravatar]" id="wpa0_gravatar" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Read more about the gravatar integration ', WPA0_LANG ); ?>
					<a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#gravatar-boolean"><?php echo __( 'HERE', WPA0_LANG ); ?></a></span>
			</div>
		<?php
	}

	public static function render_icon_url() {
		$v = WP_Auth0_Options::Instance()->get( 'icon_url' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[icon_url]" id="wpa0_icon_url" value="<?php echo esc_attr( $v ); ?>"/>
			<a target="_blank" href="javascript:void(0);" id="wpa0_choose_icon" class="button-secondary"><?php echo __( 'Choose Icon', WPA0_LANG ); ?></a>
			<div class="subelement">
				<span class="description"><?php echo __( 'The icon should be 32x32 pixels!', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_cdn_url() {
		$v = WP_Auth0_Options::Instance()->get( 'cdn_url' );
		?>
			<input type="text" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[cdn_url]" id="wpa0_cdn_url" value="<?php echo esc_attr( $v ); ?>"/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Point this to the latest widget available in the CDN', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_sso() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'sso' ) );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[sso]" id="wpa0_sso" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to enable SSO. More info ', WPA0_LANG ); ?>
					<a target="_blank" href="https://auth0.com/docs/sso/single-sign-on"><?php echo __( 'HERE', WPA0_LANG ); ?></a>
				</span>
			</div>
		<?php
	}

	public static function render_mfa() {
		$v = WP_Auth0_Options::Instance()->get( 'mfa' );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[mfa]" id="wpa0_mfa" value="1" <?php echo (empty($v) ? '' : 'checked'); ?>/>
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

	public static function render_geo() {
		$v = WP_Auth0_Options::Instance()->get( 'geo_rule' );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[geo_rule]" id="wpa0_geo_rule" value="1" <?php echo (is_null($v) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to store geo location information based on your users IP in the user_metadata', WPA0_LANG );?>
				</span>
			</div>
		<?php
	}

	public static function render_income() {
		$v = WP_Auth0_Options::Instance()->get( 'income_rule' );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[income_rule]" id="wpa0_income_rule" value="1" <?php (is_null($v) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you want to store income data based on the zipcode (calculated using the users IP).', WPA0_LANG ); ?></span>
			</div>
			<div class="subelement">
				<span class="description"><?php echo __( 'Represents the median income of the users zipcode, based on last US census data.', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_fullcontact() {
		$v = WP_Auth0_Options::Instance()->get( 'fullcontact' );
		?>
			<input type="checkbox" id="wpa0_fullcontact" value="1" <?php echo (empty($v) ? '' : 'checked'); ?> />

			<div class="subelement fullcontact <?php echo (empty($v) ? 'hidden' : ''); ?>">
				<label for="wpa0_fullcontact_key" id="wpa0_fullcontact_key_label">Enter your FullContact api key:</label>
				<input type="text" id="wpa0_fullcontact_key" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[fullcontact]" value="<?php echo $v; ?>" />
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

	public static function render_social_facebook() {
		$social_facebook = WP_Auth0_Options::Instance()->get( 'social_facebook' );
		$social_facebook_key = WP_Auth0_Options::Instance()->get( 'social_facebook_key' );
		$social_facebook_secret = WP_Auth0_Options::Instance()->get( 'social_facebook_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_facebook]" id="wpa0_social_facebook" value="1" <?php echo checked( $social_facebook, 1, false ); ?>/>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_key" id="wpa0_social_facebook_key_label">Api key:</label>
				<input type="text" id="wpa0_social_facebook_key" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_facebook_key]" value="<?php echo $social_facebook_key; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_secret" id="wpa0_social_facebook_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_facebook_secret" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_facebook_secret]" value="<?php echo $social_facebook_secret; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_social_twitter() {
		$social_twitter = WP_Auth0_Options::Instance()->get( 'social_twitter' );
		$social_twitter_key = WP_Auth0_Options::Instance()->get( 'social_twitter_key' );
		$social_twitter_secret = WP_Auth0_Options::Instance()->get( 'social_twitter_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_twitter]" id="wpa0_social_twitter" value="1" <?php echo checked( $social_twitter, 1, false ); ?>/>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<label for="wpa0_social_twitter_key" id="wpa0_social_twitter_key_label">Api key:</label>
				<input type="text" id="wpa0_social_twitter_key" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_twitter_key]" value="<?php echo $social_twitter_key; ?>" />
			</div>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<label for="wpa0_social_twitter_secret" id="wpa0_social_twitter_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_twitter_secret" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_twitter_secret]" value="<?php echo $social_twitter_secret; ?>" />
			</div>
			<div class="subelement social_twitter <?php echo ($social_twitter ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_social_google_oauth2() {
		$social_google_oauth2 = WP_Auth0_Options::Instance()->get( 'social_google-oauth2' );
		$social_google_oauth2_key = WP_Auth0_Options::Instance()->get( 'social_google-oauth2_key' );
		$social_google_oauth2_secret = WP_Auth0_Options::Instance()->get( 'social_google-oauth2_secret' );
		?>
			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_google-oauth2]" id="wpa0_social_google_oauth2" value="1" <?php echo checked( $social_google_oauth2, 1, false ); ?>/>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<label for="wpa0_social_google_oauth2_key" id="wpa0_social_google_oauth2_key_label">Api key:</label>
				<input type="text" id="wpa0_social_google_oauth2_key" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_google-oauth2_key]" value="<?php echo $social_google_oauth2_key; ?>" />
			</div>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<label for="wpa0_social_google_oauth2_secret" id="wpa0_social_google_oauth2_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_google_oauth2_secret" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[social_google-oauth2_secret]" value="<?php echo $social_google_oauth2_secret; ?>" />
			</div>
			<div class="subelement social_google_oauth2 <?php echo ($social_google_oauth2 ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_social_other() {
		?>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Auth0 supports more than 30 different social connections (like Github, LinkedIn, Fitbit and more), enterprise connections (like google apps, ADFS, office 365 and more) and also custom Oauth2 connections. You can enable and configure them using the ', WPA0_LANG ); ?>
					<a href="https://manage.auth0.com/#/connections/social">Auth0 dashboard</a>
				</span>
			</div>
		<?php
	}

	public static function render_verified_email() {
		$v = absint( WP_Auth0_Options::Instance()->get( 'requires_verified_email' ) );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[requires_verified_email]" id="wpa0_verified_email" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you require the user to have a verified email to login', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_allow_signup() {
		$allow_signup = WP_Auth0_Options::Instance()->is_wp_registration_enabled();
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

				<?php echo __( 'You can manage this setting on Settings > General > Membership, Anyone can register', WPA0_LANG ); ?>?>
			</span>

		<?php
	}

	public static function render_allow_wordpress_login () {
		$v = absint( WP_Auth0_Options::Instance()->get( 'wordpress_login_enabled' ) );
		?>
			<input type="checkbox" name="<?php echo WP_Auth0_Options::Instance()->get_options_name(); ?>[wordpress_login_enabled]" id="wpa0_wp_login_enabled" value="1" <?php echo checked( $v, 1, false ); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you want to enable the regular WordPress login', WPA0_LANG ); ?></span>
			</div>
		<?php
	}

	public static function render_basic_description() {

	}

	public static function render_appearance_description() {

	}

	public static function render_features_description() {

	}

	public static function render_advanced_description() {

	}

	public static function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}

	protected static function add_validation_error( $error ) {
		add_settings_error(
			WP_Auth0_Options::Instance()->get_options_name(),
			WP_Auth0_Options::Instance()->get_options_name(),
			$error,
			'error'
		);
	}

	public static function basic_validation( $old_options, $input ) {
		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['form_title'] = sanitize_text_field( $input['form_title'] );
		$input['icon_url'] = esc_url( $input['icon_url'], array( 'http', 'https' ) );
		$input['requires_verified_email'] = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
		$input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] ) ? $input['wordpress_login_enabled'] : 0 );
		$input['jwt_auth_integration'] = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
		$input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );
		$input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
		$input['social_big_buttons'] = ( isset( $input['social_big_buttons'] ) ? $input['social_big_buttons'] : 0 );
		$input['gravatar'] = ( isset( $input['gravatar'] ) ? $input['gravatar'] : 0 );
		$input['remember_last_login'] = ( isset( $input['remember_last_login'] ) ? $input['remember_last_login'] : 0 );
		$input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );

		if ( trim( $input['dict'] ) !== '' ) {
			if ( strpos( $input['dict'], '{' ) !== false && json_decode( $input['dict'] ) === null ) {
				$error = __( 'The Translation parameter should be a valid json object.', WPA0_LANG );
				self::add_validation_error( $error );
			}
		}

		if ( trim( $input['extra_conf'] ) !== '' ) {
			if ( json_decode( $input['extra_conf'] ) === null ) {
				$error = __( 'The Extra settings parameter should be a valid json object.', WPA0_LANG );
				self::add_validation_error( $error );
			}
		}

		return $input;
	}

	public static function sso_validation( $old_options, $input ) {
		$input['sso'] = ( isset( $input['sso'] ) ? $input['sso'] : 0 );
		if ($old_options['sso'] != $input['sso'] && 1 == $input['sso']) {
			if ( false === WP_Auth0_Api_Client::update_client($input['domain'], $input['auth0_app_token'], $input['client_id'],$input['sso'] == 1) ) {

				$error = __( 'There was an error updating your Auth0 App to enable SSO. To do it manually, turn it on ', WPA0_LANG );
				$error .= '<a href="https://auth0.com/docs/sso/single-sign-on#1">HERE</a>.';
				self::add_validation_error( $error );

			}
		}
		return $input;
	}

	public static function fullcontact_validation( $old_options, $input ) {
		if ($old_options['fullcontact'] != $input['fullcontact']) {
			if (!empty($input['fullcontact'])) {
				$fullcontact_script = WP_Auth0_RulesLib::$fullcontact['script'];
				$fullcontact_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['fullcontact'], $fullcontact_script);
				$rule = WP_Auth0_Api_Client::create_rule($input['domain'], $input['auth0_app_token'], WP_Auth0_RulesLib::$fullcontact['name'], $fullcontact_script);

				if ( $rule === false ) {
					$error = __( 'There was an error creating the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
					self::add_validation_error( $error );
					$input['fullcontact'] = 0;
				} else {
					$input['fullcontact_rule'] = $rule->id;
				}
			}
			else {
				if ( false === WP_Auth0_Api_Client::delete_rule($input['domain'], $input['auth0_app_token'], $old_options['fullcontact_rule']) ) {
					$error = __( 'There was an error deleting your Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
					self::add_validation_error( $error );
					$input['fullcontact'] = 1;
				}
			}
		}
		return $input;
	}

	public static function mfa_validation( $old_options, $input ) {
		$input['mfa'] = ( isset( $input['mfa'] ) ? $input['mfa'] : 0 );

		if ($old_options['mfa'] == null && 1 == $input['mfa']) {
			$mfa_script = WP_Auth0_RulesLib::$google_MFA['script'];
			$mfa_script = str_replace('REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $mfa_script);
			$rule = WP_Auth0_Api_Client::create_rule($input['domain'], $input['auth0_app_token'], WP_Auth0_RulesLib::$google_MFA['name'], $mfa_script);

			if ( $rule === false ) {
				$error = __( 'There was an error creating the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['mfa'] = 0;
			} else {
				$input['mfa'] = $rule->id;
			}

		}
		elseif ($old_options['mfa'] != null && 0 == $input['mfa']) {
			if ( false === WP_Auth0_Api_Client::delete_rule($input['domain'], $input['auth0_app_token'], $old_options['mfa']) ) {
				$error = __( 'There was an error deleting the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['mfa'] = 1;
			} else {
				$input['mfa'] = null;
			}
		}
		else {
			$input['mfa'] = $old_options['mfa'];
		}
		return $input;
	}

	public static function georule_validation( $old_options, $input ) {
		$input['geo_rule'] = ( isset( $input['geo_rule'] ) ? $input['geo_rule'] : 0 );
		if ($old_options['geo_rule'] == null && 1 == $input['geo_rule']) {
			$rule = WP_Auth0_Api_Client::create_rule($input['domain'], $input['auth0_app_token'], WP_Auth0_RulesLib::$geo['name'], WP_Auth0_RulesLib::$geo['script']);

			if ( $rule === false ) {
				$error = __( 'There was an error creating the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['geo_rule'] = 0;
			} else {
				$input['geo_rule'] = $rule->id;
			}
		}
		elseif ($old_options['geo_rule'] != null && 0 == $input['geo_rule']) {
			if ( false === WP_Auth0_Api_Client::delete_rule($input['domain'], $input['auth0_app_token'], $old_options['geo_rule']) ) {
				$error = __( 'There was an error deleting the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['geo_rule'] = 1;
			} else {
				$input['geo_rule'] = null;
			}
		}
		else {
			$input['geo_rule'] = $old_options['geo_rule'];
		}
		return $input;
	}

	public static function incomerule_validation( $old_options, $input ) {
		$input['income_rule'] = ( isset( $input['income_rule'] ) ? $input['income_rule'] : 0 );
		if ($old_options['income_rule'] == null && 1 == $input['income_rule']) {
			$rule = WP_Auth0_Api_Client::create_rule($input['domain'], $input['auth0_app_token'], WP_Auth0_RulesLib::$income['name'], WP_Auth0_RulesLib::$income['script']);

			if ( $rule === false ) {
				$error = __( 'There was an error creating the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['income_rule'] = 0;
			} else {
				$input['income_rule'] = $rule->id;
			}
		}
		elseif ($old_options['income_rule'] != null && 0 == $input['income_rule']) {
			if ( false === WP_Auth0_Api_Client::delete_rule($input['domain'], $input['auth0_app_token'], $old_options['income_rule']) ) {
				$error = __( 'There was an error deleting the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
				self::add_validation_error( $error );
				$input['income_rule'] = 1;
			} else {
				$input['income_rule'] = null;
			}
		}
		else {
			$input['income_rule'] = $old_options['income_rule'];
		}
		return $input;
	}

	public static function socialfacebook_validation( $old_options, $input ) {
		return self::social_validation( $old_options, $input, 'facebook', array(
			"public_profile" => true,
			"email" => true,
			"user_birthday" => true,
		) );
 	}

	public static function socialtwitter_validation( $old_options, $input ) {
		return self::social_validation( $old_options, $input, 'twitter', array(
			"profile" => true,
		) );
 	}

	public static function socialgoogle_validation( $old_options, $input ) {
		return self::social_validation( $old_options, $input, 'google-oauth2', array(
			"google_plus" => true,
			"email" => true,
      		"profile" => true,
		) );
 	}

	/**
	 * This function will sync and update the connection setting with auth0
	 * First it checks if there is any connection with this strategy enabled for the app.
	 * - If exists, it checks if it has the facebook keys, in this case will ignore WP setting and will update the WP settings
	 * - If exists, it checks if it has the facebook keys, if not, it will update the connection with the new keys
	 *
	 * - If not exists, it will create a new connection
	 *
	 * In the case that the user disable the connection on WP, it check if there is an active connection with the client_id.
	 * - If exists, it will remove the client_id and if there is no other client_id it will delete the connection.
	 */
	public static function social_validation( $old_options, $input, $strategy, $connection_options ) {
		$main_key = "social_$strategy";

		$input[$main_key] = ( isset( $input[$main_key] ) ? $input[$main_key]  : 0);
		$input["{$main_key}_key"] = ( empty( $input["{$main_key}_key"] ) ? null  : trim( $input["{$main_key}_key"] ) );
		$input["{$main_key}_secret"] = ( empty( $input["{$main_key}_secret"] ) ? null  : trim( $input["{$main_key}_secret"] ) );

		if (
			$old_options[$main_key] != $input[$main_key] ||
			$old_options["{$main_key}_key"] != $input["{$main_key}_key"] ||
			$old_options["{$main_key}_secret"] != $input["{$main_key}_secret"]
			) {

			$connections = WP_Auth0_Api_Client::search_connection($input['domain'], $input['auth0_app_token'], $strategy);

			if ( ! $connections ) {
				$error = __( 'There was an error searching your active social connections.', WPA0_LANG );
				self::add_validation_error( $error );

				$input[$main_key] = 0;

				return $input;
			}

			$selected_connection = null;

			foreach ($connections as $connection) {
				if (in_array($input['client_id'], $connection->enabled_clients)) {
					$selected_connection = $connection;
					break;
				} elseif ( ! $selected_connection && count($connection->enabled_clients) == 0 ) {
					$selected_connection = $connection;
					$selected_connection->enabled_clients[] = $input['client_id'];
				} elseif ( $connection->name == 'facebook' ) {
					$selected_connection = $connection;
					$selected_connection->enabled_clients[] = $input['client_id'];
				}
			}
			if ( $selected_connection === null && count($connections) === 1) {
				$selected_connection = $connections[0];
				$selected_connection->enabled_clients[] = $input['client_id'];
			}

			if ( $input[$main_key] ) {

				if ( $selected_connection && empty($selected_connection->options->client_id) && empty($selected_connection->options->client_secret) ) {

					$data = array(
						'options' => array_merge($connection_options, array(
							"client_id" => $input["{$main_key}_key"],
      						"client_secret" => $input["{$main_key}_secret"],
						) ),
						'enabled_clients' => $connection->enabled_clients
					);

					if ( false === WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $selected_connection->id, $data) ) {
						$error = __( 'There was an error updating your social connection', WPA0_LANG );
						self::add_validation_error( $error );

						$input[$main_key] = 0;

						return $input;
					}
				} elseif ( $selected_connection && !empty($selected_connection->options->client_id) && !empty($selected_connection->options->client_secret) ) {

					$input["{$main_key}_key"] = $selected_connection->options->client_id;
					$input["{$main_key}_secret"] = $selected_connection->options->client_secret;

					self::add_validation_error('The connection has already setted an api key and secret and can not be overrided. Please update them from the <a href="https://manage.auth0.com/#/connections/social">Auth0 dashboard</a>');

					$data = array(
						'options' => array_merge($connection_options, array(
							"client_id" => $input["{$main_key}_key"],
      						"client_secret" => $input["{$main_key}_secret"],
						) ),
						'enabled_clients' => $connection->enabled_clients
					);

					if ( false === WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $selected_connection->id, $data) ) {
						$error = __( 'There was an error updating your social connection', WPA0_LANG );
						self::add_validation_error( $error );

						$input[$main_key] = 0;

						return $input;
					}

				} elseif ( ! $selected_connection ) {

					$data = array(
						'name' => $strategy,
						'strategy' => $strategy,
						'enabled_clients' => array( $input['client_id'] ),
						'options' => array_merge($connection_options, array(
							"client_id" => $input["{$main_key}_key"],
      						"client_secret" => $input["{$main_key}_secret"],
						) ),
					);

					if ( false === WP_Auth0_Api_Client::create_connection($input['domain'], $input['auth0_app_token'], $data) ) {
						$error = __( 'There was an error creating your social connection', WPA0_LANG );
						self::add_validation_error( $error );

						$input[$main_key] = 0;

						return $input;
					}
				}

			}
			else {
				if ($selected_connection) {
					$data['enabled_clients'] = array();
					foreach ($selected_connection->enabled_clients as $client) {
						if ($client != $input['client_id']) {
							$data['enabled_clients'][] = $client;
						}
					}

					if ( false === $a = WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $selected_connection->id, $data) ) {
						$error = __( 'There was an error disabling your social connection for this app.', WPA0_LANG );
						self::add_validation_error( $error );
						$input[$main_key] = 1;
					}
				}
			}

		}

		return $input;
	}

	public static function loginredirection_validation( $old_options, $input ) {
		$home_url = home_url();

		if ( empty( $input['default_login_redirection'] ) ) {
			$input['default_login_redirection'] = $home_url;
		} else {
			if ( strpos( $input['default_login_redirection'], $home_url ) !== 0 ) {
				if ( strpos( $input['default_login_redirection'], 'http' ) === 0 ) {
					$input['default_login_redirection'] = $home_url;
					$error = __( "The 'Login redirect URL' cannot point to a foreign page.", WPA0_LANG );
					self::add_validation_error( $error );
				}
			}

			if ( strpos( $input['default_login_redirection'], 'action=logout' ) !== false ) {
				$input['default_login_redirection'] = $home_url;

				$error = __( "The 'Login redirect URL' cannot point to the logout page. ", WPA0_LANG );
				self::add_validation_error( $error );
			}
		}
		return $input;
	}

	public static function basicdata_validation( $old_options, $input ) {
		$error = '';
		$completeBasicData = true;
		if ( empty( $input['domain'] ) ) {
			$error = __( 'You need to specify domain', WPA0_LANG );
			self::add_validation_error( $error );
			$completeBasicData = false;
		}

		if ( empty( $input['client_id'] ) ) {
			$error = __( 'You need to specify a client id', WPA0_LANG );
			self::add_validation_error( $error );
			$completeBasicData = false;
		}
		if ( empty( $input['client_secret'] ) ) {
			$error = __( 'You need to specify a client secret', WPA0_LANG );
			self::add_validation_error( $error );
			$completeBasicData = false;
		}

		if ( $completeBasicData ) {
			$response = WP_Auth0_Api_Client::get_token( $input['domain'], $input['client_id'], $input['client_secret'] );

			if ( $response instanceof WP_Error ) {
				$error = $response->get_error_message();
				self::add_validation_error( $error );
			} elseif ( 200 !== (int) $response['response']['code'] ) {
				$error = __( 'The client id or secret is not valid.', WPA0_LANG );
				self::add_validation_error( $error );
			}
		}
		return $input;
	}

	public static function input_validator( $input ){
		$old_options = WP_Auth0_Options::Instance()->get_options();

		$actions_middlewares = array(
			'basic_validation',
			'sso_validation',
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
			$input = self::$action($old_options, $input);
		}

		return $input;
	}

	protected static function build_section_title($title, $description) {
		return "<span class=\"title\">$title</span><span class=\"description\" title=\"$description\">$description</span>";
	}
}
