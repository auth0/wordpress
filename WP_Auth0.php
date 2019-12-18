<?php
/**
 * Plugin Name: Login by Auth0
 * Plugin URL: https://auth0.com/docs/cms/wordpress
 * Description: Login by Auth0 provides improved username/password login, Passwordless login, Social login, MFA, and Single Sign On for all your sites.
 * Version: 4.0.0-beta
 * Author: Auth0
 * Author URI: https://auth0.com
 * Text Domain: wp-auth0
 */

define( 'WPA0_VERSION', '4.0.0-beta' );
define( 'AUTH0_DB_VERSION', 23 );

define( 'WPA0_PLUGIN_FILE', __FILE__ );
define( 'WPA0_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Includes trailing slash
define( 'WPA0_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPA0_PLUGIN_JS_URL', WPA0_PLUGIN_URL . 'assets/js/' );
define( 'WPA0_PLUGIN_CSS_URL', WPA0_PLUGIN_URL . 'assets/css/' );
define( 'WPA0_PLUGIN_IMG_URL', WPA0_PLUGIN_URL . 'assets/img/' );
define( 'WPA0_PLUGIN_LIB_URL', WPA0_PLUGIN_URL . 'assets/lib/' );
define( 'WPA0_PLUGIN_BS_URL', WPA0_PLUGIN_URL . 'assets/bootstrap/' );

define( 'WPA0_LOCK_CDN_URL', 'https://cdn.auth0.com/js/lock/11.16/lock.min.js' );
define( 'WPA0_AUTH0_JS_CDN_URL', 'https://cdn.auth0.com/js/auth0/9.10/auth0.min.js' );

define( 'WPA0_AUTH0_LOGIN_FORM_ID', 'auth0-login-form' );
define( 'WPA0_CACHE_GROUP', 'wp_auth0' );
define( 'WPA0_JWKS_CACHE_TRANSIENT_NAME', 'WP_Auth0_JWKS_cache' );

define( 'WPA0_LANG', 'wp-auth0' ); // deprecated; do not use for translations

require_once WPA0_PLUGIN_DIR.'vendor/autoload.php';
require_once WPA0_PLUGIN_DIR.'functions.php';

/*
 * Localization
 */

function wp_auth0_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-auth0', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_auth0_load_plugin_textdomain' );

/**
 * Main plugin class
 */
class WP_Auth0 {

	/**
	 * @var WP_Auth0_DBManager
	 */
	protected $db_manager;

	/**
	 * @var null|WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * @deprecated - 3.9.0, functionality removed
	 *
	 * @var WP_Auth0_Amplificator
	 */
	protected $social_amplificator;

	/**
	 * @var WP_Auth0_Routes
	 */
	protected $router;

	/**
	 * @var string
	 */
	protected $basename;

	/**
	 * WP_Auth0 constructor.
	 *
	 * @param null|WP_Auth0_Options $options - WP_Auth0_Options instance.
	 */
	public function __construct( $options = null ) {
		spl_autoload_register( [ $this, 'autoloader' ] );
		$this->a0_options = $options instanceof WP_Auth0_Options ? $options : WP_Auth0_Options::Instance();
		$this->basename   = plugin_basename( __FILE__ );
	}

