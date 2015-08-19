<?php
/**
 * Plugin Name: Wordpress Auth0 Integration
 * Description: Implements the Auth0 Single Sign On solution into Wordpress
 * Version: 2.0.0
 * Author: Auth0
 * Author URI: https://auth0.com
 */

define( 'WPA0_PLUGIN_FILE', __FILE__ );
define( 'WPA0_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPA0_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPA0_LANG', 'wp-auth0' );
define( 'AUTH0_DB_VERSION', 4 );
define( 'WPA0_VERSION', '2.0.0' );

/**
 * Main plugin class
 */
class WP_Auth0 {

	protected $db_manager;
	protected $a0_options;
	protected $dashboard_options;
	protected $social_amplificator;
	protected $router;

	public function init() {
		spl_autoload_register( array( $this, 'autoloader' ) );

		$ip_checker = new WP_Auth0_Ip_Check();
		$ip_checker->init();

		$this->a0_options = WP_Auth0_Options::Instance();
		$this->dashboard_options = WP_Auth0_Dashboard_Options::Instance();

		$this->db_manager = new WP_Auth0_DBManager();
		$this->db_manager->init();

		add_action( 'init', array( $this, 'wp_init' ) );

		// Add hooks for install uninstall and update.
		register_activation_hook( WPA0_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( WPA0_PLUGIN_FILE, array( $this, 'uninstall' ) );

		// Add an action to append a stylesheet for the login page.
		add_action( 'login_enqueue_scripts', array( $this, 'render_auth0_login_css' ) );

		// Add a hook to add Auth0 code on the login page.
		add_filter( 'login_message', array( $this, 'render_form' ) );

		// Add hook to handle when a user is deleted.
		add_action( 'delete_user', array( $this, 'delete_user' ) );

		add_shortcode( 'auth0', array( $this, 'shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue' ) );

		add_action( 'widgets_init', array( $this, 'wp_register_widget' ) );

		add_filter( 'query_vars', array( $this, 'a0_register_query_vars' ) );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( $this, 'wp_add_plugin_settings_link' ) );

		if ( isset( $_GET['message'] ) ) {
			add_action( 'wp_footer', array( $this, 'a0_render_message' ) );
		}

		$initial_setup = new WP_Auth0_InitialSetup($this->a0_options);
		$initial_setup->init();

		$login_manager = new WP_Auth0_LoginManager($this->a0_options);
		$login_manager->init();

		$users_repo = new WP_Auth0_UsersRepo($this->a0_options);
		$users_repo->init();

		$auth0_admin = new WP_Auth0_Admin($this->a0_options);
		$auth0_admin->init();

		$dashboard_preferences = new WP_Auth0_Dashboard_Preferences($this->dashboard_options);
		$dashboard_preferences->init();

		$error_log = new WP_Auth0_ErrorLog();
		$error_log->init();

		$configure_jwt_auth = new WP_Auth0_Configure_JWTAUTH($this->a0_options);
		$configure_jwt_auth->init();

		$dashboard_widgets = new WP_Auth0_Dashboard_Widgets($this->dashboard_options, $this->db_manager);
		$dashboard_widgets->init();

		$woocommerce_override = new WP_Auth0_WooCommerceOverrides($this);
		$woocommerce_override->init();

		$users_exporter = new WP_Auth0_Export_Users($this->db_manager);
		$users_exporter->init();

		$settings_section = new WP_Auth0_Settings_Section($this->a0_options, $initial_setup, $users_exporter, $configure_jwt_auth, $error_log, $dashboard_preferences, $auth0_admin);
		$settings_section->init();

		$this->social_amplificator = new WP_Auth0_Amplificator($this->db_manager, $this->a0_options);
		$this->social_amplificator->init();

		$edit_profile = new WP_Auth0_EditProfile($this->a0_options);
		$edit_profile->init();

		$this->router = new WP_Auth0_Routes();
		$this->router->init();

		add_action( 'plugins_loaded', array( $this, 'check_jwt_auth' ) );
	}

