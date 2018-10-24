<?php
/**
 * Plugin Name: Login by Auth0
 * Description: Login by Auth0 provides improved username/password login, Passwordless login, Social login and Single Sign On for all your sites.
 * Version: 3.8.0-beta
 * Author: Auth0
 * Author URI: https://auth0.com
 * Text Domain: wp-auth0
 */
define( 'WPA0_VERSION', '3.8.0-beta' );
define( 'AUTH0_DB_VERSION', 19 );

define( 'WPA0_PLUGIN_FILE', __FILE__ );
define( 'WPA0_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPA0_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPA0_PLUGIN_JS_URL', WPA0_PLUGIN_URL . 'assets/js/' );
define( 'WPA0_PLUGIN_CSS_URL', WPA0_PLUGIN_URL . 'assets/css/' );
define( 'WPA0_PLUGIN_IMG_URL', WPA0_PLUGIN_URL . 'assets/img/' );
define( 'WPA0_PLUGIN_LIB_URL', WPA0_PLUGIN_URL . 'assets/lib/' );
define( 'WPA0_PLUGIN_BS_URL', WPA0_PLUGIN_URL . 'assets/bootstrap/' );

define( 'WPA0_LOCK_CDN_URL', 'https://cdn.auth0.com/js/lock/11.5/lock.min.js' );
define( 'WPA0_AUTH0_JS_CDN_URL', 'https://cdn.auth0.com/js/auth0/9.4/auth0.min.js' );

define( 'WPA0_AUTH0_LOGIN_FORM_ID', 'auth0-login-form' );
define( 'WPA0_CACHE_GROUP', 'wp_auth0' );
define( 'WPA0_JWKS_CACHE_TRANSIENT_NAME', 'WP_Auth0_JWKS_cache' );