	/**
	 * Initialize the plugin and its modules setting all the hooks.
	 *
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function init() {

		$this->db_manager = new WP_Auth0_DBManager( $this->a0_options );

		add_action( 'init', [ $this, 'wp_init' ] );

		// Add hooks for install uninstall and update.
		register_activation_hook( WPA0_PLUGIN_FILE, [ $this, 'install' ] );
		register_deactivation_hook( WPA0_PLUGIN_FILE, [ $this, 'deactivate' ] );
		register_uninstall_hook( WPA0_PLUGIN_FILE, [ 'WP_Auth0', 'uninstall' ] );

		add_action( 'activated_plugin', [ $this, 'on_activate_redirect' ] );

		add_filter( 'get_avatar', [ $this, 'filter_get_avatar' ], 1, 5 );

		// Add an action to append a stylesheet for the login page.
		add_action( 'login_enqueue_scripts', [ $this, 'render_auth0_login_css' ] );

		// Add a hook to add Auth0 code on the login page.
		add_filter( 'login_message', [ $this, 'render_form' ], 5 );

		add_shortcode( 'auth0', [ $this, 'shortcode' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue' ] );

		add_action( 'widgets_init', [ $this, 'wp_register_widget' ] );

		add_filter( 'query_vars', [ $this, 'a0_register_query_vars' ] );

		add_filter( 'plugin_action_links_' . $this->basename, [ $this, 'wp_add_plugin_settings_link' ] );

		$initial_setup = new WP_Auth0_InitialSetup( $this->a0_options );
		$initial_setup->init();

		$this->router = new WP_Auth0_Routes( $this->a0_options );
	}

	/**
	 * Is the Auth0 plugin ready to process logins?
	 *
	 * @return bool
	 */
	public static function ready() {
		$options = WP_Auth0_Options::Instance();
		if ( ! $options->get( 'domain' ) || ! $options->get( 'client_id' ) || ! $options->get( 'client_secret' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Get the tenant region based on a domain.
	 *
	 * @param string $domain Tenant domain.
	 *
	 * @return string
	 */
	public static function get_tenant_region( $domain ) {
		preg_match( '/^[\w\d\-_0-9]+\.([\w\d\-_0-9]*)[\.]*auth0\.com$/', $domain, $matches );
		return ! empty( $matches[1] ) ? $matches[1] : 'us';
	}

	/**
	 * Get the full tenant name with region.
	 *
	 * @param null|string $domain Tenant domain.
	 *
	 * @return string
	 */
	public static function get_tenant( $domain = null ) {

		if ( empty( $domain ) ) {
			$options = WP_Auth0_Options::Instance();
			$domain  = $options->get( 'domain' );
		}

		$parts = explode( '.', $domain );
		return $parts[0] . '@' . self::get_tenant_region( $domain );
	}

	/**
	 * Filter the avatar to use the Auth0 profile image
	 *
	 * @param string                                $avatar - avatar HTML
	 * @param int|string|WP_User|WP_Comment|WP_Post $id_or_email - user identifier
	 * @param int                                   $size - width and height of avatar
	 * @param string                                $default - what to do if nothing
	 * @param string                                $alt - alt text for the <img> tag
	 *
	 * @return string
	 */
	function filter_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
		if ( ! $this->a0_options->get( 'override_wp_avatars' ) ) {
			return $avatar;
		}

		$user_id = null;

		if ( $id_or_email instanceof WP_User ) {
			$user_id = $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			$user_id = $id_or_email->user_id;
		} elseif ( $id_or_email instanceof WP_Post ) {
			$user_id = $id_or_email->post_author;
		} elseif ( is_email( $id_or_email ) ) {
			$maybe_user = get_user_by( 'email', $id_or_email );

			if ( $maybe_user instanceof WP_User ) {
				$user_id = $maybe_user->ID;
			}
		} elseif ( is_numeric( $id_or_email ) ) {
			$user_id = absint( $id_or_email );
		}

		if ( ! $user_id ) {
			return $avatar;
		}

		$auth0Profile = get_auth0userinfo( $user_id );

		if ( ! $auth0Profile || empty( $auth0Profile->picture ) ) {
			return $avatar;
		}

		return sprintf(
			'<img alt="%s" src="%s" class="avatar avatar-%d photo avatar-auth0" width="%d" height="%d"/>',
			esc_attr( $alt ),
			esc_url( $auth0Profile->picture ),
			absint( $size ),
			absint( $size ),
			absint( $size )
		);
	}

	function on_activate_redirect( $plugin ) {

		if ( ! defined( 'WP_CLI' ) && $plugin == $this->basename ) {

			$this->router->setup_rewrites();
			flush_rewrite_rules();

			$client_id     = $this->a0_options->get( 'client_id' );
			$client_secret = $this->a0_options->get( 'client_secret' );
			$domain        = $this->a0_options->get( 'domain' );

			$show_initial_setup = ( ( ! $client_id ) || ( ! $client_secret ) || ( ! $domain ) );

			if ( $show_initial_setup ) {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0-setup&activation=1' ) ) );
			} else {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0' ) ) );
			}
		}
	}

	public function a0_register_query_vars( $qvars ) {
		$qvars[] = 'error';
		$qvars[] = 'error_description';
		$qvars[] = 'a0_action';
		$qvars[] = 'auth0';
		$qvars[] = 'state';
		$qvars[] = 'code';
		$qvars[] = 'state';
		return $qvars;
	}

	/**
	 * Add settings link on plugin page.
	 */
	public function wp_add_plugin_settings_link( $links ) {

		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpa0' ),
				__( 'Settings', 'wp-auth0' )
			)
		);

		if ( ! self::ready() ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=wpa0-setup' ),
					__( 'Setup Wizard', 'wp-auth0' )
				)
			);
		}

		return $links;
	}

	public function wp_register_widget() {
		register_widget( 'WP_Auth0_Embed_Widget' );
		register_widget( 'WP_Auth0_Popup_Widget' );
	}

	public function wp_enqueue() {
		$options   = WP_Auth0_Options::Instance();
		$client_id = $options->get( 'client_id' );

		if ( trim( $client_id ) === '' ) {
			return;
		}

		if ( isset( $_GET['message'] ) ) {
			wp_enqueue_script( 'jquery' );
		}

		wp_enqueue_style( 'auth0-widget', WPA0_PLUGIN_CSS_URL . 'main.css' );
	}

	public function shortcode( $atts ) {
		if ( empty( $atts ) ) {
			$atts = [];
		}

		if ( empty( $atts['redirect_to'] ) ) {
			$atts['redirect_to'] = home_url( $_SERVER['REQUEST_URI'] );
		}

		ob_start();
		\WP_Auth0_Lock::render( false, $atts );
		return ob_get_clean();
	}

	public static function render_back_to_auth0() {

		include WPA0_PLUGIN_DIR . 'templates/back-to-auth0.php';

	}

	/**
	 * Enqueue styles and scripts on the wp-login.php page if the plugin has been configured
	 */
	public function render_auth0_login_css() {
		if ( ! WP_Auth0::ready() ) {
			return;
		}

		wp_enqueue_style( 'auth0', WPA0_PLUGIN_CSS_URL . 'login.css', false, WPA0_VERSION );
	}

	/**
	 * Output the Auth0 form on wp-login.php
	 *
	 * @hook filter:login_message
	 *
	 * @param $html
	 *
	 * @return string
	 */
	public function render_form( $html ) {
		ob_start();
		\WP_Auth0_Lock::render();
		$auth0_form = ob_get_clean();
		return $auth0_form ? $auth0_form : $html;
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

		$error_log = new WP_Auth0_ErrorLog();
		$error_log->delete();

		delete_option( 'auth0_db_version' );

		delete_option( 'widget_wp_auth0_popup_widget' );
		delete_option( 'widget_wp_auth0_widget' );
		delete_option( 'widget_wp_auth0_social_amplification_widget' );

		delete_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME );
	}

	/**
	 * Look for a class within a specific set of paths.
	 *
	 * @param string $class - Class name to look for.
	 *
	 * @return bool
	 */
	private function autoloader( $class ) {
		$source_dir = WPA0_PLUGIN_DIR . 'lib/';

		// Anything that's not part of the above and not name-spaced can be skipped.
		if ( 0 !== strpos( $class, 'WP_Auth0' ) ) {
			return false;
		}

		$paths = [
			$source_dir,
			$source_dir . 'admin/',
			$source_dir . 'api/',
			$source_dir . 'exceptions/',
			$source_dir . 'profile/',
			$source_dir . 'wizard/',
			$source_dir . 'initial-setup/',
			$source_dir . 'token-verifier/',
		];

		foreach ( $paths as $path ) {
			if ( file_exists( $path . $class . '.php' ) ) {
				require_once $path . $class . '.php';
				return true;
			}
		}

		return false;
	}

	/*
	 *
	 * DEPRECATED
	 *
	 */

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function a0_render_message() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$message = null;

		if ( $message ) {
			echo "<div class=\"a0-message\">$message <small onclick=\"jQuery('.a0-message').hide();\">(Close)</small></div>";
			echo '<script type="text/javascript">
				setTimeout(function(){jQuery(".a0-message").hide();}, 10 * 1000);
			</script>';
		}
	}

	/**
	 * @deprecated - 3.6.0, use WPA0_PLUGIN_URL constant
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_plugin_dir_url() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return WPA0_PLUGIN_URL;
	}
}

/*
 * Startup
 */

