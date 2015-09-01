<?php

class WP_Auth0_InitialSetup {

    protected $a0_options;

    protected $domain = 'login0.myauth0.com';
    protected $client_id = 'MefoL5F3mJHJ48RDbUTEertAWIT2nRCY';
    protected $client_secret = 'QXxTULIzbPnfZTHk-PNZXIdkyEHNKmLhuuiqjk7qMfXAGc83tfQZvOV79-7iIXGv';

    public function __construct(WP_Auth0_Options $a0_options) {
        $this->a0_options = $a0_options;
    }

    public function init() {

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
        add_action( 'init', array( $this, 'init_setup' ), 1 );

        if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
          $client_id = $this->a0_options->get('client_id');

          if ( ! $client_id ) {
              add_action( 'admin_notices', array( $this, 'notify_setup' ) );
          }
    		}

        if ( isset( $_REQUEST['error'] ) && 'cant_create_client' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( $this, 'cant_create_client_message' ) );
    		}

        if ( isset( $_REQUEST['error'] ) && 'cant_exchange_token' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( $this, 'cant_exchange_token_message' ) );
    		}

        if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
    			add_action( 'admin_notices', array( $this, 'rejected_message' ) );
    		}

    }

    public function admin_enqueue() {
  		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
  			return;
  		}

  		wp_enqueue_media();
  		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );
  		wp_enqueue_style( 'wpa0_admin_setting', WPA0_PLUGIN_URL . 'assets/css/settings.css' );
  		wp_enqueue_style( 'media' );
  	}

    public function notify_setup() {
  		?>
  		<div class="update-nag">
        Auth0 for WordPress is not yet configured. Click <a href="<?php echo admin_url('admin.php?page=wpa0-setup'); ?>">HERE</a> to configure the Auth0 for WordPress plugin using the Quick Setup Wizard.
  		</div>
  		<?php
  	}

    public function render_setup_page() {
        //cant_exchange_token cant_create_client
        $consent_url = $this->build_consent_url();
        include WPA0_PLUGIN_DIR . 'templates/initial-setup-step1.php';
  	}

    public function cant_create_client_message() {
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

    public function cant_exchange_token_message() {
      $domain = $this->a0_options->get( 'domain' );
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

    public function rejected_message() {
      $domain = $this->a0_options->get( 'domain' );
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

    public function init_setup() {
      if ( ( ! isset( $_REQUEST['page'] ) ) || ( 'wpa0-setup' !== $_REQUEST['page'] ) || ( ! isset( $_REQUEST['callback'] ) ) ) {
        return;
      }

      if ( isset($_REQUEST['error']) ) {
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=rejected' ) );
        exit;
      }

      $sucess = $this->store_token_domain();

      if ( ! $sucess ) {
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=cant_exchange_token' ) );
        exit;
      }

      $name = get_bloginfo('name');
      $this->step2_action($name);
    }

    protected function parse_token_domain($token) {
      $parts = explode('.', $token);
      $payload = json_decode( JWT::urlsafeB64Decode( $parts[1] ) );
      return trim(str_replace( array('/api/v2', 'https://'), '', $payload->aud ), ' /');
    }

    public function build_consent_url() {
      $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );

      $scope = urlencode( implode( ' ', array(
          'read:connections',
          'create:clients'
      ) ) );

      $url = "https://{$this->domain}/authorize?client_id={$this->client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}";

      return $url;
    }

    public function exchange_code() {
      if ( ! isset($_REQUEST['code']) ) {
          return null;
      }

      $code = $_REQUEST['code'];
      $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );

      $response = WP_Auth0_Api_Client::get_token( $this->domain, $this->client_id, $this->client_secret, 'authorization_code', array(
              'redirect_uri' => home_url(),
              'code' => $code,
          ) );

      $obj = json_decode($response['body']);

      if (isset($obj->error)) {
          return null;
      }

      return $obj->access_token;
    }

    public function store_token_domain() {
      $app_token = $this->exchange_code();

      if ($app_token === null) {
          return false;
      }

      $app_domain = $this->parse_token_domain($app_token);

      $this->a0_options->set( 'auth0_app_token', $app_token );
      $this->a0_options->set( 'domain', $app_domain );

      return true;
    }

    public function step2_action($name) {

      $app_token = $this->a0_options->get( 'auth0_app_token' );
      $domain = $this->a0_options->get( 'domain' );

      $response = WP_Auth0_Api_Client::create_client($domain, $app_token, $name);

      if ($response === false) {
          wp_redirect( admin_url( 'admin.php?page=wpa0&error=cant_create_client' ) );
          exit;
      }

      $this->a0_options->set( 'client_id', $response->client_id );
      $this->a0_options->set( 'client_secret', $response->client_secret );

      $connections = WP_Auth0_Api_Client::search_connection($domain, $app_token);

      $enabled_connections = $this->a0_options->get_enabled_connections();

      foreach ($connections as $connection) {
          if ( in_array( $connection->name, $enabled_connections ) ) {

              $this->a0_options->set( "social_{$connection->name}" , 1 );
              $this->a0_options->set( "social_{$connection->name}_key" , isset($connection->options->client_id) ? $connection->options->client_id : null );
              $this->a0_options->set( "social_{$connection->name}_secret" , isset($connection->options->client_secret) ? $connection->options->client_secret : null );

          }
      }

      wp_redirect( admin_url( 'admin.php?page=wpa0' ) );
      exit();

    }

}
