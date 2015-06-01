<?php
/**
 * Plugin Name: Wordpress Auth0 Integration
 * Description: Implements the Auth0 Single Sign On solution into Wordpress
 * Version: 1.2.8
 * Author: Auth0
 * Author URI: https://auth0.com
 */

define('WPA0_PLUGIN_FILE', __FILE__);
define('WPA0_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
define('WPA0_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__) ));
define('WPA0_LANG', 'wp-auth0');
define('AUTH0_DB_VERSION', 2);
define('WPA0_VERSION', '1.2.8');

class WP_Auth0 {
    public static function init(){ 
        spl_autoload_register(array(__CLASS__, 'autoloader'));

        // WP_Auth0_Referer_Check::init();
        WP_Auth0_Ip_Check::init();

        add_action( 'init', array(__CLASS__, 'wp_init') );

        // Add hooks for clear up session
        add_action( 'wp_logout', array(__CLASS__, 'logout') );
        add_action( 'wp_login', array(__CLASS__, 'end_session') );

        // Add hooks for install uninstall and update
        register_activation_hook( WPA0_PLUGIN_FILE, array(__CLASS__, 'install') );
        register_deactivation_hook( WPA0_PLUGIN_FILE, array(__CLASS__, 'uninstall') );
        add_action( 'plugins_loaded', array(__CLASS__, 'check_update'));


        add_action( 'plugins_loaded', array(__CLASS__, 'initialize_wpdb_tables'));
        add_action( 'template_redirect', array(__CLASS__, 'init_auth0'), 1 );

        // Add an action to append a stylesheet for the login page
        add_action( 'login_enqueue_scripts', array(__CLASS__, 'render_auth0_login_css') );

        // Add a hook to add Auth0 code on the login page
        add_filter( 'login_message', array(__CLASS__, 'render_form') );

        // Add hook to redirect directly on login auto
        add_action('login_init', array(__CLASS__, 'login_auto'));
        // Add hook to handle when a user is deleted
        add_action( 'delete_user', array(__CLASS__, 'delete_user') );

        add_shortcode( 'auth0', array(__CLASS__, 'shortcode' ) );

        add_action( 'wp_enqueue_scripts', array(__CLASS__, 'wp_enqueue'));

        add_action( 'widgets_init', array(__CLASS__, 'wp_register_widget'));

        add_filter('query_vars', array(__CLASS__, 'a0_register_query_vars'));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(__CLASS__, 'wp_add_plugin_settings_link'));

        if (isset($_GET['message']))
        {
            add_action( 'wp_footer', array( __CLASS__, 'a0_render_message' ) );
        }

        WP_Auth0_UsersRepo::init();
        WP_Auth0_Settings_Section::init();
        WP_Auth0_Admin::init();
        WP_Auth0_ErrorLog::init();
        WP_Auth0_Configure_JWTAUTH::init();

        add_action('plugins_loaded', array( __CLASS__, 'checkJWTAuth' ));
        add_filter( 'woocommerce_checkout_login_message', array(__CLASS__, 'override_woocommerce_checkout_login_form') );
        add_filter( 'woocommerce_before_customer_login_form', array(__CLASS__, 'override_woocommerce_login_form') );
    }

    public static function override_woocommerce_checkout_login_form( $html ){
        self::override_woocommerce_login_form($html);

        if (isset($_GET['wle'])) {
            echo "<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>";
        }
    }

    public static function override_woocommerce_login_form( $html ){
        self::render_auth0_login_css();
        echo self::render_form('');
    }

    public static function isJWTAuthEnabled() {
        if (!function_exists('is_plugin_active')) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        return is_plugin_active('wp-jwt-auth/JWT_AUTH.php');
    }

    public static function isJWTConfigured() {

        return (
            JWT_AUTH_Options::get('aud') == WP_Auth0_Options::get('client_id') &&
            JWT_AUTH_Options::get('secret') == WP_Auth0_Options::get('client_secret') &&
            JWT_AUTH_Options::get('secret_base64_encoded') &&
            WP_Auth0_Options::get('jwt_auth_integration') &&
            JWT_AUTH_Options::get('jwt_attribute') == 'sub'
        );

    }

    public static function checkJWTAuth() {
        if ( isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpa0-jwt-auth' ) return;

        if( self::isJWTAuthEnabled() && !self::isJWTConfigured() ) {
            add_action( 'admin_notices', array(__CLASS__,'notify_jwt' ));
        }

    }

    public static function notify_jwt() {
        ?>
        <div class="update-nag">
            JWT Auth installed. To configure it to work the Auth0 plugin, click <a href="admin.php?page=wpa0-jwt-auth">HERE</a>
        </div>
        <?php

    }


    public static function getPluginDirUrl()
    {
        return plugin_dir_url( __FILE__ );
    }

    public static function  a0_register_query_vars( $qvars ) {
        $qvars[] = 'error_description';
        return $qvars;
    }

    public static function a0_render_message()
    {
        $message = null;

        switch (strtolower($_GET['message']))
        {
            //case '': $message = ""; break;
        }

        if ($message)
        {
            echo "<div class=\"a0-message\">$message <small onclick=\"jQuery('.a0-message').hide();\">(Close)</small></div>";
            echo '<script type="text/javascript">
                setTimeout(function(){jQuery(".a0-message").hide();}, 10 * 1000);
            </script>';
        }
    }

    // Add settings link on plugin page
    public static function wp_add_plugin_settings_link($links) {

        $settings_link = '<a href="admin.php?page=wpa0-errors">Error Log</a>';
        array_unshift($links, $settings_link);

        $settings_link = '<a href="admin.php?page=wpa0">Settings</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    public static function wp_register_widget() {
        register_widget( 'WP_Auth0_Embed_Widget' );
        register_widget( 'WP_Auth0_Popup_Widget' );
    }

    public static function wp_enqueue(){
        $client_id = WP_Auth0_Options::get('client_id');

        if (trim($client_id) == "") return;

        if (isset($_GET['message']))
        {
            wp_enqueue_script('jquery');
        }

        wp_enqueue_style( 'auth0-widget', WPA0_PLUGIN_URL . 'assets/css/main.css' );
    }

    public static function shortcode( $atts ){
        ob_start();

        require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
        renderAuth0Form(false, self::buildSettings($atts));

        $html = ob_get_clean();
        return $html;
    }

    public static function login_auto() {
        $auto_login = absint(WP_Auth0_Options::get( 'auto_login' ));

        if ($auto_login && $_GET["action"] != "logout" && !isset($_GET['wle'])) {

            $stateObj = array("interim" => false, "uuid" =>uniqid());
            if (isset($_GET['redirect_to'])) {
                $stateObj["redirect_to"] = $_GET['redirect_to'];
            }
            $state = json_encode($stateObj);
            // Create the link to log in

            $login_url = "https://". WP_Auth0_Options::get('domain') .
                         "/authorize?response_type=code&scope=openid%20profile".
                         "&client_id=".WP_Auth0_Options::get('client_id') .
                         "&redirect_uri=".site_url('/index.php?auth0=1') .
                         "&state=".urlencode($state).
                         "&connection=".WP_Auth0_Options::get('auto_login_method');

            wp_redirect($login_url);
            die();
        }
    }

    public static function logout() {
        self::end_session();

        $auto_login = absint(WP_Auth0_Options::get( 'auto_login' ));
        if ($auto_login) {
            wp_redirect(home_url());
            die();
        }

    }

    public static function render_back_to_auth0() {

        include WPA0_PLUGIN_DIR . 'templates/back-to-auth0.php';

    }

    protected static function GetBoolean($value)
    {
        return ($value == 1 || strtolower($value) == 'true');
    }
    protected static function IsValid($array, $key)
    {
        return (isset($array[$key]) && trim($array[$key]) != '');
    }
    public static function buildSettings($settings)
    {
        $options_obj = array();
        if (isset($settings['form_title']) &&
            (!isset($settings['dict']) || (isset($settings['dict']) && trim($settings['dict']) == '')) &&
            trim($settings['form_title']) != '') {
            $options_obj['dict'] = array(
                "signin" => array(
                    "title" => $settings['form_title']
                )
            );
        }
        elseif (isset($settings['dict']) && trim($settings['dict']) != '') {
            if ($oDict = json_decode($settings['dict'], true)) {
                $options_obj['dict'] = $oDict;
            }
            else{
                $options_obj['dict'] = $settings['dict'];
            }
        }
        if (self::IsValid($settings,'custom_css')) {
            $options_obj['custom_css'] = $settings['custom_css'];
        }
        if (self::IsValid($settings,'custom_js')) {
            $options_obj['custom_js'] = $settings['custom_js'];
        }
        if (self::IsValid($settings,'social_big_buttons')) {
            $options_obj['socialBigButtons'] = self::GetBoolean($settings['social_big_buttons']);
        }
        if (self::IsValid($settings,'gravatar')) {
            $options_obj['gravatar'] = self::GetBoolean($settings['gravatar']);
        }
        if (self::IsValid($settings,'username_style')) {
            $options_obj['usernameStyle'] = $settings['username_style'];
        }
        if (self::IsValid($settings,'remember_last_login')) {
            $options_obj['rememberLastLogin'] = self::GetBoolean($settings['remember_last_login']);
        }
        if (self::IsValid($settings,'sso')) {
            $options_obj['sso'] = self::GetBoolean($settings['sso']);
        }
        if (self::IsValid($settings,'auth0_implicit_workflow')) {
            $options_obj['auth0_implicit_workflow'] = self::GetBoolean($settings['auth0_implicit_workflow']);
        }
        if (self::IsValid($settings,'icon_url')) {
            $options_obj['icon'] = $settings['icon_url'];
        }
        if (isset($settings['extra_conf']) && trim($settings['extra_conf']) != '') {
            $extra_conf_arr = json_decode($settings['extra_conf'], true);
            $options_obj = array_merge( $extra_conf_arr, $options_obj );
        }
        return $options_obj;
    }

    public static function render_auth0_login_css() {
        $client_id = WP_Auth0_Options::get('client_id');

        if (trim($client_id) == "") return;
    ?>
        <link rel='stylesheet' href='<?php echo plugins_url( 'assets/css/login.css', __FILE__ ); ?>' type='text/css' />
    <?php
    }

    public static function render_form( $html ){
        $client_id = WP_Auth0_Options::get('client_id');

        if (trim($client_id) == "") return;

        ob_start();
        require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
        renderAuth0Form();

        $html = ob_get_clean();
        return $html;
    }

    public static function init_auth0(){
        global $wp_query;

        if(!isset($wp_query->query_vars['auth0'])) {
            return;
        }

        if ($wp_query->query_vars['auth0'] == 'implicit') {
            self::implicitLogin();
        }

        if ($wp_query->query_vars['auth0'] != '1') {
            return;
        }

        if (isset($wp_query->query_vars['error_description']) && trim($wp_query->query_vars['error_description']) != '')
        {
            $msg = __('There was a problem with your log in:', WPA0_LANG);
            $msg .= ' '.$wp_query->query_vars['error_description'];
            $msg .= '<br/><br/>';
            $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
            wp_die($msg);
        }
        if (isset($wp_query->query_vars['error']) && trim($wp_query->query_vars['error']) != '')
        {
            $msg = __('There was a problem with your log in:', WPA0_LANG);
            $msg .= ' '.$wp_query->query_vars['error'];
            $msg .= '<br/><br/>';
            $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
            wp_die($msg);
        }

        $code = $wp_query->query_vars['code'];
        $state = $wp_query->query_vars['state'];
        $stateFromGet = json_decode(stripcslashes($state));

        $domain = WP_Auth0_Options::get( 'domain' );

        $client_id = WP_Auth0_Options::get( 'client_id' );
        $client_secret = WP_Auth0_Options::get( 'client_secret' );

        if(empty($client_id)) wp_die(__('Error: Your Auth0 Client ID has not been entered in the Auth0 SSO plugin settings.', WPA0_LANG));
        if(empty($client_secret)) wp_die(__('Error: Your Auth0 Client Secret has not been entered in the Auth0 SSO plugin settings.', WPA0_LANG));
        if(empty($domain)) wp_die(__('Error: No Domain defined in Wordpress Administration!', WPA0_LANG));

        $response = WP_Auth0_Api_Client::get_token($domain, $client_id, $client_secret, 'authorization_code', array(
                'redirect_uri' => home_url(),
                'code' => $code,
            ));

        if ($response instanceof WP_Error) {

            self::insertAuth0Error('init_auth0_oauth/token',$response);

            error_log($response->get_error_message());
            $msg = __('Sorry. There was a problem logging you in.', WPA0_LANG);
            $msg .= '<br/><br/>';
            $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
            wp_die($msg);
        }

        $data = json_decode( $response['body'] );

        if(isset($data->access_token)){
            // Get the user information
            $response = WP_Auth0_Api_Client::get_user_info($domain, $data->access_token);

            if ($response instanceof WP_Error) {

                self::insertAuth0Error('init_auth0_userinfo',$response);

                error_log($response->get_error_message());
                $msg = __('There was a problem with your log in.', WPA0_LANG);
                $msg .= '<br/><br/>';
                $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
                wp_die($msg);
            }

            $userinfo = json_decode( $response['body'] );
            if (self::login_user($userinfo, $data->id_token)) {
                if ($stateFromGet !== null && isset($stateFromGet->interim) && $stateFromGet->interim) {
                    include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
                    exit();

                } else {

                    if ($stateFromGet !== null && isset($stateFromGet->redirect_to)) {
                        $redirectURL = $stateFromGet->redirect_to;
                    } else {
                        $redirectURL = WP_Auth0_Options::get( 'default_login_redirection' );
                    }

                    wp_safe_redirect($redirectURL);
                }
            }
        }elseif (is_array($response['response']) && $response['response']['code'] == 401) {

            $error = new WP_Error('401', 'auth/token response code: 401 Unauthorized');

            self::insertAuth0Error('init_auth0_oauth/token',$error);

            $msg = __('Error: the Client Secret configured on the Auth0 plugin is wrong. Make sure to copy the right one from the Auth0 dashboard.', WPA0_LANG);
            $msg .= '<br/><br/>';
            $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
            wp_die($msg);

        }else{

            $error = '';
            $description = '';

            if (isset($data->error)) $error = $data->error;
            if (isset($data->error_description)) $description = $data->error_description;

            if (!empty($error) || !empty($description))
            {
                $error = new WP_Error($error, $description);
                self::insertAuth0Error('init_auth0_oauth/token',$error);
            }

            // Login failed!
            wp_redirect( home_url() . '?message=' . $data->error_description );
            //echo "Error logging in! Description received was:<br/>" . $data->error_description;
        }

        exit();
    }

    private static function findAuth0User($id) {
        global $wpdb;
        $sql = 'SELECT u.*
                FROM ' . $wpdb->auth0_user .' a
                JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
                WHERE a.auth0_id = %s';
        $userRow = $wpdb->get_row($wpdb->prepare($sql, $id));

        if (is_null($userRow)) {
            return null;
        }elseif($userRow instanceof WP_Error ) {
            self::insertAuth0Error('findAuth0User',$userRow);
            return null;
        }
        $user = new WP_User();
        $user->init($userRow);
        return $user;
    }

    public static function insertAuth0Error($section, $wp_error) {

        if ($wp_error instanceof WP_Error) {
            $code = $wp_error->get_error_code();
            $message = $wp_error->get_error_message();
        }
        elseif($wp_error instanceof Exception) {
            $code = $wp_error->getCode();
            $message = $wp_error->getMessage();
        }
        
        global $wpdb;
        $wpdb->insert(
            $wpdb->auth0_error_logs,
            array(
                'section' => $section,
                'date' => date('c'),
                'code' => $code,
                'message' => $message
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
    }

    private static function updateAuth0Object($userinfo) {
        global $wpdb;
        $wpdb->update(
            $wpdb->auth0_user,
            array(
                'auth0_obj' => serialize($userinfo)
            ),
            array( 'auth0_id' => $userinfo->user_id ),
            array( '%s' ),
            array( '%s' )
        );
    }

    public static function delete_user ($user_id) {
        global $wpdb;
        $wpdb->delete( $wpdb->auth0_user, array( 'wp_id' => $user_id), array( '%d' ) );
    }

    private static function dieWithVerifyEmail($userinfo, $id_token) {

        ob_start();
        $domain = WP_Auth0_Options::get( 'domain' );
        $token =  $id_token;
        $email = $userinfo->email;
        $connection = $userinfo->identities[0]->connection;
        $userId = $userinfo->user_id;
        include WPA0_PLUGIN_DIR . 'templates/verify-email.php';

        $html = ob_get_clean();

        wp_die($html);

    }
    public static function login_user( $userinfo, $id_token ){
        // If the userinfo has no email or an unverified email, and in the options we require a verified email
        // notify the user he cant login until he does so.
        $requires_verified_email = WP_Auth0_Options::get( 'requires_verified_email' );

        if ($requires_verified_email == 1){
            if (empty($userinfo->email)) {
                $msg = __('This account does not have an email associated. Please login with a different provider.', WPA0_LANG);
                $msg .= '<br/><br/>';
                $msg .= '<a href="' . site_url() . '">' . __('← Go back', WPA0_LANG) . '</a>';

                wp_die($msg);
            }

            if (!$userinfo->email_verified) {
                self::dieWithVerifyEmail($userinfo, $id_token);
            }

        }
        // See if there is a user in the auth0_user table with the user info client id
        $user = self::findAuth0User($userinfo->user_id);

        if (!is_null($user)) {
            // User exists! Log in
            self::updateAuth0Object($userinfo);
            wp_set_auth_cookie( $user->ID );
            return true;
        } else {

            try {
                $creator = new WP_Auth0_UserCreator();
                $user_id = $creator->create($userinfo, $id_token);

                wp_set_auth_cookie( $user_id );
            }
            catch (WP_Auth0_CouldNotCreateUserException $e) {
                $msg = __('Error: Could not create user.', WPA0_LANG);
                $msg =  ' ' . $e->getMessage();
                $msg .= '<br/><br/>';
                $msg .= '<a href="' . site_url() . '">' . __('← Go back', WPA0_LANG) . '</a>';
                wp_die($msg);
            }
            catch (WP_Auth0_RegistrationNotEnabledException $e) {
                $msg = __('Error: Could not create user. The registration process is not available.', WPA0_LANG);
                $msg .= '<br/><br/>';
                $msg .= '<a href="' . site_url() . '">' . __('← Go back', WPA0_LANG) . '</a>';
                wp_die($msg);
            }
            catch (WP_Auth0_EmailNotVerifiedException $e) {
                self::dieWithVerifyEmail($e->userinfo, $e->id_token);
            }

            
            return true;
        }
    }

    public static function implicitLogin() {

        require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/BeforeValidException.php';
        require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/ExpiredException.php';
        require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Exceptions/SignatureInvalidException.php';
        require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Authentication/JWT.php';

        $token = $_POST["token"];
        $stateFromGet = json_decode($_POST["state"]);

        $secret = WP_Auth0_Options::get('client_secret');
        $secret = base64_decode(strtr($secret, '-_', '+/'));

        try {
            // Decode the user
            $decodedToken = \JWT::decode($token, $secret, array('HS256'));

            // validate that this JWT was made for us
            if ($decodedToken->aud != WP_Auth0_Options::get('client_id')) {
                throw new Exception("This token is not intended for us.");
            }

            $decodedToken->user_id = $decodedToken->sub;

            if (self::login_user($decodedToken, $token)) {
                if ($stateFromGet !== null && isset($stateFromGet->interim) && $stateFromGet->interim) {
                    include WPA0_PLUGIN_DIR . 'templates/login-interim.php';
                    exit();

                } else {

                    if ($stateFromGet !== null && isset($stateFromGet->redirect_to)) {
                        $redirectURL = $stateFromGet->redirect_to;
                    } else {
                        $redirectURL = WP_Auth0_Options::get( 'default_login_redirection' );
                    }

                    wp_safe_redirect($redirectURL);
                }
            }

        } catch(\UnexpectedValueException $e) {
            self::insertAuth0Error('implicitLogin',$e);

            error_log($e->getMessage());
            $msg = __('Sorry. There was a problem logging you in.', WPA0_LANG);
            $msg .= '<br/><br/>';
            $msg .= '<a href="' . wp_login_url() . '">' . __('← Login', WPA0_LANG) . '</a>';
            wp_die($msg);
        }

    }

    public static function wp_init(){
        self::setup_rewrites();

        $cdn_url = WP_Auth0_Options::get('cdn_url');
        if (strpos($cdn_url, 'auth0-widget-5') !== false)
        {
            WP_Auth0_Options::set( 'cdn_url', '//cdn.auth0.com/js/lock-6.min.js' );
            //WP_Auth0_Options::set( 'version', 1 );
        }

        // Initialize session
        // if(!session_id()) {
            // session_start();
        // }
    }
    public static function end_session() {
        if(session_id()) {
            session_destroy();
        }
    }

    private static function setup_rewrites(){
        add_rewrite_tag('%auth0%', '([^&]+)');
        add_rewrite_tag('%code%', '([^&]+)');
        add_rewrite_tag('%state%', '([^&]+)');
        add_rewrite_tag('%auth0_error%', '([^&]+)');
        add_rewrite_rule('^auth0', 'index.php?auth0=1', 'top');
    }

    public static function install(){
        self::install_db();
        self::setup_rewrites();

        flush_rewrite_rules();
    }

    public static function uninstall(){
        flush_rewrite_rules();
    }

    private static function install_db(){
        global $wpdb;

        self::initialize_wpdb_tables();

        $sql = array();

        $sql[] = "CREATE TABLE ".$wpdb->auth0_log." (
                    id INT(11) AUTO_INCREMENT NOT NULL,
                    event VARCHAR(100) NOT NULL,
                    level VARCHAR(100) NOT NULL DEFAULT 'notice',
                    description TEXT,
                    details LONGTEXT,
                    logtime INT(11) NOT NULL,
                    PRIMARY KEY  (id)
                );";

        $sql[] = "CREATE TABLE ".$wpdb->auth0_user." (
                    auth0_id VARCHAR(100) NOT NULL,
                    wp_id INT(11)  NOT NULL,
                    auth0_obj TEXT,
                    PRIMARY KEY  (auth0_id)
                );";

        $sql[] = "CREATE TABLE ".$wpdb->auth0_error_logs." (
                    id INT(11) AUTO_INCREMENT NOT NULL,
                    date DATETIME  NOT NULL,
                    section VARCHAR(255),
                    code VARCHAR(255),
                    message TEXT,
                    PRIMARY KEY  (id)
                );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach($sql as $s) {
            dbDelta($s);
        }
        update_option( "auth0_db_version", AUTH0_DB_VERSION );

    }

    public static function check_update() {
        if ( get_site_option( 'auth0_db_version' ) != AUTH0_DB_VERSION) {
            self::install_db();
        }
    }

    public static function initialize_wpdb_tables(){
        global $wpdb;

        $wpdb->auth0_log = $wpdb->prefix."auth0_log";
        $wpdb->auth0_user = $wpdb->prefix."auth0_user";
        $wpdb->auth0_error_logs = $wpdb->prefix."auth0_error_logs";
    }

    private static function autoloader($class){
        $path = WPA0_PLUGIN_DIR;
        $paths = array();
        $exts = array('.php', '.class.php');

        $paths[] = $path;
        $paths[] = $path.'lib/';
        $paths[] = $path.'lib/exceptions/';

        foreach($paths as $p)
            foreach($exts as $ext){
                if(file_exists($p.$class.$ext)){
                    require_once($p.$class.$ext);
                    return true;
                }
            }

        return false;
    }
}


if ( !function_exists('get_currentauth0userinfo') ) :

function get_currentauth0userinfo() {
    global $current_user;
    global $currentauth0_user;
    global $wpdb;

    get_currentuserinfo();
    if ($current_user instanceof WP_User && $current_user->ID > 0 ) {
        $sql = 'SELECT auth0_obj
                FROM ' . $wpdb->auth0_user .'
                WHERE wp_id = %d';
        $result = $wpdb->get_row($wpdb->prepare($sql, $current_user->ID));
        if (is_null($result) || $result instanceof WP_Error ) {

            self::insertAuth0Error('get_currentauth0userinfo',$result);

            return null;
        }
        $currentauth0_user = unserialize($result->auth0_obj);
    }
}
endif;

WP_Auth0::init();