function wp_auth0_db_check_update() {
	$db_manager = new WP_Auth0_DBManager( WP_Auth0_Options::Instance() );
	$db_manager->install_db();
}
add_action( 'plugins_loaded', 'wp_auth0_db_check_update' );

$a0_plugin = new WP_Auth0();
$a0_plugin->init();

/*
 * Core WP hooks
 */

/**
 * Function to call the method that clears out the error log.
 *
 * @hook admin_action_wpauth0_clear_error_log
 */
function wp_auth0_errorlog_clear_error_log() {

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'clear_error_log' ) ) {
		wp_die( __( 'Not allowed.', 'wp-auth0' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Not authorized.', 'wp-auth0' ) );
	}

	$error_log = new WP_Auth0_ErrorLog();
	$error_log->clear();

	wp_safe_redirect( admin_url( 'admin.php?page=wpa0-errors&cleared=1' ) );
	exit;
}
add_action( 'admin_action_wpauth0_clear_error_log', 'wp_auth0_errorlog_clear_error_log' );

function wp_auth0_export_settings_admin_action() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized.', 'wp-auth0' ) );
		exit;
	}

	$options  = WP_Auth0_Options::Instance();
	$name     = urlencode( get_auth0_curatedBlogName() );
	$settings = get_option( $options->get_options_name() );

	header( 'Content-Type: application/json' );
	header( "Content-Disposition: attachment; filename=auth0_for_wordpress_settings-$name.json" );
	header( 'Pragma: no-cache' );

	echo wp_json_encode( $settings );
	exit;
}
add_action( 'admin_action_wpauth0_export_settings', 'wp_auth0_export_settings_admin_action' );

