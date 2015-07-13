<?php
//eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJqY05ZOHd4YVoxWnVRYjhldlJJSGgzYkt3V0dWdEdqZyIsInNjb3BlcyI6eyJjbGllbnRzIjp7ImFjdGlvbnMiOlsicmVhZCIsImNyZWF0ZSIsInVwZGF0ZSJdfSwiY2xpZW50X2tleXMiOnsiYWN0aW9ucyI6WyJyZWFkIl19LCJjb25uZWN0aW9ucyI6eyJhY3Rpb25zIjpbInJlYWQiLCJjcmVhdGUiLCJ1cGRhdGUiXX0sInJ1bGVzIjp7ImFjdGlvbnMiOlsicmVhZCIsImNyZWF0ZSIsInVwZGF0ZSJdfSwidXNlcnMiOnsiYWN0aW9ucyI6WyJyZWFkIiwiY3JlYXRlIiwidXBkYXRlIl19LCJ1c2Vyc19hcHBfbWV0YWRhdGEiOnsiYWN0aW9ucyI6WyJ1cGRhdGUiXX19LCJpYXQiOjE0MzY4MTk4NDAsImp0aSI6IjRlMzYyZmU4NDE3ZGQ1YjBjMzdhN2VlMjI0N2VjMDhjIn0.CcOVZcdkXdazB0-hdkGaNGFLs65G7szJOBTGgehVito
class WP_Auth0_InitialSetup {

    public static function init() {

        add_action( 'admin_action_wpauth0_initialsetup_step2', array(__CLASS__, 'step2_action') );
        add_action( 'admin_action_wpauth0_initialsetup_step3', array(__CLASS__, 'step3_action') );

        $options = WP_Auth0_Options::Instance();
        $auth0_jwt = $options->get('auth0_app_token');

        if ( ! $auth0_jwt ) {
            add_action( 'admin_notices', array( __CLASS__, 'notify_setup' ) );
        }

    }

    public static function notify_setup() {
		?>
		<div class="update-nag">
			Click <a href="<?php echo admin_url('admin.php?page=wpa0-setup&step=3'); ?>">HERE</a> to configure the Auth0 plugin.
		</div>
		<?php
	}

    public static function render_setup_page() {
        $step = 1;
        if (isset($_REQUEST['step'])) {
            $step = $_REQUEST['step'];
        }

        self::render($step);
    }

    protected static function render($step) {
        switch ( $step ) {
            case 3:
                include WPA0_PLUGIN_DIR . 'templates/initial-setup-step3.php';
                break;

            case 2:
                include WPA0_PLUGIN_DIR . 'templates/initial-setup-step2.php';
                break;

            case 1:
            default:
                include WPA0_PLUGIN_DIR . 'templates/initial-setup-step1.php';
                break;
        }
	}

    public static function step2_action() {

        $app_token = $_REQUEST['app_token'];
        $options = WP_Auth0_Options::Instance();
        $options->set( 'auth0_app_token', $app_token );
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=3' ) );
        exit();

    }


    public static function step3_action() {

        $name = $_REQUEST['app_name'];
        $options = WP_Auth0_Options::Instance();
        $app_token = $options->get( 'auth0_app_token' );
        $domain = $options->get( 'domain' );
        $callbackUrl = site_url('/index.php?auth0=1');

        $response = WP_Auth0_Api_Client::create_client($domain, $app_token, $name, $callbackUrl);

        $options->set( 'client_id', $response->client_id );
        $options->set( 'client_secret', $response->client_secret );

        wp_redirect( admin_url( 'admin.php?page=wpa0-setup' ) );
        exit();

    }

}
