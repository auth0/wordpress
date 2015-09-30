<?php

class WP_Auth0_InitialSetup_AdminUser {

      protected $a0_options;

      public function __construct(WP_Auth0_Options $a0_options) {
          $this->a0_options = $a0_options;

          add_action( 'init', array( $this, 'callback' ), 1 );
      }

      public function render($step) {
        wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );
        $client_id = $this->a0_options->get( 'client_id' );
        $domain = $this->a0_options->get( 'domain' );
        include WPA0_PLUGIN_DIR . 'templates/initial-setup/admin-creation.php';
      }

      public function callback() {

        if (!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpa0-setup') {
          return;
        }

        if (!isset($_REQUEST['auth0'])) {
          return;
        }

        // wp_logout();

        $login_manager = new WP_Auth0_LoginManager($this->a0_options, 'administrator', true);
        $login_manager->redirect_login();
      }

    }