function wp_auth0_import_settings_admin_action() {
	$options         = WP_Auth0_Options::Instance();
	$import_settings = new WP_Auth0_Import_Settings( $options );
	$import_settings->import_settings();
}
add_action( 'admin_action_wpauth0_import_settings', 'wp_auth0_import_settings_admin_action' );

function wp_auth0_settings_admin_action_error() {
	if ( ! wp_auth0_is_admin_page( 'wpa0-import-settings' ) || empty( $_REQUEST['error'] ) ) {
		return false;
	}

	printf(
		'<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>',
		sanitize_text_field( $_REQUEST['error'] )
	);
	return true;
}
add_action( 'admin_notices', 'wp_auth0_settings_admin_action_error' );

function wp_auth0_initial_setup_init() {
	if ( ( ! isset( $_REQUEST['page'] ) ) || ( 'wpa0-setup' !== $_REQUEST['page'] ) || ( ! isset( $_REQUEST['callback'] ) ) ) {
		return false;
	}

	if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=rejected' ) );
		exit;
	}

	if ( isset( $_REQUEST['error'] ) && 'access_denied' == $_REQUEST['error'] ) {
		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=access_denied' ) );
		exit;
	}

	(new WP_Auth0_InitialSetup_Consent( WP_Auth0_Options::Instance() ))->callback();
}
add_action( 'init', 'wp_auth0_initial_setup_init', 1 );