	public static function is_jwt_auth_enabled() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( 'wp-jwt-auth/JWT_AUTH.php' );
	}

	public static function is_jwt_configured() {
		$options = WP_Auth0_Options::Instance();
		return (
			JWT_AUTH_Options::get( 'aud' ) === $options->get( 'client_id' ) &&
			JWT_AUTH_Options::get( 'secret' ) === $options->get( 'client_secret' ) &&
			JWT_AUTH_Options::get( 'secret_base64_encoded' ) &&
			$options->get( 'jwt_auth_integration' ) &&
			JWT_AUTH_Options::get( 'jwt_attribute' ) === 'sub'
		);
	}

	public function check_jwt_auth() {
		if ( isset( $_REQUEST['page'] ) && 'wpa0-jwt-auth' === $_REQUEST['page'] ) {
			return;
		}

		if ( self::is_jwt_auth_enabled() && ! self::is_jwt_configured() ) {
			add_action( 'admin_notices', array( __CLASS__, 'notify_jwt' ) );
		}
	}

	public static function notify_jwt() {
		?>
		<div class="update-nag">
			JWT Auth installed. To configure it to work the Auth0 plugin, click <a href="admin.php?page=wpa0-jwt-auth">HERE</a>
		</div>
		<?php
	}

	public static function get_plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}

	public function  a0_register_query_vars( $qvars ) {
		$qvars[] = 'error_description';
		$qvars[] = 'a0_action';
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

		$options = WP_Auth0_Options::Instance();
		$auth0_app_token = $options->get('auth0_app_token');
		if ( ! $auth0_app_token ) {
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
		wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );

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

	public function render_form( $html ) {
		$client_id = WP_Auth0_Options::Instance()->get( 'client_id' );

		if ( trim( $client_id ) === '' ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );

		ob_start();
		require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
		renderAuth0Form();

		$html = ob_get_clean();
		return $html;
	}

	public static function insert_auth0_error( $section, $wp_error ) {

		if ( $wp_error instanceof WP_Error ) {
			$code = $wp_error->get_error_code();
			$message = $wp_error->get_error_message();
		} elseif ( $wp_error instanceof Exception ) {
			$code = $wp_error->getCode();
			$message = $wp_error->getMessage();
		} else {
			$code = null;
			$message = $wp_error;
		}

		global $wpdb;
		$wpdb->insert(
			$wpdb->auth0_error_logs,
			array(
				'section' => $section,
				'date' => date( 'c' ),
				'code' => $code,
				'message' => $message,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	public function delete_user( $user_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->auth0_user, array( 'wp_id' => $user_id ), array( '%d' ) );
	}

	public function wp_init() {
		$this->router->setup_rewrites();
	}


	public function install() {
		$this->db_manager->install_db();
		$this->router->setup_rewrites();

		flush_rewrite_rules();
	}

	public function uninstall() {
		flush_rewrite_rules();
	}

	private function autoloader( $class ) {
		$path = WPA0_PLUGIN_DIR;
		$paths = array();
		$exts = array( '.php', '.class.php' );

		$paths[] = $path.'lib/';
		$paths[] = $path.'lib/exceptions/';
		$paths[] = $path.'lib/wizard/';
		$paths[] = $path.'lib/dashboard-widgets/';
		$paths[] = $path.'lib/twitter-api-php/';
		$paths[] = $path.'lib/php-jwt/Exceptions/';
		$paths[] = $path.'lib/php-jwt/Authentication/';
		$paths[] = $path;

		foreach ( $paths as $p ) {
			foreach ( $exts as $ext ) {
				if ( file_exists( $p.$class.$ext ) ) {
					require_once( $p.$class.$ext );
					return true;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'get_currentauth0userinfo' ) ) {
	function get_currentauth0userinfo() {

		global $currentauth0_user;

		$result = get_currentauth0user();
		if ($result) {
			$currentauth0_user = unserialize( $result->auth0_obj );
		}

		return $currentauth0_user;
	}
}

if ( ! function_exists( 'get_currentauth0user' ) ) {
	function get_currentauth0user() {
		global $current_user;
		global $wpdb;

		get_currentuserinfo();

		if ( $current_user instanceof WP_User && $current_user->ID > 0 ) {
			$sql = 'SELECT * FROM ' . $wpdb->auth0_user .' WHERE wp_id = %d order by last_update desc limit 1';
			$result = $wpdb->get_row( $wpdb->prepare( $sql, $current_user->ID ) );

			if ( is_null( $result ) || $result instanceof WP_Error ) {
				return null;
			}

		}

		return $result;
	}
}

$a0_plugin = new WP_Auth0();
$a0_plugin->init();
