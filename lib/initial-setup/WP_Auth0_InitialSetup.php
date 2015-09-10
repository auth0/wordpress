<?php

class WP_Auth0_InitialSetup {

    protected $a0_options;

    protected $consent_step;
    protected $migration_step;
    protected $adminuser_step;
    protected $connections_step;
    protected $rules_step;
    protected $end_step;

    public function __construct(WP_Auth0_Options $a0_options) {
        $this->a0_options = $a0_options;

        $this->consent_step = new WP_Auth0_InitialSetup_Consent($this->a0_options);
        $this->migration_step = new WP_Auth0_InitialSetup_Migration($this->a0_options);
        $this->adminuser_step = new WP_Auth0_InitialSetup_AdminUser($this->a0_options);
        $this->connections_step = new WP_Auth0_InitialSetup_Connections($this->a0_options);
        $this->rules_step = new WP_Auth0_InitialSetup_Rules($this->a0_options);
        $this->end_step = new WP_Auth0_InitialSetup_End($this->a0_options);
    }

    public function init() {

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
        add_action( 'init', array( $this, 'init_setup' ), 1 );

        add_action( 'admin_action_wpauth0_callback_step2', array($this->migration_step, 'callback') );
        add_action( 'admin_action_wpauth0_callback_step3', array($this->adminuser_step, 'callback') );
        add_action( 'admin_action_wpauth0_callback_step4', array($this->connections_step, 'callback') );
        add_action( 'admin_action_wpauth0_callback_step5', array($this->rules_step, 'callback') );

        if ( ! isset( $_REQUEST['page'] ) || 'wpa0-setup' !== $_REQUEST['page'] ) {
          $client_id = $this->a0_options->get('client_id');
          $client_secret = $this->a0_options->get('client_secret');
          $domain = $this->a0_options->get('domain');

          if ( ( ! $client_id) || ( ! $client_secret) || ( ! $domain) ) {
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

        $step = (isset($_REQUEST['step']) ? $_REQUEST['step'] : 1);

        if (is_numeric($step) && $step >= 1 && $step <= 6) {

          $last_step = $this->a0_options->get('last_step');

          if ($step > $last_step) {
            $this->a0_options->set('last_step', $step);
          }

          switch ($step) {
            case 1:
              $this->consent_step->render($step);
              break;

            case 2:
              $this->migration_step->render($step);
              break;

            case 3:
              $this->adminuser_step->render($step);
              break;

            case 4:
              $this->connections_step->render($step);
              break;

            case 5:
              $this->rules_step->render($step);
              break;

            case 6:
              $this->end_step->render($step);
              break;
          }
        }
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

      $this->consent_step->callback();
    }

}
