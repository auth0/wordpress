<?php

class WP_Auth0_Admin {

	protected $a0_options;
	protected $router;

	protected $sections = array();

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
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css' );
    wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.min.js' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );
		wp_enqueue_style( 'media' );

		wp_localize_script( 'wpa0_admin', 'wpa0', array(
			'media_title' => __( 'Choose your icon', WPA0_LANG ),
			'media_button' => __( 'Choose icon', WPA0_LANG ),
		) );
	}

	protected function validate_required_api_scopes() {
		$app_token = $this->a0_options->get( 'auth0_app_token' );
		if ( ! $app_token ) {
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
		$options_name = $this->a0_options->get_options_name() . '_' . strtolower($id);

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

		$this->sections['basic'] = new WP_Auth0_Admin_Basic($this->a0_options);
		$this->sections['basic']->init();

		/* ------------------------- Features ------------------------- */

		$this->sections['features'] = new WP_Auth0_Admin_Features($this->a0_options);
		$this->sections['features']->init();

		/* ------------------------- Appearance ------------------------- */

		$this->sections['appearance'] = new WP_Auth0_Admin_Appearance($this->a0_options);
		$this->sections['appearance']->init();

		/* ------------------------- ADVANCED ------------------------- */

		$this->sections['advanced'] = new WP_Auth0_Admin_Advanced($this->a0_options);
		$this->sections['advanced']->init();
		
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

	

	

	

	public function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}

	


	protected function render_a0_switch($id, $name, $value, $checked) {
		?>

		<div class="a0-switch">
			<input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[<?php echo $name; ?>]" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php echo checked( $checked ); ?>/>
			<label for="<?php echo $id; ?>"></label>
		</div>

		<?php
	}


}