define( 'WPA0_LANG', 'wp-auth0' ); // deprecated; do not use for translations

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
		spl_autoload_register( array( $this, 'autoloader' ) );
		$this->a0_options = $options instanceof WP_Auth0_Options ? $options : WP_Auth0_Options::Instance();
	}

	/**
	 * Initialize the plugin and its modules setting all the hooks
	 */
	public function init() {
		$this->basename = plugin_basename( __FILE__ );

		$ip_checker = new WP_Auth0_Ip_Check();
		$ip_checker->init();

		$this->db_manager = new WP_Auth0_DBManager( $this->a0_options );
		$this->db_manager->init();

		add_action( 'init', array( $this, 'wp_init' ) );

		// Add hooks for install uninstall and update.
		register_activation_hook( WPA0_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( WPA0_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( WPA0_PLUGIN_FILE, array( 'WP_Auth0', 'uninstall' ) );

		add_action( 'activated_plugin', array( $this, 'on_activate_redirect' ) );

		add_filter( 'get_avatar', array( $this, 'filter_get_avatar' ), 1, 5 );

		// Add an action to append a stylesheet for the login page.
		add_action( 'login_enqueue_scripts', array( $this, 'render_auth0_login_css' ) );

		// Add a hook to add Auth0 code on the login page.
		add_filter( 'login_message', array( $this, 'render_form' ), 5 );

		add_shortcode( 'auth0', array( $this, 'shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue' ) );

		add_action( 'widgets_init', array( $this, 'wp_register_widget' ) );

		add_filter( 'query_vars', array( $this, 'a0_register_query_vars' ) );

		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'wp_add_plugin_settings_link' ) );

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

		$auth0_admin = new WP_Auth0_Admin( $this->a0_options, $this->router );
		$auth0_admin->init();

		$error_log = new WP_Auth0_ErrorLog();
		$error_log->init();

		$configure_jwt_auth = new WP_Auth0_Configure_JWTAUTH( $this->a0_options );
		$configure_jwt_auth->init();

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

		$api_client_creds = new WP_Auth0_Api_Client_Credentials( $this->a0_options );

		$api_change_password = new WP_Auth0_Api_Change_Password( $this->a0_options, $api_client_creds );
		$profile_change_pwd  = new WP_Auth0_Profile_Change_Password( $api_change_password );
		$profile_change_pwd->init();

		$profile_delete_data = new WP_Auth0_Profile_Delete_Data( $users_repo );
		$profile_delete_data->init();

		$api_delete_mfa     = new WP_Auth0_Api_Delete_User_Mfa( $this->a0_options, $api_client_creds );
		$profile_delete_mfa = new WP_Auth0_Profile_Delete_Mfa( $this->a0_options, $api_delete_mfa );
		$profile_delete_mfa->init();

		WP_Auth0_Email_Verification::init();
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

		$settings_link = '<a href="admin.php?page=wpa0-errors">Error Log</a>';
		array_unshift( $links, $settings_link );

		$settings_link = '<a href="admin.php?page=wpa0">Settings</a>';
		array_unshift( $links, $settings_link );

		$client_id     = $this->a0_options->get( 'client_id' );
		$client_secret = $this->a0_options->get( 'client_secret' );
		$domain        = $this->a0_options->get( 'domain' );

		if ( ( ! $client_id ) || ( ! $client_secret ) || ( ! $domain ) ) {
			$settings_link = '<a href="admin.php?page=wpa0-setup">Quick Setup</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function wp_register_widget() {
		register_widget( 'WP_Auth0_Embed_Widget' );
		register_widget( 'WP_Auth0_Popup_Widget' );

		WP_Auth0_SocialAmplification_Widget::set_context( $this->db_manager, $this->social_amplificator );
		register_widget( 'WP_Auth0_SocialAmplification_Widget' );
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
			$atts = array();
		}

		if ( empty( $atts['redirect_to'] ) ) {
			$atts['redirect_to'] = home_url( $_SERVER['REQUEST_URI'] );
		}

		ob_start();
		require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
		renderAuth0Form( false, $atts );

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
		// Do not show Auth0 form when ...
		if (
			// .. processing lost password
			( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'lostpassword', 'rp' ) ) )
			// ... handling an Auth0 callback
			|| ! empty( $_GET['auth0'] )
			// ... plugin is not configured
			|| ! self::ready()
		) {
			return $html;
		}

		// If the user has a WP session, determine where they should end up and redirect.
		if ( is_user_logged_in() ) {
			$login_redirect = empty( $_REQUEST['redirect_to'] ) ?
				$this->a0_options->get( 'default_login_redirection' ) :
				filter_var( $_REQUEST['redirect_to'], FILTER_SANITIZE_URL );

			// Add a cache buster to avoid an infinite redirect loop on pages that check for auth.
			$login_redirect = add_query_arg( time(), '', $login_redirect );
			wp_safe_redirect( $login_redirect );
			exit;
		}

		ob_start();
		require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
		renderAuth0Form();
		return ob_get_clean();
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

		delete_option( 'wp_auth0_client_grant_failed' );
		delete_option( 'wp_auth0_client_grant_success' );
		delete_option( 'wp_auth0_grant_types_failed' );
		delete_option( 'wp_auth0_grant_types_success' );

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

		// Catch non-name-spaced classes that still need auto-loading.
		switch ( $class ) {
			case 'TwitterAPIExchange':
				require_once $source_dir . 'twitter-api-php/' . $class . '.php';
				return true;

			case 'JWT':
				require_once $source_dir . 'php-jwt/Authentication/' . $class . '.php';
				return true;

			case 'BeforeValidException':
			case 'ExpiredException':
			case 'SignatureInvalidException':
				require_once $source_dir . 'php-jwt/Exceptions/' . $class . '.php';
				return true;
		}

		// Anything that's not part of the above and not name-spaced can be skipped.
		if ( 0 !== strpos( $class, 'WP_Auth0' ) ) {
			return false;
		}

		$paths = array(
			$source_dir,
			$source_dir . 'admin/',
			$source_dir . 'api/',
			$source_dir . 'exceptions/',
			$source_dir . 'profile/',
			$source_dir . 'wizard/',
			$source_dir . 'initial-setup/',
		);

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
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * Checks it it should update the database connection no enable or disable signups and create or delete
	 * the rule that will disable social signups.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function check_signup_status() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$app_token = $this->a0_options->get( 'auth0_app_token' );

		if ( $app_token ) {
			$disable_signup_rule        = $this->a0_options->get( 'disable_signup_rule' );
			$is_wp_registration_enabled = $this->a0_options->is_wp_registration_enabled();

			if ( $is_wp_registration_enabled != $this->a0_options->get( 'registration_enabled' ) ) {
				$this->a0_options->set( 'registration_enabled', $is_wp_registration_enabled );

				$operations = new WP_Auth0_Api_Operations( $this->a0_options );

				$operations->disable_signup_wordpress_connection( $app_token, ! $is_wp_registration_enabled );

				$rule_name = WP_Auth0_RulesLib::$disable_social_signup['name'] . '-' . get_bloginfo( 'name' );

				$rule_script = WP_Auth0_RulesLib::$disable_social_signup['script'];
				$rule_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $this->a0_options->get( 'client_id' ), $rule_script );

				try {
					if ( $is_wp_registration_enabled && $disable_signup_rule === null ) {
						return;
					}
					$disable_signup_rule = $operations->toggle_rule( $app_token, ( $is_wp_registration_enabled ? $disable_signup_rule : null ), $rule_name, $rule_script );
					$this->a0_options->set( 'disable_signup_rule', $disable_signup_rule );
				} catch ( Exception $e ) {

				}
			}
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

