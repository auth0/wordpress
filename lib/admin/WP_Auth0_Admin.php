<?php

class WP_Auth0_Admin {

	protected $a0_options;
	protected $router;

	protected $providers = array(
		array( 'provider' => 'facebook', 'name' => 'Facebook', "icon" => 'Facebook', 'options' => array(
				"public_profile" => true,
				"email" => true,
				"user_birthday" => true,
				"publish_actions" => true,
			) ),
		array( 'provider' => 'twitter', 'name' => 'Twitter', "icon" => 'Twitter', 'options' => array(
				"profile" => true,
			) ),
		array( 'provider' => 'google-oauth2', 'name' => 'Google +', "icon" => 'Google', 'options' => array(
				"google_plus" => true,
				"email" => true,
				"profile" => true,
			) ),
		array( "provider" => 'windowslive', "name" => 'Microsoft Accounts', "icon" => 'Windows LiveID' ),
		array( "provider" => 'yahoo', "name" => 'Yahoo', "icon" => 'Yahoo' ),
		array( "provider" => 'aol', "name" => 'AOL', "icon" => 'Aol' ),
		array( "provider" => 'linkedin', "name" => 'Linkedin', "icon" => 'LinkedIn' ),
		array( "provider" => 'paypal', "name" => 'Paypal', "icon" => 'PayPal' ),
		array( "provider" => 'github', "name" => 'GitHub', "icon" => 'GitHub' ),
		array( "provider" => 'amazon', "name" => 'Amazon', "icon" => 'Amazon' ),
		array( "provider" => 'vkontakte', "name" => 'vkontakte', "icon" => 'vk' ),
		array( "provider" => 'yandex', "name" => 'yandex', "icon" => 'Yandex Metrica' ),
		array( "provider" => 'thirtysevensignals', "name" => 'thirtysevensignals', "icon" => '37signals' ),
		array( "provider" => 'box', "name" => 'box', "icon" => 'Box' ),
		array( "provider" => 'salesforce', "name" => 'salesforce', "icon" => 'Salesforce' ),
		array( "provider" => 'salesforce-sandbox', "name" => 'salesforce-sandbox', "icon" => 'SalesforceSandbox' ),
		array( "provider" => 'salesforce-community', "name" => 'salesforce-community', "icon" => 'SalesforceCommunity' ),
		array( "provider" => 'fitbit', "name" => 'Fitbit', "icon" => 'Fitbit' ),
		array( "provider" => 'baidu', "name" => '百度 (Baidu)', "icon" => 'Baidu' ),
		array( "provider" => 'renren', "name" => '人人 (RenRen)', "icon" => 'RenRen' ),
		array( "provider" => 'weibo', "name" => '新浪微 (Weibo)', "icon" => 'Weibo' ),
		array( "provider" => 'shopify', "name" => 'Shopify', "icon" => 'Shopify' ),
		array( "provider" => 'dwolla', "name" => 'Dwolla', "icon" => 'dwolla' ),
		array( "provider" => 'miicard', "name" => 'miiCard', "icon" => 'miiCard' ),
		array( "provider" => 'wordpress', "name" => 'wordpress', "icon" => 'WordPress' ),
		array( "provider" => 'yammer', "name" => 'Yammer', "icon" => 'Yammer' ),
		array( "provider" => 'soundcloud', "name" => 'soundcloud', "icon" => 'Soundcloud' ),
		array( "provider" => 'instagram', "name" => 'instagram', "icon" => 'Instagram' ),
		array( "provider" => 'evernote', "name" => 'evernote', "icon" => 'Evernote' ),
		array( "provider" => 'evernote-sandbox', "name" => 'evernote-sandbox', "icon" => 'Evernote' ),
		array( "provider" => 'thecity', "name" => 'thecity', "icon" => 'The City' ),
		array( "provider" => 'thecity-sandbox', "name" => 'thecity-sandbox', "icon" => 'The City Sandbox' ),
		array( "provider" => 'planningcenter', "name" => 'planningcenter', "icon" => 'Planning Center' ),
		array( "provider" => 'exact', "name" => 'exact', "icon" => 'Exact' ),
	);

	protected $sections = array();

	public function __construct( WP_Auth0_Options $a0_options, WP_Auth0_Routes $router ) {
		$this->a0_options = $a0_options;
		$this->router = $router;
	}