function wp_auth0_profile_change_email( $wp_user_id, $old_user_data ) {
	$options              = WP_Auth0_Options::Instance();
	$api_client_creds     = new WP_Auth0_Api_Client_Credentials( $options );
	$api_change_email     = new WP_Auth0_Api_Change_Email( $options, $api_client_creds );
	$profile_change_email = new WP_Auth0_Profile_Change_Email( $api_change_email );
	return $profile_change_email->update_email( $wp_user_id, $old_user_data );
}
add_action( 'profile_update', 'wp_auth0_profile_change_email', 100, 2 );

function wp_auth0_validate_new_password( $errors, $user ) {
	$options             = WP_Auth0_Options::Instance();
	$api_client_creds    = new WP_Auth0_Api_Client_Credentials( $options );
	$api_change_password = new WP_Auth0_Api_Change_Password( $options, $api_client_creds );
	$profile_change_pwd  = new WP_Auth0_Profile_Change_Password( $api_change_password );
	return $profile_change_pwd->validate_new_password( $errors, $user );
}

// Used during profile update in wp-admin.
add_action( 'user_profile_update_errors', 'wp_auth0_validate_new_password', 10, 2 );

// Used during password reset on wp-login.php.
add_action( 'validate_password_reset', 'wp_auth0_validate_new_password', 10, 2 );

// Used during WooCommerce edit account save.
add_action( 'woocommerce_save_account_details_errors', 'wp_auth0_validate_new_password', 10, 2 );

function wp_auth0_show_delete_identity() {
	$profile_delete_data = new WP_Auth0_Profile_Delete_Data();
	$profile_delete_data->show_delete_identity();
}
add_action( 'edit_user_profile', 'wp_auth0_show_delete_identity' );
add_action( 'show_user_profile', 'wp_auth0_show_delete_identity' );

function wp_auth0_delete_user_data() {
	$profile_delete_data = new WP_Auth0_Profile_Delete_Data();
	$profile_delete_data->delete_user_data();
}
add_action( 'wp_ajax_auth0_delete_data', 'wp_auth0_delete_user_data' );

function wp_auth0_init_admin_menu() {

	if ( wp_auth0_is_admin_page( 'wpa0-help' ) ) {
		wp_redirect( admin_url( 'admin.php?page=wpa0#help' ), 301 );
		exit;
	}

	$options       = WP_Auth0_Options::Instance();
	$initial_setup = new WP_Auth0_InitialSetup( $options );
	$routes        = new WP_Auth0_Routes( $options );
	$admin         = new WP_Auth0_Admin( $options, $routes );

	$setup_slug  = 'wpa0-setup';
	$setup_title = __( 'Setup Wizard', 'wp-auth0' );
	$setup_func  = [ $initial_setup, 'render_setup_page' ];

	$settings_slug  = 'wpa0';
	$settings_title = __( 'Settings', 'wp-auth0' );
	$settings_func  = [ $admin, 'render_settings_page' ];

	$menu_parent = ! WP_Auth0::ready() ? $setup_slug : $settings_slug;
	$cap         = 'manage_options';

	add_menu_page(
		'Auth0',
		'Auth0',
		$cap,
		$menu_parent,
		! WP_Auth0::ready() ? $setup_func : $settings_func,
		WPA0_PLUGIN_IMG_URL . 'a0icon.png',
		86
	);

	if ( ! WP_Auth0::ready() ) {
		add_submenu_page( $menu_parent, $setup_title, $setup_title, $cap, $setup_slug, $setup_func );
		add_submenu_page( $menu_parent, $settings_title, $settings_title, $cap, $settings_slug, $settings_func );
	} else {
		add_submenu_page( $menu_parent, $settings_title, $settings_title, $cap, $settings_slug, $settings_func );
		add_submenu_page(
			$menu_parent,
			__( 'Help', 'wp-auth0' ),
			__( 'Help', 'wp-auth0' ),
			$cap,
			'wpa0-help',
			'__return_false'
		);
		add_submenu_page( null, $setup_title, $setup_title, $cap, 'wpa0-setup', $setup_func );
	}

	add_submenu_page(
		$menu_parent,
		__( 'Error Log', 'wp-auth0' ),
		__( 'Error Log', 'wp-auth0' ),
		$cap,
		'wpa0-errors',
		[ new WP_Auth0_ErrorLog(), 'render_settings_page' ]
	);

	add_submenu_page(
		$menu_parent,
		__( 'Import-Export Settings', 'wp-auth0' ),
		__( 'Import-Export settings', 'wp-auth0' ),
		$cap,
		'wpa0-import-settings',
		[ new WP_Auth0_Import_Settings( $options ), 'render_import_settings_page' ]
	);
}
add_action( 'admin_menu', 'wp_auth0_init_admin_menu', 96, 0 );

