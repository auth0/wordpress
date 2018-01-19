<?php

class WP_Auth0_InitialSetup {

	protected $a0_options;

	protected $connection_profile;
	protected $enterprise_connection_step;

	protected $consent_step;
	protected $adminuser_step;
	protected $connections_step;
	protected $end_step;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;

		$this->connection_profile = new WP_Auth0_InitialSetup_ConnectionProfile( $this->a0_options );
		$this->enterprise_connection_step = new WP_Auth0_InitialSetup_EnterpriseConnection( $this->a0_options );
		$this->consent_step = new WP_Auth0_InitialSetup_Consent( $this->a0_options );
		$this->adminuser_step = new WP_Auth0_InitialSetup_AdminUser( $this->a0_options );
		$this->connections_step = new WP_Auth0_InitialSetup_Connections( $this->a0_options );
		$this->end_step = new WP_Auth0_InitialSetup_End( $this->a0_options );
		$this->signup = new WP_Auth0_InitialSetup_Signup( $this->a0_options );
	}

	public function init() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'init', array( $this, 'init_setup' ), 1 );

		add_action( 'admin_action_wpauth0_callback_step1', array( $this->connection_profile, 'callback' ) );
		add_action( 'admin_action_wpauth0_callback_step3_social', array( $this->adminuser_step, 'callback' ) );

		if ( isset( $_REQUEST['page'] ) && 'wpa0-setup' === $_REQUEST['page'] ) {
			if ( isset( $_REQUEST['error'] ) ) {
				add_action( 'admin_notices', array( $this, 'notify_error' ) );
			}
		}
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
			$client_id = $this->a0_options->get( 'client_id' );
			$client_secret = $this->a0_options->get( 'client_secret' );
			$domain = $this->a0_options->get( 'domain' );

			if ( ( ! $client_id ) || ( ! $client_secret ) || ( ! $domain ) ) {
				add_action( 'admin_notices', array( $this, 'notify_setup' ) );
			}
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_create_client' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', array( $this, 'cant_create_client_message' ) );
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_create_client_grant' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', array( $this, 'cant_create_client_grant_message' ) );
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_exchange_token' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', array( $this, 'cant_exchange_token_message' ) );
		}

		if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', array( $this, 'rejected_message' ) );
		}

		if ( isset( $_REQUEST['error'] ) && 'access_denied' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', array( $this, 'access_denied' ) );
		}

	}

	public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.min.js' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );

		if ( isset( $_REQUEST['signup'] ) ) {
			$cdn_url = $this->a0_options->get( 'cdn_url' );
			wp_enqueue_script( 'wpa0_lock', $cdn_url, 'jquery' );
		}

		wp_enqueue_style( 'media' );
	}

	public function notify_setup() {
?>
  		<div class="update-nag">
        Auth0 for WordPress is not yet configured. Click <a href="<?php echo admin_url( 'admin.php?page=wpa0-setup' ); ?>">HERE</a> to configure the Auth0 for WordPress plugin using the Quick Setup Wizard.
  		</div>
  		<?php
	}

	public function notify_error() {
?>
  		<div class="error">
        <?php echo $_REQUEST['error']; ?>
  		</div>
  		<?php
	}

	public function render_setup_page() {

		$step = ( isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1 );
		$profile = ( isset( $_REQUEST['profile'] ) ? $_REQUEST['profile'] : null );

		if ( isset( $_REQUEST['signup'] ) ) {
			$this->signup->render();
			return;
		}

		if ( is_numeric( $step ) && $step >= 1 && $step <= 6 ) {

			$last_step = $this->a0_options->get( 'last_step' );

			if ( $step > $last_step ) {
				$this->a0_options->set( 'last_step', $step );
			}

			switch ( $step ) {
			case 1:
				$this->connection_profile->render( $step );
				break;

			case 2:
				if ( $profile == "social" ) {
					$this->connections_step->render( $step );
				} elseif ( $profile == "enterprise" ) {
					$this->enterprise_connection_step->render( $step );
				}
				break;

			case 3:
				if ( $profile == "social" ) {
					$this->adminuser_step->render( $step );
				} elseif ( $profile == "enterprise" ) {
					// $this->connections_step->render($step);
				}
				break;

			case 4:
				$this->end_step->render( $step );
				break;
			}
		}
	}

	public function cant_create_client_message() {
?>
  		<div id="message" class="error">
  			<p>
  				<strong>
  					<?php echo __( 'There was an error creating the Auth0 App. Check the ', 'wp-auth0' ); ?>
  					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a>
  					<?php echo __( ' for more information. If the problem persists, please create it manually in the ', 'wp-auth0' ); ?>
  					<a target="_blank" href="https://manage.auth0.com/#/applications"><?php echo __( 'Auth0 Dashboard', 'wp-auth0' ); ?></a>
  					<?php echo __( ' and copy the client_id and secret.', 'wp-auth0' ); ?>
  				</strong>
  			</p>
  		</div>
  		<?php
	}

	public function cant_create_client_grant_message() {
		?>
		<div id="message" class="error">
			<p>
				<strong>
					<?php echo __( 'There was an error creating the necessary client grants. ', 'wp-auth0' ); ?>
					<?php echo __( 'Go to your Auth0 dashboard > APIs > Auth0 Management API > Non-Interactive Clients'
					               . ' tab and authorize the client for this site. ', 'wp-auth0' ); ?>
					<?php echo __( 'Make sure to add the following scopes: ', 'wp-auth0' ); ?>
					<code><?php echo implode( '</code>, <code>', WP_Auth0_Api_Client::ConsentRequiredScopes() ) ?></code>
					<?php echo __( 'You can also check the ', 'wp-auth0' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a> <?php echo __( ' for more information.' ); ?>
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
  					<?php echo __( 'There was an error retrieving your auth0 credentials. Check the ', 'wp-auth0' ); ?>
  					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a>
  					<?php echo __( ' for more information. Please check that your sever has internet access and can reach "https://'.$domain.'/" ', 'wp-auth0' ); ?>
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
            <?php echo __( 'The required scoped were rejected.', 'wp-auth0' ); ?>
          </strong>
        </p>
      </div>
      <?php
	}
	public function access_denied() {
		$domain = $this->a0_options->get( 'domain' );
?>
  		<div id="message" class="error">
  			<p>
  				<strong>
  					<?php echo __( 'Please create your Auth0 account first at ', 'wp-auth0' ); ?>
            <a href="https://manage.auth0.com">https://manage.auth0.com</a>
  				</strong>
  			</p>
  		</div>
  		<?php
	}

	public function init_setup() {
		if ( ( ! isset( $_REQUEST['page'] ) ) || ( 'wpa0-setup' !== $_REQUEST['page'] ) || ( ! isset( $_REQUEST['callback'] ) ) ) {
			return;
		}

		if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
			wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=rejected' ) );
			exit;
		}

		if ( isset( $_REQUEST['error'] ) && 'access_denied' == $_REQUEST['error'] ) {
			wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=access_denied' ) );
			exit;
		}

		$this->consent_step->callback();
	}

}