	public function init() {
		add_action( 'admin_init', array( $this, 'init_admin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 1 );
	}

	/**
	 * Enqueue scripts for all Auth0 wp-admin pages
	 */
	public function admin_enqueue() {
		$wpa0_pages = [ 'wpa0', 'wpa0-errors', 'wpa0-users-export', 'wpa0-import-settings', 'wpa0-setup' ];
		$wpa0_curr_page = ! empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( ! in_array( $wpa0_curr_page, $wpa0_pages )  ) {
			return;
		}

		if ( ! WP_Auth0::ready() ) {
			add_action( 'admin_notices', array( $this, 'create_account_message' ) );
		}
		
		if ( 'wpa0' === $wpa0_curr_page ) {
			wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_JS_URL . 'admin.js', array( 'jquery' ), WPA0_VERSION );
			wp_localize_script( 'wpa0_admin', 'wpa0', array(
				'media_title' => __( 'Choose your icon', 'wp-auth0' ),
				'media_button' => __( 'Choose icon', 'wp-auth0' ),
				'clear_cache_working' => __( 'Working ...', 'wp-auth0' ),
				'clear_cache_done' => __( 'Done!', 'wp-auth0' ),
				'clear_cache_nonce' => wp_create_nonce( 'auth0_delete_cache_transient' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );

			wp_enqueue_script( 'wpa0_async', WPA0_PLUGIN_LIB_URL . 'async.min.js', FALSE, WPA0_VERSION );
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_BS_URL . 'css/bootstrap.min.css', FALSE, '3.3.5' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_BS_URL . 'js/bootstrap.min.js', array( 'jquery' ), '3.3.6' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_CSS_URL . 'initial-setup.css', FALSE, WPA0_VERSION );

		if ( 'wpa0-setup' === $wpa0_curr_page && isset( $_REQUEST['signup'] ) ) {
			$cdn_url = $this->a0_options->get( 'cdn_url' );
			wp_enqueue_script( 'wpa0_lock', $cdn_url, array( 'jquery' ) );
		}

		wp_enqueue_style( 'media' );
	}

	// TODO: Deprecate, not used
	public function cant_connect_to_auth0() {
		// Not used
	}

	public function init_admin() {
		$this->sections['basic'] = new WP_Auth0_Admin_Basic( $this->a0_options );
		$this->sections['basic']->init();

		$this->sections['features'] = new WP_Auth0_Admin_Features( $this->a0_options );
		$this->sections['features']->init();

		$this->sections['appearance'] = new WP_Auth0_Admin_Appearance( $this->a0_options );
		$this->sections['appearance']->init();

		$this->sections['advanced'] = new WP_Auth0_Admin_Advanced( $this->a0_options, $this->router );
		$this->sections['advanced']->init();

		register_setting(
			$this->a0_options->get_options_name() . '_basic',
			$this->a0_options->get_options_name(),
			array( $this, 'input_validator' )
		);
	}

	public function input_validator( $input ) {

		$old_options = $this->a0_options->get_options();

		$input['connections'] = $old_options['connections'];

		foreach ( $this->sections as $name => $section ) {
			$input = $section->input_validator( $input, $old_options );
		}

		return $input;
	}

	/**
	 * Show a message on all Auth0 admin pages when the plugin is not ready to process logins
	 */
	public function create_account_message() {
		printf(
			'<div class="update-nag">%s<strong><a href="%s">%s</a></strong>%s
			<strong><a href="https://auth0.com/docs/cms/wordpress/installation#manual-setup" target="_blank">
			%s</a></strong>.</div>',
			__( 'Login by Auth0 is not yet configured. Please use the ', 'wp-auth0' ),
			admin_url( 'admin.php?page=wpa0-setup' ),
			__( 'Setup Wizard', 'wp-auth0' ),
			__( ' or follow the ', 'wp-auth0' ),
			__( 'Manual setup instructions', 'wp-auth0' )
		);
	}

	protected function get_social_connection( $provider, $name, $icon ) {
		return array(
			'name' => $name,
			'provider' => $provider,
			'icon' => $icon,
			'status' => $this->a0_options->get_connection( "social_{$provider}" ),
			'key' => $this->a0_options->get_connection( "social_{$provider}_key" ),
			'secret' => $this->a0_options->get_connection( "social_{$provider}_secret" ),
		);
	}

	public function render_settings_page() {
		$social_connections = array();

		foreach ( $this->providers as $provider ) {
			$social_connections[] = $this->get_social_connection( $provider['provider'], $provider['name'], $provider['icon'] );
		}

		$domain = $this->a0_options->get( 'domain' );
		$parts = explode( '.', $domain );

		$tenant = $parts[0];

		if ( strpos( $domain, 'au.auth0.com' ) !== false ) {
			$tenant .= '@au';
		}
		elseif ( strpos( $domain, 'eu.auth0.com' ) !== false ) {
			$tenant .= '@eu';
		}
		elseif ( strpos( $domain, 'auth0.com' ) !== false ) {
			$tenant .= '@us';
		}

		$options = $this->a0_options;

		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}
}