function wp_auth0_create_account_message() {
	$current_page = $_GET['page'] ?? null;
	if ( WP_Auth0::ready() || ! $current_page || 0 !== strpos( $current_page, 'wpa' ) ) {
		return false;
	}

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
	return true;
}
add_action( 'admin_notices', 'wp_auth0_create_account_message' );

function wp_auth0_init_admin() {
	$options = WP_Auth0_Options::Instance();
	$routes  = new WP_Auth0_Routes( $options );
	$admin   = new WP_Auth0_Admin( $options, $routes );
	$admin->init_admin();
}
add_action( 'admin_init', 'wp_auth0_init_admin' );

function wp_auth0_admin_enqueue_scripts() {
	$options = WP_Auth0_Options::Instance();
	$routes  = new WP_Auth0_Routes( $options );
	$admin   = new WP_Auth0_Admin( $options, $routes );
	return $admin->admin_enqueue();
}
add_action( 'admin_enqueue_scripts', 'wp_auth0_admin_enqueue_scripts', 1 );

function wp_auth0_custom_requests( $wp, $return = false ) {
	$routes = new WP_Auth0_Routes( WP_Auth0_Options::Instance() );
	return $routes->custom_requests( $wp, $return );
}
add_action( 'parse_request', 'wp_auth0_custom_requests' );

function wp_auth0_profile_enqueue_scripts() {
	global $pagenow;

	if ( ! in_array( $pagenow, [ 'profile.php', 'user-edit.php' ] ) ) {
		return false;
	}

	wp_enqueue_script(
		'wpa0_user_profile',
		WPA0_PLUGIN_JS_URL . 'edit-user-profile.js',
		[ 'jquery' ],
		WPA0_VERSION
	);

	$profile  = get_auth0userinfo( $GLOBALS['user_id'] );
	$strategy = isset( $profile->sub ) ? WP_Auth0_Users::get_strategy( $profile->sub ) : '';

	wp_localize_script(
		'wpa0_user_profile',
		'wpa0UserProfile',
		[
			'userId'        => intval( $GLOBALS['user_id'] ),
			'userStrategy'  => sanitize_text_field( $strategy ),
			'deleteIdNonce' => wp_create_nonce( 'delete_auth0_identity' ),
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'i18n'          => [
				'confirmDeleteId'   => __( 'Are you sure you want to delete the Auth0 user data for this user?', 'wp-auth0' ),
				'actionComplete'    => __( 'Deleted', 'wp-auth0' ),
				'actionFailed'      => __( 'Action failed, please see the Auth0 error log for details.', 'wp-auth0' ),
				'cannotChangeEmail' => __( 'Email cannot be changed for non-database connections.', 'wp-auth0' ),
			],
		]
	);

	return true;
}
add_action( 'admin_enqueue_scripts', 'wp_auth0_profile_enqueue_scripts' );

function wp_auth0_process_auth_callback() {
	$users_repo    = new WP_Auth0_UsersRepo( WP_Auth0_Options::Instance() );
	$login_manager = new WP_Auth0_LoginManager( $users_repo, WP_Auth0_Options::Instance() );
	return $login_manager->init_auth0();
}
add_action( 'template_redirect', 'wp_auth0_process_auth_callback' );

