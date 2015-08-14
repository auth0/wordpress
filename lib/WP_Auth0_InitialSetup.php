<?php

class WP_Auth0_InitialSetup {

    public static function init() {

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
        add_action( 'init', array( __CLASS__, 'init_setup' ), 1 );

        $options = WP_Auth0_Options::Instance();
        $auth0_jwt = $options->get('auth0_app_token');

        if ( ! $auth0_jwt ) {
            add_action( 'admin_notices', array( __CLASS__, 'notify_setup' ) );
        }

        if ( isset( $_REQUEST['error'] ) && 'cant_create_client' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( __CLASS__, 'cant_create_client_message' ) );
    		}

        if ( isset( $_REQUEST['error'] ) && 'cant_exchange_token' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( __CLASS__, 'cant_exchange_token_message' ) );
    		}

        if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( __CLASS__, 'rejected_message' ) );
    		}

    }

    public static function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );
		wp_enqueue_style( 'wpa0_admin_setting', WPA0_PLUGIN_URL . 'assets/css/settings.css' );
		wp_enqueue_style( 'media' );
	}

    public static function notify_setup() {
		?>
		<div class="update-nag">
			Click <a href="<?php echo admin_url('admin.php?page=wpa0-setup'); ?>">HERE</a> to configure the Auth0 plugin.
		</div>
		<?php
	}

    public static function render_setup_page() {
        //cant_exchange_token cant_create_client
        $consent_url = self::build_consent_url();
        include WPA0_PLUGIN_DIR . 'templates/initial-setup-step1.php';
	}
    public static function cant_create_client_message() {
		?>
		<div id="message" class="error">
			<p>
				<strong>
					<?php echo __( 'There was an error creating the Auth0 App. Check the ', WPA0_LANG ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', WPA0_LANG ); ?></a>
					<?php echo __( ' for more information. If the problem persists, please create it manually in the ', WPA0_LANG ); ?>
					<a target="_blank" href="https://manage.auth0.com/#/applications"><?php echo __( 'Auth0 Dashboard', WPA0_LANG ); ?></a>
					<?php echo __( ' and copy the client_id and secret.', WPA0_LANG ); ?>
				</strong>
			</p>
		</div>
		<?php
	}
    public static function cant_exchange_token_message() {
        $options = WP_Auth0_Options::Instance();
        $domain = $options->get( 'domain' );
  		?>
  		<div id="message" class="error">
  			<p>
  				<strong>
  					<?php echo __( 'There was an error retieving your auth0 credentials. Check the ', WPA0_LANG ); ?>
  					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', WPA0_LANG ); ?></a>
  					<?php echo __( ' for more information. Please check that your sever has internet access and can reach "https://'.$domain.'/" ', WPA0_LANG ); ?>
  				</strong>
  			</p>
  		</div>
  		<?php
  	}
    public static function rejected_message() {
        $options = WP_Auth0_Options::Instance();
        $domain = $options->get( 'domain' );
  		?>
  		<div id="message" class="error">
  			<p>
  				<strong>
  					<?php echo __( 'The required scoped were rejected.', WPA0_LANG ); ?>
  				</strong>
  			</p>
  		</div>
  		<?php
  	}
    public static function init_setup() {
        if ( ( ! isset( $_REQUEST['page'] ) ) || ( 'wpa0-setup' !== $_REQUEST['page'] ) || ( ! isset( $_REQUEST['callback'] ) ) ) {
            return;
        }

        if ( isset($_REQUEST['error']) ) {
            wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=rejected' ) );
            exit;
        }

        $sucess = self::store_token_domain();

        if ( ! $sucess ) {
            wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=cant_exchange_token' ) );
            exit;
        }

        $name = get_bloginfo('name');
        self::step2_action($name);
    }
    protected static function parse_token_domain($token) {
        $parts = explode('.', $token);
        $payload = json_decode( base64_decode( strtr( $parts[1], '-_', '+/' ) ) );
        return trim(str_replace( array('/api/v2', 'https://'), '', $payload->aud ), ' /');
    }
    public static function build_consent_url() {

        $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );
        $client_id = base64_decode('QmxjVlh0VXVmRm54cnZUTFdLRXBTNG9ET3hCZm95eFo=');
        $scope = urlencode( implode( ' ', array(
            'read:connections',
            'create:clients'
        ) ) );

        $url = "https://auth0.auth0.com/authorize?client_id={$client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}";

        return $url;
    }
    public static function exchange_code() {

        if ( ! isset($_REQUEST['code']) ) {
            return null;
        }

        $code = $_REQUEST['code'];
        $domain = 'auth0.auth0.com';
        $client_id = base64_decode('QmxjVlh0VXVmRm54cnZUTFdLRXBTNG9ET3hCZm95eFo=');
        $client_secret = base64_decode('a3JrN09COFJBWngwQ0JkcVEwdXVmV1k5WjJLdTUxV0l6Ml9qRjM3aVVSMmpQbWU5RjNUT1lBNmJUVkpseFNldQ==');
        $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );

        $response = WP_Auth0_Api_Client::get_token( $domain, $client_id, $client_secret, 'authorization_code', array(
                'redirect_uri' => home_url(),
                'code' => $code,
            ) );

        $obj = json_decode($response['body']);

        if (isset($obj->error)) {
            return null;
        }

        return $obj->access_token;
    }
    public static function store_token_domain() {
        $app_token = self::exchange_code();

        if ($app_token === null) {
            return false;
        }

        $app_domain = self::parse_token_domain($app_token);

        $options = WP_Auth0_Options::Instance();
        $options->set( 'auth0_app_token', $app_token );
        $options->set( 'domain', $app_domain );

        return true;
    }
    public static function step2_action($name) {

        // $name = $_REQUEST['app_name'];
        $options = WP_Auth0_Options::Instance();
        $app_token = $options->get( 'auth0_app_token' );
        $domain = $options->get( 'domain' );
        $callbackUrl = site_url('/index.php?auth0=1');

        $response = WP_Auth0_Api_Client::create_client($domain, $app_token, $name, $callbackUrl);

        if ($response === false) {
            wp_redirect( admin_url( 'admin.php?page=wpa0&error=cant_create_client' ) );
            exit;
        }

        $options->set( 'client_id', $response->client_id );
        $options->set( 'client_secret', $response->client_secret );

        $connections = WP_Auth0_Api_Client::search_connection($domain, $app_token);

        $enabled_connections = $options->get_enabled_connections();

        foreach ($connections as $connection) {
            if ( in_array( $connection->name, $enabled_connections ) ) {

                $options->set( "social_{$connection->name}" , 1 );
                $options->set( "social_{$connection->name}_key" , $connection->options->client_id );
                $options->set( "social_{$connection->name}_secret" , $connection->options->client_secret );

            }
        }

        wp_redirect( admin_url( 'admin.php?page=wpa0' ) );
        exit();

    }

}
