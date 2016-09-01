<?php
/**
 * Plugin Name: PLUGIN_NAME
 * Description: PLUGIN_DESCRIPTION
 * Version: 3.2.4
 * Author: Auth0
 * Author URI: https://auth0.com
 */

define( 'WPA0_PLUGIN_FILE', __FILE__ );
define( 'WPA0_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPA0_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPA0_LANG', 'wp-auth0' );
define( 'AUTH0_DB_VERSION', 11 );
define( 'WPA0_VERSION', '3.2.4' );

/**
 * Main plugin class
 */
class WP_Auth0 {

	protected $db_manager;
	protected $a0_options;
	protected $social_amplificator;
	protected $router;

	/**
	 * Initialize the plugin and its modules setting all the hooks
	 */
	public function init() {

		spl_autoload_register( array( $this, 'autoloader' ) );

		$ip_checker = new WP_Auth0_Ip_Check();
		$ip_checker->init();

		$this->a0_options = WP_Auth0_Options::Instance();

		$this->db_manager = new WP_Auth0_DBManager($this->a0_options);
		$this->db_manager->init();

		add_action( 'init', array( $this, 'wp_init' ) );

		// Add hooks for install uninstall and update.
		register_activation_hook( WPA0_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( WPA0_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( WPA0_PLUGIN_FILE, array( 'WP_Auth0', 'uninstall' ) );

		add_action( 'activated_plugin', array( $this, 'on_activate_redirect' ) );

		add_filter( 'get_avatar' , array( $this, 'my_custom_avatar') , 1 , 5 );

		// Add an action to append a stylesheet for the login page.
		add_action( 'login_enqueue_scripts', array( $this, 'render_auth0_login_css' ) );

		// Add a hook to add Auth0 code on the login page.
		add_filter( 'login_message', array( $this, 'render_form' ) );

		add_filter( 'auth0_verify_email_page', array( $this, 'render_verify_email_page' ), 0, 3 );

		add_shortcode( 'auth0', array( $this, 'shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue' ) );

		add_action( 'widgets_init', array( $this, 'wp_register_widget' ) );

		add_filter( 'query_vars', array( $this, 'a0_register_query_vars' ) );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( $this, 'wp_add_plugin_settings_link' ) );

		if ( isset( $_GET['message'] ) ) {
			add_action( 'wp_footer', array( $this, 'a0_render_message' ) );
		}

		$initial_setup = new WP_Auth0_InitialSetup( $this->a0_options );
		$initial_setup->init();

		$users_repo = new WP_Auth0_UsersRepo( $this->a0_options );
		$users_repo->init();

		$login_manager = new WP_Auth0_LoginManager( $users_repo, $this->a0_options );
		$login_manager->init();

		$this->router = new WP_Auth0_Routes( $this->a0_options );
		$this->router->init();

		$metrics = new WP_Auth0_Metrics( $this->a0_options );
		$metrics->init();

		$auth0_admin = new WP_Auth0_Admin( $this->a0_options, $this->router );
		$auth0_admin->init();

		$error_log = new WP_Auth0_ErrorLog();
		$error_log->init();

		$configure_jwt_auth = new WP_Auth0_Configure_JWTAUTH( $this->a0_options );
		$configure_jwt_auth->init();

		$dashboard_widgets = new WP_Auth0_Dashboard_Widgets( $this->a0_options, $this->db_manager );
		$dashboard_widgets->init();

		$woocommerce_override = new WP_Auth0_WooCommerceOverrides( $this );
		$woocommerce_override->init();

		$users_exporter = new WP_Auth0_Export_Users( $this->db_manager );
		$users_exporter->init();

		$import_settings = new WP_Auth0_Import_Settings( $this->a0_options );
		$import_settings->init();

		$settings_section = new WP_Auth0_Settings_Section( $this->a0_options, $initial_setup, $users_exporter, $configure_jwt_auth, $error_log, $auth0_admin, $import_settings );
		$settings_section->init();

		$this->social_amplificator = new WP_Auth0_Amplificator( $this->db_manager, $this->a0_options );
		$this->social_amplificator->init();

		$edit_profile = new WP_Auth0_EditProfile( $this->db_manager, $users_repo, $this->a0_options );
		$edit_profile->init();

		$this->check_signup_status();
	}

	/**
	 * Checks it it should update the database connection no enable or disable signups and create or delete
	 * the rule that will disable social signups.
	 */
	function check_signup_status() {
		$app_token = $this->a0_options->get( 'auth0_app_token' );

		if ( $app_token ) {
			$disable_signup_rule = $this->a0_options->get( 'disable_signup_rule' );
			$is_wp_registration_enabled = $this->a0_options->is_wp_registration_enabled();

			if ( $is_wp_registration_enabled != $this->a0_options->get( 'registration_enabled' ) ) {
					$this->a0_options->set( 'registration_enabled', $is_wp_registration_enabled );

					$operations = new WP_Auth0_Api_Operations( $this->a0_options );

					$operations->disable_signup_wordpress_connection( $app_token, !$is_wp_registration_enabled );

					$rule_name = WP_Auth0_RulesLib::$disable_social_signup['name'] . '-' . get_bloginfo('name');

					$rule_script = WP_Auth0_RulesLib::$disable_social_signup['script'];
					$rule_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $this->a0_options->get( 'client_id' ), $rule_script );

					try {
						if ($is_wp_registration_enabled && $disable_signup_rule === null) {
							return;
						}
						$disable_signup_rule = $operations->toggle_rule( $app_token, ( $is_wp_registration_enabled ? $disable_signup_rule : null ), $rule_name, $rule_script );
						$this->a0_options->set( 'disable_signup_rule', $disable_signup_rule );
					} catch(Exception $e) {

					}
			}
		}
	}