$a0_plugin = new WP_Auth0();
$a0_plugin->init();

if ( ! function_exists( 'get_auth0userinfo' ) ) {
	function get_auth0userinfo( $user_id ) {
		$profile = WP_Auth0_UsersRepo::get_meta( $user_id, 'auth0_obj' );
		return $profile ? WP_Auth0_Serializer::unserialize( $profile ) : false;
	}
}

if ( ! function_exists( 'get_currentauth0userinfo' ) ) {
	function get_currentauth0userinfo() {
		global $currentauth0_user;
		$currentauth0_user = get_auth0userinfo( get_current_user_id() );
		return $currentauth0_user;
	}
}

if ( ! function_exists( 'get_currentauth0user' ) ) {
	function get_currentauth0user() {
		return (object) array(
			'auth0_obj'   => get_auth0userinfo( get_current_user_id() ),
			'last_update' => WP_Auth0_UsersRepo::get_meta( get_current_user_id(), 'last_update' ),
			'auth0_id'    => WP_Auth0_UsersRepo::get_meta( get_current_user_id(), 'auth0_id' ),
		);
	}
}

if ( ! function_exists( 'get_auth0_curatedBlogName' ) ) {
	function get_auth0_curatedBlogName() {

		$name = get_bloginfo( 'name' );

		// WordPress can have a blank site title, which will cause initial client creation to fail
		if ( empty( $name ) ) {
			$name = wp_parse_url( home_url(), PHP_URL_HOST );

			if ( $port = wp_parse_url( home_url(), PHP_URL_PORT ) ) {
				$name .= ':' . $port;
			}
		}

		$name = preg_replace( '/[^A-Za-z0-9 ]/', '', $name );
		$name = preg_replace( '/\s+/', ' ', $name );
		$name = str_replace( ' ', '-', $name );

		return $name;
	}
}

/*
 * Localization
 */

function wp_auth0_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-auth0', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_auth0_load_plugin_textdomain' );

/*
 * Beta plugin deactivation
 */

// Passwordless beta testing - https://github.com/auth0/wp-auth0/issues/400
remove_filter( 'login_message', 'wp_auth0_pwl_plugin_login_message_before', 5 );
remove_filter( 'login_message', 'wp_auth0_pwl_plugin_login_message_after', 6 );
