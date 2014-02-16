<?php

class WP_Auth0_Admin{
	public static function init(){
		add_action( 'admin_menu', array(__CLASS__, 'init_menu') );
		add_action( 'admin_init', array(__CLASS__, 'init_admin'));
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue'));
	}
	
	public static function admin_enqueue(){
		if(!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpa0')
			return;
		
		wp_enqueue_media();
		wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/js/admin.js', array('jquery'));
		wp_enqueue_style('media');
		
		wp_localize_script( 'wpa0_admin', 'wpa0', array(
			'media_title' => __('Choose your icon', WPA0_LANG),
			'media_button' => __('Choose icon', WPA0_LANG)
		));
	}
	
	public static function init_admin(){
		add_settings_section(
			'wp_auth0_settings_section', 
			__('Auth0 Settings', WPA0_LANG), 
			array(__CLASS__, 'render_description'), 
			WP_Auth0_Options::OPTIONS_NAME
		);
		
		add_settings_field(
			'wpa0_active', 
			__('Activate Auth0', WPA0_LANG), 
			array(__CLASS__, 'render_activate'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_active')
		);

		add_settings_field(
			'wpa0_auto_login',
			__('Auto Login (no widget)', WPA0_LANG),
			array(__CLASS__, 'render_auto_login'),
			WP_Auth0_Options::OPTIONS_NAME,
			'wp_auth0_settings_section',
			array('label_for' => 'wpa0_auto_login')
		);

		$auto_login = absint(WP_Auth0_Options::get( 'auto_login' )) == 1;
		if($auto_login)
			add_settings_field(
				'wpa0_auto_login_method',
				__('Auto Login Method', WPA0_LANG),
				array(__CLASS__, 'render_auto_login_method'),
				WP_Auth0_Options::OPTIONS_NAME,
				'wp_auth0_settings_section',
				array('label_for' => 'wpa0_auto_login_method')
			);
        
        add_settings_field(
			'wpa0_redirect_referer', 
			__('Enable on /sso/ Redirect', WPA0_LANG), 
			array(__CLASS__, 'render_redirect_referer'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_redirect_referer')
		);

		add_settings_field(
			'wpa0_ip_range_check',
			__('Enable on IP Ranges', WPA0_LANG),
			array(__CLASS__, 'render_ip_range_check'),
			WP_Auth0_Options::OPTIONS_NAME,
			'wp_auth0_settings_section',
			array('label_for' => 'wpa0_ip_range_check')
		);

		$use_ip_ranges = absint(WP_Auth0_Options::get( 'ip_range_check' )) == 1;
		if($use_ip_ranges)
			add_settings_field(
				'wpa0_ip_ranges',
				__('IP Ranges', WPA0_LANG),
				array(__CLASS__, 'render_ip_ranges'),
				WP_Auth0_Options::OPTIONS_NAME,
				'wp_auth0_settings_section',
				array('label_for' => 'wpa0_ip_ranges')
			);
        add_settings_field(
			'wpa0_wp_login_form', 
			__('Show WP Login Method', WPA0_LANG), 
			array(__CLASS__, 'render_wp_login_form'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_wp_login_form')
		);
        add_settings_field(
			'wpa0_wp_login_btn_text', 
			__('WP Login Button Text', WPA0_LANG), 
			array(__CLASS__, 'render_wp_login_btn_text'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_wp_login_btn_text')
		);
        
		add_settings_field(
			'wpa0_client_id', 
			__('Client ID', WPA0_LANG), 
			array(__CLASS__, 'render_client_id'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_client_id')
		);
		add_settings_field(
			'wpa0_client_secret', 
			__('Client Secret', WPA0_LANG), 
			array(__CLASS__, 'render_client_secret'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_client_secret')
		);
		add_settings_field(
			'wpa0_endpoint', 
			__('Auth0 OAuth Endpoint', WPA0_LANG), 
			array(__CLASS__, 'render_endpoint'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_endpoint')
		);
		add_settings_field(
			'wpa0_form_title', 
			__('Form Title', WPA0_LANG), 
			array(__CLASS__, 'render_form_title'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_form_title')
		);
		add_settings_field(
			'wpa0_form_desc', 
			__('Form Description', WPA0_LANG), 
			array(__CLASS__, 'render_form_desc'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_form_desc')
		);
		add_settings_field(
			'wpa0_show_icon', 
			__('Show Icon', WPA0_LANG), 
			array(__CLASS__, 'render_show_icon'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_show_icon')
		);
		add_settings_field(
			'wpa0_icon_url', 
			__('Icon URL', WPA0_LANG), 
			array(__CLASS__, 'render_icon_url'), 
			WP_Auth0_Options::OPTIONS_NAME, 
			'wp_auth0_settings_section', 
			array('label_for' => 'wpa0_icon_url')
		);
		
		
		register_setting(WP_Auth0_Options::OPTIONS_NAME, WP_Auth0_Options::OPTIONS_NAME, array(__CLASS__, 'input_validator'));
	}
	
	public static function render_client_id(){
		$v = WP_Auth0_Options::get( 'client_id' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[client_id]" id="wpa0_client_id" value="' . esc_attr( $v ) . '"/>';
	}
	public static function render_client_secret(){
		$v = WP_Auth0_Options::get( 'client_secret' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[client_secret]" id="wpa0_client_secret" value="' . esc_attr( $v ) . '"/>';
	}
	public static function render_endpoint(){
		$v = WP_Auth0_Options::get( 'endpoint' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[endpoint]" id="wpa0_endpoint" value="' . esc_attr( $v ) . '"/>';
		echo '<br/><span class="description">' . __('This should only contain the HTTP protocol and domain! Example: https://1337.auth0.com/', WPA0_LANG) . '</span>';
	}
	public static function render_form_title(){
		$v = WP_Auth0_Options::get( 'form_title' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[form_title]" id="wpa0_form_title" value="' . esc_attr( $v ) . '"/>';
	}
	public static function render_form_desc(){
		$v = WP_Auth0_Options::get( 'form_desc' );
		echo '<textarea name="' . WP_Auth0_Options::OPTIONS_NAME . '[form_desc]" id="wpa0_form_desc">' . esc_textarea( $v ) . '</textarea>';
	}
	public static function render_wp_login_btn_text(){
		$v = WP_Auth0_Options::get( 'wp_login_btn_text' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[wp_login_btn_text]" id="wpa0_wp_login_btn_text" value="' . esc_attr( $v ) . '"/>';
	}
	
	public static function render_activate(){
		$v = absint(WP_Auth0_Options::get( 'active' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[active]" id="wpa0_active" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	public static function render_auto_login(){
		$v = absint(WP_Auth0_Options::get( 'auto_login' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[auto_login]" id="wpa0_auto_login" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	public static function render_auto_login_method(){
		$v = WP_Auth0_Options::get( 'auto_login_method' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[auto_login_method]" id="wpa0_auto_login_method" value="' . esc_attr( $v ) . '"/>';
		echo '<br/><span class="description">' . __('To find the method name, log into Auth0 Dashboard, and navigate to: Connection -> [Connection Type] (eg. Social or Enterprise). Click the "down arrow" to expand the wanted method, and use the value in the "Name"-field. Example: google-oauth2', WPA0_LANG) . '</span>';
	}
	public static function render_redirect_referer(){
		$v = absint(WP_Auth0_Options::get( 'redirect_referer' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[redirect_referer]" id="wpa0_redirect_referer" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	public static function render_ip_range_check(){
		$v = absint(WP_Auth0_Options::get( 'ip_range_check' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[ip_range_check]" id="wpa0_ip_range_check" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	public static function render_ip_ranges(){
		$v = WP_Auth0_Options::get( 'ip_ranges' );
		echo '<textarea cols="25" name="' . WP_Auth0_Options::OPTIONS_NAME . '[ip_ranges]" id="wpa0_ip_ranges">' . esc_textarea( $v ) . '</textarea>';
		echo '<br/><span class="description">' . __('Only one range per line! Range format should be as: <code>xx.xx.xx.xx - yy.yy.yy.yy</code> (spaces will be trimmed)', WPA0_LANG) . '</span>';
	}
	public static function render_wp_login_form(){
		$v = absint(WP_Auth0_Options::get( 'wp_login_form' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[wp_login_form]" id="wpa0_wp_login_form" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	public static function render_show_icon(){
		$v = absint(WP_Auth0_Options::get( 'show_icon' ));
		echo '<input type="checkbox" name="' . WP_Auth0_Options::OPTIONS_NAME . '[show_icon]" id="wpa0_show_icon" value="1" ' . checked( $v, 1, false ) . '/>';
	}
	
	public static function render_icon_url(){
		$v = WP_Auth0_Options::get( 'icon_url' );
		echo '<input type="text" name="' . WP_Auth0_Options::OPTIONS_NAME . '[icon_url]" id="wpa0_icon_url" value="' . esc_attr( $v ) . '"/>';
		echo ' <a href="javascript:void(0);" id="wpa0_choose_icon" class="button-secondary">' . __( 'Choose Icon', WPA0_LANG ) . '</a>';
		echo '<br/><span class="description">' . __('The icon should be 32x32 pixels!', WPA0_LANG) . '</span>';
	}
	
	public static function render_description(){
		
	}
	
	public static function init_menu(){
		add_options_page( __('Auth0 Settings', WPA0_LANG), __('Auth0 Settings', WPA0_LANG), 'manage_options', 'wpa0', array(__CLASS__, 'render_settings_page') );
	}
	
	public static function render_settings_page(){
		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}
	
	public static function input_validator( $input ){
		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['form_title'] = sanitize_text_field( $input['form_title'] );
		$input['icon_url'] = esc_url( $input['icon_url'], array(
			'http',
			'https'
		));
		if(empty($input['icon_url']))
			$input['show_icon'] = 0;
		else
			$input['show_icon'] = (isset($input['show_icon']) ? 1 : 0);
		$input['active'] = (isset($input['active']) ? 1 : 0);
		
		
		$input['endpoint'] = esc_url( $input['endpoint'], array('https', 'http') );
		if(!empty($input['endpoint']))
			$input['endpoint'] = trailingslashit($input['endpoint']);
		
		return $input;
	}
}