	function my_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
		$auth0Profile = get_auth0userinfo($id_or_email);

		if ($this->a0_options->get('override_wp_avatars')) {
			if ($auth0Profile && isset($auth0Profile->picture)) {
				$avatar_url = $auth0Profile->picture;
				$avatar = "<img alt='{$alt}' src='{$avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			}
		}

		return $avatar;
	}

	function on_activate_redirect( $plugin ) {

		if ( $plugin == plugin_basename( __FILE__ ) ) {

			$this->router->setup_rewrites();
			flush_rewrite_rules();

			$client_id = $this->a0_options->get( 'client_id' );
			$client_secret = $this->a0_options->get( 'client_secret' );
			$domain = $this->a0_options->get( 'domain' );

			$show_initial_setup = ( ( ! $client_id ) || ( ! $client_secret ) || ( ! $domain ) ) ;

			if ( $show_initial_setup ) {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0-setup&activation=1' ) ) );
			} else {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0' ) ) );
			}
		}
	}

	public static function get_plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}

	public function a0_register_query_vars( $qvars ) {
		$qvars[] = 'error_description';
		$qvars[] = 'a0_action';
		$qvars[] = 'auth0';
		$qvars[] = 'code';
		return $qvars;
	}

	public function a0_render_message() {
		$message = null;

		if ( $message ) {
			echo "<div class=\"a0-message\">$message <small onclick=\"jQuery('.a0-message').hide();\">(Close)</small></div>";
			echo '<script type="text/javascript">
				setTimeout(function(){jQuery(".a0-message").hide();}, 10 * 1000);
			</script>';
		}
	}

	/**
	 * Add settings link on plugin page.
	 */
	public function wp_add_plugin_settings_link( $links ) {

		$settings_link = '<a href="admin.php?page=wpa0-errors">Error Log</a>';
		array_unshift( $links, $settings_link );

		$settings_link = '<a href="admin.php?page=wpa0">Settings</a>';
		array_unshift( $links, $settings_link );

		$client_id = $this->a0_options->get('client_id');
		$client_secret = $this->a0_options->get('client_secret');
		$domain = $this->a0_options->get('domain');

		if ( ( ! $client_id) || ( ! $client_secret) || ( ! $domain) ) {
			$settings_link = '<a href="admin.php?page=wpa0-setup">Quick Setup</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function wp_register_widget() {
		register_widget( 'WP_Auth0_Embed_Widget' );
		register_widget( 'WP_Auth0_Popup_Widget' );

		WP_Auth0_SocialAmplification_Widget::set_context($this->db_manager, $this->social_amplificator);
		register_widget( 'WP_Auth0_SocialAmplification_Widget' );
	}

	public function wp_enqueue() {
		$options = WP_Auth0_Options::Instance();
		$client_id = $options->get( 'client_id' );

		if ( trim( $client_id ) === '' ) {
			return;
		}

		if ( isset( $_GET['message'] ) ) {
			wp_enqueue_script( 'jquery' );
		}

		wp_enqueue_style( 'auth0-widget', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/css/main.css' );
	}

	public function shortcode( $atts ) {
		wp_enqueue_script( 'jquery' );

		if ( WP_Auth0_Options::Instance()->get('passwordless_enabled') ) {
			wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('passwordless_cdn_url'), 'jquery' );
		} else {
			wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );
		}

		if (!isset($atts['redirect_to'])) {
			$atts['redirect_to'] = home_url($_SERVER["REQUEST_URI"]);
		}

		ob_start();
		require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
		renderAuth0Form( false, $atts );

		$html = ob_get_clean();
		return $html;
	}

	public static function render_back_to_auth0() {

		include WPA0_PLUGIN_DIR . 'templates/back-to-auth0.php';

	}

	public function render_auth0_login_css() {
		$client_id = WP_Auth0_Options::Instance()->get( 'client_id' );

		if ( trim( $client_id ) === '' ) {
			return;
		}
?>
		<link rel='stylesheet' href='<?php echo plugins_url( 'assets/css/login.css', __FILE__ ); ?>' type='text/css' />
	<?php
	}

	public function render_verify_email_page($html, $userinfo, $id_token) {
		ob_start();
		$domain = $this->a0_options->get( 'domain' );
		$token = $id_token;
		$email = $userinfo->email;
		$connection = $userinfo->identities[0]->connection;
		$userId = $userinfo->user_id;
		include WPA0_PLUGIN_DIR . 'templates/verify-email.php';

		return ob_get_clean();
	}

	public function render_form( $html ) {

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'lostpassword' ) {
			return $html;
		}

		$client_id = WP_Auth0_Options::Instance()->get( 'client_id' );

		if ( trim( $client_id ) === '' ) {
			return;
		}

		wp_enqueue_script( 'jquery' );

		if ( WP_Auth0_Options::Instance()->get('passwordless_enabled') ) {
			wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('passwordless_cdn_url'), 'jquery' );
		} else {
			wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );
		}

		ob_start();
		require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
		renderAuth0Form();

		$html = ob_get_clean();
		return $html;
	}

	public function wp_init() {
		$this->router->setup_rewrites();
	}


	public function install() {
		$this->db_manager->install_db();
		$this->router->setup_rewrites();
		$this->a0_options->save();

		flush_rewrite_rules();
	}

	public function deactivate() {
		flush_rewrite_rules();
	}
	public static function uninstall() {
		$a0_options = WP_Auth0_Options::Instance();
		$a0_options->delete();
	}

	private function autoloader( $class ) {
		$path = WPA0_PLUGIN_DIR;
		$paths = array();
		$exts = array( '.php', '.class.php' );

		$paths[] = $path.'lib/';
		$paths[] = $path.'lib/admin/';
		$paths[] = $path.'lib/exceptions/';
		$paths[] = $path.'lib/wizard/';
		$paths[] = $path.'lib/initial-setup/';
		$paths[] = $path.'lib/dashboard-widgets/';
		$paths[] = $path.'lib/twitter-api-php/';
		$paths[] = $path.'lib/php-jwt/Exceptions/';
		$paths[] = $path.'lib/php-jwt/Authentication/';
		$paths[] = $path;

		foreach ( $paths as $p ) {
			foreach ( $exts as $ext ) {
				if ( file_exists( $p.$class.$ext ) ) {
					require_once $p.$class.$ext;
					return true;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'get_auth0userinfo' ) ) {
	function get_auth0userinfo( $user_id ) {

		global $wpdb;

		$profile = get_user_meta( $user_id, $wpdb->prefix.'auth0_obj', true);

		if ($profile) {
			return WP_Auth0_Serializer::unserialize( $profile );
		}

		return false;
	}
}

if ( ! function_exists( 'get_currentauth0userinfo' ) ) {
	function get_currentauth0userinfo() {

		global $currentauth0_user;

		$current_user = wp_get_current_user();

		$currentauth0_user = get_auth0userinfo($current_user->ID);
		
		return $currentauth0_user;
	}
}

if ( ! function_exists( 'get_currentauth0user' ) ) {
	function get_currentauth0user() {

		global $wpdb; 

		$current_user = wp_get_current_user();

		$serialized_profile = get_user_meta( $current_user->ID, $wpdb->prefix.'auth0_obj', true);

		$data = new stdClass;

		$data->auth0_obj = empty($serialized_profile) ? false : WP_Auth0_Serializer::unserialize( $serialized_profile );
		$data->last_update = get_user_meta( $current_user->ID, $wpdb->prefix.'last_update', true);
		$data->auth0_id = get_user_meta( $current_user->ID, $wpdb->prefix.'auth0_id', true);

		return $data;
	}
}

if ( ! function_exists( 'get_auth0_curatedBlogName' ) ) {
	function get_auth0_curatedBlogName() {
		$name = get_bloginfo( 'name' );

		$name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
		$name = str_replace(" ", "-", $name);

		return $name;
	}
}

$a0_plugin = new WP_Auth0();
$a0_plugin->init();