function wp_auth0_login_ulp_redirect() {
	$users_repo    = new WP_Auth0_UsersRepo( WP_Auth0_Options::Instance() );
	$login_manager = new WP_Auth0_LoginManager( $users_repo, WP_Auth0_Options::Instance() );
	return $login_manager->login_auto();
}
add_action( 'login_init', 'wp_auth0_login_ulp_redirect' );

function wp_auth0_process_logout() {
	$users_repo    = new WP_Auth0_UsersRepo( WP_Auth0_Options::Instance() );
	$login_manager = new WP_Auth0_LoginManager( $users_repo, WP_Auth0_Options::Instance() );
	$login_manager->logout();
}
add_action( 'wp_logout', 'wp_auth0_process_logout' );

function wp_auth0_ajax_delete_cache_transient() {
	check_ajax_referer( 'auth0_delete_cache_transient' );
	delete_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME );
	wp_send_json_success();
}
add_action( 'wp_ajax_auth0_delete_cache_transient', 'wp_auth0_ajax_delete_cache_transient' );

/**
 * AJAX endpoint to rotate the migration token.
 */
function wp_auth0_ajax_rotate_migration_token() {
	check_ajax_referer( WP_Auth0_Admin_Advanced::ROTATE_TOKEN_NONCE_ACTION );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'error' => __( 'Not authorized.', 'wp-auth0' ) ] );
		return;
	}

	WP_Auth0_Options::Instance()->set( 'migration_token', wp_auth0_generate_token() );
	wp_send_json_success();
}
add_action( 'wp_ajax_auth0_rotate_migration_token', 'wp_auth0_ajax_rotate_migration_token' );

/**
 * AJAX handler to re-send verification email.
 * Hooked to: wp_ajax_nopriv_resend_verification_email
 *
 * @codeCoverageIgnore - Tested in TestEmailVerification::testResendVerificationEmail()
 */
function wp_auth0_ajax_resend_verification_email() {
	check_ajax_referer( WP_Auth0_Email_Verification::RESEND_NONCE_ACTION );

	$options               = WP_Auth0_Options::Instance();
	$api_client_creds      = new WP_Auth0_Api_Client_Credentials( $options );
	$api_jobs_verification = new WP_Auth0_Api_Jobs_Verification( $options, $api_client_creds );

	if ( empty( $_POST['sub'] ) ) {
		wp_send_json_error( [ 'error' => __( 'No Auth0 user ID provided.', 'wp-auth0' ) ] );
	}

	if ( ! $api_jobs_verification->call( $_POST['sub'] ) ) {
		wp_send_json_error( [ 'error' => __( 'API call failed.', 'wp-auth0' ) ] );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_nopriv_resend_verification_email', 'wp_auth0_ajax_resend_verification_email' );

/**
 * Redirect a successful lost password submission to a login override page.
 *
 * @param string $location - Redirect in process.
 *
 * @return string
 */
function wp_auth0_filter_wp_redirect_lostpassword( $location ) {
	// Make sure we're going to the check email action on the wp-login page.
	if ( 'wp-login.php?checkemail=confirm' !== $location ) {
		return $location;
	}

	// Make sure we're on the lost password action on the wp-login page.
	if ( ! wp_auth0_is_current_login_action( [ 'lostpassword' ] ) ) {
		return $location;
	}

	// Make sure plugin settings allow core WP login form overrides
	if ( 'never' === wp_auth0_get_option( 'wordpress_login_enabled' ) ) {
		return $location;
	}

	// Make sure we're coming from an override page.
	$required_referrer = remove_query_arg( 'wle', wp_login_url() );
	$required_referrer = add_query_arg( 'action', 'lostpassword', $required_referrer );
	$required_referrer = wp_auth0_login_override_url( $required_referrer );
	if ( ! isset( $_SERVER['HTTP_REFERER'] ) || $required_referrer !== $_SERVER['HTTP_REFERER'] ) {
		return $location;
	}

	return wp_auth0_login_override_url( $location );
}

add_filter( 'wp_redirect', 'wp_auth0_filter_wp_redirect_lostpassword', 100 );

/**
 * Add an override code to the lost password URL if authorized.
 *
 * @param string $wp_login_url - Existing lost password URL.
 *
 * @return string
 */
function wp_auth0_filter_login_override_url( $wp_login_url ) {
	if ( wp_auth0_can_show_wp_login_form() && isset( $_REQUEST['wle'] ) ) {
		// We are on an override page.
		$wp_login_url = add_query_arg( 'wle', $_REQUEST['wle'], $wp_login_url );
	} elseif ( wp_auth0_is_current_login_action( [ 'resetpass' ] ) ) {
		// We are on the reset password page with a link to login.
		// This page will not be shown unless we get here via a valid reset password request.
		$wp_login_url = wp_auth0_login_override_url( $wp_login_url );
	}
	return $wp_login_url;
}

add_filter( 'lostpassword_url', 'wp_auth0_filter_login_override_url', 100 );
add_filter( 'login_url', 'wp_auth0_filter_login_override_url', 100 );

/**
 * Add the core WP form override to the lost password and login forms.
 */
function wp_auth0_filter_login_override_form() {
	if ( wp_auth0_can_show_wp_login_form() && isset( $_REQUEST['wle'] ) ) {
		printf( '<input type="hidden" name="wle" value="%s" />', $_REQUEST['wle'] );
	}
}

add_action( 'login_form', 'wp_auth0_filter_login_override_form', 100 );
add_action( 'lostpassword_form', 'wp_auth0_filter_login_override_form', 100 );

/**
 * Add new classes to the body element on all front-end and login pages.
 *
 * @param array $classes - Array of existing classes.
 *
 * @return array
 */
function wp_auth0_filter_body_class( array $classes ) {
	if ( wp_auth0_can_show_wp_login_form() ) {
		$classes[] = 'a0-show-core-login';
	}
	return $classes;
}
add_filter( 'body_class', 'wp_auth0_filter_body_class' );
add_filter( 'login_body_class', 'wp_auth0_filter_body_class' );

/*
 * WooCommerce hooks
 */

/**
 * Add the Auth0 login form to the checkout page.
 *
 * @param string $html - Original HTML passed to this hook.
 *
 * @return mixed
 */
function wp_auth0_filter_woocommerce_checkout_login_message( $html ) {
	$wp_auth0_opts        = WP_Auth0_Options::Instance();
	$wp_auth0_woocommerce = new WP_Auth0_WooCommerceOverrides( new WP_Auth0( $wp_auth0_opts ), $wp_auth0_opts );
	return $wp_auth0_woocommerce->override_woocommerce_checkout_login_form( $html );
}
add_filter( 'woocommerce_checkout_login_message', 'wp_auth0_filter_woocommerce_checkout_login_message' );

/**
 * Add the Auth0 login form to the account page.
 *
 * @param string $html - Original HTML passed to this hook.
 *
 * @return mixed
 */
function wp_auth0_filter_woocommerce_before_customer_login_form( $html ) {
	$wp_auth0_opts        = WP_Auth0_Options::Instance();
	$wp_auth0_woocommerce = new WP_Auth0_WooCommerceOverrides( new WP_Auth0( $wp_auth0_opts ), $wp_auth0_opts );
	return $wp_auth0_woocommerce->override_woocommerce_login_form( $html );
}
add_filter( 'woocommerce_before_customer_login_form', 'wp_auth0_filter_woocommerce_before_customer_login_form' );

/*
 * Beta plugin deactivation
 */

// Passwordless beta testing - https://github.com/auth0/wp-auth0/issues/400
remove_filter( 'login_message', 'wp_auth0_pwl_plugin_login_message_before', 5 );
remove_filter( 'login_message', 'wp_auth0_pwl_plugin_login_message_after', 6 );
